<?php

declare(strict_types=1);

use App\Domains\ContentStorage\Exceptions\ReadException;
use App\Domains\ContentStorage\Exceptions\WriteException;
use App\Domains\ContentStorage\Models\ContentData;
use App\Domains\ContentStorage\Repositories\GitHubRepository;
use Github\Client as GitHubClient;
use Github\Exception\RuntimeException as GitHubRuntimeException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery as m;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->mockClient = m::mock(GitHubClient::class);

    $this->repository = new GitHubRepository([
        'token' => 'test-token',
        'owner' => 'test-owner',
        'repo' => 'test-repo',
        'branch' => 'main',
        'base_path' => 'content',
    ]);

    // Use reflection to inject mock client
    $reflection = new ReflectionClass($this->repository);
    $property = $reflection->getProperty('client');
    $property->setAccessible(true);
    $property->setValue($this->repository, $this->mockClient);
});

afterEach(function () {
    m::close();
});

// Basic Operations Tests

test('reads content from github', function () {
    $fileContent = "---\ntitle: Test Page\nslug: test-page\n---\n\n# Test Page\n\nContent here.";
    $base64Content = base64_encode($fileContent);

    $mockContents = m::mock();
    $mockContents
        ->shouldReceive('show')
        ->with('test-owner', 'test-repo', 'content/pages/test.md', 'main')
        ->andReturn([
            'content' => $base64Content,
            'size' => strlen($fileContent),
            'sha' => 'abc123',
        ]);

    $mockCommits = m::mock();
    $mockCommits
        ->shouldReceive('all')
        ->with('test-owner', 'test-repo', ['sha' => 'main', 'path' => 'content/pages/test.md', 'per_page' => 1])
        ->andReturn([
            [
                'commit' => [
                    'committer' => [
                        'date' => '2025-12-02T10:00:00Z',
                    ],
                ],
            ],
        ]);

    $mockRepo = m::mock(\Github\Api\Repo::class);
    $mockRepo->shouldReceive('contents')->andReturn($mockContents);
    $mockRepo->shouldReceive('commits')->andReturn($mockCommits);

    $this->mockClient
        ->shouldReceive('api')
        ->with('repo')
        ->andReturn($mockRepo);

    $result = $this->repository->read('pages/test.md');

    expect($result)->toBeInstanceOf(ContentData::class)
        ->and($result->content)->toContain('Test Page')
        ->and($result->frontmatter)->toHaveKey('title')
        ->and($result->frontmatter['title'])->toBe('Test Page');
});

test('writes new content to github', function () {
    $contentData = new ContentData(
        content: '# New Page',
        frontmatter: ['title' => 'New Page', 'slug' => 'new-page']
    );

    $mockContents = m::mock();

    // First call to check if file exists (should throw for new file)
    $mockContents
        ->shouldReceive('show')
        ->with('test-owner', 'test-repo', 'content/pages/new-page.md', 'main')
        ->andThrow(new GitHubRuntimeException('Not Found'));

    // Second call to create file
    $capturedArgs = null;
    $mockContents
        ->shouldReceive('createFile')
        ->once()
        ->andReturnUsing(function ($owner, $repo, $path, $params) use (&$capturedArgs) {
            $capturedArgs = compact('owner', 'repo', 'path', 'params');
            return ['content' => ['sha' => 'def456']];
        });

    $mockRepo = m::mock(\Github\Api\Repo::class);
    $mockRepo->shouldReceive('contents')->andReturn($mockContents);

    $this->mockClient
        ->shouldReceive('api')
        ->with('repo')
        ->andReturn($mockRepo);

    $result = $this->repository->write('pages/new-page.md', $contentData);

    expect($result)->toBeTrue()
        ->and($capturedArgs['owner'])->toBe('test-owner')
        ->and($capturedArgs['repo'])->toBe('test-repo')
        ->and($capturedArgs['path'])->toBe('content/pages/new-page.md')
        ->and($capturedArgs['params'])->toHaveKey('message')
        ->and($capturedArgs['params']['message'])->toContain('Create')
        ->and($capturedArgs['params'])->toHaveKey('content')
        ->and($capturedArgs['params'])->not->toHaveKey('sha'); // No SHA for new file
});

test('updates existing content on github', function () {
    $contentData = new ContentData(
        content: '# Updated Page',
        frontmatter: ['title' => 'Updated Page', 'slug' => 'test-page']
    );

    $mockContents = m::mock();

    // First call to check if file exists (should return existing file)
    $mockContents
        ->shouldReceive('show')
        ->with('test-owner', 'test-repo', 'content/pages/test.md', 'main')
        ->andReturn([
            'sha' => 'existing-sha',
            'content' => base64_encode('old content'),
        ]);

    // Second call to update file
    $capturedArgs = null;
    $mockContents
        ->shouldReceive('createFile')
        ->once()
        ->andReturnUsing(function ($owner, $repo, $path, $params) use (&$capturedArgs) {
            $capturedArgs = compact('owner', 'repo', 'path', 'params');
            return ['content' => ['sha' => 'new-sha']];
        });

    $mockRepo = m::mock(\Github\Api\Repo::class);
    $mockRepo->shouldReceive('contents')->andReturn($mockContents);

    $this->mockClient
        ->shouldReceive('api')
        ->with('repo')
        ->andReturn($mockRepo);

    $result = $this->repository->write('pages/test.md', $contentData);

    expect($result)->toBeTrue()
        ->and($capturedArgs['params'])->toHaveKey('message')
        ->and($capturedArgs['params']['message'])->toContain('Update')
        ->and($capturedArgs['params'])->toHaveKey('sha')
        ->and($capturedArgs['params']['sha'])->toBe('existing-sha'); // SHA included for update
});

test('checks if content exists on github', function () {
    $mockContents = m::mock();
    $mockContents
        ->shouldReceive('show')
        ->with('test-owner', 'test-repo', 'content/pages/test.md', 'main')
        ->andReturn(['sha' => 'abc123']);

    $mockRepo = m::mock(\Github\Api\Repo::class);
    $mockRepo->shouldReceive('contents')->andReturn($mockContents);

    $this->mockClient
        ->shouldReceive('api')
        ->with('repo')
        ->andReturn($mockRepo);

    $result = $this->repository->exists('pages/test.md');

    expect($result)->toBeTrue();
});

test('returns false when content does not exist on github', function () {
    $mockContents = m::mock();
    $mockContents
        ->shouldReceive('show')
        ->with('test-owner', 'test-repo', 'content/pages/missing.md', 'main')
        ->andThrow(new GitHubRuntimeException('Not Found'));

    $mockRepo = m::mock(\Github\Api\Repo::class);
    $mockRepo->shouldReceive('contents')->andReturn($mockContents);

    $this->mockClient
        ->shouldReceive('api')
        ->with('repo')
        ->andReturn($mockRepo);

    $result = $this->repository->exists('pages/missing.md');

    expect($result)->toBeFalse();
});

test('deletes content from github', function () {
    $mockContents = m::mock();

    // First call to get SHA
    $mockContents
        ->shouldReceive('show')
        ->with('test-owner', 'test-repo', 'content/pages/test.md', 'main')
        ->andReturn(['sha' => 'delete-sha']);

    // Second call to delete
    $capturedArgs = null;
    $mockContents
        ->shouldReceive('deleteFile')
        ->once()
        ->andReturnUsing(function ($owner, $repo, $path, $params) use (&$capturedArgs) {
            $capturedArgs = compact('owner', 'repo', 'path', 'params');
            return ['commit' => ['sha' => 'commit-sha']];
        });

    $mockRepo = m::mock(\Github\Api\Repo::class);
    $mockRepo->shouldReceive('contents')->andReturn($mockContents);

    $this->mockClient
        ->shouldReceive('api')
        ->with('repo')
        ->andReturn($mockRepo);

    $result = $this->repository->delete('pages/test.md');

    expect($result)->toBeTrue()
        ->and($capturedArgs['params'])->toHaveKey('message')
        ->and($capturedArgs['params']['message'])->toContain('Delete')
        ->and($capturedArgs['params'])->toHaveKey('sha')
        ->and($capturedArgs['params']['sha'])->toBe('delete-sha');
});

test('lists markdown files from github repository', function () {
    $mockContents = m::mock();
    $mockContents
        ->shouldReceive('show')
        ->with('test-owner', 'test-repo', 'content/pages', 'main')
        ->andReturn([
            ['type' => 'file', 'path' => 'content/pages/about.md'],
            ['type' => 'file', 'path' => 'content/pages/contact.md'],
            ['type' => 'file', 'path' => 'content/pages/index.html'], // Should be excluded
        ]);

    $mockRepo = m::mock(\Github\Api\Repo::class);
    $mockRepo->shouldReceive('contents')->andReturn($mockContents);

    $this->mockClient
        ->shouldReceive('api')
        ->with('repo')
        ->andReturn($mockRepo);

    $result = $this->repository->list('pages');

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(2)
        ->and($result)->toContain('pages/about.md')
        ->and($result)->toContain('pages/contact.md')
        ->and($result)->not->toContain('pages/index.html');
});

test('lists files recursively from github directories', function () {
    $mockContents = m::mock();

    // First call - root directory with subdirectory
    $mockContents
        ->shouldReceive('show')
        ->with('test-owner', 'test-repo', 'content/posts', 'main')
        ->andReturn([
            ['type' => 'file', 'path' => 'content/posts/post1.md'],
            ['type' => 'dir', 'path' => 'content/posts/2025'],
        ]);

    // Second call - subdirectory
    $mockContents
        ->shouldReceive('show')
        ->with('test-owner', 'test-repo', 'content/posts/2025', 'main')
        ->andReturn([
            ['type' => 'file', 'path' => 'content/posts/2025/post2.md'],
            ['type' => 'file', 'path' => 'content/posts/2025/post3.md'],
        ]);

    $mockRepo = m::mock(\Github\Api\Repo::class);
    $mockRepo->shouldReceive('contents')->andReturn($mockContents);

    $this->mockClient
        ->shouldReceive('api')
        ->with('repo')
        ->andReturn($mockRepo);

    $result = $this->repository->list('posts');

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(3)
        ->and($result)->toContain('posts/post1.md')
        ->and($result)->toContain('posts/2025/post2.md')
        ->and($result)->toContain('posts/2025/post3.md');
});

// Error Handling Tests

test('throws read exception when file not found', function () {
    $mockContents = m::mock();
    $mockContents
        ->shouldReceive('show')
        ->with('test-owner', 'test-repo', 'content/pages/missing.md', 'main')
        ->andThrow(new GitHubRuntimeException('Not Found'));

    $mockRepo = m::mock(\Github\Api\Repo::class);
    $mockRepo->shouldReceive('contents')->andReturn($mockContents);

    $this->mockClient
        ->shouldReceive('api')
        ->with('repo')
        ->andReturn($mockRepo);

    $this->repository->read('pages/missing.md');
})->throws(ReadException::class, 'not found');

test('throws read exception on github api error', function () {
    $mockContents = m::mock();
    $mockContents
        ->shouldReceive('show')
        ->with('test-owner', 'test-repo', 'content/pages/error.md', 'main')
        ->andThrow(new GitHubRuntimeException('API rate limit exceeded'));

    $mockRepo = m::mock(\Github\Api\Repo::class);
    $mockRepo->shouldReceive('contents')->andReturn($mockContents);

    $this->mockClient
        ->shouldReceive('api')
        ->with('repo')
        ->andReturn($mockRepo);

    $this->repository->read('pages/error.md');
})->throws(ReadException::class, 'Failed to read');

test('throws write exception on github write error', function () {
    $contentData = new ContentData(
        content: '# Test',
        frontmatter: ['title' => 'Test']
    );

    $mockContents = m::mock();
    $mockContents
        ->shouldReceive('show')
        ->andThrow(new GitHubRuntimeException('Not Found'));

    $mockContents
        ->shouldReceive('createFile')
        ->andThrow(new GitHubRuntimeException('Insufficient permissions'));

    $mockRepo = m::mock(\Github\Api\Repo::class);
    $mockRepo->shouldReceive('contents')->andReturn($mockContents);

    $this->mockClient
        ->shouldReceive('api')
        ->with('repo')
        ->andReturn($mockRepo);

    $this->repository->write('pages/test.md', $contentData);
})->throws(WriteException::class, 'Failed to write');

test('throws write exception when sha not found for delete', function () {
    $mockContents = m::mock();
    $mockContents
        ->shouldReceive('show')
        ->with('test-owner', 'test-repo', 'content/pages/test.md', 'main')
        ->andReturn(['content' => 'test']); // Missing 'sha' key

    $mockRepo = m::mock(\Github\Api\Repo::class);
    $mockRepo->shouldReceive('contents')->andReturn($mockContents);

    $this->mockClient
        ->shouldReceive('api')
        ->with('repo')
        ->andReturn($mockRepo);

    $this->repository->delete('pages/test.md');
})->throws(WriteException::class, 'Could not get file SHA');

// GitHub-Specific Feature Tests

test('uses correct branch for operations', function () {
    $repository = new GitHubRepository([
        'token' => 'test-token',
        'owner' => 'test-owner',
        'repo' => 'test-repo',
        'branch' => 'develop',
        'base_path' => '',
    ]);

    $mockClient = m::mock(GitHubClient::class);
    $reflection = new ReflectionClass($repository);
    $property = $reflection->getProperty('client');
    $property->setAccessible(true);
    $property->setValue($repository, $mockClient);

    $mockContents = m::mock();
    $mockContents
        ->shouldReceive('show')
        ->with('test-owner', 'test-repo', 'pages/test.md', 'develop')
        ->andReturn(['sha' => 'abc123']);

    $mockRepo = m::mock(\Github\Api\Repo::class);
    $mockRepo->shouldReceive('contents')->andReturn($mockContents);

    $mockClient
        ->shouldReceive('api')
        ->with('repo')
        ->andReturn($mockRepo);

    $result = $repository->exists('pages/test.md');

    expect($result)->toBeTrue();
});

test('removes base path from listed file paths', function () {
    $mockContents = m::mock();
    $mockContents
        ->shouldReceive('show')
        ->with('test-owner', 'test-repo', 'content/pages', 'main')
        ->andReturn([
            ['type' => 'file', 'path' => 'content/pages/about.md'],
            ['type' => 'file', 'path' => 'content/pages/contact.md'],
        ]);

    $mockRepo = m::mock(\Github\Api\Repo::class);
    $mockRepo->shouldReceive('contents')->andReturn($mockContents);

    $this->mockClient
        ->shouldReceive('api')
        ->with('repo')
        ->andReturn($mockRepo);

    $result = $this->repository->list('pages');

    // Should remove 'content/' prefix from paths
    expect($result)->toBeArray()
        ->and($result)->toContain('pages/about.md')
        ->and($result)->not->toContain('content/pages/about.md');
});

test('generates appropriate commit messages', function () {
    $contentData = new ContentData(
        content: '# Test',
        frontmatter: ['title' => 'Test']
    );

    $mockContents = m::mock();
    $mockContents
        ->shouldReceive('show')
        ->andThrow(new GitHubRuntimeException('Not Found'));

    $capturedMessage = null;
    $mockContents
        ->shouldReceive('createFile')
        ->andReturnUsing(function ($owner, $repo, $path, $params) use (&$capturedMessage) {
            $capturedMessage = $params['message'];
            return ['content' => ['sha' => 'new-sha']];
        });

    $mockRepo = m::mock(\Github\Api\Repo::class);
    $mockRepo->shouldReceive('contents')->andReturn($mockContents);

    $this->mockClient
        ->shouldReceive('api')
        ->with('repo')
        ->andReturn($mockRepo);

    $this->repository->write('pages/about-us.md', $contentData);

    expect($capturedMessage)->toBe('Create about-us.md');
});

test('handles base64 encoding correctly', function () {
    $contentData = new ContentData(
        content: '# Test with special chars: <>&"',
        frontmatter: ['title' => 'Test']
    );

    $mockContents = m::mock();
    $mockContents
        ->shouldReceive('show')
        ->andThrow(new GitHubRuntimeException('Not Found'));

    $capturedContent = null;
    $mockContents
        ->shouldReceive('createFile')
        ->andReturnUsing(function ($owner, $repo, $path, $params) use (&$capturedContent) {
            $capturedContent = $params['content'];
            return ['content' => ['sha' => 'new-sha']];
        });

    $mockRepo = m::mock(\Github\Api\Repo::class);
    $mockRepo->shouldReceive('contents')->andReturn($mockContents);

    $this->mockClient
        ->shouldReceive('api')
        ->with('repo')
        ->andReturn($mockRepo);

    $this->repository->write('pages/test.md', $contentData);

    // Verify content is base64 encoded
    expect($capturedContent)->not->toContain('<>&"')
        ->and(base64_decode($capturedContent))->toContain('# Test with special chars: <>&"');
});

// Connection Testing Tests

test('tests connection successfully', function () {
    $mockRepo = m::mock(\Github\Api\Repo::class);
    $mockRepo
        ->shouldReceive('show')
        ->with('test-owner', 'test-repo')
        ->andReturn(['id' => 123, 'name' => 'test-repo']);

    $this->mockClient
        ->shouldReceive('api')
        ->with('repo')
        ->andReturn($mockRepo);

    $result = $this->repository->testConnection();

    expect($result)->toBeTrue();
});

test('tests connection fails with invalid credentials', function () {
    $mockRepo = m::mock(\Github\Api\Repo::class);
    $mockRepo
        ->shouldReceive('show')
        ->with('test-owner', 'test-repo')
        ->andThrow(new GitHubRuntimeException('Bad credentials'));

    $this->mockClient
        ->shouldReceive('api')
        ->with('repo')
        ->andReturn($mockRepo);

    $result = $this->repository->testConnection();

    expect($result)->toBeFalse();
});

test('returns correct driver name', function () {
    expect($this->repository->getDriverName())->toBe('github');
});

// Edge Cases

test('handles empty directory listing', function () {
    $mockContents = m::mock();
    $mockContents
        ->shouldReceive('show')
        ->with('test-owner', 'test-repo', 'content/empty', 'main')
        ->andReturn([]);

    $mockRepo = m::mock(\Github\Api\Repo::class);
    $mockRepo->shouldReceive('contents')->andReturn($mockContents);

    $this->mockClient
        ->shouldReceive('api')
        ->with('repo')
        ->andReturn($mockRepo);

    $result = $this->repository->list('empty');

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(0);
});

test('handles single file response for list', function () {
    $mockContents = m::mock();

    // GitHub returns single object (not array) when path points to file
    $mockContents
        ->shouldReceive('show')
        ->with('test-owner', 'test-repo', 'content/pages/single.md', 'main')
        ->andReturn(['type' => 'file', 'path' => 'content/pages/single.md']);

    $mockRepo = m::mock(\Github\Api\Repo::class);
    $mockRepo->shouldReceive('contents')->andReturn($mockContents);

    $this->mockClient
        ->shouldReceive('api')
        ->with('repo')
        ->andReturn($mockRepo);

    $result = $this->repository->list('pages/single.md');

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(0); // Returns empty array for single file
});

test('skips inaccessible directories during recursive listing', function () {
    $mockContents = m::mock();

    // Root directory with accessible file and inaccessible directory
    $mockContents
        ->shouldReceive('show')
        ->with('test-owner', 'test-repo', 'content/posts', 'main')
        ->andReturn([
            ['type' => 'file', 'path' => 'content/posts/accessible.md'],
            ['type' => 'dir', 'path' => 'content/posts/restricted'],
        ]);

    // Subdirectory throws permission error
    $mockContents
        ->shouldReceive('show')
        ->with('test-owner', 'test-repo', 'content/posts/restricted', 'main')
        ->andThrow(new GitHubRuntimeException('Forbidden'));

    $mockRepo = m::mock(\Github\Api\Repo::class);
    $mockRepo->shouldReceive('contents')->andReturn($mockContents);

    $this->mockClient
        ->shouldReceive('api')
        ->with('repo')
        ->andReturn($mockRepo);

    $result = $this->repository->list('posts');

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(1)
        ->and($result)->toContain('posts/accessible.md');
});

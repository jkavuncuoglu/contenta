<?php

declare(strict_types=1);

use App\Domains\ContentManagement\ContentStorage\Exceptions\ReadException;
use App\Domains\ContentManagement\ContentStorage\Exceptions\WriteException;
use App\Domains\ContentManagement\ContentStorage\Models\ContentData;
use App\Domains\ContentManagement\ContentStorage\Repositories\S3Repository;
use Aws\CommandInterface;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Mockery as m;

beforeEach(function () {
    // Create mock S3 client
    $this->mockClient = m::mock(S3Client::class);

    // Create repository with mock client
    $this->repository = new S3Repository([
        'key' => 'test-key',
        'secret' => 'test-secret',
        'region' => 'us-east-1',
        'bucket' => 'test-bucket',
        'prefix' => 'content',
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

test('reads content from s3', function () {
    $markdown = <<<'MD'
---
title: "Test Page"
slug: test-page
status: published
---

# Test Content
MD;

    $this->mockClient
        ->shouldReceive('getObject')
        ->once()
        ->with([
            'Bucket' => 'test-bucket',
            'Key' => 'content/pages/test-page.md',
        ])
        ->andReturn(new Result([
            'Body' => $markdown,
            'ContentLength' => strlen($markdown),
            'LastModified' => '2025-12-02T10:00:00Z',
        ]));

    $content = $this->repository->read('pages/test-page.md');

    expect($content)->toBeInstanceOf(ContentData::class)
        ->and($content->frontmatter)->toHaveKey('title')
        ->and($content->frontmatter['title'])->toBe('Test Page')
        ->and($content->content)->toContain('# Test Content');
});

test('throws exception when reading non-existent file', function () {
    $mockCommand = m::mock(CommandInterface::class);

    $this->mockClient
        ->shouldReceive('getObject')
        ->once()
        ->andThrow(new S3Exception(
            'Not found',
            $mockCommand,
            ['code' => 'NoSuchKey']
        ));

    expect(fn () => $this->repository->read('pages/missing.md'))
        ->toThrow(ReadException::class);
});

test('writes content to s3', function () {
    $contentData = new ContentData(
        content: '# Test Content',
        frontmatter: [
            'title' => 'Test Page',
            'slug' => 'test-page',
            'status' => 'published',
        ]
    );

    $this->mockClient
        ->shouldReceive('putObject')
        ->once()
        ->withArgs(function ($args) {
            return $args['Bucket'] === 'test-bucket'
                && $args['Key'] === 'content/pages/test-page.md'
                && str_contains($args['Body'], '# Test Content')
                && $args['ContentType'] === 'text/markdown';
        })
        ->andReturn(new Result([]));

    $result = $this->repository->write('pages/test-page.md', $contentData);

    expect($result)->toBeTrue();
});

test('throws exception when writing fails', function () {
    $mockCommand = m::mock(CommandInterface::class);
    $contentData = new ContentData(content: 'test');

    $this->mockClient
        ->shouldReceive('putObject')
        ->once()
        ->andThrow(new S3Exception('Access denied', $mockCommand));

    expect(fn () => $this->repository->write('pages/test.md', $contentData))
        ->toThrow(WriteException::class);
});

test('checks if file exists', function () {
    $this->mockClient
        ->shouldReceive('doesObjectExist')
        ->once()
        ->with('test-bucket', 'content/pages/test-page.md')
        ->andReturn(true);

    $exists = $this->repository->exists('pages/test-page.md');

    expect($exists)->toBeTrue();
});

test('returns false when file does not exist', function () {
    $this->mockClient
        ->shouldReceive('doesObjectExist')
        ->once()
        ->with('test-bucket', 'content/pages/missing.md')
        ->andReturn(false);

    $exists = $this->repository->exists('pages/missing.md');

    expect($exists)->toBeFalse();
});

test('deletes file from s3', function () {
    $this->mockClient
        ->shouldReceive('deleteObject')
        ->once()
        ->with([
            'Bucket' => 'test-bucket',
            'Key' => 'content/pages/test-page.md',
        ])
        ->andReturn(new Result([]));

    $result = $this->repository->delete('pages/test-page.md');

    expect($result)->toBeTrue();
});

test('throws exception when deletion fails', function () {
    $mockCommand = m::mock(CommandInterface::class);

    $this->mockClient
        ->shouldReceive('deleteObject')
        ->once()
        ->andThrow(new S3Exception('Access denied', $mockCommand));

    expect(fn () => $this->repository->delete('pages/test.md'))
        ->toThrow(WriteException::class);
});

test('lists files in directory', function () {
    $this->mockClient
        ->shouldReceive('listObjectsV2')
        ->once()
        ->with([
            'Bucket' => 'test-bucket',
            'Prefix' => 'content/pages/',
        ])
        ->andReturn(new Result([
            'Contents' => [
                ['Key' => 'content/pages/page1.md'],
                ['Key' => 'content/pages/page2.md'],
                ['Key' => 'content/pages/subfolder/'], // Should be skipped
                ['Key' => 'content/pages/page3.md'],
                ['Key' => 'content/pages/readme.txt'], // Should be skipped (not .md)
            ],
        ]));

    $files = $this->repository->list('pages');

    expect($files)->toBe([
        'pages/page1.md',
        'pages/page2.md',
        'pages/page3.md',
    ]);
});

test('lists all files when no directory specified', function () {
    $this->mockClient
        ->shouldReceive('listObjectsV2')
        ->once()
        ->with([
            'Bucket' => 'test-bucket',
            'Prefix' => 'content/',
        ])
        ->andReturn(new Result([
            'Contents' => [
                ['Key' => 'content/pages/page1.md'],
                ['Key' => 'content/posts/post1.md'],
            ],
        ]));

    $files = $this->repository->list();

    expect($files)->toHaveCount(2)
        ->and($files)->toContain('pages/page1.md')
        ->and($files)->toContain('posts/post1.md');
});

test('returns empty array when no files found', function () {
    $this->mockClient
        ->shouldReceive('listObjectsV2')
        ->once()
        ->andReturn(new Result([]));

    $files = $this->repository->list('empty');

    expect($files)->toBe([]);
});

test('tests connection successfully', function () {
    $this->mockClient
        ->shouldReceive('listObjectsV2')
        ->once()
        ->with([
            'Bucket' => 'test-bucket',
            'MaxKeys' => 1,
        ])
        ->andReturn(new Result([]));

    $result = $this->repository->testConnection();

    expect($result)->toBeTrue();
});

test('returns false when connection test fails', function () {
    $mockCommand = m::mock(CommandInterface::class);

    $this->mockClient
        ->shouldReceive('listObjectsV2')
        ->once()
        ->andThrow(new S3Exception('Invalid credentials', $mockCommand));

    $result = $this->repository->testConnection();

    expect($result)->toBeFalse();
});

test('returns correct driver name', function () {
    expect($this->repository->getDriverName())->toBe('s3');
});

test('builds key with prefix', function () {
    $repository = new S3Repository([
        'key' => 'test-key',
        'secret' => 'test-secret',
        'region' => 'us-east-1',
        'bucket' => 'test-bucket',
        'prefix' => 'my-prefix',
    ]);

    $reflection = new ReflectionClass($repository);
    $property = $reflection->getProperty('client');
    $property->setAccessible(true);
    $property->setValue($repository, $this->mockClient);

    $this->mockClient
        ->shouldReceive('getObject')
        ->once()
        ->withArgs(function ($args) {
            return $args['Key'] === 'my-prefix/pages/test.md';
        })
        ->andReturn(new Result([
            'Body' => '# Test',
            'ContentLength' => 6,
            'LastModified' => '2025-12-02T10:00:00Z',
        ]));

    $repository->read('pages/test.md');
});

test('builds key without prefix', function () {
    $repository = new S3Repository([
        'key' => 'test-key',
        'secret' => 'test-secret',
        'region' => 'us-east-1',
        'bucket' => 'test-bucket',
        'prefix' => '',
    ]);

    $reflection = new ReflectionClass($repository);
    $property = $reflection->getProperty('client');
    $property->setAccessible(true);
    $property->setValue($repository, $this->mockClient);

    $this->mockClient
        ->shouldReceive('getObject')
        ->once()
        ->withArgs(function ($args) {
            return $args['Key'] === 'pages/test.md';
        })
        ->andReturn(new Result([
            'Body' => '# Test',
            'ContentLength' => 6,
            'LastModified' => '2025-12-02T10:00:00Z',
        ]));

    $repository->read('pages/test.md');
});

test('stores content hash in metadata', function () {
    $contentData = new ContentData(
        content: '# Test Content',
        frontmatter: ['title' => 'Test']
    );

    $capturedArgs = null;
    $this->mockClient
        ->shouldReceive('putObject')
        ->once()
        ->andReturnUsing(function ($args) use (&$capturedArgs) {
            $capturedArgs = $args;

            return new Result([]);
        });

    $result = $this->repository->write('pages/test.md', $contentData);

    expect($result)->toBeTrue()
        ->and($capturedArgs)->toHaveKey('Metadata')
        ->and($capturedArgs['Metadata'])->toHaveKey('content-hash')
        ->and($capturedArgs['Metadata']['content-hash'])->toBe($contentData->hash);
});

test('handles s3 pagination', function () {
    // This is a simplified test - in reality S3 pagination would be more complex
    $this->mockClient
        ->shouldReceive('listObjectsV2')
        ->once()
        ->andReturn(new Result([
            'Contents' => array_map(
                fn ($i) => ['Key' => "content/pages/page{$i}.md"],
                range(1, 50)
            ),
        ]));

    $files = $this->repository->list('pages');

    expect($files)->toHaveCount(50);
});

test('handles read exception with generic error', function () {
    $mockCommand = m::mock(CommandInterface::class);

    $this->mockClient
        ->shouldReceive('getObject')
        ->once()
        ->andThrow(new S3Exception(
            'Unknown error',
            $mockCommand,
            ['code' => 'UnknownError']
        ));

    expect(fn () => $this->repository->read('pages/test.md'))
        ->toThrow(ReadException::class);
});

test('handles non-s3 exceptions gracefully', function () {
    $this->mockClient
        ->shouldReceive('getObject')
        ->once()
        ->andThrow(new \RuntimeException('Network error'));

    expect(fn () => $this->repository->read('pages/test.md'))
        ->toThrow(ReadException::class);
});

test('preserves metadata from s3 response', function () {
    $lastModified = '2025-12-02T10:30:00Z';
    $contentLength = 1024;

    $this->mockClient
        ->shouldReceive('getObject')
        ->once()
        ->andReturn(new Result([
            'Body' => "---\ntitle: Test\n---\n# Content",
            'ContentLength' => $contentLength,
            'LastModified' => $lastModified,
        ]));

    $content = $this->repository->read('pages/test.md');

    expect($content->size)->toBe($contentLength)
        ->and($content->modifiedAt)->toBeInstanceOf(DateTimeImmutable::class);
});

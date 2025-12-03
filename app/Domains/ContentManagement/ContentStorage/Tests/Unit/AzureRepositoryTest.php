<?php

declare(strict_types=1);

use App\Domains\ContentManagement\ContentStorage\Exceptions\ReadException;
use App\Domains\ContentManagement\ContentStorage\Exceptions\WriteException;
use App\Domains\ContentManagement\ContentStorage\Models\ContentData;
use App\Domains\ContentManagement\ContentStorage\Repositories\AzureRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Microsoft\Azure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use Mockery as m;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->mockClient = m::mock(BlobRestProxy::class);

    $this->repository = new AzureRepository([
        'account_name' => 'testaccount',
        'account_key' => base64_encode('testkey123'),
        'container' => 'content',
        'prefix' => 'cms',
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

test('reads content from azure blob storage', function () {
    $fileContent = "---\ntitle: Test Page\nslug: test-page\n---\n\n# Test Page\n\nContent here.";

    $mockStream = fopen('php://memory', 'r+');
    fwrite($mockStream, $fileContent);
    rewind($mockStream);

    $mockBlob = m::mock();
    $mockBlob->shouldReceive('getContentStream')->andReturn($mockStream);

    $mockProperties = m::mock();
    $mockBlobProperties = m::mock();
    $mockBlobProperties->shouldReceive('getContentLength')->andReturn(strlen($fileContent));
    $mockBlobProperties->shouldReceive('getLastModified')->andReturn(new \DateTime('2025-12-02 10:00:00'));

    $mockProperties->shouldReceive('getProperties')->andReturn($mockBlobProperties);
    $mockProperties->shouldReceive('getMetadata')->andReturn(['content-hash' => 'abc123']);

    $this->mockClient
        ->shouldReceive('getBlob')
        ->with('content', 'cms/pages/test.md')
        ->andReturn($mockBlob);

    $this->mockClient
        ->shouldReceive('getBlobProperties')
        ->with('content', 'cms/pages/test.md')
        ->andReturn($mockProperties);

    $result = $this->repository->read('pages/test.md');

    expect($result)->toBeInstanceOf(ContentData::class)
        ->and($result->content)->toContain('Test Page')
        ->and($result->frontmatter)->toHaveKey('title')
        ->and($result->frontmatter['title'])->toBe('Test Page');

    fclose($mockStream);
});

test('writes content to azure blob storage', function () {
    $contentData = new ContentData(
        content: '# New Page',
        frontmatter: ['title' => 'New Page', 'slug' => 'new-page']
    );

    $capturedArgs = null;
    $this->mockClient
        ->shouldReceive('createBlockBlob')
        ->once()
        ->andReturnUsing(function ($container, $blobName, $content, $options) use (&$capturedArgs) {
            $capturedArgs = compact('container', 'blobName', 'content', 'options');
            return m::mock();
        });

    $result = $this->repository->write('pages/new-page.md', $contentData);

    expect($result)->toBeTrue()
        ->and($capturedArgs['container'])->toBe('content')
        ->and($capturedArgs['blobName'])->toBe('cms/pages/new-page.md')
        ->and($capturedArgs['content'])->toContain('# New Page')
        ->and($capturedArgs['options'])->toBeInstanceOf(\MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions::class);
});

test('checks if content exists in azure', function () {
    $mockProperties = m::mock();

    $this->mockClient
        ->shouldReceive('getBlobProperties')
        ->with('content', 'cms/pages/test.md')
        ->andReturn($mockProperties);

    $result = $this->repository->exists('pages/test.md');

    expect($result)->toBeTrue();
});

test('returns false when content does not exist in azure', function () {
    $this->mockClient
        ->shouldReceive('getBlobProperties')
        ->with('content', 'cms/pages/missing.md')
        ->andThrow(new \RuntimeException('Not found'));

    $result = $this->repository->exists('pages/missing.md');

    expect($result)->toBeFalse();
});

test('deletes content from azure', function () {
    $this->mockClient
        ->shouldReceive('deleteBlob')
        ->with('content', 'cms/pages/test.md')
        ->once()
        ->andReturn(m::mock());

    $result = $this->repository->delete('pages/test.md');

    expect($result)->toBeTrue();
});

test('lists markdown files from azure container', function () {
    $mockBlob1 = m::mock();
    $mockBlob1->shouldReceive('getName')->andReturn('cms/pages/about.md');

    $mockBlob2 = m::mock();
    $mockBlob2->shouldReceive('getName')->andReturn('cms/pages/contact.md');

    $mockBlob3 = m::mock();
    $mockBlob3->shouldReceive('getName')->andReturn('cms/pages/index.html');

    $mockResult = m::mock();
    $mockResult->shouldReceive('getBlobs')->andReturn([$mockBlob1, $mockBlob2, $mockBlob3]);
    $mockResult->shouldReceive('getNextMarker')->andReturn(null);

    $this->mockClient
        ->shouldReceive('listBlobs')
        ->once()
        ->andReturn($mockResult);

    $result = $this->repository->list('pages');

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(2)
        ->and($result)->toContain('pages/about.md')
        ->and($result)->toContain('pages/contact.md')
        ->and($result)->not->toContain('pages/index.html');
});

test('lists files with pagination', function () {
    // First page
    $mockBlob1 = m::mock();
    $mockBlob1->shouldReceive('getName')->andReturn('cms/posts/post1.md');

    $mockResult1 = m::mock();
    $mockResult1->shouldReceive('getBlobs')->andReturn([$mockBlob1]);
    $mockResult1->shouldReceive('getNextMarker')->andReturn('marker123');

    // Second page
    $mockBlob2 = m::mock();
    $mockBlob2->shouldReceive('getName')->andReturn('cms/posts/post2.md');

    $mockResult2 = m::mock();
    $mockResult2->shouldReceive('getBlobs')->andReturn([$mockBlob2]);
    $mockResult2->shouldReceive('getNextMarker')->andReturn(null);

    $this->mockClient
        ->shouldReceive('listBlobs')
        ->twice()
        ->andReturn($mockResult1, $mockResult2);

    $result = $this->repository->list('posts');

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(2)
        ->and($result)->toContain('posts/post1.md')
        ->and($result)->toContain('posts/post2.md');
});

// Error Handling Tests

test('throws read exception when blob not found', function () {
    $this->mockClient
        ->shouldReceive('getBlob')
        ->with('content', 'cms/pages/missing.md')
        ->andThrow(new \RuntimeException('Blob not found', 404));

    $this->repository->read('pages/missing.md');
})->throws(ReadException::class);

test('throws read exception on azure service error', function () {
    $this->mockClient
        ->shouldReceive('getBlob')
        ->with('content', 'cms/pages/error.md')
        ->andThrow(new \RuntimeException('Service error'));

    $this->repository->read('pages/error.md');
})->throws(ReadException::class);

test('throws write exception on azure write error', function () {
    $contentData = new ContentData(
        content: '# Test',
        frontmatter: ['title' => 'Test']
    );

    $this->mockClient
        ->shouldReceive('createBlockBlob')
        ->andThrow(new \RuntimeException('Permission denied'));

    $this->repository->write('pages/test.md', $contentData);
})->throws(WriteException::class);

test('throws write exception on delete error', function () {
    $this->mockClient
        ->shouldReceive('deleteBlob')
        ->with('content', 'cms/pages/test.md')
        ->andThrow(new \RuntimeException('Permission denied'));

    $this->repository->delete('pages/test.md');
})->throws(WriteException::class);

// Azure-Specific Feature Tests

test('uses correct container for operations', function () {
    $repository = new AzureRepository([
        'account_name' => 'testaccount',
        'account_key' => base64_encode('testkey123'),
        'container' => 'my-container',
        'prefix' => '',
    ]);

    $mockClient = m::mock(BlobRestProxy::class);
    $reflection = new ReflectionClass($repository);
    $property = $reflection->getProperty('client');
    $property->setAccessible(true);
    $property->setValue($repository, $mockClient);

    $mockProperties = m::mock();

    $mockClient
        ->shouldReceive('getBlobProperties')
        ->with('my-container', 'pages/test.md')
        ->andReturn($mockProperties);

    $result = $repository->exists('pages/test.md');

    expect($result)->toBeTrue();
});

test('removes prefix from listed blob names', function () {
    $mockBlob1 = m::mock();
    $mockBlob1->shouldReceive('getName')->andReturn('cms/pages/about.md');

    $mockBlob2 = m::mock();
    $mockBlob2->shouldReceive('getName')->andReturn('cms/pages/contact.md');

    $mockResult = m::mock();
    $mockResult->shouldReceive('getBlobs')->andReturn([$mockBlob1, $mockBlob2]);
    $mockResult->shouldReceive('getNextMarker')->andReturn(null);

    $this->mockClient
        ->shouldReceive('listBlobs')
        ->andReturn($mockResult);

    $result = $this->repository->list('pages');

    // Should remove 'cms/' prefix from blob names
    expect($result)->toBeArray()
        ->and($result)->toContain('pages/about.md')
        ->and($result)->not->toContain('cms/pages/about.md');
});

test('stores content hash in blob metadata', function () {
    $contentData = new ContentData(
        content: '# Test Content',
        frontmatter: ['title' => 'Test']
    );

    $capturedOptions = null;
    $this->mockClient
        ->shouldReceive('createBlockBlob')
        ->andReturnUsing(function ($container, $blobName, $content, $options) use (&$capturedOptions) {
            $capturedOptions = $options;
            return m::mock();
        });

    $this->repository->write('pages/test.md', $contentData);

    expect($capturedOptions)->toBeInstanceOf(\MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions::class)
        ->and($capturedOptions->getMetadata())->toHaveKey('content-hash')
        ->and($capturedOptions->getMetadata()['content-hash'])->toBe($contentData->hash);
});

test('sets correct content type for markdown', function () {
    $contentData = new ContentData(
        content: '# Test',
        frontmatter: ['title' => 'Test']
    );

    $capturedOptions = null;
    $this->mockClient
        ->shouldReceive('createBlockBlob')
        ->andReturnUsing(function ($container, $blobName, $content, $options) use (&$capturedOptions) {
            $capturedOptions = $options;
            return m::mock();
        });

    $this->repository->write('pages/test.md', $contentData);

    expect($capturedOptions->getContentType())->toBe('text/markdown');
});

// Connection Testing Tests

test('tests connection successfully', function () {
    $mockProperties = m::mock();

    $this->mockClient
        ->shouldReceive('getContainerProperties')
        ->with('content')
        ->andReturn($mockProperties);

    $result = $this->repository->testConnection();

    expect($result)->toBeTrue();
});

test('tests connection fails with invalid credentials', function () {
    $this->mockClient
        ->shouldReceive('getContainerProperties')
        ->with('content')
        ->andThrow(new \RuntimeException('Authentication failed'));

    $result = $this->repository->testConnection();

    expect($result)->toBeFalse();
});

test('returns correct driver name', function () {
    expect($this->repository->getDriverName())->toBe('azure');
});

// Edge Cases

test('handles empty directory listing', function () {
    $mockResult = m::mock();
    $mockResult->shouldReceive('getBlobs')->andReturn([]);
    $mockResult->shouldReceive('getNextMarker')->andReturn(null);

    $this->mockClient
        ->shouldReceive('listBlobs')
        ->andReturn($mockResult);

    $result = $this->repository->list('empty');

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(0);
});

test('handles blobs without prefix', function () {
    $repository = new AzureRepository([
        'account_name' => 'testaccount',
        'account_key' => base64_encode('testkey123'),
        'container' => 'content',
        'prefix' => '',
    ]);

    $mockClient = m::mock(BlobRestProxy::class);
    $reflection = new ReflectionClass($repository);
    $property = $reflection->getProperty('client');
    $property->setAccessible(true);
    $property->setValue($repository, $mockClient);

    $mockBlob1 = m::mock();
    $mockBlob1->shouldReceive('getName')->andReturn('pages/about.md');

    $mockResult = m::mock();
    $mockResult->shouldReceive('getBlobs')->andReturn([$mockBlob1]);
    $mockResult->shouldReceive('getNextMarker')->andReturn(null);

    $mockClient
        ->shouldReceive('listBlobs')
        ->andReturn($mockResult);

    $result = $repository->list('pages');

    expect($result)->toContain('pages/about.md');
});

test('handles datetime conversion from blob properties', function () {
    $fileContent = "---\ntitle: Test\n---\nContent";

    $mockStream = fopen('php://memory', 'r+');
    fwrite($mockStream, $fileContent);
    rewind($mockStream);

    $mockBlob = m::mock();
    $mockBlob->shouldReceive('getContentStream')->andReturn($mockStream);

    $mockProperties = m::mock();
    $mockBlobProperties = m::mock();

    $testDate = new \DateTime('2025-12-02 15:30:45');
    $mockBlobProperties->shouldReceive('getContentLength')->andReturn(strlen($fileContent));
    $mockBlobProperties->shouldReceive('getLastModified')->andReturn($testDate);

    $mockProperties->shouldReceive('getProperties')->andReturn($mockBlobProperties);
    $mockProperties->shouldReceive('getMetadata')->andReturn([]);

    $this->mockClient
        ->shouldReceive('getBlob')
        ->andReturn($mockBlob);

    $this->mockClient
        ->shouldReceive('getBlobProperties')
        ->andReturn($mockProperties);

    $result = $this->repository->read('pages/test.md');

    expect($result->modifiedAt)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($result->modifiedAt->format('Y-m-d H:i:s'))->toBe('2025-12-02 15:30:45');

    fclose($mockStream);
});

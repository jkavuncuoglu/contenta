<?php

declare(strict_types=1);

use App\Domains\ContentManagement\ContentStorage\Exceptions\ReadException;
use App\Domains\ContentManagement\ContentStorage\Exceptions\WriteException;
use App\Domains\ContentManagement\ContentStorage\Models\ContentData;
use App\Domains\ContentManagement\ContentStorage\Repositories\GcsRepository;
use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\StorageObject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery as m;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->mockClient = m::mock(StorageClient::class);
    $this->mockBucket = m::mock(Bucket::class);

    $this->mockClient
        ->shouldReceive('bucket')
        ->with('test-bucket')
        ->andReturn($this->mockBucket);

    $this->repository = new GcsRepository([
        'project_id' => 'test-project',
        'bucket' => 'test-bucket',
        'prefix' => 'content',
        'key_file' => ['type' => 'service_account'], // Mock key
    ]);

    // Use reflection to inject mock client and bucket
    $reflection = new ReflectionClass($this->repository);

    $clientProp = $reflection->getProperty('client');
    $clientProp->setAccessible(true);
    $clientProp->setValue($this->repository, $this->mockClient);

    $bucketProp = $reflection->getProperty('bucket');
    $bucketProp->setAccessible(true);
    $bucketProp->setValue($this->repository, $this->mockBucket);
});

afterEach(function () {
    m::close();
});

// Basic Operations Tests

test('reads content from gcs', function () {
    $fileContent = "---\ntitle: Test Page\nslug: test-page\n---\n\n# Test Page\n\nContent here.";

    $mockObject = m::mock(StorageObject::class);
    $mockObject->shouldReceive('exists')->andReturn(true);
    $mockObject->shouldReceive('downloadAsString')->andReturn($fileContent);
    $mockObject->shouldReceive('info')->andReturn([
        'size' => strlen($fileContent),
        'updated' => '2025-12-02T10:00:00Z',
        'metadata' => ['content-hash' => 'abc123'],
    ]);

    $this->mockBucket
        ->shouldReceive('object')
        ->with('content/pages/test.md')
        ->andReturn($mockObject);

    $result = $this->repository->read('pages/test.md');

    expect($result)->toBeInstanceOf(ContentData::class)
        ->and($result->content)->toContain('Test Page')
        ->and($result->frontmatter)->toHaveKey('title')
        ->and($result->frontmatter['title'])->toBe('Test Page');
});

test('writes content to gcs', function () {
    $contentData = new ContentData(
        content: '# New Page',
        frontmatter: ['title' => 'New Page', 'slug' => 'new-page']
    );

    $capturedArgs = null;
    $this->mockBucket
        ->shouldReceive('upload')
        ->once()
        ->andReturnUsing(function ($content, $options) use (&$capturedArgs) {
            $capturedArgs = compact('content', 'options');
            return m::mock(StorageObject::class);
        });

    $result = $this->repository->write('pages/new-page.md', $contentData);

    expect($result)->toBeTrue()
        ->and($capturedArgs['content'])->toContain('# New Page')
        ->and($capturedArgs['options'])->toHaveKey('name')
        ->and($capturedArgs['options']['name'])->toBe('content/pages/new-page.md')
        ->and($capturedArgs['options'])->toHaveKey('metadata');
});

test('checks if content exists in gcs', function () {
    $mockObject = m::mock(StorageObject::class);
    $mockObject->shouldReceive('exists')->andReturn(true);

    $this->mockBucket
        ->shouldReceive('object')
        ->with('content/pages/test.md')
        ->andReturn($mockObject);

    $result = $this->repository->exists('pages/test.md');

    expect($result)->toBeTrue();
});

test('returns false when content does not exist in gcs', function () {
    $mockObject = m::mock(StorageObject::class);
    $mockObject->shouldReceive('exists')->andReturn(false);

    $this->mockBucket
        ->shouldReceive('object')
        ->with('content/pages/missing.md')
        ->andReturn($mockObject);

    $result = $this->repository->exists('pages/missing.md');

    expect($result)->toBeFalse();
});

test('deletes content from gcs', function () {
    $mockObject = m::mock(StorageObject::class);
    $mockObject->shouldReceive('delete')->once()->andReturn(null);

    $this->mockBucket
        ->shouldReceive('object')
        ->with('content/pages/test.md')
        ->andReturn($mockObject);

    $result = $this->repository->delete('pages/test.md');

    expect($result)->toBeTrue();
});

test('lists markdown files from gcs bucket', function () {
    $mockObject1 = m::mock(StorageObject::class);
    $mockObject1->shouldReceive('name')->andReturn('content/pages/about.md');

    $mockObject2 = m::mock(StorageObject::class);
    $mockObject2->shouldReceive('name')->andReturn('content/pages/contact.md');

    $mockObject3 = m::mock(StorageObject::class);
    $mockObject3->shouldReceive('name')->andReturn('content/pages/index.html');

    $mockIterator = new ArrayIterator([$mockObject1, $mockObject2, $mockObject3]);

    $this->mockBucket
        ->shouldReceive('objects')
        ->once()
        ->andReturn($mockIterator);

    $result = $this->repository->list('pages');

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(2)
        ->and($result)->toContain('pages/about.md')
        ->and($result)->toContain('pages/contact.md')
        ->and($result)->not->toContain('pages/index.html');
});

// Error Handling Tests

test('throws read exception when object not found', function () {
    $mockObject = m::mock(StorageObject::class);
    $mockObject->shouldReceive('exists')->andReturn(false);

    $this->mockBucket
        ->shouldReceive('object')
        ->with('content/pages/missing.md')
        ->andReturn($mockObject);

    $this->repository->read('pages/missing.md');
})->throws(ReadException::class, 'not found');

test('throws read exception on gcs service error', function () {
    $mockObject = m::mock(StorageObject::class);
    $mockObject->shouldReceive('exists')->andReturn(true);
    $mockObject->shouldReceive('downloadAsString')->andThrow(new \RuntimeException('Service error'));

    $this->mockBucket
        ->shouldReceive('object')
        ->with('content/pages/error.md')
        ->andReturn($mockObject);

    $this->repository->read('pages/error.md');
})->throws(ReadException::class);

test('throws write exception on gcs write error', function () {
    $contentData = new ContentData(
        content: '# Test',
        frontmatter: ['title' => 'Test']
    );

    $this->mockBucket
        ->shouldReceive('upload')
        ->andThrow(new \RuntimeException('Permission denied'));

    $this->repository->write('pages/test.md', $contentData);
})->throws(WriteException::class);

test('throws write exception on delete error', function () {
    $mockObject = m::mock(StorageObject::class);
    $mockObject->shouldReceive('delete')->andThrow(new \RuntimeException('Permission denied'));

    $this->mockBucket
        ->shouldReceive('object')
        ->with('content/pages/test.md')
        ->andReturn($mockObject);

    $this->repository->delete('pages/test.md');
})->throws(WriteException::class);

// GCS-Specific Feature Tests

test('uses correct bucket for operations', function () {
    $repository = new GcsRepository([
        'project_id' => 'test-project',
        'bucket' => 'my-bucket',
        'prefix' => '',
        'key_file' => ['type' => 'service_account'],
    ]);

    $mockClient = m::mock(StorageClient::class);
    $mockBucket = m::mock(Bucket::class);

    $mockClient
        ->shouldReceive('bucket')
        ->with('my-bucket')
        ->andReturn($mockBucket);

    $reflection = new ReflectionClass($repository);
    $clientProp = $reflection->getProperty('client');
    $clientProp->setAccessible(true);
    $clientProp->setValue($repository, $mockClient);

    $bucketProp = $reflection->getProperty('bucket');
    $bucketProp->setAccessible(true);
    $bucketProp->setValue($repository, $mockBucket);

    $mockObject = m::mock(StorageObject::class);
    $mockObject->shouldReceive('exists')->andReturn(true);

    $mockBucket
        ->shouldReceive('object')
        ->with('pages/test.md')
        ->andReturn($mockObject);

    $result = $repository->exists('pages/test.md');

    expect($result)->toBeTrue();
});

test('removes prefix from listed object names', function () {
    $mockObject1 = m::mock(StorageObject::class);
    $mockObject1->shouldReceive('name')->andReturn('content/pages/about.md');

    $mockObject2 = m::mock(StorageObject::class);
    $mockObject2->shouldReceive('name')->andReturn('content/pages/contact.md');

    $mockIterator = new ArrayIterator([$mockObject1, $mockObject2]);

    $this->mockBucket
        ->shouldReceive('objects')
        ->andReturn($mockIterator);

    $result = $this->repository->list('pages');

    // Should remove 'content/' prefix from object names
    expect($result)->toBeArray()
        ->and($result)->toContain('pages/about.md')
        ->and($result)->not->toContain('content/pages/about.md');
});

test('stores content hash in object metadata', function () {
    $contentData = new ContentData(
        content: '# Test Content',
        frontmatter: ['title' => 'Test']
    );

    $capturedOptions = null;
    $this->mockBucket
        ->shouldReceive('upload')
        ->andReturnUsing(function ($content, $options) use (&$capturedOptions) {
            $capturedOptions = $options;
            return m::mock(StorageObject::class);
        });

    $this->repository->write('pages/test.md', $contentData);

    expect($capturedOptions)->toHaveKey('metadata')
        ->and($capturedOptions['metadata'])->toHaveKey('metadata')
        ->and($capturedOptions['metadata']['metadata'])->toHaveKey('content-hash')
        ->and($capturedOptions['metadata']['metadata']['content-hash'])->toBe($contentData->hash);
});

test('sets correct content type for markdown', function () {
    $contentData = new ContentData(
        content: '# Test',
        frontmatter: ['title' => 'Test']
    );

    $capturedOptions = null;
    $this->mockBucket
        ->shouldReceive('upload')
        ->andReturnUsing(function ($content, $options) use (&$capturedOptions) {
            $capturedOptions = $options;
            return m::mock(StorageObject::class);
        });

    $this->repository->write('pages/test.md', $contentData);

    expect($capturedOptions['metadata'])->toHaveKey('contentType')
        ->and($capturedOptions['metadata']['contentType'])->toBe('text/markdown');
});

// Connection Testing Tests

test('tests connection successfully', function () {
    $this->mockBucket
        ->shouldReceive('exists')
        ->andReturn(true);

    $result = $this->repository->testConnection();

    expect($result)->toBeTrue();
});

test('tests connection fails with invalid credentials', function () {
    $this->mockBucket
        ->shouldReceive('exists')
        ->andThrow(new \RuntimeException('Authentication failed'));

    $result = $this->repository->testConnection();

    expect($result)->toBeFalse();
});

test('returns correct driver name', function () {
    expect($this->repository->getDriverName())->toBe('gcs');
});

// Edge Cases

test('handles empty directory listing', function () {
    $mockIterator = new ArrayIterator([]);

    $this->mockBucket
        ->shouldReceive('objects')
        ->andReturn($mockIterator);

    $result = $this->repository->list('empty');

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(0);
});

test('handles objects without prefix', function () {
    $repository = new GcsRepository([
        'project_id' => 'test-project',
        'bucket' => 'test-bucket',
        'prefix' => '',
        'key_file' => ['type' => 'service_account'],
    ]);

    $mockClient = m::mock(StorageClient::class);
    $mockBucket = m::mock(Bucket::class);

    $mockClient
        ->shouldReceive('bucket')
        ->with('test-bucket')
        ->andReturn($mockBucket);

    $reflection = new ReflectionClass($repository);
    $clientProp = $reflection->getProperty('client');
    $clientProp->setAccessible(true);
    $clientProp->setValue($repository, $mockClient);

    $bucketProp = $reflection->getProperty('bucket');
    $bucketProp->setAccessible(true);
    $bucketProp->setValue($repository, $mockBucket);

    $mockObject1 = m::mock(StorageObject::class);
    $mockObject1->shouldReceive('name')->andReturn('pages/about.md');

    $mockIterator = new ArrayIterator([$mockObject1]);

    $mockBucket
        ->shouldReceive('objects')
        ->andReturn($mockIterator);

    $result = $repository->list('pages');

    expect($result)->toContain('pages/about.md');
});

test('handles datetime conversion from object info', function () {
    $fileContent = "---\ntitle: Test\n---\nContent";

    $mockObject = m::mock(StorageObject::class);
    $mockObject->shouldReceive('exists')->andReturn(true);
    $mockObject->shouldReceive('downloadAsString')->andReturn($fileContent);
    $mockObject->shouldReceive('info')->andReturn([
        'size' => strlen($fileContent),
        'updated' => '2025-12-02T15:30:45Z',
        'metadata' => [],
    ]);

    $this->mockBucket
        ->shouldReceive('object')
        ->andReturn($mockObject);

    $result = $this->repository->read('pages/test.md');

    expect($result->modifiedAt)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($result->modifiedAt->format('Y-m-d'))->toBe('2025-12-02');
});

test('handles missing updated timestamp', function () {
    $fileContent = "---\ntitle: Test\n---\nContent";

    $mockObject = m::mock(StorageObject::class);
    $mockObject->shouldReceive('exists')->andReturn(true);
    $mockObject->shouldReceive('downloadAsString')->andReturn($fileContent);
    $mockObject->shouldReceive('info')->andReturn([
        'size' => strlen($fileContent),
        'metadata' => [],
    ]);

    $this->mockBucket
        ->shouldReceive('object')
        ->andReturn($mockObject);

    $result = $this->repository->read('pages/test.md');

    expect($result->modifiedAt)->toBeInstanceOf(DateTimeImmutable::class);
});

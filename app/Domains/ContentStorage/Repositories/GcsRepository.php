<?php

declare(strict_types=1);

namespace App\Domains\ContentStorage\Repositories;

use App\Domains\ContentStorage\Contracts\ContentRepositoryContract;
use App\Domains\ContentStorage\Exceptions\ReadException;
use App\Domains\ContentStorage\Exceptions\StorageException;
use App\Domains\ContentStorage\Exceptions\WriteException;
use App\Domains\ContentStorage\Models\ContentData;
use DateTimeImmutable;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\Core\Exception\ServiceException as GcsServiceException;

/**
 * Google Cloud Storage Repository for storing content in GCS
 *
 * Implements content storage using Google Cloud Storage with support for:
 * - Bucket-based organization
 * - Object metadata for content hash
 * - Prefix support for path organization
 * - Connection testing
 */
class GcsRepository implements ContentRepositoryContract
{
    private $client;

    private $bucket;

    private string $bucketName;

    private string $prefix;

    /**
     * Create a new GCS Repository instance
     *
     * @param  array{
     *     project_id: string,
     *     key_file_path?: string,
     *     key_file?: array,
     *     bucket: string,
     *     prefix?: string
     * }  $config
     */
    public function __construct(array $config)
    {
        $this->bucketName = $config['bucket'];
        $this->prefix = $config['prefix'] ?? '';

        $clientConfig = [
            'projectId' => $config['project_id'],
        ];

        // Support both key file path and direct key array
        if (isset($config['key_file_path'])) {
            $clientConfig['keyFilePath'] = $config['key_file_path'];
        } elseif (isset($config['key_file'])) {
            $clientConfig['keyFile'] = $config['key_file'];
        }

        $this->client = new StorageClient($clientConfig);
        $this->bucket = $this->client->bucket($this->bucketName);
    }

    /**
     * {@inheritDoc}
     */
    public function read(string $path): ContentData
    {
        try {
            $objectName = $this->buildObjectName($path);

            $object = $this->bucket->object($objectName);

            if (! $object->exists()) {
                throw ReadException::notFound($path);
            }

            $markdown = $object->downloadAsString();
            $info = $object->info();

            $metadata = $info['metadata'] ?? [];
            $updated = isset($info['updated'])
                ? new DateTimeImmutable($info['updated'])
                : new DateTimeImmutable();

            $contentData = ContentData::fromMarkdown($markdown);

            // Update metadata from GCS
            return new ContentData(
                content: $contentData->content,
                frontmatter: $contentData->frontmatter,
                hash: $contentData->hash,
                size: $info['size'] ?? strlen($markdown),
                modifiedAt: $updated
            );
        } catch (NotFoundException $e) {
            throw ReadException::notFound($path);
        } catch (GcsServiceException $e) {
            throw ReadException::failed($path, $e->getMessage());
        } catch (\Exception $e) {
            if ($e instanceof ReadException) {
                throw $e;
            }
            throw ReadException::failed($path, $e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function write(string $path, ContentData $data): bool
    {
        try {
            $objectName = $this->buildObjectName($path);
            $markdown = $data->toMarkdown();

            $this->bucket->upload($markdown, [
                'name' => $objectName,
                'metadata' => [
                    'contentType' => 'text/markdown',
                    'metadata' => [
                        'content-hash' => $data->hash,
                    ],
                ],
            ]);

            return true;
        } catch (GcsServiceException $e) {
            throw WriteException::failed($path, $e->getMessage());
        } catch (\Exception $e) {
            throw WriteException::failed($path, $e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function exists(string $path): bool
    {
        try {
            $objectName = $this->buildObjectName($path);

            $object = $this->bucket->object($objectName);

            return $object->exists();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $path): bool
    {
        try {
            $objectName = $this->buildObjectName($path);

            $object = $this->bucket->object($objectName);
            $object->delete();

            return true;
        } catch (GcsServiceException $e) {
            throw WriteException::failed($path, "Failed to delete: {$e->getMessage()}");
        } catch (\Exception $e) {
            throw WriteException::failed($path, "Failed to delete: {$e->getMessage()}");
        }
    }

    /**
     * {@inheritDoc}
     */
    public function list(string $directory = ''): array
    {
        try {
            $prefix = $this->buildObjectName($directory);
            if (! empty($prefix) && ! str_ends_with($prefix, '/')) {
                $prefix .= '/';
            }

            $options = ['prefix' => $prefix];

            $objects = $this->bucket->objects($options);

            $files = [];
            foreach ($objects as $object) {
                $objectName = $object->name();

                // Only include markdown files
                if (str_ends_with($objectName, '.md')) {
                    $relativePath = $this->removePrefix($objectName);
                    $files[] = $relativePath;
                }
            }

            return $files;
        } catch (GcsServiceException $e) {
            throw new StorageException("Failed to list directory '$directory': {$e->getMessage()}");
        } catch (\Exception $e) {
            throw new StorageException("Failed to list directory '$directory': {$e->getMessage()}");
        }
    }

    /**
     * {@inheritDoc}
     */
    public function testConnection(): bool
    {
        try {
            // Try to check if bucket exists
            return $this->bucket->exists();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getDriverName(): string
    {
        return 'gcs';
    }

    /**
     * Build the full object name including prefix
     */
    private function buildObjectName(string $path): string
    {
        if (empty($this->prefix)) {
            return $path;
        }

        $prefix = trim($this->prefix, '/');
        $path = ltrim($path, '/');

        return "{$prefix}/{$path}";
    }

    /**
     * Remove the prefix from an object name to get relative path
     */
    private function removePrefix(string $objectName): string
    {
        if (empty($this->prefix)) {
            return $objectName;
        }

        $prefix = trim($this->prefix, '/').'/';

        if (str_starts_with($objectName, $prefix)) {
            return substr($objectName, strlen($prefix));
        }

        return $objectName;
    }
}

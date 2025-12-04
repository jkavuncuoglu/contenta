<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\ContentStorage\Repositories;

use App\Domains\ContentManagement\ContentStorage\Contracts\ContentRepositoryContract;
use App\Domains\ContentManagement\ContentStorage\Exceptions\ReadException;
use App\Domains\ContentManagement\ContentStorage\Exceptions\StorageException;
use App\Domains\ContentManagement\ContentStorage\Exceptions\WriteException;
use App\Domains\ContentManagement\ContentStorage\Models\ContentData;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use DateTimeImmutable;

/**
 * S3 Repository for storing content in Amazon S3
 *
 * Implements content storage using AWS S3 with support for:
 * - Direct file uploads/downloads
 * - Directory listing
 * - Metadata tracking
 * - Connection testing
 */
class S3Repository implements ContentRepositoryContract
{
    private S3Client $client;

    private string $bucket;

    private string $region;

    private string $prefix;

    /**
     * Create a new S3 Repository instance
     *
     * @param  array{
     *     key: string,
     *     secret: string,
     *     region: string,
     *     bucket: string,
     *     prefix?: string,
     *     version?: string
     * }  $config
     */
    public function __construct(array $config)
    {
        $this->bucket = $config['bucket'];
        $this->region = $config['region'];
        $this->prefix = $config['prefix'] ?? '';

        $this->client = new S3Client([
            'version' => $config['version'] ?? 'latest',
            'region' => $this->region,
            'credentials' => [
                'key' => $config['key'],
                'secret' => $config['secret'],
            ],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function read(string $path): ContentData
    {
        try {
            $key = $this->buildKey($path);

            $result = $this->client->getObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);

            $markdown = (string) $result['Body'];
            $lastModified = $result['LastModified'] instanceof DateTimeImmutable
                ? $result['LastModified']
                : new DateTimeImmutable($result['LastModified']);

            $contentData = ContentData::fromMarkdown($markdown);

            // Update metadata from S3
            return new ContentData(
                content: $contentData->content,
                frontmatter: $contentData->frontmatter,
                hash: $contentData->hash,
                size: (int) $result['ContentLength'],
                modifiedAt: $lastModified
            );
        } catch (S3Exception $e) {
            if ($e->getAwsErrorCode() === 'NoSuchKey') {
                throw ReadException::notFound($path);
            }

            throw ReadException::failed($path, $e->getMessage());
        } catch (\Exception $e) {
            throw ReadException::failed($path, $e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function write(string $path, ContentData $data): bool
    {
        try {
            $key = $this->buildKey($path);
            $markdown = $data->toMarkdown();

            $this->client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
                'Body' => $markdown,
                'ContentType' => 'text/markdown',
                'Metadata' => [
                    'content-hash' => $data->hash,
                ],
            ]);

            return true;
        } catch (S3Exception $e) {
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
            $key = $this->buildKey($path);

            return $this->client->doesObjectExist($this->bucket, $key);
        } catch (S3Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $path): bool
    {
        try {
            $key = $this->buildKey($path);

            $this->client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);

            return true;
        } catch (S3Exception $e) {
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
            $prefix = $this->buildKey($directory);
            if (! empty($prefix) && ! str_ends_with($prefix, '/')) {
                $prefix .= '/';
            }

            $results = $this->client->listObjectsV2([
                'Bucket' => $this->bucket,
                'Prefix' => $prefix,
            ]);

            $files = [];
            if (isset($results['Contents'])) {
                foreach ($results['Contents'] as $object) {
                    $key = $object['Key'];
                    // Skip directories (keys ending with /)
                    if (str_ends_with($key, '/')) {
                        continue;
                    }

                    // Only include markdown files
                    if (! str_ends_with($key, '.md')) {
                        continue;
                    }

                    // Remove prefix to get relative path
                    $relativePath = $this->removePrefix($key);
                    $files[] = $relativePath;
                }
            }

            return $files;
        } catch (S3Exception $e) {
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
            // Try to list objects (with limit 1) to test credentials and bucket access
            $this->client->listObjectsV2([
                'Bucket' => $this->bucket,
                'MaxKeys' => 1,
            ]);

            return true;
        } catch (S3Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getDriverName(): string
    {
        return 's3';
    }

    /**
     * Build the full S3 key from a relative path
     */
    private function buildKey(string $path): string
    {
        if (empty($this->prefix)) {
            return $path;
        }

        $prefix = rtrim($this->prefix, '/');
        $path = ltrim($path, '/');

        return "{$prefix}/{$path}";
    }

    /**
     * Remove the prefix from an S3 key to get relative path
     */
    private function removePrefix(string $key): string
    {
        if (empty($this->prefix)) {
            return $key;
        }

        $prefix = rtrim($this->prefix, '/').'/';

        if (str_starts_with($key, $prefix)) {
            return substr($key, strlen($prefix));
        }

        return $key;
    }
}

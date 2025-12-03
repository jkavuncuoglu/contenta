<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\ContentStorage\Repositories;

use App\Domains\ContentManagement\ContentStorage\Contracts\ContentRepositoryContract;
use App\Domains\ContentManagement\ContentStorage\Exceptions\ReadException;
use App\Domains\ContentManagement\ContentStorage\Exceptions\StorageException;
use App\Domains\ContentManagement\ContentStorage\Exceptions\WriteException;
use App\Domains\ContentManagement\ContentStorage\Models\ContentData;
use DateTimeImmutable;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;

/**
 * Azure Blob Storage Repository for storing content in Azure
 *
 * Implements content storage using Azure Blob Storage with support for:
 * - Container-based organization
 * - Blob metadata for content hash
 * - Prefix support for path organization
 * - Connection testing
 */
class AzureRepository implements ContentRepositoryContract
{
    private $client;

    private string $container;

    private string $prefix;

    /**
     * Create a new Azure Repository instance
     *
     * @param  array{
     *     account_name: string,
     *     account_key: string,
     *     container: string,
     *     prefix?: string
     * }  $config
     */
    public function __construct(array $config)
    {
        $this->container = $config['container'];
        $this->prefix = $config['prefix'] ?? '';

        $connectionString = sprintf(
            'DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s;EndpointSuffix=core.windows.net',
            $config['account_name'],
            $config['account_key']
        );

        $this->client = BlobRestProxy::createBlobService($connectionString);
    }

    /**
     * {@inheritDoc}
     */
    public function read(string $path): ContentData
    {
        try {
            $blobName = $this->buildBlobName($path);

            $blob = $this->client->getBlob($this->container, $blobName);
            $markdown = stream_get_contents($blob->getContentStream());

            // Get blob properties for metadata
            $properties = $this->client->getBlobProperties($this->container, $blobName);
            $metadata = $properties->getMetadata();

            $lastModified = $properties->getProperties()->getLastModified();

            $contentData = ContentData::fromMarkdown($markdown);

            // Update metadata from Azure
            return new ContentData(
                content: $contentData->content,
                frontmatter: $contentData->frontmatter,
                hash: $contentData->hash,
                size: $properties->getProperties()->getContentLength(),
                modifiedAt: $lastModified instanceof \DateTime
                    ? DateTimeImmutable::createFromMutable($lastModified)
                    : new DateTimeImmutable()
            );
        } catch (ServiceException $e) {
            if ($e->getCode() === 404) {
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
            $blobName = $this->buildBlobName($path);
            $markdown = $data->toMarkdown();

            $options = new CreateBlockBlobOptions();
            $options->setContentType('text/markdown');
            $options->setMetadata([
                'content-hash' => $data->hash,
            ]);

            $this->client->createBlockBlob(
                $this->container,
                $blobName,
                $markdown,
                $options
            );

            return true;
        } catch (ServiceException $e) {
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
            $blobName = $this->buildBlobName($path);

            $this->client->getBlobProperties($this->container, $blobName);

            return true;
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
            $blobName = $this->buildBlobName($path);

            $this->client->deleteBlob($this->container, $blobName);

            return true;
        } catch (ServiceException $e) {
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
            $prefix = $this->buildBlobName($directory);
            if (! empty($prefix) && ! str_ends_with($prefix, '/')) {
                $prefix .= '/';
            }

            $options = new ListBlobsOptions();
            $options->setPrefix($prefix);

            $files = [];
            $marker = null;

            do {
                if ($marker !== null) {
                    $options->setMarker($marker);
                }

                $result = $this->client->listBlobs($this->container, $options);

                foreach ($result->getBlobs() as $blob) {
                    $blobName = $blob->getName();

                    // Only include markdown files
                    if (str_ends_with($blobName, '.md')) {
                        $relativePath = $this->removePrefix($blobName);
                        $files[] = $relativePath;
                    }
                }

                $marker = $result->getNextMarker();
            } while ($marker !== null);

            return $files;
        } catch (ServiceException $e) {
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
            // Try to get container properties
            $this->client->getContainerProperties($this->container);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getDriverName(): string
    {
        return 'azure';
    }

    /**
     * Build the full blob name including prefix
     */
    private function buildBlobName(string $path): string
    {
        if (empty($this->prefix)) {
            return $path;
        }

        $prefix = trim($this->prefix, '/');
        $path = ltrim($path, '/');

        return "{$prefix}/{$path}";
    }

    /**
     * Remove the prefix from a blob name to get relative path
     */
    private function removePrefix(string $blobName): string
    {
        if (empty($this->prefix)) {
            return $blobName;
        }

        $prefix = trim($this->prefix, '/').'/';

        if (str_starts_with($blobName, $prefix)) {
            return substr($blobName, strlen($prefix));
        }

        return $blobName;
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\ContentStorage\RevisionProviders;

use App\Domains\ContentManagement\ContentStorage\Contracts\RevisionProviderInterface;
use App\Domains\ContentManagement\ContentStorage\Repositories\AzureBlobRepository;
use App\Domains\ContentManagement\ContentStorage\ValueObjects\Revision;
use App\Domains\ContentManagement\ContentStorage\ValueObjects\RevisionCollection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AzureBlobRevisionProvider implements RevisionProviderInterface
{
    public function __construct(
        private AzureBlobRepository $repository
    ) {}

    public function supportsRevisions(): bool
    {
        // Check if container has versioning enabled
        try {
            $properties = $this->repository->getContainerClient()->getProperties();

            return $properties->getIsVersioningEnabled() ?? false;
        } catch (\Exception $e) {
            Log::warning('Failed to check Azure Blob versioning', [
                'container' => $this->repository->getConfig()['container'],
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function getRevisions(string $storagePath, int $page = 1, int $perPage = 10): RevisionCollection
    {
        try {
            // Azure Blob versioning API
            $blobClient = $this->repository->getContainerClient()->getBlobClient($storagePath);
            $versions = $blobClient->listBlobVersions([
                'maxResults' => $perPage * $page,
            ]);

            $revisions = [];
            $start = ($page - 1) * $perPage;
            $count = 0;

            foreach ($versions as $version) {
                if ($count < $start) {
                    $count++;

                    continue;
                }

                if (count($revisions) >= $perPage) {
                    break;
                }

                try {
                    $content = $version->downloadToString();

                    $revisions[] = new Revision(
                        id: $version->getVersionId(),
                        content: $content,
                        message: 'Azure Blob Version',
                        author: $version->getMetadata()['author'] ?? 'Unknown',
                        authorEmail: null,
                        timestamp: Carbon::parse($version->getProperties()->getLastModified()),
                        metadata: json_encode([
                            'size' => $version->getProperties()->getContentLength(),
                            'etag' => $version->getProperties()->getETag(),
                            'content_type' => $version->getProperties()->getContentType(),
                        ]),
                        isCurrent: $version->isCurrentVersion(),
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to fetch Azure version content', [
                        'version_id' => $version->getVersionId(),
                        'error' => $e->getMessage(),
                    ]);
                }

                $count++;
            }

            return new RevisionCollection(
                revisions: $revisions,
                total: $count,
                currentPage: $page,
                perPage: $perPage,
                hasMore: $count > ($page * $perPage),
            );

        } catch (\Exception $e) {
            Log::error('Failed to fetch Azure Blob revisions', [
                'path' => $storagePath,
                'error' => $e->getMessage(),
            ]);

            return new RevisionCollection([], 0, $page, $perPage, false);
        }
    }

    public function getRevision(string $storagePath, string $revisionId): ?Revision
    {
        try {
            $blobClient = $this->repository->getContainerClient()->getBlobClient($storagePath);
            $versionClient = $blobClient->getVersionClient($revisionId);

            $content = $versionClient->downloadToString();
            $properties = $versionClient->getProperties();

            return new Revision(
                id: $revisionId,
                content: $content,
                message: 'Azure Blob Version',
                author: $properties->getMetadata()['author'] ?? 'Unknown',
                authorEmail: null,
                timestamp: Carbon::parse($properties->getLastModified()),
                metadata: json_encode([
                    'size' => $properties->getContentLength(),
                    'etag' => $properties->getETag(),
                    'content_type' => $properties->getContentType(),
                ]),
                isCurrent: false,
            );

        } catch (\Exception $e) {
            Log::error('Failed to fetch Azure Blob revision', [
                'path' => $storagePath,
                'version_id' => $revisionId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function restoreRevision(string $storagePath, string $revisionId): bool
    {
        try {
            $blobClient = $this->repository->getContainerClient()->getBlobClient($storagePath);
            $versionClient = $blobClient->getVersionClient($revisionId);

            // Copy version to current blob
            $content = $versionClient->downloadToString();
            $this->repository->write($storagePath, $content);

            Log::info('Azure Blob revision restored', [
                'path' => $storagePath,
                'version_id' => $revisionId,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to restore Azure Blob revision', [
                'path' => $storagePath,
                'version_id' => $revisionId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function getLatestRevision(string $storagePath): ?Revision
    {
        $revisions = $this->getRevisions($storagePath, 1, 1);

        return $revisions->first();
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\ContentStorage\RevisionProviders;

use App\Domains\ContentManagement\ContentStorage\Contracts\RevisionProviderInterface;
use App\Domains\ContentManagement\ContentStorage\Repositories\S3Repository;
use App\Domains\ContentManagement\ContentStorage\ValueObjects\Revision;
use App\Domains\ContentManagement\ContentStorage\ValueObjects\RevisionCollection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class S3RevisionProvider implements RevisionProviderInterface
{
    public function __construct(
        private S3Repository $repository
    ) {}

    public function supportsRevisions(): bool
    {
        // Check if bucket has versioning enabled
        try {
            $result = $this->repository->getClient()->getBucketVersioning([
                'Bucket' => $this->repository->getBucket(),
            ]);

            return ($result['Status'] ?? '') === 'Enabled';
        } catch (\Exception $e) {
            Log::warning('Failed to check S3 bucket versioning', [
                'bucket' => $this->repository->getBucket(),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function getRevisions(string $storagePath, int $page = 1, int $perPage = 10): RevisionCollection
    {
        try {
            // Use S3 ListObjectVersions API
            $params = [
                'Bucket' => $this->repository->getBucket(),
                'Prefix' => $storagePath,
                'MaxKeys' => $perPage,
            ];

            // Handle pagination using KeyMarker and VersionIdMarker
            if ($page > 1) {
                // For simplicity, we'll fetch all and paginate in memory
                // In production, implement proper marker-based pagination
                $params['MaxKeys'] = $page * $perPage;
            }

            $result = $this->repository->getClient()->listObjectVersions($params);

            $versions = $result['Versions'] ?? [];
            $revisions = [];

            // Apply pagination
            $start = ($page - 1) * $perPage;
            $paginatedVersions = array_slice($versions, $start, $perPage);

            foreach ($paginatedVersions as $version) {
                // Fetch the actual content for each version
                try {
                    $content = $this->repository->getClient()->getObject([
                        'Bucket' => $this->repository->getBucket(),
                        'Key' => $storagePath,
                        'VersionId' => $version['VersionId'],
                    ])['Body']->getContents();

                    $revisions[] = new Revision(
                        id: $version['VersionId'],
                        content: $content,
                        message: 'S3 Version',
                        author: $version['Owner']['DisplayName'] ?? 'Unknown',
                        authorEmail: null,
                        timestamp: Carbon::parse($version['LastModified']),
                        metadata: json_encode([
                            'size' => $version['Size'],
                            'etag' => trim($version['ETag'], '"'),
                            'storage_class' => $version['StorageClass'] ?? 'STANDARD',
                            'is_latest' => $version['IsLatest'] ?? false,
                        ]),
                        isCurrent: $version['IsLatest'] ?? false,
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to fetch S3 version content', [
                        'version_id' => $version['VersionId'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $total = count($versions);

            return new RevisionCollection(
                revisions: $revisions,
                total: $total,
                currentPage: $page,
                perPage: $perPage,
                hasMore: $total > ($page * $perPage),
            );

        } catch (\Exception $e) {
            Log::error('Failed to fetch S3 revisions', [
                'path' => $storagePath,
                'error' => $e->getMessage(),
            ]);

            return new RevisionCollection([], 0, $page, $perPage, false);
        }
    }

    public function getRevision(string $storagePath, string $revisionId): ?Revision
    {
        try {
            // Fetch specific version
            $result = $this->repository->getClient()->getObject([
                'Bucket' => $this->repository->getBucket(),
                'Key' => $storagePath,
                'VersionId' => $revisionId,
            ]);

            $content = $result['Body']->getContents();

            // Get version metadata
            $metadata = $this->repository->getClient()->headObject([
                'Bucket' => $this->repository->getBucket(),
                'Key' => $storagePath,
                'VersionId' => $revisionId,
            ]);

            return new Revision(
                id: $revisionId,
                content: $content,
                message: 'S3 Version',
                author: $metadata['Metadata']['author'] ?? 'Unknown',
                authorEmail: null,
                timestamp: Carbon::parse($metadata['LastModified']),
                metadata: json_encode([
                    'size' => $metadata['ContentLength'],
                    'etag' => trim($metadata['ETag'], '"'),
                    'content_type' => $metadata['ContentType'] ?? 'application/json',
                    'version_id' => $revisionId,
                ]),
                isCurrent: false,
            );

        } catch (\Exception $e) {
            Log::error('Failed to fetch S3 revision', [
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
            // Copy the old version to become the current version
            $this->repository->getClient()->copyObject([
                'Bucket' => $this->repository->getBucket(),
                'CopySource' => urlencode($this->repository->getBucket().'/'.$storagePath).'?versionId='.$revisionId,
                'Key' => $storagePath,
            ]);

            Log::info('S3 revision restored', [
                'path' => $storagePath,
                'version_id' => $revisionId,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to restore S3 revision', [
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

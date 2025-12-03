<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\ContentStorage\RevisionProviders;

use App\Domains\ContentManagement\ContentStorage\Contracts\RevisionProviderInterface;
use App\Domains\ContentManagement\ContentStorage\Repositories\GCSRepository;
use App\Domains\ContentManagement\ContentStorage\ValueObjects\Revision;
use App\Domains\ContentManagement\ContentStorage\ValueObjects\RevisionCollection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GCSRevisionProvider implements RevisionProviderInterface
{
    public function __construct(
        private GCSRepository $repository
    ) {}

    public function supportsRevisions(): bool
    {
        // Check if bucket has versioning enabled
        try {
            $bucket = $this->repository->getClient()->bucket($this->repository->getConfig()['bucket']);
            $info = $bucket->info();

            return $info['versioning']['enabled'] ?? false;
        } catch (\Exception $e) {
            Log::warning('Failed to check GCS versioning', [
                'bucket' => $this->repository->getConfig()['bucket'],
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function getRevisions(string $storagePath, int $page = 1, int $perPage = 10): RevisionCollection
    {
        try {
            $bucket = $this->repository->getClient()->bucket($this->repository->getConfig()['bucket']);

            // List all versions
            $options = [
                'versions' => true,
                'maxResults' => $perPage * $page,
            ];

            $objects = $bucket->objects($options);
            $revisions = [];
            $start = ($page - 1) * $perPage;
            $count = 0;

            foreach ($objects as $object) {
                if ($object->name() !== $storagePath) {
                    continue;
                }

                if ($count < $start) {
                    $count++;
                    continue;
                }

                if (count($revisions) >= $perPage) {
                    break;
                }

                try {
                    $content = $object->downloadAsString();
                    $info = $object->info();

                    $revisions[] = new Revision(
                        id: $info['generation'],
                        content: $content,
                        message: 'GCS Version',
                        author: $info['metadata']['author'] ?? 'Unknown',
                        authorEmail: null,
                        timestamp: Carbon::parse($info['updated']),
                        metadata: json_encode([
                            'size' => $info['size'],
                            'generation' => $info['generation'],
                            'metageneration' => $info['metageneration'],
                            'content_type' => $info['contentType'],
                        ]),
                        isCurrent: !isset($info['timeDeleted']),
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to fetch GCS version content', [
                        'generation' => $object->info()['generation'] ?? 'unknown',
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
            Log::error('Failed to fetch GCS revisions', [
                'path' => $storagePath,
                'error' => $e->getMessage(),
            ]);

            return new RevisionCollection([], 0, $page, $perPage, false);
        }
    }

    public function getRevision(string $storagePath, string $revisionId): ?Revision
    {
        try {
            $bucket = $this->repository->getClient()->bucket($this->repository->getConfig()['bucket']);
            $object = $bucket->object($storagePath, ['generation' => $revisionId]);

            $content = $object->downloadAsString();
            $info = $object->info();

            return new Revision(
                id: $info['generation'],
                content: $content,
                message: 'GCS Version',
                author: $info['metadata']['author'] ?? 'Unknown',
                authorEmail: null,
                timestamp: Carbon::parse($info['updated']),
                metadata: json_encode([
                    'size' => $info['size'],
                    'generation' => $info['generation'],
                    'metageneration' => $info['metageneration'],
                    'content_type' => $info['contentType'],
                ]),
                isCurrent: !isset($info['timeDeleted']),
            );

        } catch (\Exception $e) {
            Log::error('Failed to fetch GCS revision', [
                'path' => $storagePath,
                'generation' => $revisionId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function restoreRevision(string $storagePath, string $revisionId): bool
    {
        try {
            $bucket = $this->repository->getClient()->bucket($this->repository->getConfig()['bucket']);
            $object = $bucket->object($storagePath, ['generation' => $revisionId]);

            // Copy old version to current
            $content = $object->downloadAsString();
            $this->repository->write($storagePath, $content);

            Log::info('GCS revision restored', [
                'path' => $storagePath,
                'generation' => $revisionId,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to restore GCS revision', [
                'path' => $storagePath,
                'generation' => $revisionId,
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

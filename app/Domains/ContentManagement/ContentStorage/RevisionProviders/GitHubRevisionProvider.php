<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\ContentStorage\RevisionProviders;

use App\Domains\ContentManagement\ContentStorage\Contracts\RevisionProviderInterface;
use App\Domains\ContentManagement\ContentStorage\Repositories\GithubRepository;
use App\Domains\ContentManagement\ContentStorage\ValueObjects\Revision;
use App\Domains\ContentManagement\ContentStorage\ValueObjects\RevisionCollection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GitHubRevisionProvider implements RevisionProviderInterface
{
    public function __construct(
        private GithubRepository $repository
    ) {}

    public function supportsRevisions(): bool
    {
        return true; // GitHub always has commit history
    }

    public function getRevisions(string $storagePath, int $page = 1, int $perPage = 10): RevisionCollection
    {
        try {
            // Use GitHub Commits API
            $commits = $this->repository->getClient()->repo()->commits()->all(
                $this->repository->getOwner(),
                $this->repository->getRepo(),
                [
                    'path' => $storagePath,
                    'page' => $page,
                    'per_page' => $perPage,
                ]
            );

            $revisions = [];

            foreach ($commits as $commit) {
                // Fetch file content at this commit
                try {
                    $content = $this->repository->read($storagePath, $commit['sha']);

                    $revisions[] = new Revision(
                        id: $commit['sha'],
                        content: $content,
                        message: $commit['commit']['message'],
                        author: $commit['commit']['author']['name'] ?? 'Unknown',
                        authorEmail: $commit['commit']['author']['email'] ?? null,
                        timestamp: Carbon::parse($commit['commit']['author']['date']),
                        metadata: json_encode([
                            'sha' => $commit['sha'],
                            'short_sha' => substr($commit['sha'], 0, 7),
                            'url' => $commit['html_url'] ?? null,
                            'parents' => array_map(fn($p) => $p['sha'], $commit['parents'] ?? []),
                            'committer' => $commit['commit']['committer']['name'] ?? null,
                        ]),
                        isCurrent: false,
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to fetch GitHub commit content', [
                        'sha' => $commit['sha'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Mark first revision as current if on page 1
            if (count($revisions) > 0 && $page === 1) {
                $first = $revisions[0];
                $revisions[0] = new Revision(
                    id: $first->id,
                    content: $first->content,
                    message: $first->message,
                    author: $first->author,
                    authorEmail: $first->authorEmail,
                    timestamp: $first->timestamp,
                    metadata: $first->metadata,
                    isCurrent: true,
                );
            }

            return new RevisionCollection(
                revisions: $revisions,
                total: count($commits), // GitHub doesn't provide total in response
                currentPage: $page,
                perPage: $perPage,
                hasMore: count($commits) === $perPage,
            );

        } catch (\Exception $e) {
            Log::error('Failed to fetch GitHub revisions', [
                'path' => $storagePath,
                'error' => $e->getMessage(),
            ]);

            return new RevisionCollection([], 0, $page, $perPage, false);
        }
    }

    public function getRevision(string $storagePath, string $revisionId): ?Revision
    {
        try {
            // Fetch commit details
            $commit = $this->repository->getClient()->repo()->commits()->show(
                $this->repository->getOwner(),
                $this->repository->getRepo(),
                $revisionId
            );

            // Fetch file content at this commit
            $content = $this->repository->read($storagePath, $revisionId);

            return new Revision(
                id: $commit['sha'],
                content: $content,
                message: $commit['commit']['message'],
                author: $commit['commit']['author']['name'] ?? 'Unknown',
                authorEmail: $commit['commit']['author']['email'] ?? null,
                timestamp: Carbon::parse($commit['commit']['author']['date']),
                metadata: json_encode([
                    'sha' => $commit['sha'],
                    'short_sha' => substr($commit['sha'], 0, 7),
                    'url' => $commit['html_url'] ?? null,
                    'parents' => array_map(fn($p) => $p['sha'], $commit['parents'] ?? []),
                    'stats' => $commit['stats'] ?? null,
                    'files_changed' => count($commit['files'] ?? []),
                ]),
                isCurrent: false,
            );

        } catch (\Exception $e) {
            Log::error('Failed to fetch GitHub revision', [
                'path' => $storagePath,
                'sha' => $revisionId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function restoreRevision(string $storagePath, string $revisionId): bool
    {
        try {
            // Get the content from the old commit
            $content = $this->repository->read($storagePath, $revisionId);

            // Get the old commit details for the message
            $oldCommit = $this->getRevision($storagePath, $revisionId);

            if (!$oldCommit) {
                return false;
            }

            // Write it as a new commit (revert)
            $commitMessage = sprintf(
                "Restore to revision: %s\n\n%s",
                substr($revisionId, 0, 7),
                $oldCommit->message
            );

            $this->repository->write(
                $storagePath,
                $content,
                ['commit_message' => $commitMessage]
            );

            Log::info('GitHub revision restored', [
                'path' => $storagePath,
                'sha' => $revisionId,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to restore GitHub revision', [
                'path' => $storagePath,
                'sha' => $revisionId,
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

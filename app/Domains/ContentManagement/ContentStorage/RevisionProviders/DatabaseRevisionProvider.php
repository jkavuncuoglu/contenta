<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\ContentStorage\RevisionProviders;

use App\Domains\ContentManagement\ContentStorage\Contracts\RevisionProviderInterface;
use App\Domains\ContentManagement\ContentStorage\ValueObjects\Revision;
use App\Domains\ContentManagement\ContentStorage\ValueObjects\RevisionCollection;
use Illuminate\Database\Eloquent\Model;

class DatabaseRevisionProvider implements RevisionProviderInterface
{
    public function __construct(
        private Model $model  // Page or Post model
    ) {}

    public function supportsRevisions(): bool
    {
        return true;
    }

    public function getRevisions(string $storagePath, int $page = 1, int $perPage = 10): RevisionCollection
    {
        // Query revisions table
        $query = $this->model->revisions()
            ->with('author')
            ->orderByDesc('created_at');

        $total = $query->count();
        $revisions = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(fn ($rev) => new Revision(
                id: (string) $rev->id,
                content: $rev->content_markdown ?? '',
                message: $rev->change_summary ?? 'Database revision',
                author: $rev->author?->name,
                authorEmail: $rev->author?->email,
                timestamp: $rev->created_at,
                metadata: json_encode([
                    'size' => strlen($rev->content_markdown ?? ''),
                    'revision_number' => $rev->id,
                ]),
                isCurrent: false,
            ))->toArray();

        return new RevisionCollection(
            revisions: $revisions,
            total: $total,
            currentPage: $page,
            perPage: $perPage,
            hasMore: $total > ($page * $perPage),
        );
    }

    public function getRevision(string $storagePath, string $revisionId): ?Revision
    {
        $rev = $this->model->revisions()->with('author')->find($revisionId);

        if (! $rev) {
            return null;
        }

        return new Revision(
            id: (string) $rev->id,
            content: $rev->content_markdown ?? '',
            message: $rev->change_summary ?? 'Database revision',
            author: $rev->author?->name,
            authorEmail: $rev->author?->email,
            timestamp: $rev->created_at,
            metadata: json_encode([
                'size' => strlen($rev->content_markdown ?? ''),
                'revision_number' => $rev->id,
            ]),
            isCurrent: false,
        );
    }

    public function restoreRevision(string $storagePath, string $revisionId): bool
    {
        $revision = $this->getRevision($storagePath, $revisionId);

        if (! $revision) {
            return false;
        }

        // Update the model with revision content
        $this->model->update([
            'content_markdown' => $revision->content,
        ]);

        return true;
    }

    public function getLatestRevision(string $storagePath): ?Revision
    {
        $rev = $this->model->revisions()->with('author')->latest()->first();

        if (! $rev) {
            return null;
        }

        return $this->getRevision($storagePath, (string) $rev->id);
    }
}

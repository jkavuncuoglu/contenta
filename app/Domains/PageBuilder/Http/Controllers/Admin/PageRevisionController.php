<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Http\Controllers\Admin;

use App\Domains\PageBuilder\Models\Page;
use App\Domains\PageBuilder\Models\PageRevision;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PageRevisionController extends Controller
{
    /**
     * Get all revisions for a page
     */
    public function index(Page $page): JsonResponse
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, PageRevision> $revisions */
        $revisions = $page->revisions()
            ->with('user:id,first_name,last_name,email')
            ->get();

        $mappedRevisions = $revisions->map(function (PageRevision $revision) {
            $userName = 'System';
            if ($revision->user) {
                if ($revision->user->first_name || $revision->user->last_name) {
                    $userName = trim($revision->user->first_name.' '.$revision->user->last_name);
                } else {
                    $userName = $revision->user->email;
                }
            }

            return [
                'id' => $revision->id,
                'revision_number' => $revision->revision_number,
                'title' => $revision->title,
                'user' => $userName,
                'reason' => $revision->reason,
                'created_at' => $revision->created_at?->toISOString(),
                'created_at_human' => $revision->created_at?->diffForHumans(),
            ];
        });

        return response()->json($mappedRevisions);
    }

    /**
     * Get a specific revision
     */
    public function show(Page $page, PageRevision $revision): JsonResponse
    {
        if ($revision->page_id !== $page->id) {
            return response()->json(['error' => 'Revision does not belong to this page'], 404);
        }

        return response()->json($revision->load('user:id,first_name,last_name,email'));
    }

    /**
     * Restore a page to a specific revision
     */
    public function restore(Page $page, PageRevision $revision): JsonResponse
    {
        if ($revision->page_id !== $page->id) {
            return response()->json(['error' => 'Revision does not belong to this page'], 404);
        }

        $userId = auth()->id();
        // Create a new revision for the current state before restoring
        $this->createRevision($page, is_int($userId) ? $userId : null, 'Before restoring to revision #'.$revision->revision_number);

        // Restore the page to the revision state
        $page->update([
            'title' => $revision->title,
            'slug' => $revision->slug,
            'layout_id' => $revision->layout_id,
            'data' => $revision->data,
            'status' => $revision->status,
            'meta_title' => $revision->meta_title,
            'meta_description' => $revision->meta_description,
            'meta_keywords' => $revision->meta_keywords,
            'schema_data' => $revision->schema_data,
        ]);

        $userId = auth()->id();
        // Create a new revision for the restored state
        $this->createRevision($page, is_int($userId) ? $userId : null, 'Restored to revision #'.$revision->revision_number);

        return response()->json([
            'success' => true,
            'message' => 'Page restored to revision #'.$revision->revision_number,
        ]);
    }

    /**
     * Create a revision for a page
     */
    protected function createRevision(Page $page, ?int $userId, ?string $reason = null): PageRevision
    {
        /** @var PageRevision|null $latestRevision */
        $latestRevision = $page->revisions()->orderBy('revision_number', 'desc')->first();
        $revisionNumber = $latestRevision ? $latestRevision->revision_number + 1 : 1;

        return PageRevision::create([
            'page_id' => $page->id,
            'user_id' => $userId,
            'title' => $page->title,
            'slug' => $page->slug,
            'layout_id' => $page->layout_id,
            'data' => $page->data,
            'status' => $page->status,
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
            'meta_keywords' => $page->meta_keywords,
            'schema_data' => $page->schema_data,
            'reason' => $reason,
            'revision_number' => $revisionNumber,
        ]);
    }
}

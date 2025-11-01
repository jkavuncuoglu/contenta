<?php

declare(strict_types=1);

namespace App\Domains\Media\Http\Controllers\Admin;

use App\Domains\Media\Services\MediaServiceContract;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MediaController extends Controller
{
    public function __construct(
        private readonly MediaServiceContract $mediaService
    ) {}

    /**
     * Display a listing of media files
     */
    public function index(Request $request): Response
    {
        $media = $this->mediaService->getPaginatedMedia(
            perPage: (int) $request->get('per_page', 24)
        );

        return Inertia::render('admin/Media', [
            'media' => $media->items(),
            'pagination' => [
                'current_page' => $media->currentPage(),
                'last_page' => $media->lastPage(),
                'per_page' => $media->perPage(),
                'total' => $media->total(),
                'from' => $media->firstItem(),
                'to' => $media->lastItem(),
            ],
        ]);
    }

    /**
     * Store a newly uploaded media file
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'collection' => 'nullable|string|max:255',
        ]);

        try {
            $this->mediaService->uploadMedia(
                file: $request->file('file'),
                collection: $request->get('collection', 'uploads')
            );

            return redirect()->route('admin.media.index')
                ->with('success', 'Media uploaded successfully');
        } catch (\Exception $e) {
            return redirect()->route('admin.media.index')
                ->with('error', 'Failed to upload media: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified media file
     */
    public function show(int $id)
    {
        $media = $this->mediaService->getMediaById($id);

        if (!$media) {
            return response()->json([
                'success' => false,
                'message' => 'Media not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'media' => [
                'id' => $media->id,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'url' => $media->getUrl(),
                'type' => str_starts_with($media->mime_type, 'image/') ? 'image' : 'document',
                'size' => $media->size,
                'formatted_size' => $media->humanReadableSize,
                'mime_type' => $media->mime_type,
                'collection_name' => $media->collection_name,
                'created_at' => $media->created_at->format('M j, Y'),
            ],
        ]);
    }

    /**
     * Remove the specified media file
     */
    public function destroy(int $id)
    {
        $deleted = $this->mediaService->deleteMedia($id);

        if (!$deleted) {
            return redirect()->route('admin.media.index')
                ->with('error', 'Media not found or could not be deleted');
        }

        return redirect()->route('admin.media.index')
            ->with('success', 'Media deleted successfully');
    }

    /**
     * Get media by collection
     */
    public function collection(string $collection)
    {
        $media = $this->mediaService->getMediaByCollection($collection);

        return response()->json([
            'success' => true,
            'media' => $media,
        ]);
    }
}
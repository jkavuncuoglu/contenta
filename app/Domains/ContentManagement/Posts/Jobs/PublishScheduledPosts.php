<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Posts\Jobs;

use App\Domains\ContentManagement\Posts\Services\PostServiceContract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PublishScheduledPosts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(PostServiceContract $postService): void
    {
        $publishedPosts = $postService->publishDuePosts();

        if (count($publishedPosts) > 0) {
            Log::info('Published scheduled posts', [
                'count' => count($publishedPosts),
                'post_ids' => array_map(fn($post) => $post->id, $publishedPosts),
            ]);
        }
    }
}

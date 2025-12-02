<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Observers;

use App\Domains\PageBuilder\Models\Page;
use Illuminate\Support\Facades\Cache;

class PageObserver
{
    /**
     * Handle the Page "updated" event.
     */
    public function updated(Page $page): void
    {
        $this->clearPageCache($page);
    }

    /**
     * Handle the Page "deleted" event.
     */
    public function deleted(Page $page): void
    {
        $this->clearPageCache($page);
    }

    /**
     * Clear cached HTML for a page
     */
    private function clearPageCache(Page $page): void
    {
        $cacheKeys = [
            'page.html.' . $page->id,
            'page.html.slug.' . $page->slug,
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }
}

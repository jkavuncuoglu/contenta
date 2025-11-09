<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Services;

use App\Domains\PageBuilder\Models\Page;

interface PageRenderServiceContract
{
    /**
     * Render a page to HTML
     */
    public function renderPage(Page $page): string;
}

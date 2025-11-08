<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Http\Controllers\Admin;

use App\Domains\PageBuilder\Models\Block;
use App\Domains\PageBuilder\Models\Layout;
use App\Domains\PageBuilder\Models\Page;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PageBuilderController extends Controller
{
    public function index(): Response
    {
        $pages = Page::with(['layout', 'author'])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return Inertia::render('Admin/PageBuilder/Index', [
            'pages' => $pages,
        ]);
    }

    public function create(): Response
    {
        $layouts = Layout::active()->get(['id', 'name', 'slug']);

        return Inertia::render('Admin/PageBuilder/Create', [
            'layouts' => $layouts,
        ]);
    }

    public function edit(Page $page): Response
    {
        $layouts = Layout::active()->get(['id', 'name', 'slug']);
        $blocks = Block::active()->get(['id', 'name', 'type', 'category', 'config_schema', 'preview_image', 'description', 'is_active']);

        return Inertia::render('Admin/PageBuilder/Edit', [
            'page' => $page->load('layout'),
            'layouts' => $layouts,
            'blocks' => $blocks,
            'categories' => Block::getCategories(),
        ]);
    }

    public function layouts(): Response
    {
        $layouts = Layout::orderBy('name')->get();

        return Inertia::render('Admin/PageBuilder/Layouts/Index', [
            'layouts' => $layouts,
        ]);
    }

    public function createLayout(): Response
    {
        return Inertia::render('Admin/PageBuilder/Layouts/Create');
    }

    public function editLayout(Layout $layout): Response
    {
        return Inertia::render('Admin/PageBuilder/Layouts/Edit', [
            'layout' => $layout,
        ]);
    }

    public function blocks(): Response
    {
        $blocks = Block::orderBy('category')->orderBy('name')->get();

        return Inertia::render('Admin/PageBuilder/Blocks/Index', [
            'blocks' => $blocks,
            'categories' => Block::getCategories(),
        ]);
    }

    public function createBlock(): Response
    {
        return Inertia::render('Admin/PageBuilder/Blocks/Create', [
            'categories' => Block::getCategories(),
        ]);
    }

    public function editBlock(Block $block): Response
    {
        return Inertia::render('Admin/PageBuilder/Blocks/Edit', [
            'block' => $block,
            'categories' => Block::getCategories(),
        ]);
    }
}
<?php
namespace App\Domains\ContentManagement\Pages\Http\Controllers\Admin;

use App\Domains\ContentManagement\Pages\Models\Page;
use App\Domains\ContentManagement\Pages\Services\PagesServiceContract;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PagesController
{
    protected PagesServiceContract $service;
    public function index()
    {
        $pages = Page::orderBy('created_at', 'desc')->get();
        return Inertia::render('admin/content/pages/Index', ['pages' => $pages]);
    }

    public function create()
    {
        return Inertia::render('admin/content/pages/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pages,slug',
            'content' => 'nullable|string',
            'published' => 'boolean',
        ]);
        $this->service->create($data);
        return redirect()->route('admin.pages.index');
    }

    public function edit(Page $page)
    {
        return Inertia::render('admin/content/pages/Edit', ['page' => $page]);
    }

    public function update(Request $request, Page $page)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pages,slug,' . $page->id,
            'content' => 'nullable|string',
            'published' => 'boolean',
        ]);
        $this->service->update($page, $data);
        return redirect()->route('admin.pages.index');
    }

    public function destroy(Page $page)
    {
        $this->service->delete($page);
        return redirect()->route('admin.pages.index');
    }
}


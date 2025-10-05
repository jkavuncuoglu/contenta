<?php

namespace App\Domains\ContentManagement\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\ContentManagement\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoriesController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $page = (int) $request->input('page', 1);

        $paginator = Category::orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);

        $categories = $paginator->through(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'posts_count' => $c->posts_count ?? 0,
            ];
        });

        return Inertia::render('admin/categories/Index', [
            'categories' => $categories,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/categories/Create');
    }

    public function edit($id)
    {
        return Inertia::render('admin/categories/Edit', ['id' => $id]);
    }
}


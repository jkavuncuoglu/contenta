<?php

namespace App\Domains\ContentManagement\Categories\Services;

use App\Domains\ContentManagement\Categories\Models\Category;

class CategoryService implements CategoryServiceContract
{
    public function getAllCategories()
    {
        return Category::all();
    }

    public function findCategoryById($id)
    {
        return Category::find($id);
    }

    public function createCategory(array $data)
    {
        return Category::create($data);
    }

    public function updateCategory($id, array $data)
    {
        $category = Category::findOrFail($id);
        $category->update($data);
        return $category;
    }

    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);
        return $category->delete();
    }
}


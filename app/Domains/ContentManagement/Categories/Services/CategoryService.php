<?php

namespace App\Domains\ContentManagement\Categories\Services;

use App\Domains\ContentManagement\Categories\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryService implements CategoryServiceContract
{
    /**
     * @return Collection<int, Category>
     */
    public function getAllCategories(): Collection
    {
        return Category::all();
    }

    public function findCategoryById(int $id): ?Category
    {
        return Category::find($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createCategory(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateCategory(int $id, array $data): Category
    {
        $category = Category::findOrFail($id);
        $category->update($data);

        return $category;
    }

    public function deleteCategory(int $id): ?bool
    {
        $category = Category::findOrFail($id);

        return $category->delete();
    }
}

<?php

namespace App\Domains\ContentManagement\Categories\Services;

interface CategoryServiceContract
{
    public function getAllCategories();
    public function findCategoryById($id);
    public function createCategory(array $data);
    public function updateCategory($id, array $data);
    public function deleteCategory($id);
}

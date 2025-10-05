<?php

namespace App\Domains\ContentManagement\Categories;

use Illuminate\Support\Facades\Facade;
use App\Domains\ContentManagement\Categories\Services\CategoryServiceContract;

class CategoryFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \App\Domains\ContentManagement\Categories\Services\CategoryServiceContract::class;
    }
}
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

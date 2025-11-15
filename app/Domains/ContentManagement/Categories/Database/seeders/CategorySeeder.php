<?php

namespace App\Domains\ContentManagement\Categories\Database\seeders;

use App\Domains\ContentManagement\Categories\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::factory()->count(10)->create();
    }
}

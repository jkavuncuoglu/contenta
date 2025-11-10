<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\ContentManagement\Categories\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::factory()->count(10)->create();
    }
}

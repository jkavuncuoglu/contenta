<?php

namespace Database\Seeders;

use App\Domains\ContentManagement\Categories\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::factory()->count(10)->create();
    }
}

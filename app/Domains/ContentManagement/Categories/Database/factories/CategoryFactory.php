<?php

namespace Database\Factories;

use App\Domains\ContentManagement\Categories\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'parent_id' => null,
            'sort_order' => $this->faker->numberBetween(1, 10),
        ];
    }
}


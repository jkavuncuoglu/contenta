<?php

namespace App\Domains\ContentManagement\Categories\Database\factories;

use App\Domains\ContentManagement\Categories\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'parent_id' => null,
            'sort_order' => $this->faker->numberBetween(1, 10),
        ];
    }
}

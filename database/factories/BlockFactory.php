<?php

namespace Database\Factories;

use App\Domains\PageBuilder\Models\Block;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\PageBuilder\Models\Block>
 */
class BlockFactory extends Factory
{
    protected $model = Block::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'type' => fake()->unique()->word(),
            'category' => 'general',
            'config_schema' => [],
            'component_path' => 'components/blocks/TextBlock.vue',
            'preview_image' => null,
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }
}

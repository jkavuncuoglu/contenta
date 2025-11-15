<?php

namespace Database\Factories;

use App\Domains\PageBuilder\Models\Layout;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\PageBuilder\Models\Layout>
 */
class LayoutFactory extends Factory
{
    protected $model = Layout::class;

    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'structure' => ['areas' => ['header', 'main', 'footer']],
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

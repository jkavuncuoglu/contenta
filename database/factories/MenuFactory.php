<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Navigation\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Menu::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => \Illuminate\Support\Str::slug($name),
            'description' => fake()->optional()->sentence(),
            'location' => fake()->randomElement(['primary', 'footer', 'sidebar', 'mobile']),
            'settings' => [],
            'is_active' => fake()->boolean(80),
        ];
    }

    /**
     * Indicate that the menu is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the menu is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set a specific location for the menu.
     */
    public function location(string $location): static
    {
        return $this->state(fn (array $attributes) => [
            'location' => $location,
        ]);
    }
}

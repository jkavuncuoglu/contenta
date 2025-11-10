<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Navigation\Models\Menu;
use App\Domains\Navigation\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = MenuItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'parent_id' => null,
            'type' => fake()->randomElement(['custom', 'page', 'post', 'category', 'tag']),
            'title' => fake()->words(3, true),
            'url' => fake()->optional()->url(),
            'object_id' => fake()->optional()->randomNumber(),
            'object_type' => null,
            'target' => fake()->randomElement(['_self', '_blank']),
            'css_classes' => fake()->optional()->word(),
            'icon' => fake()->optional()->word(),
            'order' => 0,
            'attributes' => [],
            'metadata' => [],
            'is_visible' => fake()->boolean(90),
        ];
    }

    /**
     * Indicate that the menu item is a custom link.
     */
    public function customLink(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'custom',
            'url' => fake()->url(),
            'object_id' => null,
            'object_type' => null,
        ]);
    }

    /**
     * Indicate that the menu item is visible.
     */
    public function visible(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible' => true,
        ]);
    }

    /**
     * Indicate that the menu item is hidden.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible' => false,
        ]);
    }

    /**
     * Set a specific parent for the menu item.
     */
    public function withParent(int $parentId): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parentId,
        ]);
    }

    /**
     * Set a specific menu for the menu item.
     */
    public function forMenu(int $menuId): static
    {
        return $this->state(fn (array $attributes) => [
            'menu_id' => $menuId,
        ]);
    }
}

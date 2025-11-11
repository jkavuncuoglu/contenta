<?php

namespace App\Domains\Plugins\Database\Factories;

use App\Domains\Plugins\Models\Plugin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\Plugins\Models\Plugin>
 */
class PluginFactory extends Factory
{
    protected $model = Plugin::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique()->slug(2),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'version' => $this->faker->semver(),
            'author' => $this->faker->name(),
            'author_url' => $this->faker->url(),
            'metadata' => [],
            'entry_point' => 'plugin.php',
            'plugin_type' => Plugin::TYPE_UNIVERSAL,
            'is_enabled' => false,
            'is_verified' => true,
            'scanned_at' => now(),
            'scan_results' => [
                'safe' => true,
                'threats' => [],
                'warnings' => [],
                'scanned_files' => 1,
                'scanned_at' => now()->toISOString(),
            ],
            'installed_at' => now(),
        ];
    }

    /**
     * Indicate that the plugin is frontend only.
     */
    public function frontend(): static
    {
        return $this->state(fn (array $attributes) => [
            'plugin_type' => Plugin::TYPE_FRONTEND,
        ]);
    }

    /**
     * Indicate that the plugin is admin only.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'plugin_type' => Plugin::TYPE_ADMIN,
        ]);
    }

    /**
     * Indicate that the plugin is universal.
     */
    public function universal(): static
    {
        return $this->state(fn (array $attributes) => [
            'plugin_type' => Plugin::TYPE_UNIVERSAL,
        ]);
    }

    /**
     * Indicate that the plugin is enabled.
     */
    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => true,
        ]);
    }

    /**
     * Indicate that the plugin is unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => false,
        ]);
    }
}

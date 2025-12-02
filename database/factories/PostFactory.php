<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\ContentManagement\Posts\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = \App\Domains\ContentManagement\Posts\Models\Post::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'slug' => fake()->slug(),
            'content_markdown' => fake()->paragraphs(3, true),
            'content_html' => fake()->paragraphs(3, true),
            'excerpt' => fake()->text(160),
            'status' => 'draft',
            'published_at' => now(),
            'meta_title' => fake()->sentence(),
            'meta_description' => fake()->text(160),
            'meta_keywords' => implode(', ', fake()->words(5)),
            'template' => 'blog-post',
            'language' => 'en',
        ];
    }
}

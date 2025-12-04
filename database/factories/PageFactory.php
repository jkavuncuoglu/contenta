<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\PageBuilder\Models\Page>
 */
class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = \App\Domains\PageBuilder\Models\Page::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'slug' => fake()->slug(),
            'markdown_content' => fake()->paragraphs(3, true),
            'content_type' => 'markdown',
            'status' => 'draft',
            'layout_template' => 'default',
            'meta_title' => fake()->sentence(),
            'meta_description' => fake()->text(160),
            'meta_keywords' => implode(', ', fake()->words(5)),
        ];
    }
}

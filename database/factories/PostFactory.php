<?php

namespace Database\Factories;

use App\Domains\ContentManagement\Posts\Models\Post;
use App\Domains\Security\UserManagement\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\ContentManagement\Posts\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();
        $contentMarkdown = fake()->paragraphs(3, true);
        // Ensure content_markdown is never empty/null
        if (empty($contentMarkdown)) {
            $contentMarkdown = 'Default post content.';
        }

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'content_markdown' => $contentMarkdown,
            'content_html' => '<p>'.str_replace("\n\n", '</p><p>', $contentMarkdown).'</p>',
            'table_of_contents' => null,
            'excerpt' => fake()->sentence(),
            'status' => 'draft',
            'author_id' => User::factory(),
            'parent_id' => null,
            'featured_image_id' => null,
            'published_at' => null,
            'meta_title' => null,
            'meta_description' => null,
            'meta_keywords' => null,
            'open_graph_title' => null,
            'open_graph_description' => null,
            'twitter_title' => null,
            'twitter_description' => null,
            'structured_data' => null,
            'custom_fields' => null,
            'view_count' => 0,
            'comment_count' => 0,
            'like_count' => 0,
            'reading_time_minutes' => 5,
            'language' => 'en',
            'template' => null,
            'version' => 1,
        ];
    }

    /**
     * Indicate that the post is published.
     */
    public function published(): static
    {
        return $this->state(fn () => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * Indicate that the post is scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn () => [
            'status' => 'scheduled',
            'published_at' => now()->addDays(7),
        ]);
    }

    /**
     * Indicate that the post is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }
}

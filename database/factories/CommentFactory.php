<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\ContentManagement\Posts\Models\Comment;
use App\Domains\ContentManagement\Posts\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'parent_id' => null,
            'author_name' => fake()->name(),
            'author_email' => fake()->safeEmail(),
            'author_url' => fake()->optional()->url(),
            'author_ip' => fake()->ipv4(),
            'content' => fake()->paragraph(3),
            'status' => fake()->randomElement(['pending', 'approved', 'spam', 'trash']),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the comment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the comment is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    /**
     * Indicate that the comment is spam.
     */
    public function spam(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'spam',
        ]);
    }

    /**
     * Indicate that the comment is trash.
     */
    public function trash(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'trash',
        ]);
    }

    /**
     * Indicate that the comment is a reply to another comment.
     */
    public function reply(?int $parentId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parentId ?? Comment::factory(),
        ]);
    }

    /**
     * Set a specific post for the comment.
     */
    public function forPost(Post|int $post): static
    {
        return $this->state(fn (array $attributes) => [
            'post_id' => $post instanceof Post ? $post->id : $post,
        ]);
    }
}

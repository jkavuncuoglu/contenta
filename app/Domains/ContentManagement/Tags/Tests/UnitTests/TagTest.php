<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Tags\Tests\UnitTests;

use App\Domains\ContentManagement\Posts\Models\Post;
use App\Domains\ContentManagement\Tags\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_has_fillable_attributes(): void
    {
        // Arrange
        $data = [
            'name' => 'Laravel',
            'slug' => 'laravel',
            'description' => 'Laravel framework',
            'color' => '#FF2D20',
            'meta_title' => 'Laravel Meta',
            'meta_description' => 'Laravel meta description',
        ];

        // Act
        $tag = Tag::create($data);

        // Assert
        $this->assertEquals('Laravel', $tag->name);
        $this->assertEquals('laravel', $tag->slug);
        $this->assertEquals('Laravel framework', $tag->description);
        $this->assertEquals('#FF2D20', $tag->color);
    }

    public function test_it_belongs_to_many_posts(): void
    {
        // Arrange
        $tag = Tag::factory()->create();
        $posts = Post::factory()->count(3)->create();
        $tag->posts()->attach($posts->pluck('id'));

        // Act
        $result = $tag->posts;

        // Assert
        $this->assertCount(3, $result);
    }

    public function test_popular_scope_returns_tags_ordered_by_post_count(): void
    {
        // Arrange
        $tag1 = Tag::factory()->create(['name' => 'Tag 1']);
        $tag2 = Tag::factory()->create(['name' => 'Tag 2']);
        $tag3 = Tag::factory()->create(['name' => 'Tag 3']);

        $posts1 = Post::factory()->count(5)->create();
        $posts2 = Post::factory()->count(10)->create();
        $posts3 = Post::factory()->count(3)->create();

        $tag1->posts()->attach($posts1->pluck('id'));
        $tag2->posts()->attach($posts2->pluck('id'));
        $tag3->posts()->attach($posts3->pluck('id'));

        // Act
        $popular = Tag::popular(3)->get();

        // Assert
        $this->assertCount(3, $popular);
        $this->assertEquals('Tag 2', $popular[0]->name);
        $this->assertEquals('Tag 1', $popular[1]->name);
        $this->assertEquals('Tag 3', $popular[2]->name);
    }

    public function test_with_post_count_scope_loads_post_count(): void
    {
        // Arrange
        $tag = Tag::factory()->create();
        $posts = Post::factory()->count(5)->create();
        $tag->posts()->attach($posts->pluck('id'));

        // Act
        $result = Tag::withPostCount()->find($tag->id);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals(5, $result->posts_count);
    }

    public function test_it_uses_soft_deletes(): void
    {
        // Arrange
        $tag = Tag::factory()->create();
        $id = $tag->id;

        // Act
        $tag->delete();

        // Assert
        $this->assertSoftDeleted('tags', ['id' => $id]);
        $this->assertNotNull(Tag::withTrashed()->find($id)->deleted_at);
    }
}

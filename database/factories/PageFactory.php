<?php

namespace Database\Factories;

use App\Domains\PageBuilder\Models\Layout;
use App\Domains\PageBuilder\Models\Page;
use App\Domains\Security\UserManagement\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\PageBuilder\Models\Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'layout_id' => Layout::factory(),
            'data' => ['layout' => 'default', 'sections' => []],
            'published_html' => null,
            'status' => 'draft',
            'meta_title' => null,
            'meta_description' => null,
            'meta_keywords' => null,
            'schema_data' => null,
            'author_id' => User::factory(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => 'published',
            'published_html' => '<div>Published content</div>',
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => 'draft',
            'published_html' => null,
        ]);
    }
}

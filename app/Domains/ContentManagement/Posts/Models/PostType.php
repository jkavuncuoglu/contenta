<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Posts\Models;

use App\Domains\ContentManagement\Posts\Models\Post;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'singular_name',
        'slug',
        'description',
        'icon',
        'public',
        'hierarchical',
        'supports',
        'taxonomies',
        'capabilities',
        'menu_position',
        'menu_icon',
        'show_in_menu',
        'show_in_admin_bar',
        'show_in_nav_menus',
        'show_in_rest',
        'rest_base',
        'rest_controller_class',
        'custom_fields',
        'template_options',
        'is_active',
    ];

    protected $casts = [
        'public' => 'boolean',
        'hierarchical' => 'boolean',
        'supports' => 'array',
        'taxonomies' => 'array',
        'capabilities' => 'array',
        'show_in_menu' => 'boolean',
        'show_in_admin_bar' => 'boolean',
        'show_in_nav_menus' => 'boolean',
        'show_in_rest' => 'boolean',
        'custom_fields' => 'array',
        'template_options' => 'array',
        'is_active' => 'boolean',
        'menu_position' => 'integer',
    ];

    /**
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Check if post type supports a feature
     */
    public function supports(string $feature): bool
    {
        return in_array($feature, $this->supports ?? []);
    }

    /**
     * Check if post type has a taxonomy
     */
    public function hasTaxonomy(string $taxonomy): bool
    {
        return in_array($taxonomy, $this->taxonomies ?? []);
    }

    /**
     * Get template options for this post type
     *
     * @return array<string, mixed>
     */
    public function getTemplateOptions(): array
    {
        return $this->template_options ?? [];
    }

    /**
     * Default post types seeder data
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getDefaultTypes(): array
    {
        return [
            [
                'name' => 'Posts',
                'singular_name' => 'Post',
                'slug' => 'post',
                'description' => 'Standard blog posts',
                'icon' => 'document-text',
                'public' => true,
                'hierarchical' => false,
                'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'comments', 'revisions', 'author', 'tags', 'categories'],
                'taxonomies' => ['category', 'post_tag'],
                'show_in_menu' => true,
                'show_in_admin_bar' => true,
                'show_in_nav_menus' => true,
                'show_in_rest' => true,
                'rest_base' => 'posts',
                'menu_position' => 5,
                'menu_icon' => 'document-text',
                'is_active' => true,
            ],
            [
                'name' => 'Pages',
                'singular_name' => 'Page',
                'slug' => 'page',
                'description' => 'Static pages',
                'icon' => 'document',
                'public' => true,
                'hierarchical' => true,
                'supports' => ['title', 'editor', 'thumbnail', 'revisions', 'author', 'custom-fields'],
                'taxonomies' => [],
                'show_in_menu' => true,
                'show_in_admin_bar' => true,
                'show_in_nav_menus' => true,
                'show_in_rest' => true,
                'rest_base' => 'pages',
                'menu_position' => 20,
                'menu_icon' => 'document',
                'is_active' => true,
            ],
            [
                'name' => 'Products',
                'singular_name' => 'Product',
                'slug' => 'product',
                'description' => 'E-commerce products',
                'icon' => 'shopping-bag',
                'public' => true,
                'hierarchical' => false,
                'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'comments', 'custom-fields'],
                'taxonomies' => ['product_category', 'product_tag'],
                'show_in_menu' => true,
                'show_in_admin_bar' => true,
                'show_in_nav_menus' => true,
                'show_in_rest' => true,
                'rest_base' => 'products',
                'menu_position' => 25,
                'menu_icon' => 'shopping-bag',
                'is_active' => false,
            ],
        ];
    }
}

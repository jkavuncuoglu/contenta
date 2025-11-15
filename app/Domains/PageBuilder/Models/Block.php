<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @use HasFactory<\Database\Factories\PageBuilder\BlockFactory>
 */
class Block extends Model
{
    use HasFactory;

    protected $table = 'pagebuilder_blocks';

    protected $fillable = [
        'name',
        'type',
        'category',
        'config_schema',
        'component_path',
        'preview_image',
        'description',
        'is_active',
    ];

    protected $casts = [
        'config_schema' => 'array',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
        'category' => 'general',
        'config_schema' => '{}',
    ];

    // Block categories
    const CATEGORY_GENERAL = 'general';

    const CATEGORY_LAYOUT = 'layout';

    const CATEGORY_CONTENT = 'content';

    const CATEGORY_MEDIA = 'media';

    const CATEGORY_FORMS = 'forms';

    const CATEGORY_NAVIGATION = 'navigation';

    /**
     * Scope for active blocks
     *
     * @param  Builder<Block>  $query
     * @return Builder<Block>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by category
     *
     * @param  Builder<Block>  $query
     * @return Builder<Block>
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Get available categories
     *
     * @return array<string, string>
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_GENERAL => 'General',
            self::CATEGORY_LAYOUT => 'Layout',
            self::CATEGORY_CONTENT => 'Content',
            self::CATEGORY_MEDIA => 'Media',
            self::CATEGORY_FORMS => 'Forms',
            self::CATEGORY_NAVIGATION => 'Navigation',
        ];
    }

    /**
     * Get default configuration
     *
     * @return array<string, mixed>
     */
    public function getDefaultConfigAttribute(): array
    {
        $schema = $this->config_schema;
        $defaults = [];

        foreach ($schema as $field => $config) {
            $defaults[$field] = $config['default'] ?? null;
        }

        return $defaults;
    }

    /**
     * Validate configuration against schema
     *
     * @param  array<string, mixed>  $config
     * @return array<string, string>
     */
    public function validateConfig(array $config): array
    {
        $errors = [];
        $schema = $this->config_schema;

        foreach ($schema as $field => $fieldSchema) {
            $required = $fieldSchema['required'] ?? false;
            $type = $fieldSchema['type'] ?? 'string';

            if ($required && ! isset($config[$field])) {
                $errors[$field] = "Field {$field} is required";

                continue;
            }

            if (isset($config[$field])) {
                $value = $config[$field];

                switch ($type) {
                    case 'string':
                        if (! is_string($value)) {
                            $errors[$field] = "Field {$field} must be a string";
                        }
                        break;
                    case 'number':
                        if (! is_numeric($value)) {
                            $errors[$field] = "Field {$field} must be a number";
                        }
                        break;
                    case 'boolean':
                        if (! is_bool($value)) {
                            $errors[$field] = "Field {$field} must be a boolean";
                        }
                        break;
                    case 'array':
                        if (! is_array($value)) {
                            $errors[$field] = "Field {$field} must be an array";
                        }
                        break;
                }
            }
        }

        return $errors;
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content_markdown');
            $table->longText('content_html')->nullable();
            $table->json('table_of_contents')->nullable();
            $table->text('excerpt')->nullable();
            $table->enum('status', ['draft', 'published', 'scheduled', 'private', 'trash'])->default('draft');

            // Relationships
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('posts')->cascadeOnDelete();
            $table->foreignId('featured_image_id')->nullable()->constrained('media')->nullOnDelete();

            // Publishing
            $table->timestamp('published_at')->nullable();

            // SEO fields
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('open_graph_title')->nullable();
            $table->text('open_graph_description')->nullable();
            $table->string('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->json('structured_data')->nullable();

            // Custom fields and metadata
            $table->json('custom_fields')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('comment_count')->default(0);
            $table->unsignedInteger('like_count')->default(0);
            $table->unsignedInteger('reading_time_minutes')->default(0);

            // Localization
            $table->string('language', 5)->default('en');

            // Template
            $table->string('template')->nullable();

            // Versioning
            $table->unsignedInteger('version')->default(1);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['status', 'published_at']);
            $table->index(['author_id', 'status']);
            $table->index(['slug']);
            $table->index(['parent_id']);
            // $table->fullText(['title', 'content_markdown', 'excerpt']); // SQLite doesn't support fulltext
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};

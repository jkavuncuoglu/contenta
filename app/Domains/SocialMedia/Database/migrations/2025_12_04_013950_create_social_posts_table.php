<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('social_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_account_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('source_type', 20)->index(); // manual or auto_blog_post
            $table->unsignedBigInteger('source_blog_post_id')->nullable()->index();

            $table->text('content');
            $table->json('media_urls')->nullable();
            $table->string('link_url', 500)->nullable();

            $table->string('status', 20)->index(); // draft, scheduled, publishing, published, failed, cancelled
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('published_at')->nullable();

            $table->string('platform_post_id')->nullable();
            $table->string('platform_permalink', 500)->nullable();

            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Foreign key for blog post (don't use constrained to avoid circular dependencies)
            $table->foreign('source_blog_post_id')
                ->references('id')
                ->on('posts')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_posts');
    }
};

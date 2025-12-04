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
        Schema::create('blog_post_social_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('blog_post_id')->index();
            $table->foreignId('social_account_id')->constrained()->onDelete('cascade');

            $table->string('status', 20)->index(); // pending, scheduled, posted, cancelled, failed
            $table->timestamp('scheduled_for')->index();
            $table->text('generated_content')->nullable();

            $table->unsignedBigInteger('social_post_id')->nullable();
            $table->boolean('has_manual_override')->default(false);

            $table->timestamps();

            // Foreign keys
            $table->foreign('blog_post_id')
                ->references('id')
                ->on('posts')
                ->onDelete('cascade');

            $table->foreign('social_post_id')
                ->references('id')
                ->on('social_posts')
                ->onDelete('set null');

            // Unique constraint: one queue entry per blog post per account
            $table->unique(['blog_post_id', 'social_account_id'], 'blog_social_queue_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_post_social_queue');
    }
};

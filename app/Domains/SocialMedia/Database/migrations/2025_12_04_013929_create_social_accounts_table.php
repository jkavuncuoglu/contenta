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
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('platform', 50)->index(); // twitter, facebook, linkedin, instagram, pinterest, tiktok
            $table->string('platform_account_id');
            $table->string('platform_username')->nullable();
            $table->string('platform_display_name')->nullable();
            $table->text('access_token')->nullable(); // Encrypted
            $table->text('refresh_token')->nullable(); // Encrypted
            $table->timestamp('token_expires_at')->nullable()->index();
            $table->boolean('auto_post_enabled')->default(false);
            $table->string('auto_post_mode', 20)->default('immediate'); // immediate or scheduled
            $table->time('scheduled_post_time')->nullable(); // e.g., 09:00:00
            $table->json('platform_settings')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Unique constraint: one account per user per platform per platform account
            $table->unique(['user_id', 'platform', 'platform_account_id'], 'social_accounts_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};

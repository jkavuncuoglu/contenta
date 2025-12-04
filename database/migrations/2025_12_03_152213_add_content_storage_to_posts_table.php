<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add ContentStorage fields to posts table to enable multi-backend storage
     * Make content_markdown and content_html nullable for cloud storage
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Make content fields nullable (content stored in ContentStorage backends)
            $table->longText('content_markdown')->nullable()->change();
            $table->longText('content_html')->nullable()->change();

            // ContentStorage fields
            $table->string('storage_driver')->default('database')->after('content_html');
            $table->string('storage_path')->nullable()->after('storage_driver');

            // Add index for storage queries
            $table->index(['storage_driver', 'storage_path']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Drop ContentStorage fields
            $table->dropIndex(['storage_driver', 'storage_path']);
            $table->dropColumn(['storage_driver', 'storage_path']);

            // Revert content fields to NOT NULL (requires data to exist)
            $table->longText('content_markdown')->nullable(false)->change();
            $table->longText('content_html')->nullable(false)->change();
        });
    }
};

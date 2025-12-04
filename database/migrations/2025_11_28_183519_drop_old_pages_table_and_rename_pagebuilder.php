<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the old/empty pages table if it exists
        Schema::dropIfExists('pages');

        // Now rename pagebuilder_pages to pages
        if (Schema::hasTable('pagebuilder_pages')) {
            Schema::rename('pagebuilder_pages', 'pages');
        }

        // Rename pagebuilder_page_revisions to page_revisions
        if (Schema::hasTable('pagebuilder_page_revisions')) {
            Schema::rename('pagebuilder_page_revisions', 'page_revisions');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename back to original names
        if (Schema::hasTable('pages')) {
            Schema::rename('pages', 'pagebuilder_pages');
        }

        if (Schema::hasTable('page_revisions')) {
            Schema::rename('page_revisions', 'pagebuilder_page_revisions');
        }

        // Recreate the old simple pages table
        Schema::create('pages', function ($table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->boolean('published')->default(false);
            $table->timestamps();
        });
    }
};

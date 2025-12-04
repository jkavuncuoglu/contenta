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
        Schema::table('pagebuilder_page_revisions', function (Blueprint $table) {
            // Add markdown content field
            $table->longText('markdown_content')->nullable()->after('data');

            // Add content type
            $table->enum('content_type', ['legacy', 'markdown'])
                ->default('legacy')
                ->after('data');

            // Add layout template name
            $table->string('layout_template')->nullable()->after('layout_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagebuilder_page_revisions', function (Blueprint $table) {
            $table->dropColumn(['markdown_content', 'content_type', 'layout_template']);
        });
    }
};

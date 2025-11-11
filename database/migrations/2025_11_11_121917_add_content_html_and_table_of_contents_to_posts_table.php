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
        Schema::table('posts', function (Blueprint $table) {
            // Make content_html nullable if it exists and is not nullable
            if (Schema::hasColumn('posts', 'content_html')) {
                $table->longText('content_html')->nullable()->change();
            }

            // Add table_of_contents column if it doesn't exist
            if (!Schema::hasColumn('posts', 'table_of_contents')) {
                $table->json('table_of_contents')->nullable()->after('content_html');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Make content_html NOT nullable again
            if (Schema::hasColumn('posts', 'content_html')) {
                $table->longText('content_html')->nullable(false)->change();
            }

            // Drop table_of_contents column
            if (Schema::hasColumn('posts', 'table_of_contents')) {
                $table->dropColumn('table_of_contents');
            }
        });
    }
};


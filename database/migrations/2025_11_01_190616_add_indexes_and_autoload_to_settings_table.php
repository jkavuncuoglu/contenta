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
        Schema::table('settings', function (Blueprint $table) {
            // Add autoload column if it doesn't exist
            if (!Schema::hasColumn('settings', 'autoload')) {
                $table->boolean('autoload')->default(true)->after('description');
            }

            // Add optimized indexes
            $table->index(['group', 'key'], 'settings_group_key_index');
            $table->index(['autoload', 'group'], 'settings_autoload_group_index');
            $table->index('group', 'settings_group_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex('settings_group_key_index');
            $table->dropIndex('settings_autoload_group_index');
            $table->dropIndex('settings_group_index');

            // Drop autoload column if we added it
            if (Schema::hasColumn('settings', 'autoload')) {
                $table->dropColumn('autoload');
            }
        });
    }
};

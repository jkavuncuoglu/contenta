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
        Schema::table('social_accounts', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['user_id']);
        });

        Schema::table('social_accounts', function (Blueprint $table) {
            // Drop the unique constraint
            $table->dropUnique('social_accounts_unique');
        });

        Schema::table('social_accounts', function (Blueprint $table) {
            // Drop the user_id column
            $table->dropColumn('user_id');

            // Add new unique constraint without user_id
            $table->unique(['platform', 'platform_account_id'], 'social_accounts_platform_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('social_accounts', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique('social_accounts_platform_unique');

            // Re-add user_id column
            $table->foreignId('user_id')->after('id')->constrained()->onDelete('cascade');

            // Re-add the original unique constraint
            $table->unique(['user_id', 'platform', 'platform_account_id'], 'social_accounts_unique');
        });
    }
};

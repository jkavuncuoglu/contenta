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
        Schema::table('users', function (Blueprint $table) {
            $table->json('two_factor_used_recovery_codes')->after('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_recovery_codes_viewed_at')->after('two_factor_used_recovery_codes')->nullable();
            $table->string('recovery_codes_regeneration_token')->after('two_factor_recovery_codes_viewed_at')->nullable();
            $table->timestamp('recovery_codes_regeneration_expires_at')->after('recovery_codes_regeneration_token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_used_recovery_codes',
                'two_factor_recovery_codes_viewed_at',
                'recovery_codes_regeneration_token',
                'recovery_codes_regeneration_expires_at',
            ]);
        });
    }
};

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
        Schema::create('theme_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('default');
            $table->boolean('is_active')->default(true);

            // Light theme colors
            $table->string('light_primary')->default('#3B82F6');
            $table->string('light_secondary')->default('#8B5CF6');
            $table->string('light_accent')->default('#10B981');
            $table->string('light_background')->default('#FFFFFF');
            $table->string('light_surface')->default('#F9FAFB');
            $table->string('light_text')->default('#1F2937');
            $table->string('light_text_secondary')->default('#6B7280');

            // Dark theme colors
            $table->string('dark_primary')->default('#60A5FA');
            $table->string('dark_secondary')->default('#A78BFA');
            $table->string('dark_accent')->default('#34D399');
            $table->string('dark_background')->default('#111827');
            $table->string('dark_surface')->default('#1F2937');
            $table->string('dark_text')->default('#F9FAFB');
            $table->string('dark_text_secondary')->default('#D1D5DB');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('theme_settings');
    }
};

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
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Unique theme identifier (slug)
            $table->string('display_name'); // Human-readable name
            $table->text('description')->nullable();
            $table->string('version')->default('1.0.0');
            $table->string('author')->nullable();
            $table->string('screenshot')->nullable(); // Relative path to screenshot
            $table->boolean('is_active')->default(false);
            $table->string('path'); // Relative path from storage/app/themes/
            $table->json('metadata')->nullable(); // Additional theme data (tags, license, homepage, etc.)
            $table->timestamps();

            // Only one theme can be active at a time
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};

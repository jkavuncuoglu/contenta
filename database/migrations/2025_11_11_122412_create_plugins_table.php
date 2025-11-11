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
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('version');
            $table->string('author')->nullable();
            $table->string('author_url')->nullable();
            $table->json('metadata')->nullable();
            $table->string('entry_point')->nullable(); // Main plugin file
            $table->boolean('is_enabled')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('scanned_at')->nullable();
            $table->json('scan_results')->nullable();
            $table->timestamp('installed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugins');
    }
};

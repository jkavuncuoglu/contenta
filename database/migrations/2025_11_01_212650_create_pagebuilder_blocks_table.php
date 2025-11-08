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
        Schema::create('pagebuilder_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->unique();
            $table->string('category')->default('general');
            $table->json('config_schema');
            $table->string('component_path');
            $table->string('preview_image')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'category']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagebuilder_blocks');
    }
};

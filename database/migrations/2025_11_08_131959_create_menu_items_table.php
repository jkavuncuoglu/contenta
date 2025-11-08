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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->onDelete('cascade');
            $table->string('type')->default('custom'); // custom, page, post, category, tag, etc.
            $table->string('title');
            $table->string('url')->nullable();
            $table->foreignId('object_id')->nullable(); // ID of related object (page, post, etc.)
            $table->string('object_type')->nullable(); // page, post, category, etc.
            $table->string('target')->default('_self'); // _self, _blank, etc.
            $table->string('css_classes')->nullable();
            $table->string('icon')->nullable(); // icon class or SVG
            $table->integer('order')->default(0);
            $table->json('attributes')->nullable(); // additional HTML attributes
            $table->json('metadata')->nullable(); // extensible metadata
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['menu_id', 'parent_id']);
            $table->index(['menu_id', 'order']);
            $table->index('type');
            $table->index(['object_type', 'object_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};

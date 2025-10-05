<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('singular_name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('public')->default(true);
            $table->boolean('hierarchical')->default(false);
            $table->json('supports')->nullable(); // features this post type supports
            $table->json('taxonomies')->nullable(); // associated taxonomies
            $table->json('capabilities')->nullable(); // user capabilities required
            $table->integer('menu_position')->nullable();
            $table->string('menu_icon')->nullable();
            $table->boolean('show_in_menu')->default(true);
            $table->boolean('show_in_admin_bar')->default(true);
            $table->boolean('show_in_nav_menus')->default(true);
            $table->boolean('show_in_rest')->default(true);
            $table->string('rest_base')->nullable();
            $table->string('rest_controller_class')->nullable();
            $table->json('custom_fields')->nullable();
            $table->json('template_options')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_types');
    }
};
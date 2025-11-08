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
        Schema::create('pagebuilder_page_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pagebuilder_pages')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->string('slug');
            $table->foreignId('layout_id')->nullable()->constrained('pagebuilder_layouts')->onDelete('set null');
            $table->json('data');
            $table->string('status');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->json('schema_data')->nullable();
            $table->text('reason')->nullable(); // Optional reason for revision
            $table->integer('revision_number');
            $table->timestamps();

            $table->index(['page_id', 'created_at']);
            $table->index('revision_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagebuilder_page_revisions');
    }
};

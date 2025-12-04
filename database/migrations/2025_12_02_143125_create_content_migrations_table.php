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
        Schema::create('content_migrations', function (Blueprint $table) {
            $table->id();
            $table->string('content_type');        // pages|posts
            $table->string('from_driver');         // source driver name
            $table->string('to_driver');           // destination driver name
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->integer('total_items')->default(0);
            $table->integer('migrated_items')->default(0);
            $table->integer('failed_items')->default(0);
            $table->json('error_log')->nullable();  // Failed item details
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes for common queries
            $table->index(['content_type', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_migrations');
    }
};

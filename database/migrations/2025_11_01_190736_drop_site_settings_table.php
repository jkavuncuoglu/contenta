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
        Schema::dropIfExists('site_settings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // If needed, recreate the old table structure
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('type', 20)->default('string');
            $table->text('description')->nullable();
            $table->boolean('autoload')->default(true);
            $table->timestamps();
        });
    }
};

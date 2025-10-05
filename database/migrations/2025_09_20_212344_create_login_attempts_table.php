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
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('username_attempted')->nullable();
            $table->ipAddress('ip_address');
            $table->string('device_fingerprint')->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('success')->default(false);
            $table->integer('failed_attempts')->default(0);
            $table->integer('escalation_level')->default(0); // 0=none, 1=1h, 2=2h, 3=4h, 4=8h, 5=permanent
            $table->timestamp('blocked_until')->nullable();
            $table->boolean('permanent_block')->default(false);
            $table->json('metadata')->nullable(); // Additional tracking data
            $table->timestamps();

            // Indexes for performance
            $table->index(['ip_address', 'created_at']);
            $table->index(['username_attempted', 'ip_address']);
            $table->index(['device_fingerprint', 'created_at']);
            $table->index(['blocked_until']);
            $table->index(['permanent_block']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};

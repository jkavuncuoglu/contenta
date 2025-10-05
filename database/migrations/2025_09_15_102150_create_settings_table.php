<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json, etc.
            $table->string('group')->default('general'); // General, Blog, Security, etc.
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('settings')->insert([
            // Blog Settings
            [
                'key' => 'blog_slug',
                'value' => 'blog',
                'type' => 'string',
                'group' => 'blog',
                'description' => 'The URL slug for the blog section',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'primary_landing_page',
                'value' => 'blog',
                'type' => 'string',
                'group' => 'blog',
                'description' => 'The primary landing page for the site',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Google Services
            [
                'key' => 'google_analytics_id',
                'value' => null,
                'type' => 'string',
                'group' => 'analytics',
                'description' => 'Google Analytics tracking ID',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'google_tag_manager_id',
                'value' => null,
                'type' => 'string',
                'group' => 'analytics',
                'description' => 'Google Tag Manager container ID',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // reCAPTCHA
            [
                'key' => 'recaptcha_site_key',
                'value' => null,
                'type' => 'string',
                'group' => 'security',
                'description' => 'Google reCAPTCHA site key',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'recaptcha_secret_key',
                'value' => null,
                'type' => 'string',
                'group' => 'security',
                'description' => 'Google reCAPTCHA secret key',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'recaptcha_enabled',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'security',
                'description' => 'Enable/disable reCAPTCHA',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Honeypot
            [
                'key' => 'honeypot_field_name',
                'value' => 'honeypot',
                'type' => 'string',
                'group' => 'security',
                'description' => 'Honeypot field name for forms',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'honeypot_timer_field_name',
                'value' => 'honeypot_timer',
                'type' => 'string',
                'group' => 'security',
                'description' => 'Honeypot timer field name for forms',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'honeypot_minimum_time',
                'value' => '3',
                'type' => 'integer',
                'group' => 'security',
                'description' => 'Minimum time in seconds for form submission to be valid',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'honeypot_enabled',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'security',
                'description' => 'Enable/disable honeypot spam protection',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

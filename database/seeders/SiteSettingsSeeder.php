<?php

namespace Database\Seeders;

use App\Domains\Settings\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultSettings = [
            [
                'key' => 'site_title',
                'value' => 'Contenta',
                'type' => 'string',
                'description' => 'The title of your website',
                'autoload' => true,
            ],
            [
                'key' => 'site_tagline',
                'value' => 'A modern content management system',
                'type' => 'string',
                'description' => 'In a few words, explain what this site is about',
                'autoload' => true,
            ],
            [
                'key' => 'site_url',
                'value' => config('app.url'),
                'type' => 'string',
                'description' => 'The URL of your website',
                'autoload' => true,
            ],
            [
                'key' => 'admin_email',
                'value' => 'admin@contenta.local',
                'type' => 'string',
                'description' => 'This address is used for admin purposes',
                'autoload' => true,
            ],
            [
                'key' => 'users_can_register',
                'value' => true,
                'type' => 'boolean',
                'description' => 'Allow new user registration',
                'autoload' => true,
            ],
            [
                'key' => 'default_user_role',
                'value' => 'subscriber',
                'type' => 'string',
                'description' => 'Default role for new users',
                'autoload' => true,
            ],
            [
                'key' => 'site_language',
                'value' => 'en_US',
                'type' => 'string',
                'description' => 'Site language',
                'autoload' => true,
            ],
            [
                'key' => 'timezone',
                'value' => 'UTC',
                'type' => 'string',
                'description' => 'Site timezone',
                'autoload' => true,
            ],
            [
                'key' => 'date_format',
                'value' => 'F j, Y',
                'type' => 'string',
                'description' => 'Default date format',
                'autoload' => true,
            ],
            [
                'key' => 'time_format',
                'value' => 'g:i a',
                'type' => 'string',
                'description' => 'Default time format',
                'autoload' => true,
            ],
        ];

        foreach ($defaultSettings as $setting) {
            SiteSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}

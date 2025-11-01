<?php

declare(strict_types=1);

namespace App\Domains\Settings\Services;

use App\Domains\Settings\Models\Setting;
use App\Domains\ContentManagement\Pages\Models\Page;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class SiteSettingsService implements SiteSettingsServiceContract
{
    /**
     * Get all site settings
     */
    public function getAllSettings(): array
    {
        return Setting::getGroup('site');
    }

    /**
     * Get a specific setting
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return Setting::get('site', $key, $default);
    }

    /**
     * Update multiple settings
     */
    public function updateSettings(array $settings): bool
    {
        try {
            DB::beginTransaction();

            foreach ($settings as $key => $value) {
                // Convert key to site_ prefixed format
                $siteKey = str_starts_with($key, 'site_') ? $key : "site_{$key}";

                Setting::set(
                    'site',
                    $siteKey,
                    $value,
                    $this->inferType($value),
                    ucfirst(str_replace(['site_', '_'], ['', ' '], $siteKey))
                );
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Get available languages
     */
    public function getAvailableLanguages(): array
    {
        return [
            'en_US' => 'English (United States)',
            'en_GB' => 'English (United Kingdom)',
            'es_ES' => 'Español',
            'fr_FR' => 'Français',
            'de_DE' => 'Deutsch',
            'it_IT' => 'Italiano',
            'pt_BR' => 'Português (Brasil)',
            'nl_NL' => 'Nederlands',
            'ru_RU' => 'Русский',
            'ja_JP' => '日本語',
            'zh_CN' => '中文 (简体)',
            'ar_SA' => 'العربية',
        ];
    }

    /**
     * Get available timezones
     */
    public function getAvailableTimezones(): array
    {
        $timezones = [];

        foreach (timezone_identifiers_list() as $timezone) {
            $offset = timezone_offset_get(new \DateTimeZone($timezone), new \DateTime());
            $offsetHours = $offset / 3600;
            $offsetFormatted = sprintf('%+03d:%02d', floor($offsetHours), abs($offset % 3600) / 60);

            $timezones[$timezone] = sprintf('(UTC%s) %s', $offsetFormatted, $timezone);
        }

        // Sort by offset first, then by name
        uasort($timezones, function($a, $b) {
            return strcmp($a, $b);
        });

        return $timezones;
    }

    /**
     * Get available user roles
     */
    public function getAvailableUserRoles(): array
    {
        try {
            return Role::all()
                ->pluck('name', 'name')
                ->toArray();
        } catch (\Exception $e) {
            // Fallback if Spatie roles are not set up
            return [
                'subscriber' => 'Subscriber',
                'contributor' => 'Contributor',
                'author' => 'Author',
                'editor' => 'Editor',
                'administrator' => 'Administrator',
            ];
        }
    }

    /**
     * Get available pages for landing page selection
     */
    public function getAvailablePages(): array
    {
        try {
            $pages = Page::where('status', 'published')
                ->orderBy('title')
                ->get(['id', 'title', 'slug'])
                ->mapWithKeys(function ($page) {
                    return [$page->id => $page->title];
                })
                ->toArray();

            // Add blog option
            $pages = ['blog' => 'Blog (Latest Posts)'] + $pages;

            return $pages;
        } catch (\Exception $e) {
            // Fallback if pages table doesn't exist
            return [
                'blog' => 'Blog (Latest Posts)',
            ];
        }
    }

    /**
     * Infer the type of a value
     */
    private function inferType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_array($value) => 'json',
            default => 'string',
        };
    }
}
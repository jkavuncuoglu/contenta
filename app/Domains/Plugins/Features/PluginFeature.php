<?php

namespace App\Domains\Plugins\Features;

use App\Domains\Plugins\Models\Plugin;

class PluginFeature
{
    /**
     * Resolve whether a plugin feature should be enabled.
     *
     * @param  mixed  $scope
     * @param  string  $slug
     * @return bool
     */
    public static function resolve($scope, string $slug): bool
    {
        $plugin = Plugin::where('slug', $slug)->first();

        if (!$plugin) {
            return false;
        }

        // Plugin must be enabled, verified, and have no security issues
        return $plugin->is_enabled
            && $plugin->is_verified
            && !$plugin->hasSecurityIssues();
    }

    /**
     * Check if a plugin is available for activation.
     *
     * @param  string  $slug
     * @return bool
     */
    public static function isAvailable(string $slug): bool
    {
        $plugin = Plugin::where('slug', $slug)->first();

        if (!$plugin) {
            return false;
        }

        return $plugin->is_verified && !$plugin->hasSecurityIssues();
    }
}

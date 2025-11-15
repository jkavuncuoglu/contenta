<?php

declare(strict_types=1);

namespace App\Domains\Plugins\Services;

use App\Domains\Plugins\Models\Plugin;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class PluginService
{
    public function __construct(
        private PluginSecurityScanner $scanner
    ) {}

    /**
     * Get all plugins
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Plugin>
     */
    public function getAllPlugins()
    {
        return Plugin::orderBy('name')->get();
    }

    /**
     * Get enabled plugins
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Plugin>
     */
    public function getEnabledPlugins()
    {
        return Plugin::where('is_enabled', true)->get();
    }

    /**
     * Install plugin from uploaded zip file
     *
     * @param  string  $zipPath  Path to uploaded zip file
     * @return array{success: bool, message: string, plugin?: Plugin, scan_results?: array<string, mixed>}
     */
    public function installFromZip(string $zipPath): array
    {
        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== true) {
            return ['success' => false, 'message' => 'Failed to open zip file'];
        }

        // Extract plugin.json to read metadata
        $pluginJson = $zip->getFromName('plugin.json');
        if (! $pluginJson) {
            $zip->close();

            return ['success' => false, 'message' => 'Invalid plugin: plugin.json not found'];
        }

        $metadata = json_decode($pluginJson, true);
        if (! $metadata || ! isset($metadata['slug'], $metadata['name'], $metadata['version'])) {
            $zip->close();

            return ['success' => false, 'message' => 'Invalid plugin.json format'];
        }

        $slug = $metadata['slug'];

        // Check if plugin already exists
        $existingPlugin = Plugin::where('slug', $slug)->first();
        if ($existingPlugin) {
            $zip->close();

            return ['success' => false, 'message' => 'Plugin already installed'];
        }

        // Create plugin directory
        $pluginDir = storage_path("app/plugins/{$slug}");
        if (! File::exists($pluginDir)) {
            File::makeDirectory($pluginDir, 0755, true);
        }

        // Extract plugin files
        $zip->extractTo($pluginDir);
        $zip->close();

        // Scan for security issues
        $scanResults = $this->scanner->scan($pluginDir);

        // If threats found, delete plugin and reject installation
        if (! $scanResults['safe']) {
            File::deleteDirectory($pluginDir);

            return [
                'success' => false,
                'message' => 'Plugin rejected: Security threats detected',
                'scan_results' => $scanResults,
            ];
        }

        // Create plugin record
        $plugin = Plugin::create([
            'slug' => $slug,
            'name' => $metadata['name'],
            'description' => $metadata['description'] ?? null,
            'version' => $metadata['version'],
            'author' => $metadata['author'] ?? null,
            'author_url' => $metadata['author_url'] ?? null,
            'metadata' => $metadata,
            'entry_point' => $metadata['entry_point'] ?? 'plugin.php',
            'plugin_type' => $metadata['plugin_type'] ?? Plugin::TYPE_UNIVERSAL,
            'is_verified' => $scanResults['safe'],
            'scanned_at' => now(),
            'scan_results' => $scanResults,
            'installed_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'Plugin installed successfully',
            'plugin' => $plugin,
            'scan_results' => $scanResults,
        ];
    }

    /**
     * Scan existing plugin directory
     */
    public function scanPlugin(string $slug): array
    {
        $plugin = Plugin::where('slug', $slug)->firstOrFail();
        $pluginDir = $plugin->getDirectoryPath();

        $scanResults = $this->scanner->scan($pluginDir);

        $plugin->update([
            'is_verified' => $scanResults['safe'],
            'scanned_at' => now(),
            'scan_results' => $scanResults,
        ]);

        return $scanResults;
    }

    /**
     * Enable a plugin
     */
    public function enable(string $slug): bool
    {
        $plugin = Plugin::where('slug', $slug)->firstOrFail();

        if (! $plugin->is_verified) {
            throw new \Exception('Cannot enable unverified plugin');
        }

        if ($plugin->hasSecurityIssues()) {
            throw new \Exception('Cannot enable plugin with security issues');
        }

        $plugin->update(['is_enabled' => true]);

        return true;
    }

    /**
     * Disable a plugin
     */
    public function disable(string $slug): bool
    {
        $plugin = Plugin::where('slug', $slug)->firstOrFail();
        $plugin->update(['is_enabled' => false]);

        return true;
    }

    /**
     * Uninstall a plugin
     */
    public function uninstall(string $slug): bool
    {
        $plugin = Plugin::where('slug', $slug)->firstOrFail();

        // Disable first if enabled
        if ($plugin->is_enabled) {
            $this->disable($slug);
        }

        // Delete plugin directory
        $pluginDir = $plugin->getDirectoryPath();
        if (File::exists($pluginDir)) {
            File::deleteDirectory($pluginDir);
        }

        // Delete plugin record
        $plugin->delete();

        return true;
    }

    /**
     * Scan all installed plugins for new threats
     */
    public function scanAllPlugins(): array
    {
        $results = [];

        foreach (Plugin::all() as $plugin) {
            $results[$plugin->slug] = $this->scanPlugin($plugin->slug);
        }

        return $results;
    }

    /**
     * Discover and register plugins from storage directory
     */
    public function discoverPlugins(): array
    {
        $discovered = [];
        $pluginsDir = storage_path('app/plugins');

        if (! File::exists($pluginsDir)) {
            File::makeDirectory($pluginsDir, 0755, true);

            return $discovered;
        }

        $directories = File::directories($pluginsDir);

        foreach ($directories as $dir) {
            $folderName = basename($dir);
            $pluginJsonPath = $dir.'/plugin.json';

            if (! File::exists($pluginJsonPath)) {
                continue;
            }

            $metadata = json_decode(File::get($pluginJsonPath), true);
            if (! $metadata || ! isset($metadata['name'], $metadata['version'])) {
                continue;
            }

            // Get slug from metadata (not folder name!)
            $pluginSlug = $metadata['slug'] ?? $folderName;

            // Check if plugin with this slug already exists in database
            $existingPlugin = Plugin::where('slug', $pluginSlug)->first();

            if ($existingPlugin) {
                // Skip if this is the normal installed plugin folder
                if ($folderName === $existingPlugin->slug) {
                    continue;
                }

                // This is a duplicate - don't register it
                $discovered[] = [
                    'folder_name' => $folderName,
                    'slug' => $pluginSlug,
                    'status' => 'skipped',
                    'reason' => 'Duplicate plugin detected (slug already exists in database)',
                ];

                continue;
            }

            // Scan for security
            $scanResults = $this->scanner->scan($dir);

            if (! $scanResults['safe']) {
                $discovered[] = [
                    'folder_name' => $folderName,
                    'slug' => $pluginSlug,
                    'status' => 'rejected',
                    'reason' => 'Security threats detected',
                ];

                continue;
            }

            // Create plugin record using the slug from metadata
            $plugin = Plugin::create([
                'slug' => $pluginSlug,
                'name' => $metadata['name'],
                'description' => $metadata['description'] ?? null,
                'version' => $metadata['version'],
                'author' => $metadata['author'] ?? null,
                'author_url' => $metadata['author_url'] ?? null,
                'metadata' => $metadata,
                'entry_point' => $metadata['entry_point'] ?? 'plugin.php',
                'plugin_type' => $metadata['plugin_type'] ?? Plugin::TYPE_UNIVERSAL,
                'is_verified' => $scanResults['safe'],
                'scanned_at' => now(),
                'scan_results' => $scanResults,
                'installed_at' => now(),
            ]);

            $discovered[] = [
                'folder_name' => $folderName,
                'slug' => $pluginSlug,
                'status' => 'registered',
                'plugin' => $plugin,
            ];
        }

        return $discovered;
    }

    /**
     * Get uninstalled plugins available in storage
     *
     * @return array<int, array<string, mixed>>
     */
    public function getUninstalledPlugins(): array
    {
        $uninstalled = [];
        $pluginsDir = storage_path('app/plugins');

        if (! File::exists($pluginsDir)) {
            File::makeDirectory($pluginsDir, 0755, true);

            return $uninstalled;
        }

        $directories = File::directories($pluginsDir);

        foreach ($directories as $dir) {
            $folderName = basename($dir);
            $pluginJsonPath = $dir.'/plugin.json';

            if (! File::exists($pluginJsonPath)) {
                continue;
            }

            $metadata = json_decode(File::get($pluginJsonPath), true);
            if (! $metadata || ! isset($metadata['name'], $metadata['version'])) {
                continue;
            }

            // Get slug from metadata (not folder name)
            $pluginSlug = $metadata['slug'] ?? $folderName;

            // Check if plugin with this slug is already installed (regardless of folder name)
            $existingPlugin = Plugin::where('slug', $pluginSlug)->first();
            $isDuplicate = $existingPlugin !== null;

            // Skip plugins where folder name matches installed plugin (normal case)
            if ($existingPlugin && $folderName === $existingPlugin->slug) {
                continue;
            }

            // Scan for security
            $scanResults = $this->scanner->scan($dir);

            $uninstalled[] = [
                'folder_name' => $folderName,
                'slug' => $pluginSlug,
                'name' => $metadata['name'],
                'description' => $metadata['description'] ?? null,
                'version' => $metadata['version'],
                'author' => $metadata['author'] ?? null,
                'author_url' => $metadata['author_url'] ?? null,
                'plugin_type' => $metadata['plugin_type'] ?? Plugin::TYPE_UNIVERSAL,
                'is_safe' => $scanResults['safe'],
                'is_duplicate' => $isDuplicate,
                'scan_results' => $scanResults,
                'metadata' => $metadata,
            ];
        }

        return $uninstalled;
    }

    /**
     * Install and enable a plugin from storage directory
     */
    public function installAndEnable(string $slug): array
    {
        $pluginDir = storage_path("app/plugins/{$slug}");
        $pluginJsonPath = $pluginDir.'/plugin.json';

        if (! File::exists($pluginJsonPath)) {
            return ['success' => false, 'message' => 'Plugin not found in storage'];
        }

        $metadata = json_decode(File::get($pluginJsonPath), true);
        if (! $metadata || ! isset($metadata['name'], $metadata['version'])) {
            return ['success' => false, 'message' => 'Invalid plugin.json format'];
        }

        // Check if plugin already exists
        $existingPlugin = Plugin::where('slug', $slug)->first();
        if ($existingPlugin) {
            // If already installed, just enable it
            if (! $existingPlugin->is_enabled) {
                $this->enable($slug);
            }

            return ['success' => true, 'message' => 'Plugin enabled', 'plugin' => $existingPlugin];
        }

        // Scan for security issues
        $scanResults = $this->scanner->scan($pluginDir);

        if (! $scanResults['safe']) {
            return [
                'success' => false,
                'message' => 'Plugin rejected: Security threats detected',
                'scan_results' => $scanResults,
            ];
        }

        // Create plugin record
        $plugin = Plugin::create([
            'slug' => $slug,
            'name' => $metadata['name'],
            'description' => $metadata['description'] ?? null,
            'version' => $metadata['version'],
            'author' => $metadata['author'] ?? null,
            'author_url' => $metadata['author_url'] ?? null,
            'metadata' => $metadata,
            'entry_point' => $metadata['entry_point'] ?? 'plugin.php',
            'plugin_type' => $metadata['plugin_type'] ?? Plugin::TYPE_UNIVERSAL,
            'is_verified' => $scanResults['safe'],
            'is_enabled' => true, // Enable immediately
            'scanned_at' => now(),
            'scan_results' => $scanResults,
            'installed_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'Plugin installed and enabled successfully',
            'plugin' => $plugin,
        ];
    }

    /**
     * Load all enabled plugins based on current context
     */
    public function loadEnabledPlugins(): void
    {
        $plugins = $this->getEnabledPlugins();
        $isAdminContext = $this->isAdminContext();

        foreach ($plugins as $plugin) {
            if (! $this->isPluginActive($plugin->slug)) {
                continue;
            }

            // Skip plugins that don't match the current context
            if ($isAdminContext && ! $plugin->shouldLoadInAdmin()) {
                continue;
            }

            if (! $isAdminContext && ! $plugin->shouldLoadInFrontend()) {
                continue;
            }

            $entryPoint = $plugin->getEntryPointPath();
            if ($entryPoint && File::exists($entryPoint)) {
                require_once $entryPoint;
            }
        }
    }

    /**
     * Determine if we're currently in the admin context
     */
    protected function isAdminContext(): bool
    {
        if (app()->runningInConsole()) {
            return false;
        }

        $request = request();

        // Check if current route starts with /admin
        return str_starts_with($request->path(), 'admin');
    }

    /**
     * Check if a plugin is active
     */
    public function isPluginActive(string $slug): bool
    {
        $plugin = Plugin::where('slug', $slug)->first();

        if (! $plugin) {
            return false;
        }

        return $plugin->is_enabled && $plugin->is_verified && ! $plugin->hasSecurityIssues();
    }

    /**
     * Delete an uninstalled plugin folder
     *
     * @param  string  $folderName  The folder name in storage/app/plugins/
     */
    public function deleteUninstalledPlugin(string $folderName): bool
    {
        $pluginDir = storage_path("app/plugins/{$folderName}");

        if (! File::exists($pluginDir)) {
            throw new \Exception('Plugin folder not found');
        }

        // Make sure it's not installed in database
        $plugin = Plugin::where('slug', $folderName)->first();
        if ($plugin) {
            throw new \Exception('Cannot delete installed plugin. Uninstall it first.');
        }

        File::deleteDirectory($pluginDir);

        return true;
    }
}

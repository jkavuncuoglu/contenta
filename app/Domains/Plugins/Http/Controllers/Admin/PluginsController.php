<?php

declare(strict_types=1);

namespace App\Domains\Plugins\Http\Controllers\Admin;

use App\Domains\Plugins\Services\PluginService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PluginsController extends Controller
{
    public function __construct(
        private readonly PluginService $pluginService
    ) {}

    /**
     * Display the plugin manager page
     */
    public function index(): Response
    {
        return Inertia::render('admin/Plugins', [
            'installedPlugins' => $this->pluginService->getAllPlugins(),
            'uninstalledPlugins' => $this->pluginService->getUninstalledPlugins(),
        ]);
    }

    /**
     * Get list of all plugins (API)
     */
    public function list(): JsonResponse
    {
        $installed = $this->pluginService->getAllPlugins();
        $uninstalled = $this->pluginService->getUninstalledPlugins();

        return response()->json([
            'success' => true,
            'installed' => $installed,
            'uninstalled' => $uninstalled,
        ]);
    }

    /**
     * Upload and install a new plugin
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:zip|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('file');
            $result = $this->pluginService->installFromZip($file->getPathname());

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'plugin' => $result['plugin'],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'scan_results' => $result['scan_results'] ?? null,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to install plugin: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Enable a plugin (or install and enable if not installed)
     */
    public function enable(string $slug): JsonResponse
    {
        try {
            // Check if plugin is installed
            $plugin = \App\Domains\Plugins\Models\Plugin::where('slug', $slug)->first();

            if (! $plugin) {
                // Plugin not installed, install and enable it
                $result = $this->pluginService->installAndEnable($slug);

                if (! $result['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => $result['message'],
                        'scan_results' => $result['scan_results'] ?? null,
                    ], 422);
                }

                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'plugin' => $result['plugin'],
                ]);
            }

            // Plugin already installed, just enable it
            $this->pluginService->enable($slug);

            return response()->json([
                'success' => true,
                'message' => 'Plugin enabled successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Disable a plugin
     */
    public function disable(string $slug): JsonResponse
    {
        try {
            $this->pluginService->disable($slug);

            return response()->json([
                'success' => true,
                'message' => 'Plugin disabled successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Scan a plugin for security issues
     */
    public function scan(string $slug): JsonResponse
    {
        try {
            $scanResults = $this->pluginService->scanPlugin($slug);

            return response()->json([
                'success' => true,
                'message' => 'Plugin scanned successfully',
                'scan_results' => $scanResults,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Uninstall a plugin
     */
    public function uninstall(string $slug): JsonResponse
    {
        try {
            $this->pluginService->uninstall($slug);

            return response()->json([
                'success' => true,
                'message' => 'Plugin uninstalled successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Discover plugins in storage directory
     */
    public function discover(): JsonResponse
    {
        try {
            $discovered = $this->pluginService->discoverPlugins();

            return response()->json([
                'success' => true,
                'message' => count($discovered).' plugin(s) discovered',
                'discovered' => $discovered,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an uninstalled plugin folder
     */
    public function deleteUninstalled(string $folderName): JsonResponse
    {
        try {
            $this->pluginService->deleteUninstalledPlugin($folderName);

            return response()->json([
                'success' => true,
                'message' => 'Plugin folder deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

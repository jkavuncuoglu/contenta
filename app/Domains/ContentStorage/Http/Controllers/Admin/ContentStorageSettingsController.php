<?php

declare(strict_types=1);

namespace App\Domains\ContentStorage\Http\Controllers\Admin;

use App\Domains\ContentStorage\Services\ContentStorageManager;
use App\Domains\Settings\Models\Setting;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContentStorageSettingsController extends Controller
{
    public function __construct(
        private readonly ContentStorageManager $storageManager
    ) {}

    /**
     * Display the content storage settings
     */
    public function index(): Response
    {
        // Get current configuration for each driver
        $settings = [
            'pages_storage_driver' => Setting::get('content_storage', 'pages_storage_driver', 'database'),
            'posts_storage_driver' => Setting::get('content_storage', 'posts_storage_driver', 'database'),

            // Local filesystem settings
            'local_base_path' => Setting::get('content_storage', 'local_base_path', ''),

            // S3 settings
            's3_region' => Setting::get('content_storage', 's3_region', 'us-east-1'),
            's3_bucket' => Setting::get('content_storage', 's3_bucket', ''),
            's3_prefix' => Setting::get('content_storage', 's3_prefix', ''),
            's3_key' => Setting::get('content_storage', 's3_key') ? '••••••••' : '',
            's3_secret' => Setting::get('content_storage', 's3_secret') ? '••••••••' : '',

            // GitHub settings
            'github_owner' => Setting::get('content_storage', 'github_owner', ''),
            'github_repo' => Setting::get('content_storage', 'github_repo', ''),
            'github_branch' => Setting::get('content_storage', 'github_branch', 'main'),
            'github_base_path' => Setting::get('content_storage', 'github_base_path', ''),
            'github_token' => Setting::get('content_storage', 'github_token') ? '••••••••' : '',

            // Azure settings
            'azure_account_name' => Setting::get('content_storage', 'azure_account_name', ''),
            'azure_container' => Setting::get('content_storage', 'azure_container', ''),
            'azure_prefix' => Setting::get('content_storage', 'azure_prefix', ''),
            'azure_account_key' => Setting::get('content_storage', 'azure_account_key') ? '••••••••' : '',

            // GCS settings
            'gcs_project_id' => Setting::get('content_storage', 'gcs_project_id', ''),
            'gcs_bucket' => Setting::get('content_storage', 'gcs_bucket', ''),
            'gcs_prefix' => Setting::get('content_storage', 'gcs_prefix', ''),
            'gcs_key_file_path' => Setting::get('content_storage', 'gcs_key_file_path', ''),
        ];

        $availableDrivers = [
            ['value' => 'database', 'label' => 'Database', 'description' => 'Traditional database storage'],
            ['value' => 'local', 'label' => 'Local Filesystem', 'description' => 'Markdown files with YAML frontmatter'],
            ['value' => 's3', 'label' => 'AWS S3', 'description' => 'Cloud object storage with CDN support'],
            ['value' => 'github', 'label' => 'GitHub', 'description' => 'Git-based version control with commit tracking'],
            ['value' => 'azure', 'label' => 'Azure Blob Storage', 'description' => 'Microsoft Azure enterprise cloud storage'],
            ['value' => 'gcs', 'label' => 'Google Cloud Storage', 'description' => 'Google Cloud Platform with global infrastructure'],
        ];

        return Inertia::render('admin/settings/content-storage/Index', [
            'settings' => $settings,
            'availableDrivers' => $availableDrivers,
        ]);
    }

    /**
     * Update the content storage settings
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'pages_storage_driver' => 'required|string|in:database,local,s3,github,azure,gcs',
            'posts_storage_driver' => 'required|string|in:database,local,s3,github,azure,gcs',

            // Local filesystem
            'local_base_path' => 'nullable|string|max:255',

            // S3
            's3_region' => 'nullable|string|max:50',
            's3_bucket' => 'nullable|string|max:255',
            's3_prefix' => 'nullable|string|max:255',
            's3_key' => 'nullable|string|max:255',
            's3_secret' => 'nullable|string|max:255',

            // GitHub
            'github_owner' => 'nullable|string|max:255',
            'github_repo' => 'nullable|string|max:255',
            'github_branch' => 'nullable|string|max:255',
            'github_base_path' => 'nullable|string|max:255',
            'github_token' => 'nullable|string|max:255',

            // Azure
            'azure_account_name' => 'nullable|string|max:255',
            'azure_container' => 'nullable|string|max:255',
            'azure_prefix' => 'nullable|string|max:255',
            'azure_account_key' => 'nullable|string|max:255',

            // GCS
            'gcs_project_id' => 'nullable|string|max:255',
            'gcs_bucket' => 'nullable|string|max:255',
            'gcs_prefix' => 'nullable|string|max:255',
            'gcs_key_file_path' => 'nullable|string|max:500',
        ]);

        // Update driver selections
        Setting::set('content_storage', 'pages_storage_driver', $request->input('pages_storage_driver'));
        Setting::set('content_storage', 'posts_storage_driver', $request->input('posts_storage_driver'));

        // Update local settings
        if ($request->filled('local_base_path')) {
            Setting::set('content_storage', 'local_base_path', $request->input('local_base_path'));
        }

        // Update S3 settings (only if not masked)
        if ($request->filled('s3_region')) {
            Setting::set('content_storage', 's3_region', $request->input('s3_region'));
        }
        if ($request->filled('s3_bucket')) {
            Setting::set('content_storage', 's3_bucket', $request->input('s3_bucket'));
        }
        if ($request->filled('s3_prefix')) {
            Setting::set('content_storage', 's3_prefix', $request->input('s3_prefix'));
        }
        if ($request->filled('s3_key') && $request->input('s3_key') !== '••••••••') {
            Setting::set('content_storage', 's3_key', encrypt($request->input('s3_key')));
        }
        if ($request->filled('s3_secret') && $request->input('s3_secret') !== '••••••••') {
            Setting::set('content_storage', 's3_secret', encrypt($request->input('s3_secret')));
        }

        // Update GitHub settings
        if ($request->filled('github_owner')) {
            Setting::set('content_storage', 'github_owner', $request->input('github_owner'));
        }
        if ($request->filled('github_repo')) {
            Setting::set('content_storage', 'github_repo', $request->input('github_repo'));
        }
        if ($request->filled('github_branch')) {
            Setting::set('content_storage', 'github_branch', $request->input('github_branch'));
        }
        if ($request->filled('github_base_path')) {
            Setting::set('content_storage', 'github_base_path', $request->input('github_base_path'));
        }
        if ($request->filled('github_token') && $request->input('github_token') !== '••••••••') {
            Setting::set('content_storage', 'github_token', encrypt($request->input('github_token')));
        }

        // Update Azure settings
        if ($request->filled('azure_account_name')) {
            Setting::set('content_storage', 'azure_account_name', $request->input('azure_account_name'));
        }
        if ($request->filled('azure_container')) {
            Setting::set('content_storage', 'azure_container', $request->input('azure_container'));
        }
        if ($request->filled('azure_prefix')) {
            Setting::set('content_storage', 'azure_prefix', $request->input('azure_prefix'));
        }
        if ($request->filled('azure_account_key') && $request->input('azure_account_key') !== '••••••••') {
            Setting::set('content_storage', 'azure_account_key', encrypt($request->input('azure_account_key')));
        }

        // Update GCS settings
        if ($request->filled('gcs_project_id')) {
            Setting::set('content_storage', 'gcs_project_id', $request->input('gcs_project_id'));
        }
        if ($request->filled('gcs_bucket')) {
            Setting::set('content_storage', 'gcs_bucket', $request->input('gcs_bucket'));
        }
        if ($request->filled('gcs_prefix')) {
            Setting::set('content_storage', 'gcs_prefix', $request->input('gcs_prefix'));
        }
        if ($request->filled('gcs_key_file_path')) {
            Setting::set('content_storage', 'gcs_key_file_path', $request->input('gcs_key_file_path'));
        }

        return redirect()->route('admin.settings.content-storage.index')
            ->with('success', 'Content storage settings updated successfully');
    }

    /**
     * Test connection to a storage driver
     */
    public function testConnection(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'driver' => 'required|string|in:database,local,s3,github,azure,gcs',
            'config' => 'nullable|array',
        ]);

        $driver = $request->input('driver');
        $config = $request->input('config', []);

        try {
            $success = $this->storageManager->testDriver($driver, $config);

            return response()->json([
                'success' => $success,
                'message' => $success
                    ? "Successfully connected to {$driver} storage"
                    : "Failed to connect to {$driver} storage",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Connection failed: {$e->getMessage()}",
            ], 400);
        }
    }
}

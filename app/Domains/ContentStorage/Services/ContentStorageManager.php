<?php

declare(strict_types=1);

namespace App\Domains\ContentStorage\Services;

use App\Domains\ContentStorage\Contracts\ContentRepositoryContract;
use App\Domains\ContentStorage\Exceptions\StorageException;
use App\Domains\ContentStorage\Repositories\DatabaseRepository;
use App\Domains\Settings\Models\Setting;
use Illuminate\Support\Manager;

/**
 * Content Storage Manager
 *
 * Manages content storage drivers following Laravel's Manager pattern.
 * Provides driver instances based on settings configuration.
 *
 * Example usage:
 * ```php
 * $storage = app(ContentStorageManager::class);
 * $repository = $storage->forContentType('pages');
 * $content = $repository->read('pages/about-us.md');
 * ```
 */
class ContentStorageManager extends Manager
{
    /**
     * Get repository for specific content type (pages/posts)
     *
     * @param string $contentType Content type (pages|posts)
     * @return ContentRepositoryContract
     * @throws StorageException If driver configuration is invalid
     */
    public function forContentType(string $contentType): ContentRepositoryContract
    {
        // Get configured driver for this content type
        $driver = $this->getConfiguredDriver($contentType);

        // Create repository instance with content type context
        return $this->driver($driver, ['content_type' => $contentType]);
    }

    /**
     * Test a driver connection with provided configuration
     *
     * @param string $driver Driver name
     * @param array<string, mixed> $config Driver configuration
     * @return bool True if connection successful
     */
    public function testDriver(string $driver, array $config = []): bool
    {
        try {
            $repository = $this->instantiateDriver($driver, $config);

            return $repository->testConnection();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the default driver name
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return 'database';
    }

    /**
     * Get configured driver for content type from settings
     *
     * @param string $contentType Content type (pages|posts)
     * @return string Driver name
     */
    protected function getConfiguredDriver(string $contentType): string
    {
        $settingKey = "{$contentType}_storage_driver";

        return Setting::get('content_storage', $settingKey, 'database');
    }

    /**
     * Create database driver instance
     *
     * @param array<string, mixed> $config
     * @return ContentRepositoryContract
     */
    protected function createDatabaseDriver(array $config = []): ContentRepositoryContract
    {
        $contentType = $config['content_type'] ?? 'pages';

        return new DatabaseRepository($contentType);
    }

    /**
     * Create local filesystem driver instance
     *
     * @param array<string, mixed> $config
     * @return ContentRepositoryContract
     */
    protected function createLocalDriver(array $config = []): ContentRepositoryContract
    {
        $disk = $config['disk'] ?? 'content';
        $basePath = $config['base_path'] ?? '';

        return new \App\Domains\ContentStorage\Repositories\LocalRepository($disk, $basePath);
    }

    /**
     * Create S3 driver instance
     *
     * @param array<string, mixed> $config
     * @return ContentRepositoryContract
     */
    protected function createS3Driver(array $config = []): ContentRepositoryContract
    {
        // Merge with default settings if not provided
        if (empty($config['key']) || empty($config['secret']) || empty($config['bucket'])) {
            $settingsConfig = $this->getConfig('s3');
            $config = array_merge($settingsConfig, $config);
        }

        // Set default prefix based on content type if provided
        if (isset($config['content_type']) && ! isset($config['prefix'])) {
            $config['prefix'] = $config['content_type'];
        }

        return new \App\Domains\ContentStorage\Repositories\S3Repository($config);
    }

    /**
     * Create GitHub driver instance
     *
     * @param array<string, mixed> $config
     * @return ContentRepositoryContract
     */
    protected function createGithubDriver(array $config = []): ContentRepositoryContract
    {
        // Merge with default settings if not provided
        if (empty($config['token']) || empty($config['owner']) || empty($config['repo'])) {
            $settingsConfig = $this->getConfig('github');
            $config = array_merge($settingsConfig, $config);
        }

        // Set default base path based on content type if provided
        if (isset($config['content_type']) && ! isset($config['base_path'])) {
            $config['base_path'] = $config['content_type'];
        }

        return new \App\Domains\ContentStorage\Repositories\GitHubRepository($config);
    }

    /**
     * Create Azure Blob Storage driver instance
     *
     * @param array<string, mixed> $config
     * @return ContentRepositoryContract
     * @throws StorageException If driver not yet implemented
     */
    protected function createAzureDriver(array $config = []): ContentRepositoryContract
    {
        // Merge with default settings if not provided
        if (empty($config['account_name']) || empty($config['account_key']) || empty($config['container'])) {
            $settingsConfig = $this->getConfig('azure');
            $config = array_merge($settingsConfig, $config);
        }

        // Set default prefix based on content type if provided
        if (isset($config['content_type']) && ! isset($config['prefix'])) {
            $config['prefix'] = $config['content_type'];
        }

        return new \App\Domains\ContentStorage\Repositories\AzureRepository($config);
    }

    /**
     * Create Google Cloud Storage driver instance
     *
     * @param array<string, mixed> $config
     * @return ContentRepositoryContract
     * @throws StorageException If driver not yet implemented
     */
    protected function createGcsDriver(array $config = []): ContentRepositoryContract
    {
        // Merge with default settings if not provided
        if (empty($config['project_id']) || empty($config['bucket'])) {
            $settingsConfig = $this->getConfig('gcs');
            $config = array_merge($settingsConfig, $config);
        }

        // Set default prefix based on content type if provided
        if (isset($config['content_type']) && ! isset($config['prefix'])) {
            $config['prefix'] = $config['content_type'];
        }

        return new \App\Domains\ContentStorage\Repositories\GcsRepository($config);
    }

    /**
     * Get driver configuration from settings
     *
     * @param string $driver Driver name
     * @return array<string, mixed> Configuration array
     */
    protected function getConfig(string $driver): array
    {
        $config = [];

        switch ($driver) {
            case 'database':
                // Database driver uses Laravel's default DB connection
                $config = [
                    'connection' => config('database.default'),
                ];
                break;

            case 'local':
                $config = [
                    'disk' => 'content',
                    'base_path' => Setting::get('content_storage', 'local_base_path', ''),
                ];
                break;

            case 's3':
                $config = [
                    'key' => Setting::getDecrypted('content_storage', 's3_key'),
                    'secret' => Setting::getDecrypted('content_storage', 's3_secret'),
                    'region' => Setting::get('content_storage', 's3_region', 'us-east-1'),
                    'bucket' => Setting::get('content_storage', 's3_bucket'),
                    'prefix' => Setting::get('content_storage', 's3_prefix', ''),
                ];
                break;

            case 'github':
                $config = [
                    'token' => Setting::getDecrypted('content_storage', 'github_token'),
                    'owner' => Setting::get('content_storage', 'github_owner'),
                    'repo' => Setting::get('content_storage', 'github_repo'),
                    'branch' => Setting::get('content_storage', 'github_branch', 'main'),
                    'base_path' => Setting::get('content_storage', 'github_base_path', ''),
                ];
                break;

            case 'azure':
                $config = [
                    'account_name' => Setting::get('content_storage', 'azure_account_name'),
                    'account_key' => Setting::getDecrypted('content_storage', 'azure_account_key'),
                    'container' => Setting::get('content_storage', 'azure_container'),
                ];
                break;

            case 'gcs':
                $config = [
                    'project_id' => Setting::get('content_storage', 'gcs_project_id'),
                    'key_file_path' => Setting::get('content_storage', 'gcs_key_file_path'),
                    'bucket' => Setting::get('content_storage', 'gcs_bucket'),
                ];
                break;

            default:
                throw StorageException::invalidDriver($driver);
        }

        return $config;
    }

    /**
     * Instantiate a driver instance with configuration
     *
     * @param string $driver Driver name
     * @param array<string, mixed> $config Configuration override
     * @return ContentRepositoryContract
     */
    private function instantiateDriver(string $driver, array $config = []): ContentRepositoryContract
    {
        // Get configuration from settings if not provided
        if (empty($config)) {
            $config = $this->getConfig($driver);
        }

        // Call the appropriate create method
        $method = 'create'.ucfirst($driver).'Driver';

        if (! method_exists($this, $method)) {
            throw StorageException::invalidDriver($driver);
        }

        return $this->$method($config);
    }
}

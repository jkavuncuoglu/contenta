<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\ContentStorage\Services;

use App\Domains\ContentManagement\ContentStorage\Contracts\ContentRepositoryContract;
use App\Domains\ContentManagement\ContentStorage\Exceptions\MigrationException;
use App\Domains\ContentManagement\ContentStorage\Exceptions\ReadException;
use App\Domains\ContentManagement\ContentStorage\Exceptions\WriteException;
use App\Domains\ContentManagement\ContentStorage\Models\ContentMigration;
use Illuminate\Support\Facades\Log;

/**
 * Migration Service
 *
 * Handles content migration between storage backends.
 * Supports progress tracking, error logging, and rollback.
 */
class MigrationService
{
    /**
     * Content Storage Manager
     */
    private ContentStorageManager $storageManager;

    /**
     * Path Pattern Resolver
     */
    private PathPatternResolver $pathResolver;

    /**
     * Create a new migration service
     */
    public function __construct(
        ContentStorageManager $storageManager,
        PathPatternResolver $pathResolver
    ) {
        $this->storageManager = $storageManager;
        $this->pathResolver = $pathResolver;
    }

    /**
     * Start a new migration
     *
     * @param  string  $contentType  Content type (pages|posts)
     * @param  string  $fromDriver  Source driver name
     * @param  string  $toDriver  Destination driver name
     * @return ContentMigration Migration record
     *
     * @throws MigrationException If migration already in progress
     */
    public function startMigration(
        string $contentType,
        string $fromDriver,
        string $toDriver
    ): ContentMigration {
        // Check for existing running migrations
        $existingMigration = ContentMigration::query()
            ->forContentType($contentType)
            ->running()
            ->first();

        if ($existingMigration) {
            throw MigrationException::alreadyRunning($contentType);
        }

        // Validate drivers
        if ($fromDriver === $toDriver) {
            throw MigrationException::invalidDriverCombination($fromDriver, $toDriver);
        }

        // Create migration record
        $migration = ContentMigration::create([
            'content_type' => $contentType,
            'from_driver' => $fromDriver,
            'to_driver' => $toDriver,
            'status' => ContentMigration::STATUS_PENDING,
            'total_items' => 0,
            'migrated_items' => 0,
            'failed_items' => 0,
        ]);

        Log::info('Migration started', [
            'migration_id' => $migration->id,
            'content_type' => $contentType,
            'from' => $fromDriver,
            'to' => $toDriver,
        ]);

        return $migration;
    }

    /**
     * Execute migration
     *
     * @param  ContentMigration  $migration  Migration record
     * @param  bool  $deleteSource  Delete source content after successful migration
     * @return ContentMigration Updated migration record
     */
    public function executeMigration(
        ContentMigration $migration,
        bool $deleteSource = false
    ): ContentMigration {
        try {
            // Mark as started
            $migration->markAsStarted();

            // Get repositories
            $sourceRepo = $this->getRepository($migration->from_driver, $migration->content_type);
            $destRepo = $this->getRepository($migration->to_driver, $migration->content_type);

            // Get list of all content from source
            $items = $sourceRepo->list();
            $totalItems = count($items);

            if ($totalItems === 0) {
                $migration->update(['total_items' => 0]);
                $migration->markAsCompleted();

                return $migration;
            }

            $migration->update(['total_items' => $totalItems]);

            Log::info('Migration execution started', [
                'migration_id' => $migration->id,
                'total_items' => $totalItems,
            ]);

            // Migrate each item
            foreach ($items as $sourcePath) {
                try {
                    // Read from source
                    $contentData = $sourceRepo->read($sourcePath);

                    // Determine destination path
                    $destPath = $this->resolveDestinationPath(
                        $sourcePath,
                        $migration->content_type,
                        $contentData
                    );

                    // Write to destination
                    $destRepo->write($destPath, $contentData);

                    // Delete from source if requested
                    if ($deleteSource) {
                        $sourceRepo->delete($sourcePath);
                    }

                    // Update progress
                    $migration->incrementMigrated();

                    Log::debug('Item migrated successfully', [
                        'migration_id' => $migration->id,
                        'source_path' => $sourcePath,
                        'dest_path' => $destPath,
                    ]);
                } catch (ReadException|WriteException $e) {
                    // Log error and continue
                    $migration->incrementFailed(1, [
                        'path' => $sourcePath,
                        'error' => $e->getMessage(),
                    ]);

                    Log::warning('Item migration failed', [
                        'migration_id' => $migration->id,
                        'path' => $sourcePath,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Mark as completed
            $migration->markAsCompleted();

            Log::info('Migration completed', [
                'migration_id' => $migration->id,
                'migrated' => $migration->migrated_items,
                'failed' => $migration->failed_items,
            ]);

            return $migration;
        } catch (\Exception $e) {
            // Mark as failed
            $migration->markAsFailed($e->getMessage());

            Log::error('Migration failed', [
                'migration_id' => $migration->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new MigrationException("Migration failed: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Rollback migration
     *
     * Migrates content back from destination to source.
     *
     * @param  ContentMigration  $migration  Original migration record
     * @return ContentMigration New rollback migration record
     */
    public function rollbackMigration(ContentMigration $migration): ContentMigration
    {
        if (! $migration->isCompleted()) {
            throw MigrationException::notFound($migration->id);
        }

        Log::info('Starting migration rollback', [
            'original_migration_id' => $migration->id,
        ]);

        // Create reverse migration
        $rollbackMigration = $this->startMigration(
            $migration->content_type,
            $migration->to_driver,  // Swap from/to
            $migration->from_driver
        );

        // Execute rollback
        return $this->executeMigration($rollbackMigration, true);
    }

    /**
     * Verify migration integrity
     *
     * Compares content hashes between source and destination.
     *
     * @param  ContentMigration  $migration  Migration to verify
     * @param  int  $sampleSize  Number of random items to verify (0 = all)
     * @return array{verified: int, mismatched: int, missing: int, errors: array}
     */
    public function verifyMigration(ContentMigration $migration, int $sampleSize = 10): array
    {
        $sourceRepo = $this->getRepository($migration->from_driver, $migration->content_type);
        $destRepo = $this->getRepository($migration->to_driver, $migration->content_type);

        $sourceItems = $sourceRepo->list();

        // Sample items if requested
        if ($sampleSize > 0 && count($sourceItems) > $sampleSize) {
            $sourceItems = array_rand(array_flip($sourceItems), $sampleSize);
        }

        $verified = 0;
        $mismatched = 0;
        $missing = 0;
        $errors = [];

        foreach ($sourceItems as $sourcePath) {
            try {
                // Read from source
                $sourceContent = $sourceRepo->read($sourcePath);

                // Determine destination path
                $destPath = $this->resolveDestinationPath(
                    $sourcePath,
                    $migration->content_type,
                    $sourceContent
                );

                // Check if exists in destination
                if (! $destRepo->exists($destPath)) {
                    $missing++;
                    $errors[] = [
                        'path' => $sourcePath,
                        'error' => 'Missing in destination',
                    ];

                    continue;
                }

                // Read from destination
                $destContent = $destRepo->read($destPath);

                // Compare hashes
                if ($sourceContent->getHash() === $destContent->getHash()) {
                    $verified++;
                } else {
                    $mismatched++;
                    $errors[] = [
                        'path' => $sourcePath,
                        'error' => 'Content hash mismatch',
                    ];
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'path' => $sourcePath,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'verified' => $verified,
            'mismatched' => $mismatched,
            'missing' => $missing,
            'errors' => $errors,
        ];
    }

    /**
     * Get active migrations
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, ContentMigration>
     */
    public function getActiveMigrations(): \Illuminate\Database\Eloquent\Collection
    {
        return ContentMigration::query()
            ->whereIn('status', [
                ContentMigration::STATUS_PENDING,
                ContentMigration::STATUS_RUNNING,
            ])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get migration history
     *
     * @param  string|null  $contentType  Filter by content type
     * @param  int  $limit  Number of records to return
     * @return \Illuminate\Database\Eloquent\Collection<int, ContentMigration>
     */
    public function getMigrationHistory(?string $contentType = null, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        $query = ContentMigration::query()
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($contentType) {
            $query->forContentType($contentType);
        }

        return $query->get();
    }

    /**
     * Get repository for driver and content type
     *
     * @param  string  $driver  Driver name
     * @param  string  $contentType  Content type
     */
    private function getRepository(string $driver, string $contentType): ContentRepositoryContract
    {
        // For database driver, use content type context
        if ($driver === 'database') {
            return new \App\Domains\ContentStorage\Repositories\DatabaseRepository($contentType);
        }

        // For other drivers, use storage manager
        return $this->storageManager->driver($driver, ['content_type' => $contentType]);
    }

    /**
     * Resolve destination path from source path
     *
     * @param  string  $sourcePath  Source path
     * @param  string  $contentType  Content type
     * @param  \App\Domains\ContentStorage\Models\ContentData  $contentData  Content data
     * @return string Destination path
     */
    private function resolveDestinationPath(
        string $sourcePath,
        string $contentType,
        \App\Domains\ContentStorage\Models\ContentData $contentData
    ): string {
        // Try to extract slug from frontmatter or source path
        $slug = $contentData->get('slug', $this->extractSlugFromPath($sourcePath));

        // For database sources, path is like "pages/123" - extract ID
        if (preg_match('/^(pages|posts)\/(\d+)$/', $sourcePath, $matches)) {
            // Database source - use pattern from settings or default
            $pattern = \App\Domains\Settings\Models\Setting::get(
                'content_storage',
                "{$contentType}_path_pattern",
                PathPatternResolver::getDefaultPattern($contentType)
            );

            // Create a mock model for path resolution
            $model = new class extends \Illuminate\Database\Eloquent\Model
            {
                public $id;

                public $slug;

                private $contentData;

                public function setData($id, $slug, $contentData)
                {
                    $this->id = $id;
                    $this->slug = $slug;
                    $this->contentData = $contentData;
                }

                public function __get($name)
                {
                    // Check if it's a standard property first
                    if (property_exists($this, $name)) {
                        return $this->{$name};
                    }

                    return $this->contentData?->get($name);
                }

                public function __isset($name)
                {
                    return property_exists($this, $name) || $this->contentData?->has($name);
                }
            };

            $model->setData($matches[2], $slug, $contentData);

            return $this->pathResolver->resolve($pattern, $contentType, $model);
        }

        // For file-based sources, keep the same path
        return $sourcePath;
    }

    /**
     * Extract slug from file path
     *
     * @param  string  $path  File path
     * @return string Slug
     */
    private function extractSlugFromPath(string $path): string
    {
        // Remove .md extension
        $path = preg_replace('/\.md$/', '', $path);

        // Get basename
        $slug = basename($path);

        // Sanitize
        return $this->pathResolver->sanitizeComponent($slug);
    }

    /**
     * Cancel a running migration
     *
     * @param  ContentMigration  $migration  Migration to cancel
     * @return bool Success
     */
    public function cancelMigration(ContentMigration $migration): bool
    {
        if (! $migration->isRunning()) {
            return false;
        }

        $migration->markAsFailed('Cancelled by user');

        Log::info('Migration cancelled', [
            'migration_id' => $migration->id,
        ]);

        return true;
    }
}

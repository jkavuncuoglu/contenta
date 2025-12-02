<?php

declare(strict_types=1);

namespace App\Domains\ContentStorage\Console\Commands;

use App\Domains\ContentStorage\Jobs\MigrateContentJob;
use App\Domains\ContentStorage\Services\MigrationService;
use Illuminate\Console\Command;

/**
 * Migrate Content Command
 *
 * Artisan command for migrating content between storage backends.
 *
 * Usage:
 * php artisan content:migrate pages database local
 * php artisan content:migrate posts database github --async
 * php artisan content:migrate pages database s3 --dry-run
 */
class MigrateContentCommand extends Command
{
    /**
     * The name and signature of the console command
     */
    protected $signature = 'content:migrate
                            {content_type : Content type (pages|posts)}
                            {from_driver : Source storage driver}
                            {to_driver : Destination storage driver}
                            {--async : Run migration in background queue}
                            {--dry-run : Preview migration without executing}
                            {--delete-source : Delete source content after migration}
                            {--force : Skip confirmation prompts}
                            {--verify : Verify migration integrity after completion}';

    /**
     * The console command description
     */
    protected $description = 'Migrate content between storage backends';

    /**
     * Migration Service
     */
    private MigrationService $migrationService;

    /**
     * Create a new command instance
     */
    public function __construct(MigrationService $migrationService)
    {
        parent::__construct();
        $this->migrationService = $migrationService;
    }

    /**
     * Execute the console command
     */
    public function handle(): int
    {
        $contentType = $this->argument('content_type');
        $fromDriver = $this->argument('from_driver');
        $toDriver = $this->argument('to_driver');
        $async = $this->option('async');
        $dryRun = $this->option('dry-run');
        $deleteSource = $this->option('delete-source');
        $force = $this->option('force');
        $verify = $this->option('verify');

        // Validate content type
        if (! in_array($contentType, ['pages', 'posts'])) {
            $this->error("Invalid content type: {$contentType}. Must be 'pages' or 'posts'.");
            return Command::FAILURE;
        }

        // Display migration info
        $this->info("Content Migration");
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->line("Content Type: <fg=cyan>{$contentType}</>");
        $this->line("From: <fg=yellow>{$fromDriver}</>");
        $this->line("To: <fg=green>{$toDriver}</>");
        $this->line("Mode: ".($async ? '<fg=magenta>Async (Queue)</>' : '<fg=blue>Synchronous</>'));
        if ($deleteSource) {
            $this->line("⚠️  Source content will be <fg=red>deleted</> after migration");
        }
        if ($dryRun) {
            $this->line("🔍 <fg=yellow>DRY RUN MODE</> - No actual changes will be made");
        }
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->newLine();

        // Dry run preview
        if ($dryRun) {
            return $this->performDryRun($contentType, $fromDriver, $toDriver);
        }

        // Confirm before proceeding (unless --force)
        if (! $force) {
            if (! $this->confirm('Do you want to proceed with the migration?', true)) {
                $this->info('Migration cancelled.');
                return Command::SUCCESS;
            }
        }

        try {
            // Start migration
            $this->info('Starting migration...');
            $migration = $this->migrationService->startMigration(
                $contentType,
                $fromDriver,
                $toDriver
            );

            $this->line("Migration ID: <fg=cyan>{$migration->id}</>");
            $this->newLine();

            if ($async) {
                // Dispatch to queue
                MigrateContentJob::dispatch($migration, $deleteSource);
                $this->info('✓ Migration job dispatched to queue');
                $this->line("Monitor progress: php artisan content:migration-status {$migration->id}");
            } else {
                // Execute synchronously with progress bar
                $this->executeSynchronousMigration($migration, $deleteSource);

                // Verify if requested
                if ($verify) {
                    $this->newLine();
                    $this->verifyMigration($migration);
                }
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Migration failed: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Perform dry run
     */
    private function performDryRun(string $contentType, string $fromDriver, string $toDriver): int
    {
        $this->info('Analyzing migration...');

        try {
            // Get source repository
            $sourceRepo = $fromDriver === 'database'
                ? new \App\Domains\ContentStorage\Repositories\DatabaseRepository($contentType)
                : app(\App\Domains\ContentStorage\Services\ContentStorageManager::class)
                    ->driver($fromDriver, ['content_type' => $contentType]);

            // Count items
            $items = $sourceRepo->list();
            $totalItems = count($items);

            $this->newLine();
            $this->info("Found {$totalItems} items to migrate");

            // Show sample paths
            if ($totalItems > 0) {
                $this->line("\nSample items (first 5):");
                foreach (array_slice($items, 0, 5) as $item) {
                    $this->line("  • {$item}");
                }

                if ($totalItems > 5) {
                    $this->line("  ... and ".($totalItems - 5)." more");
                }
            }

            $this->newLine();
            $this->info('✓ Dry run completed. No changes were made.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Dry run failed: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Execute migration synchronously with progress bar
     */
    private function executeSynchronousMigration($migration, bool $deleteSource): void
    {
        // Get source repository to count items
        $sourceRepo = $migration->from_driver === 'database'
            ? new \App\Domains\ContentStorage\Repositories\DatabaseRepository($migration->content_type)
            : app(\App\Domains\ContentStorage\Services\ContentStorageManager::class)
                ->driver($migration->from_driver, ['content_type' => $migration->content_type]);

        $items = $sourceRepo->list();
        $totalItems = count($items);

        // Create progress bar
        $progressBar = $this->output->createProgressBar($totalItems);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        $progressBar->start();

        // Execute migration with callback
        $migration->markAsStarted();
        $migration->update(['total_items' => $totalItems]);

        $destRepo = $migration->to_driver === 'database'
            ? new \App\Domains\ContentStorage\Repositories\DatabaseRepository($migration->content_type)
            : app(\App\Domains\ContentStorage\Services\ContentStorageManager::class)
                ->driver($migration->to_driver, ['content_type' => $migration->content_type]);

        foreach ($items as $sourcePath) {
            try {
                // Read from source
                $contentData = $sourceRepo->read($sourcePath);

                // Determine destination path (simplified for command)
                $destPath = $sourcePath;

                // Write to destination
                $destRepo->write($destPath, $contentData);

                // Delete from source if requested
                if ($deleteSource) {
                    $sourceRepo->delete($sourcePath);
                }

                $migration->incrementMigrated();
            } catch (\Exception $e) {
                $migration->incrementFailed(1, [
                    'path' => $sourcePath,
                    'error' => $e->getMessage(),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Mark as completed
        $migration->markAsCompleted();

        // Show summary
        $this->info('✓ Migration completed');
        $this->line("Migrated: <fg=green>{$migration->migrated_items}</>");
        $this->line("Failed: <fg=red>{$migration->failed_items}</>");
        $this->line("Duration: {$migration->getDuration()} seconds");
    }

    /**
     * Verify migration integrity
     */
    private function verifyMigration($migration): void
    {
        $this->info('Verifying migration integrity...');

        $result = $this->migrationService->verifyMigration($migration, 0); // Verify all items

        $this->newLine();
        $this->line("Verified: <fg=green>{$result['verified']}</>");
        $this->line("Mismatched: <fg=".($result['mismatched'] > 0 ? 'red' : 'green').">{$result['mismatched']}</>");
        $this->line("Missing: <fg=".($result['missing'] > 0 ? 'red' : 'green').">{$result['missing']}</>");

        if (! empty($result['errors'])) {
            $this->warn("\nErrors found:");
            foreach (array_slice($result['errors'], 0, 10) as $error) {
                $this->line("  • {$error['path']}: {$error['error']}");
            }
            if (count($result['errors']) > 10) {
                $this->line("  ... and ".(count($result['errors']) - 10)." more errors");
            }
        }
    }
}

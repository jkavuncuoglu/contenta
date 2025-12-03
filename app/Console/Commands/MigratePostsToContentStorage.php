<?php

namespace App\Console\Commands;

use App\Domains\ContentManagement\Posts\Models\Post;
use App\Domains\ContentManagement\ContentStorage\ContentStorageManager;
use App\Domains\ContentManagement\ContentStorage\ValueObjects\ContentData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigratePostsToContentStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:migrate-storage
                            {driver : The storage driver to migrate to (s3, azure, gcs, github, gitlab, bitbucket)}
                            {--dry-run : Preview changes without applying them}
                            {--batch=50 : Number of posts to process per batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing post content from database to ContentStorage backend';

    /**
     * Execute the console command.
     */
    public function handle(ContentStorageManager $storageManager): int
    {
        $driver = $this->argument('driver');
        $dryRun = $this->option('dry-run');
        $batchSize = (int) $this->option('batch');

        // Validate storage driver
        $validDrivers = ['s3', 'azure', 'gcs', 'github', 'gitlab', 'bitbucket'];
        if (!in_array($driver, $validDrivers)) {
            $this->error("Invalid storage driver: {$driver}");
            $this->info("Valid drivers: " . implode(', ', $validDrivers));
            return self::FAILURE;
        }

        // Get posts that need migration (currently using database storage)
        $totalPosts = Post::where('storage_driver', 'database')
            ->orWhereNull('storage_driver')
            ->count();

        if ($totalPosts === 0) {
            $this->info('No posts need migration. All posts are already using cloud storage.');
            return self::SUCCESS;
        }

        $this->info("Found {$totalPosts} posts to migrate to {$driver} storage.");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        if (!$dryRun && !$this->confirm('Do you want to proceed with the migration?')) {
            $this->info('Migration cancelled.');
            return self::SUCCESS;
        }

        $this->info('Starting migration...');
        $progressBar = $this->output->createProgressBar($totalPosts);
        $progressBar->start();

        $migrated = 0;
        $failed = 0;
        $errors = [];

        // Process posts in batches
        Post::where('storage_driver', 'database')
            ->orWhereNull('storage_driver')
            ->chunk($batchSize, function ($posts) use (
                $storageManager,
                $driver,
                $dryRun,
                &$migrated,
                &$failed,
                &$errors,
                $progressBar
            ) {
                foreach ($posts as $post) {
                    try {
                        if (!$dryRun) {
                            DB::beginTransaction();
                        }

                        // Get current content from database
                        $content = new ContentData(
                            markdown: $post->getRawOriginal('content_markdown'),
                            html: $post->getRawOriginal('content_html'),
                            tableOfContents: $post->getRawOriginal('table_of_contents'),
                        );

                        // Generate storage path
                        $storagePath = $post->generateStoragePath();

                        if (!$dryRun) {
                            // Write to new storage backend
                            $storageDriver = $storageManager->driver($driver);
                            $storageDriver->write($storagePath, json_encode([
                                'markdown' => $content->markdown,
                                'html' => $content->html,
                                'table_of_contents' => $content->tableOfContents,
                            ]));

                            // Update post record
                            $post->storage_driver = $driver;
                            $post->storage_path = $storagePath;

                            // Clear database content fields
                            $post->content_markdown = null;
                            $post->content_html = null;
                            $post->table_of_contents = null;

                            $post->saveQuietly(); // Don't trigger observers

                            DB::commit();
                        }

                        $migrated++;

                    } catch (\Exception $e) {
                        if (!$dryRun) {
                            DB::rollBack();
                        }

                        $failed++;
                        $errors[] = [
                            'post_id' => $post->id,
                            'title' => $post->title,
                            'error' => $e->getMessage(),
                        ];

                        \Log::error('Failed to migrate post to ContentStorage', [
                            'post_id' => $post->id,
                            'driver' => $driver,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    $progressBar->advance();
                }
            });

        $progressBar->finish();
        $this->newLine(2);

        // Display results
        $this->info("Migration complete!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total posts', $totalPosts],
                ['Successfully migrated', $migrated],
                ['Failed', $failed],
            ]
        );

        if ($failed > 0) {
            $this->error("\n{$failed} posts failed to migrate:");
            $this->table(
                ['Post ID', 'Title', 'Error'],
                array_map(fn($error) => [
                    $error['post_id'],
                    \Illuminate\Support\Str::limit($error['title'], 40),
                    \Illuminate\Support\Str::limit($error['error'], 60),
                ], $errors)
            );
        }

        if ($dryRun) {
            $this->info("\nThis was a dry run. No actual changes were made.");
            $this->info("Run without --dry-run to perform the migration.");
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\ContentStorage\Http\Controllers\Admin;

use App\Domains\ContentManagement\ContentStorage\Jobs\MigrateContentJob;
use App\Domains\ContentManagement\ContentStorage\Models\ContentMigration;
use App\Domains\ContentManagement\ContentStorage\Services\MigrationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContentMigrationController extends Controller
{
    public function __construct(
        private readonly MigrationService $migrationService
    ) {}

    /**
     * Display the migration wizard
     */
    public function index(): Response
    {
        $recentMigrations = ContentMigration::latest()
            ->take(10)
            ->get()
            ->map(fn ($migration) => [
                'id' => $migration->id,
                'content_type' => $migration->content_type,
                'from_driver' => $migration->from_driver,
                'to_driver' => $migration->to_driver,
                'status' => $migration->status,
                'progress' => $migration->getProgress(),
                'total_items' => $migration->total_items,
                'migrated_items' => $migration->migrated_items,
                'failed_items' => $migration->failed_items,
                'started_at' => $migration->started_at?->toIso8601String(),
                'completed_at' => $migration->completed_at?->toIso8601String(),
                'created_at' => $migration->created_at->toIso8601String(),
            ]);

        $availableDrivers = [
            ['value' => 'database', 'label' => 'Database'],
            ['value' => 'local', 'label' => 'Local Filesystem'],
            ['value' => 's3', 'label' => 'AWS S3'],
            ['value' => 'github', 'label' => 'GitHub'],
            ['value' => 'azure', 'label' => 'Azure Blob Storage'],
            ['value' => 'gcs', 'label' => 'Google Cloud Storage'],
        ];

        return Inertia::render('admin/settings/content-storage/Migrate', [
            'recentMigrations' => $recentMigrations,
            'availableDrivers' => $availableDrivers,
        ]);
    }

    /**
     * Start a new migration
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'content_type' => 'required|string|in:pages,posts',
            'from_driver' => 'required|string|in:database,local,s3,github,azure,gcs',
            'to_driver' => 'required|string|in:database,local,s3,github,azure,gcs',
            'delete_source' => 'boolean',
            'async' => 'boolean',
        ]);

        $contentType = $request->input('content_type');
        $fromDriver = $request->input('from_driver');
        $toDriver = $request->input('to_driver');
        $deleteSource = $request->boolean('delete_source', false);
        $async = $request->boolean('async', true);

        // Validate drivers are different
        if ($fromDriver === $toDriver) {
            return response()->json([
                'success' => false,
                'message' => 'Source and destination drivers must be different',
            ], 400);
        }

        try {
            // Start migration
            $migration = $this->migrationService->startMigration(
                $contentType,
                $fromDriver,
                $toDriver
            );

            if ($async) {
                // Dispatch to queue
                MigrateContentJob::dispatch($migration, $deleteSource);

                return response()->json([
                    'success' => true,
                    'message' => 'Migration started in background',
                    'migration' => [
                        'id' => $migration->id,
                        'status' => $migration->status,
                    ],
                ]);
            } else {
                // Execute synchronously
                $result = $this->migrationService->executeMigration($migration, $deleteSource);

                return response()->json([
                    'success' => true,
                    'message' => 'Migration completed successfully',
                    'migration' => [
                        'id' => $migration->id,
                        'status' => $migration->status,
                        'migrated_items' => $migration->migrated_items,
                        'failed_items' => $migration->failed_items,
                    ],
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Migration failed: {$e->getMessage()}",
            ], 500);
        }
    }

    /**
     * Get migration status
     */
    public function show(int $id): JsonResponse
    {
        $migration = ContentMigration::findOrFail($id);

        return response()->json([
            'id' => $migration->id,
            'content_type' => $migration->content_type,
            'from_driver' => $migration->from_driver,
            'to_driver' => $migration->to_driver,
            'status' => $migration->status,
            'progress' => $migration->getProgress(),
            'total_items' => $migration->total_items,
            'migrated_items' => $migration->migrated_items,
            'failed_items' => $migration->failed_items,
            'error_log' => $migration->error_log,
            'started_at' => $migration->started_at?->toIso8601String(),
            'completed_at' => $migration->completed_at?->toIso8601String(),
            'created_at' => $migration->created_at->toIso8601String(),
        ]);
    }

    /**
     * Verify migration integrity
     */
    public function verify(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'sample_size' => 'nullable|integer|min:0|max:1000',
        ]);

        $migration = ContentMigration::findOrFail($id);
        $sampleSize = $request->integer('sample_size', 10);

        try {
            $result = $this->migrationService->verifyMigration($migration, $sampleSize);

            return response()->json([
                'success' => true,
                'result' => $result,
                'message' => "Verification complete: {$result['verified']} verified, {$result['mismatched']} mismatched, {$result['missing']} missing",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Verification failed: {$e->getMessage()}",
            ], 500);
        }
    }

    /**
     * Rollback a migration
     */
    public function rollback(int $id): JsonResponse
    {
        $migration = ContentMigration::findOrFail($id);

        try {
            $rollbackMigration = $this->migrationService->rollbackMigration($migration);

            // Dispatch rollback to queue
            MigrateContentJob::dispatch($rollbackMigration, false);

            return response()->json([
                'success' => true,
                'message' => 'Rollback started',
                'migration' => [
                    'id' => $rollbackMigration->id,
                    'status' => $rollbackMigration->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Rollback failed: {$e->getMessage()}",
            ], 500);
        }
    }

    /**
     * Get all migrations
     */
    public function list(): JsonResponse
    {
        $migrations = ContentMigration::latest()
            ->paginate(20)
            ->through(fn ($migration) => [
                'id' => $migration->id,
                'content_type' => $migration->content_type,
                'from_driver' => $migration->from_driver,
                'to_driver' => $migration->to_driver,
                'status' => $migration->status,
                'progress' => $migration->getProgress(),
                'total_items' => $migration->total_items,
                'migrated_items' => $migration->migrated_items,
                'failed_items' => $migration->failed_items,
                'started_at' => $migration->started_at?->toIso8601String(),
                'completed_at' => $migration->completed_at?->toIso8601String(),
                'created_at' => $migration->created_at->toIso8601String(),
            ]);

        return response()->json($migrations);
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\ContentStorage\Factories;

use App\Domains\ContentManagement\ContentStorage\ContentStorageManager;
use App\Domains\ContentManagement\ContentStorage\Contracts\RevisionProviderInterface;
use App\Domains\ContentManagement\ContentStorage\RevisionProviders\AzureBlobRevisionProvider;
use App\Domains\ContentManagement\ContentStorage\RevisionProviders\DatabaseRevisionProvider;
use App\Domains\ContentManagement\ContentStorage\RevisionProviders\GCSRevisionProvider;
use App\Domains\ContentManagement\ContentStorage\RevisionProviders\GitHubRevisionProvider;
use App\Domains\ContentManagement\ContentStorage\RevisionProviders\S3RevisionProvider;
use App\Domains\ContentManagement\Pages\Models\Page;
use App\Domains\ContentManagement\Posts\Models\Post;
use Illuminate\Database\Eloquent\Model;

class RevisionProviderFactory
{
    public function __construct(
        private ContentStorageManager $storageManager
    ) {}

    /**
     * Create appropriate revision provider for the given storage driver
     *
     * @param  string  $driver  Storage driver name
     * @param  Model  $model  Post or Page model
     *
     * @throws \InvalidArgumentException
     */
    public function make(string $driver, Model $model): RevisionProviderInterface
    {
        return match ($driver) {
            'database', 'local' => new DatabaseRevisionProvider($model),

            's3' => new S3RevisionProvider(
                $this->storageManager->driver('s3')
            ),

            'azure' => new AzureBlobRevisionProvider(
                $this->storageManager->driver('azure')
            ),

            'gcs' => new GCSRevisionProvider(
                $this->storageManager->driver('gcs')
            ),

            'github' => new GitHubRevisionProvider(
                $this->storageManager->driver('github')
            ),

            'gitlab', 'bitbucket' => new GitHubRevisionProvider(
                $this->storageManager->driver($driver)
            ),

            default => throw new \InvalidArgumentException("Unsupported storage driver for revisions: {$driver}"),
        };
    }

    /**
     * Check if a storage driver supports revisions
     */
    public function supportsRevisions(string $driver): bool
    {
        try {
            // For cloud storage, we need to check if versioning is enabled
            // For now, we'll assume all drivers support revisions
            return in_array($driver, [
                'database',
                'local',
                's3',
                'azure',
                'gcs',
                'github',
                'gitlab',
                'bitbucket',
            ]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get revision provider for a model
     *
     * @param  Post|Page  $model
     */
    public function forModel(Model $model): RevisionProviderInterface
    {
        $driver = $model->storage_driver ?? 'database';

        return $this->make($driver, $model);
    }
}

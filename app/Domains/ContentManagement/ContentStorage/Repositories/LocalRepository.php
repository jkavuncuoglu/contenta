<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\ContentStorage\Repositories;

use App\Domains\ContentManagement\ContentStorage\Contracts\ContentRepositoryContract;
use App\Domains\ContentManagement\ContentStorage\Exceptions\ReadException;
use App\Domains\ContentManagement\ContentStorage\Exceptions\WriteException;
use App\Domains\ContentManagement\ContentStorage\Models\ContentData;
use DateTimeImmutable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Local Filesystem Repository
 *
 * Stores content in local filesystem using Laravel's Storage facade.
 * Files are stored as markdown with YAML frontmatter in `storage/content/`.
 */
class LocalRepository implements ContentRepositoryContract
{
    /**
     * Storage disk name
     */
    private string $disk;

    /**
     * Base path within the disk
     */
    private string $basePath;

    /**
     * Create a new local repository
     *
     * @param string $disk Storage disk name (default: 'content')
     * @param string $basePath Base path within disk (default: '')
     */
    public function __construct(string $disk = 'content', string $basePath = '')
    {
        $this->disk = $disk;
        $this->basePath = trim($basePath, '/');
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $path): ContentData
    {
        $fullPath = $this->buildFullPath($path);

        try {
            // Check if file exists
            if (! Storage::disk($this->disk)->exists($fullPath)) {
                throw ReadException::notFound($path);
            }

            // Read file contents
            $markdown = Storage::disk($this->disk)->get($fullPath);

            if ($markdown === null) {
                throw ReadException::notFound($path);
            }

            // Parse markdown with frontmatter
            $contentData = ContentData::fromMarkdown($markdown);

            // Get file metadata
            $lastModified = Storage::disk($this->disk)->lastModified($fullPath);
            $size = Storage::disk($this->disk)->size($fullPath);

            // Return with metadata
            return new ContentData(
                content: $contentData->content,
                frontmatter: $contentData->frontmatter,
                hash: $contentData->hash,
                size: $size,
                modifiedAt: DateTimeImmutable::createFromFormat('U', (string) $lastModified)
            );
        } catch (ReadException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Failed to read from local storage", [
                'driver' => 'local',
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            throw new ReadException("Failed to read content from local storage: {$e->getMessage()}");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $path, ContentData $data): bool
    {
        $fullPath = $this->buildFullPath($path);

        try {
            // Ensure directory exists
            $directory = dirname($fullPath);
            if (! Storage::disk($this->disk)->exists($directory)) {
                Storage::disk($this->disk)->makeDirectory($directory);
            }

            // Convert ContentData to markdown with frontmatter
            $markdown = $data->toMarkdown();

            // Write to file
            $result = Storage::disk($this->disk)->put($fullPath, $markdown);

            if (! $result) {
                throw WriteException::networkFailure('local', 'Failed to write file');
            }

            Log::info("Content written to local storage", [
                'driver' => 'local',
                'path' => $path,
                'size' => strlen($markdown),
            ]);

            return true;
        } catch (WriteException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Failed to write to local storage", [
                'driver' => 'local',
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            throw WriteException::networkFailure('local', $e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $path): bool
    {
        try {
            $fullPath = $this->buildFullPath($path);

            return Storage::disk($this->disk)->exists($fullPath);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $path): bool
    {
        $fullPath = $this->buildFullPath($path);

        try {
            if (! Storage::disk($this->disk)->exists($fullPath)) {
                throw WriteException::invalidPath($path, 'File does not exist');
            }

            $result = Storage::disk($this->disk)->delete($fullPath);

            if (! $result) {
                throw WriteException::networkFailure('local', 'Failed to delete file');
            }

            Log::info("Content deleted from local storage", [
                'driver' => 'local',
                'path' => $path,
            ]);

            return true;
        } catch (WriteException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Failed to delete from local storage", [
                'driver' => 'local',
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            throw new WriteException("Failed to delete content: {$e->getMessage()}");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function list(string $directory = ''): array
    {
        try {
            $searchPath = $this->buildFullPath($directory);

            // Get all .md files recursively
            $files = Storage::disk($this->disk)->allFiles($searchPath);

            // Filter for .md files and remove base path
            $mdFiles = array_filter($files, fn ($file) => str_ends_with($file, '.md'));

            // Remove base path prefix if present
            if (! empty($this->basePath)) {
                $mdFiles = array_map(
                    fn ($file) => str_starts_with($file, $this->basePath.'/')
                        ? substr($file, strlen($this->basePath) + 1)
                        : $file,
                    $mdFiles
                );
            }

            return array_values($mdFiles);
        } catch (\Exception $e) {
            Log::error("Failed to list files in local storage", [
                'driver' => 'local',
                'directory' => $directory,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function testConnection(): bool
    {
        try {
            // Test if disk is accessible
            $disk = Storage::disk($this->disk);

            // Try to get root directory
            $disk->directories('/');

            // Try to create a test file
            $testPath = '.connection-test-'.time();
            $disk->put($testPath, 'test');
            $exists = $disk->exists($testPath);
            $disk->delete($testPath);

            return $exists;
        } catch (\Exception $e) {
            Log::error("Local storage connection test failed", [
                'driver' => 'local',
                'disk' => $this->disk,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDriverName(): string
    {
        return 'local';
    }

    /**
     * Build full path with base path
     *
     * @param string $path Relative path
     * @return string Full path
     */
    private function buildFullPath(string $path): string
    {
        if (empty($this->basePath)) {
            return $path;
        }

        return $this->basePath.'/'.ltrim($path, '/');
    }

    /**
     * Get disk instance
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function getDisk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return Storage::disk($this->disk);
    }

    /**
     * Get base path
     *
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }
}

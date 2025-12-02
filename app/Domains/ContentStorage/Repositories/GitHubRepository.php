<?php

declare(strict_types=1);

namespace App\Domains\ContentStorage\Repositories;

use App\Domains\ContentStorage\Contracts\ContentRepositoryContract;
use App\Domains\ContentStorage\Exceptions\ReadException;
use App\Domains\ContentStorage\Exceptions\StorageException;
use App\Domains\ContentStorage\Exceptions\WriteException;
use App\Domains\ContentStorage\Models\ContentData;
use DateTimeImmutable;
use Github\Client as GitHubClient;
use Github\Exception\RuntimeException as GitHubRuntimeException;

/**
 * GitHub Repository for storing content in a GitHub repository
 *
 * Implements content storage using GitHub API with support for:
 * - File CRUD operations via GitHub Contents API
 * - Branch support (main, develop, feature branches)
 * - Commit tracking and attribution
 * - Path-based organization
 */
class GitHubRepository implements ContentRepositoryContract
{
    private GitHubClient $client;

    private string $owner;

    private string $repo;

    private string $branch;

    private string $basePath;

    /**
     * Create a new GitHub Repository instance
     *
     * @param  array{
     *     token: string,
     *     owner: string,
     *     repo: string,
     *     branch?: string,
     *     base_path?: string
     * }  $config
     */
    public function __construct(array $config)
    {
        $this->owner = $config['owner'];
        $this->repo = $config['repo'];
        $this->branch = $config['branch'] ?? 'main';
        $this->basePath = $config['base_path'] ?? '';

        $this->client = new GitHubClient();
        $this->client->authenticate($config['token'], null, GitHubClient::AUTH_ACCESS_TOKEN);
    }

    /**
     * {@inheritDoc}
     */
    public function read(string $path): ContentData
    {
        try {
            $fullPath = $this->buildFullPath($path);

            $fileInfo = $this->client->api('repo')->contents()->show(
                $this->owner,
                $this->repo,
                $fullPath,
                $this->branch
            );

            if (! isset($fileInfo['content'])) {
                throw ReadException::notFound($path);
            }

            // GitHub returns base64 encoded content
            $markdown = base64_decode($fileInfo['content']);

            // Get commit info for metadata
            $commits = $this->client->api('repo')->commits()->all(
                $this->owner,
                $this->repo,
                ['sha' => $this->branch, 'path' => $fullPath, 'per_page' => 1]
            );

            $lastModified = new DateTimeImmutable();
            if (! empty($commits[0]['commit']['committer']['date'])) {
                $lastModified = new DateTimeImmutable($commits[0]['commit']['committer']['date']);
            }

            $contentData = ContentData::fromMarkdown($markdown);

            // Update metadata from GitHub
            return new ContentData(
                content: $contentData->content,
                frontmatter: $contentData->frontmatter,
                hash: $contentData->hash,
                size: $fileInfo['size'] ?? strlen($markdown),
                modifiedAt: $lastModified
            );
        } catch (GitHubRuntimeException $e) {
            if (str_contains($e->getMessage(), 'Not Found')) {
                throw ReadException::notFound($path);
            }

            throw ReadException::failed($path, $e->getMessage());
        } catch (\Exception $e) {
            throw ReadException::failed($path, $e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function write(string $path, ContentData $data): bool
    {
        try {
            $fullPath = $this->buildFullPath($path);
            $markdown = $data->toMarkdown();
            $message = $this->generateCommitMessage($path, 'update');

            // Check if file exists to get SHA (required for updates)
            $sha = null;
            try {
                $fileInfo = $this->client->api('repo')->contents()->show(
                    $this->owner,
                    $this->repo,
                    $fullPath,
                    $this->branch
                );
                $sha = $fileInfo['sha'] ?? null;
                $message = $this->generateCommitMessage($path, 'update');
            } catch (GitHubRuntimeException $e) {
                // File doesn't exist, create new
                $message = $this->generateCommitMessage($path, 'create');
            }

            $params = [
                'message' => $message,
                'content' => base64_encode($markdown),
                'branch' => $this->branch,
            ];

            if ($sha !== null) {
                $params['sha'] = $sha;
            }

            $this->client->api('repo')->contents()->createFile(
                $this->owner,
                $this->repo,
                $fullPath,
                $params
            );

            return true;
        } catch (GitHubRuntimeException $e) {
            throw WriteException::failed($path, $e->getMessage());
        } catch (\Exception $e) {
            throw WriteException::failed($path, $e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function exists(string $path): bool
    {
        try {
            $fullPath = $this->buildFullPath($path);

            $this->client->api('repo')->contents()->show(
                $this->owner,
                $this->repo,
                $fullPath,
                $this->branch
            );

            return true;
        } catch (GitHubRuntimeException $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $path): bool
    {
        try {
            $fullPath = $this->buildFullPath($path);

            // Get file info to get SHA (required for deletion)
            $fileInfo = $this->client->api('repo')->contents()->show(
                $this->owner,
                $this->repo,
                $fullPath,
                $this->branch
            );

            if (! isset($fileInfo['sha'])) {
                throw WriteException::failed($path, 'Could not get file SHA');
            }

            $message = $this->generateCommitMessage($path, 'delete');

            $this->client->api('repo')->contents()->deleteFile(
                $this->owner,
                $this->repo,
                $fullPath,
                [
                    'message' => $message,
                    'sha' => $fileInfo['sha'],
                    'branch' => $this->branch,
                ]
            );

            return true;
        } catch (GitHubRuntimeException $e) {
            throw WriteException::failed($path, "Failed to delete: {$e->getMessage()}");
        } catch (\Exception $e) {
            throw WriteException::failed($path, "Failed to delete: {$e->getMessage()}");
        }
    }

    /**
     * {@inheritDoc}
     */
    public function list(string $directory = ''): array
    {
        try {
            $fullPath = $this->buildFullPath($directory);

            $contents = $this->client->api('repo')->contents()->show(
                $this->owner,
                $this->repo,
                $fullPath,
                $this->branch
            );

            // If it's a single file, return empty array
            if (isset($contents['type']) && $contents['type'] === 'file') {
                return [];
            }

            $files = [];
            $this->listRecursive($contents, $files);

            return $files;
        } catch (GitHubRuntimeException $e) {
            throw new StorageException("Failed to list directory '$directory': {$e->getMessage()}");
        } catch (\Exception $e) {
            throw new StorageException("Failed to list directory '$directory': {$e->getMessage()}");
        }
    }

    /**
     * {@inheritDoc}
     */
    public function testConnection(): bool
    {
        try {
            // Try to get repository info
            $this->client->api('repo')->show($this->owner, $this->repo);

            return true;
        } catch (GitHubRuntimeException $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getDriverName(): string
    {
        return 'github';
    }

    /**
     * Build the full path including base path
     */
    private function buildFullPath(string $path): string
    {
        if (empty($this->basePath)) {
            return $path;
        }

        $basePath = trim($this->basePath, '/');
        $path = ltrim($path, '/');

        return "{$basePath}/{$path}";
    }

    /**
     * Remove the base path from a full path to get relative path
     */
    private function removeBasePath(string $fullPath): string
    {
        if (empty($this->basePath)) {
            return $fullPath;
        }

        $basePath = trim($this->basePath, '/').'/';

        if (str_starts_with($fullPath, $basePath)) {
            return substr($fullPath, strlen($basePath));
        }

        return $fullPath;
    }

    /**
     * Recursively list files from GitHub contents
     *
     * @param  array<mixed>  $contents
     * @param  array<string>  $files
     */
    private function listRecursive(array $contents, array &$files): void
    {
        foreach ($contents as $item) {
            if (! is_array($item) || ! isset($item['type'])) {
                continue;
            }

            if ($item['type'] === 'file') {
                // Only include markdown files
                if (str_ends_with($item['path'], '.md')) {
                    $relativePath = $this->removeBasePath($item['path']);
                    $files[] = $relativePath;
                }
            } elseif ($item['type'] === 'dir') {
                // Recursively fetch directory contents
                try {
                    $dirContents = $this->client->api('repo')->contents()->show(
                        $this->owner,
                        $this->repo,
                        $item['path'],
                        $this->branch
                    );
                    $this->listRecursive($dirContents, $files);
                } catch (GitHubRuntimeException $e) {
                    // Skip directories we can't access
                    continue;
                }
            }
        }
    }

    /**
     * Generate commit message for operations
     */
    private function generateCommitMessage(string $path, string $action): string
    {
        $filename = basename($path);

        return match ($action) {
            'create' => "Create {$filename}",
            'update' => "Update {$filename}",
            'delete' => "Delete {$filename}",
            default => "Modify {$filename}",
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Posts\Aggregates;

use App\Domains\ContentManagement\Posts\Events\PostCreated;
use App\Domains\ContentManagement\Posts\Events\PostPublished;
use App\Domains\ContentManagement\Posts\Events\PostUnpublished;
use App\Domains\ContentManagement\Posts\Events\PostUpdated;
use App\Domains\ContentManagement\Posts\Exceptions\InvalidPostStatusException;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PostAggregate
{
    /**
     * @var array<int, object>
     */
    private array $events = [];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_PRIVATE = 'private';

    public const STATUS_TRASH = 'trash';

    public const VALID_STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PUBLISHED,
        self::STATUS_SCHEDULED,
        self::STATUS_PRIVATE,
        self::STATUS_TRASH,
    ];

    /**
     * @param  array<string, mixed>  $customFields
     */
    public function __construct(
        private ?int $id,
        private string $title,
        private string $slug,
        private string $contentMarkdown,
        private string $contentHtml,
        private string $status,
        private int $authorId,
        private ?Carbon $publishedAt = null,
        private array $customFields = [],
        private int $version = 1
    ) {
        $this->validateStatus($status);
    }

    /**
     * @param  array<string, mixed>  $customFields
     */
    public static function create(
        string $title,
        string $contentMarkdown,
        int $authorId,
        array $customFields = []
    ): self {
        $slug = Str::slug($title);
        $contentHtml = self::markdownToHtml($contentMarkdown);

        $aggregate = new self(
            id: null,
            title: $title,
            slug: $slug,
            contentMarkdown: $contentMarkdown,
            contentHtml: $contentHtml,
            status: self::STATUS_DRAFT,
            authorId: $authorId,
            customFields: $customFields
        );

        $aggregate->recordEvent(new PostCreated(
            title: $title,
            slug: $slug,
            authorId: $authorId,
        ));

        return $aggregate;
    }

    public function updateContent(string $title, string $contentMarkdown): void
    {
        $oldTitle = $this->title;
        $oldSlug = $this->slug;

        $this->title = $title;
        $this->slug = Str::slug($title);
        $this->contentMarkdown = $contentMarkdown;
        $this->contentHtml = self::markdownToHtml($contentMarkdown);
        $this->version++;

        $this->recordEvent(new PostUpdated(
            id: $this->id,
            oldTitle: $oldTitle,
            newTitle: $title,
            oldSlug: $oldSlug,
            newSlug: $this->slug,
            version: $this->version
        ));
    }

    public function publish(?Carbon $publishedAt = null): void
    {
        if ($this->status === self::STATUS_PUBLISHED) {
            return;
        }

        $wasScheduled = $this->status === self::STATUS_SCHEDULED;

        $this->status = self::STATUS_PUBLISHED;
        $this->publishedAt = $publishedAt ?? Carbon::now();

        $this->recordEvent(new PostPublished(
            id: $this->id,
            title: $this->title,
            slug: $this->slug,
            publishedAt: $this->publishedAt,
            wasScheduled: $wasScheduled
        ));
    }

    public function schedule(Carbon $publishedAt): void
    {
        if ($publishedAt->isPast()) {
            throw new InvalidPostStatusException('Cannot schedule post for past date');
        }

        $this->status = self::STATUS_SCHEDULED;
        $this->publishedAt = $publishedAt;
    }

    public function unpublish(): void
    {
        if ($this->status !== self::STATUS_PUBLISHED) {
            return;
        }

        $this->status = self::STATUS_DRAFT;
        $publishedAt = $this->publishedAt;
        $this->publishedAt = null;

        $this->recordEvent(new PostUnpublished(
            id: $this->id,
            title: $this->title,
            slug: $this->slug,
            previousPublishedAt: $publishedAt
        ));
    }

    public function makeDraft(): void
    {
        $this->status = self::STATUS_DRAFT;
        $this->publishedAt = null;
    }

    public function makePrivate(): void
    {
        $this->status = self::STATUS_PRIVATE;
    }

    public function trash(): void
    {
        $oldStatus = $this->status;
        $this->status = self::STATUS_TRASH;
    }

    /**
     * @param  array<string, mixed>  $customFields
     */
    public function updateCustomFields(array $customFields): void
    {
        $this->customFields = array_merge($this->customFields, $customFields);
        $this->version++;
    }

    public function addCustomField(string $key, mixed $value): void
    {
        $this->customFields[$key] = $value;
        $this->version++;
    }

    public function removeCustomField(string $key): void
    {
        unset($this->customFields[$key]);
        $this->version++;
    }

    public function getCustomField(string $key, mixed $default = null): mixed
    {
        return $this->customFields[$key] ?? $default;
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getContentMarkdown(): string
    {
        return $this->contentMarkdown;
    }

    public function getContentHtml(): string
    {
        return $this->contentHtml;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getAuthorId(): int
    {
        return $this->authorId;
    }

    public function getPublishedAt(): ?Carbon
    {
        return $this->publishedAt;
    }

    /**
     * @return array<string, mixed>
     */
    public function getCustomFields(): array
    {
        return $this->customFields;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @return array<int, object>
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    public function clearEvents(): void
    {
        $this->events = [];
    }

    // Status checks
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function isPrivate(): bool
    {
        return $this->status === self::STATUS_PRIVATE;
    }

    public function isTrashed(): bool
    {
        return $this->status === self::STATUS_TRASH;
    }

    public function canBePublished(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SCHEDULED]);
    }

    // Private methods
    private function validateStatus(string $status): void
    {
        if (! in_array($status, self::VALID_STATUSES)) {
            throw new InvalidPostStatusException("Invalid post status: {$status}");
        }
    }

    private function recordEvent(object $event): void
    {
        $this->events[] = $event;
    }

    private static function markdownToHtml(string $markdown): string
    {
        // TODO: Implement proper markdown parsing
        // For now, return basic HTML conversion
        return nl2br(htmlspecialchars($markdown));
    }
}

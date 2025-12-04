<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\ContentStorage\ValueObjects;

/**
 * Collection of revisions with pagination metadata
 */
class RevisionCollection implements \Countable, \IteratorAggregate, \JsonSerializable
{
    /**
     * @param  array<Revision>  $revisions
     */
    public function __construct(
        private array $revisions,
        private int $total,
        private int $currentPage,
        private int $perPage,
        private bool $hasMore,
    ) {}

    /**
     * Get all revisions in the collection
     *
     * @return array<Revision>
     */
    public function getRevisions(): array
    {
        return $this->revisions;
    }

    /**
     * Get total number of revisions
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * Get current page number
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Get number of items per page
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * Check if there are more pages
     */
    public function hasMore(): bool
    {
        return $this->hasMore;
    }

    /**
     * Get the next page number (if available)
     */
    public function getNextPage(): ?int
    {
        return $this->hasMore ? $this->currentPage + 1 : null;
    }

    /**
     * Convert collection to array format
     */
    public function toArray(): array
    {
        return [
            'data' => array_map(fn ($r) => $r->toArray(), $this->revisions),
            'meta' => [
                'total' => $this->total,
                'current_page' => $this->currentPage,
                'per_page' => $this->perPage,
                'has_more' => $this->hasMore,
                'next_page' => $this->getNextPage(),
            ],
        ];
    }

    /**
     * Implement JsonSerializable
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Implement IteratorAggregate
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->revisions);
    }

    /**
     * Implement Countable
     */
    public function count(): int
    {
        return count($this->revisions);
    }

    /**
     * Check if collection is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->revisions);
    }

    /**
     * Get first revision
     */
    public function first(): ?Revision
    {
        return $this->revisions[0] ?? null;
    }

    /**
     * Get last revision
     */
    public function last(): ?Revision
    {
        return $this->revisions[array_key_last($this->revisions)] ?? null;
    }
}

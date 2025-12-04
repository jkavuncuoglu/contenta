<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\AST;

class DocumentNode extends Node
{
    /** @var array<string, mixed> */
    private array $metadata = [];

    public function getType(): string
    {
        return 'document';
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getMetadataValue(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'metadata' => $this->metadata,
        ]);
    }
}

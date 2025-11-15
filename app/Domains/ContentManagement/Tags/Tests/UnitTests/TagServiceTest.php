<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Tags\Tests\UnitTests;

use App\Domains\ContentManagement\Tags\Services\TagService;
use App\Domains\ContentManagement\Tags\Services\TagServiceContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TagService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TagServiceContract::class);
    }

    public function test_it_is_bound_to_container(): void
    {
        // Act
        $service = app(TagServiceContract::class);

        // Assert
        $this->assertInstanceOf(TagService::class, $service);
    }

    public function test_it_is_registered_as_singleton(): void
    {
        // Act
        $service1 = app(TagServiceContract::class);
        $service2 = app(TagServiceContract::class);

        // Assert
        $this->assertSame($service1, $service2);
    }
}

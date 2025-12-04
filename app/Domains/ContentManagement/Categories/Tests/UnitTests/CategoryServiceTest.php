<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Categories\Tests\UnitTests;

use App\Domains\ContentManagement\Categories\Services\CategoryService;
use App\Domains\ContentManagement\Categories\Services\CategoryServiceContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CategoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CategoryServiceContract::class);
    }

    public function test_it_is_bound_to_container(): void
    {
        // Act
        $service = app(CategoryServiceContract::class);

        // Assert
        $this->assertInstanceOf(CategoryService::class, $service);
    }

    public function test_it_is_registered_as_singleton(): void
    {
        // Act
        $service1 = app(CategoryServiceContract::class);
        $service2 = app(CategoryServiceContract::class);

        // Assert
        $this->assertSame($service1, $service2);
    }
}

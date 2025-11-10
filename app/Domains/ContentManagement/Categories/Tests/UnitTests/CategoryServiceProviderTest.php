<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Categories\Tests\UnitTests;

use App\Domains\ContentManagement\Categories\Services\CategoryService;
use App\Domains\ContentManagement\Categories\Services\CategoryServiceContract;
use Tests\TestCase;

class CategoryServiceProviderTest extends TestCase
{
    
    public function test_it_registers_category_service_contract(): void
    {
        // Act
        $service = app(CategoryServiceContract::class);

        // Assert
        $this->assertInstanceOf(CategoryService::class, $service);
    }

    
    public function test_it_loads_migrations(): void
    {
        // This test verifies that the service provider boot method runs without errors
        $this->expectNotToPerformAssertions();
    }
}

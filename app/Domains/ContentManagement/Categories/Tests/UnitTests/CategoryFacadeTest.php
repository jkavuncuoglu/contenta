<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Categories\Tests\UnitTests;

use App\Domains\ContentManagement\Categories\CategoryFacade;
use App\Domains\ContentManagement\Categories\Services\CategoryServiceContract;
use Tests\TestCase;

class CategoryFacadeTest extends TestCase
{
    
    public function test_it_resolves_to_category_service_contract(): void
    {
        // Arrange
        $reflection = new \ReflectionClass(CategoryFacade::class);
        $method = $reflection->getMethod('getFacadeAccessor');

        // Act
        $accessor = $method->invoke(null);

        // Assert
        $this->assertEquals(CategoryServiceContract::class, $accessor);
    }
}

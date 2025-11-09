<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Tests\UnitTests;

use App\Domains\PageBuilder\PageBuilderFacade;
use App\Domains\PageBuilder\Services\PageBuilderServiceContract;
use Tests\TestCase;

class PageBuilderFacadeTest extends TestCase
{
    
    public function test_it_resolves_to_page_builder_service_contract(): void
    {
        // Arrange
        $reflection = new \ReflectionClass(PageBuilderFacade::class);
        $method = $reflection->getMethod('getFacadeAccessor');

        // Act
        $accessor = $method->invoke(null);

        // Assert
        $this->assertEquals(PageBuilderServiceContract::class, $accessor);
    }
}

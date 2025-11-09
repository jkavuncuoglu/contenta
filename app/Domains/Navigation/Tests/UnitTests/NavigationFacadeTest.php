<?php

declare(strict_types=1);

namespace App\Domains\Navigation\Tests\UnitTests;

use App\Domains\Navigation\NavigationFacade;
use App\Domains\Navigation\Services\MenuServiceContract;
use Tests\TestCase;

class NavigationFacadeTest extends TestCase
{
    
    public function test_it_resolves_to_menu_service_contract(): void
    {
        // Arrange
        $reflection = new \ReflectionClass(NavigationFacade::class);
        $method = $reflection->getMethod('getFacadeAccessor');

        // Act
        $accessor = $method->invoke(null);

        // Assert
        $this->assertEquals(MenuServiceContract::class, $accessor);
    }
}

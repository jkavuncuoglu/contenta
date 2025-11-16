<?php

declare(strict_types=1);

namespace App\Domains\Navigation\Tests\UnitTests;

use App\Domains\Navigation\Services\MenuService;
use App\Domains\Navigation\Services\MenuServiceContract;
use Tests\TestCase;

class NavigationServiceProviderTest extends TestCase
{
    public function test_it_registers_menu_service_contract(): void
    {
        // Act
        $service = app(MenuServiceContract::class);

        // Assert
        $this->assertInstanceOf(MenuService::class, $service);
    }

    public function test_it_registers_menu_service_as_singleton(): void
    {
        // Act
        $service1 = app(MenuServiceContract::class);
        $service2 = app(MenuServiceContract::class);

        // Assert
        $this->assertSame($service1, $service2);
    }
}

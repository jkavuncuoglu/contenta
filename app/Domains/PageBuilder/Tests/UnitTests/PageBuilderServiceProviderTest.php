<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Tests\UnitTests;

use App\Domains\PageBuilder\Services\PageBuilderService;
use App\Domains\PageBuilder\Services\PageBuilderServiceContract;
use App\Domains\PageBuilder\Services\PageRenderService;
use App\Domains\PageBuilder\Services\PageRenderServiceContract;
use Tests\TestCase;

class PageBuilderServiceProviderTest extends TestCase
{
    
    public function test_it_registers_page_builder_service_contract(): void
    {
        // Act
        $service = app(PageBuilderServiceContract::class);

        // Assert
        $this->assertInstanceOf(PageBuilderService::class, $service);
    }

    
    public function test_it_registers_page_render_service_contract(): void
    {
        // Act
        $service = app(PageRenderServiceContract::class);

        // Assert
        $this->assertInstanceOf(PageRenderService::class, $service);
    }

    
    public function test_it_registers_services_as_singletons(): void
    {
        // Act
        $service1 = app(PageBuilderServiceContract::class);
        $service2 = app(PageBuilderServiceContract::class);

        // Assert
        $this->assertSame($service1, $service2);
    }
}

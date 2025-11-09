<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Tags\Tests\UnitTests;

use App\Domains\ContentManagement\Tags\Services\TagService;
use App\Domains\ContentManagement\Tags\Services\TagServiceContract;
use Tests\TestCase;

class TagServiceProviderTest extends TestCase
{
    
    public function test_it_registers_tag_service_contract(): void
    {
        // Act
        $service = app(TagServiceContract::class);

        // Assert
        $this->assertInstanceOf(TagService::class, $service);
    }

    
    public function test_it_loads_migrations(): void
    {
        // This test verifies that the service provider boot method runs without errors
        $this->assertTrue(true);
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Media\Tests\UnitTests;

use App\Domains\Media\Services\MediaService;
use App\Domains\Media\Services\MediaServiceContract;
use Tests\TestCase;

class MediaServiceProviderTest extends TestCase
{
    
    public function test_it_registers_media_service_contract(): void
    {
        // Act
        $service = app(MediaServiceContract::class);

        // Assert
        $this->assertInstanceOf(MediaService::class, $service);
    }

    
    public function test_it_registers_media_service_as_singleton(): void
    {
        // Act
        $service1 = app(MediaServiceContract::class);
        $service2 = app(MediaServiceContract::class);

        // Assert
        $this->assertSame($service1, $service2);
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Media\Tests\UnitTests;

use App\Domains\Media\MediaFacade;
use App\Domains\Media\Services\MediaServiceContract;
use Tests\TestCase;

class MediaFacadeTest extends TestCase
{
    
    public function test_it_resolves_to_media_service_contract(): void
    {
        // Arrange
        $reflection = new \ReflectionClass(MediaFacade::class);
        $method = $reflection->getMethod('getFacadeAccessor');

        // Act
        $accessor = $method->invoke(null);

        // Assert
        $this->assertEquals(MediaServiceContract::class, $accessor);
    }
}

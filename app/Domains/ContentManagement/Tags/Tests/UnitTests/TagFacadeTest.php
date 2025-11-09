<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Tags\Tests\UnitTests;

use App\Domains\ContentManagement\Tags\Services\TagServiceContract;
use App\Domains\ContentManagement\Tags\TagFacade;
use Tests\TestCase;

class TagFacadeTest extends TestCase
{
    
    public function test_it_resolves_to_tag_service_contract(): void
    {
        // Arrange
        $reflection = new \ReflectionClass(TagFacade::class);
        $method = $reflection->getMethod('getFacadeAccessor');

        // Act
        $accessor = $method->invoke(null);

        // Assert
        $this->assertEquals(TagServiceContract::class, $accessor);
    }
}

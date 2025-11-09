<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Comments\Tests\UnitTests;

use App\Domains\ContentManagement\Comments\Services\CommentsService;
use App\Domains\ContentManagement\Comments\Services\CommentsServiceContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CommentsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CommentsServiceContract::class);
    }

    
    public function test_it_is_bound_to_container(): void
    {
        // Act
        $service = app(CommentsServiceContract::class);

        // Assert
        $this->assertInstanceOf(CommentsService::class, $service);
    }

    
    public function test_it_is_registered_as_singleton(): void
    {
        // Act
        $service1 = app(CommentsServiceContract::class);
        $service2 = app(CommentsServiceContract::class);

        // Assert
        $this->assertSame($service1, $service2);
    }
}

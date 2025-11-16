<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\HandleAppearance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class HandleAppearanceTest extends TestCase
{
    public function test_shares_appearance_cookie_with_views(): void
    {
        // Arrange
        $middleware = new HandleAppearance();
        $request = Request::create('/test');
        $request->cookies->set('appearance', 'dark');
        $called = false;

        $next = function ($req) use (&$called) {
            $called = true;

            return new Response('test');
        };

        // Act
        $response = $middleware->handle($request, $next);

        // Assert
        $this->assertTrue($called);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('dark', View::shared('appearance'));
    }

    public function test_defaults_to_system_when_no_cookie(): void
    {
        // Arrange
        $middleware = new HandleAppearance();
        $request = Request::create('/test');
        $called = false;

        $next = function ($req) use (&$called) {
            $called = true;

            return new Response('test');
        };

        // Act
        $response = $middleware->handle($request, $next);

        // Assert
        $this->assertTrue($called);
        $this->assertEquals('system', View::shared('appearance'));
    }

    public function test_handles_light_appearance(): void
    {
        // Arrange
        $middleware = new HandleAppearance();
        $request = Request::create('/test');
        $request->cookies->set('appearance', 'light');

        $next = function ($req) {
            return new Response('test');
        };

        // Act
        $response = $middleware->handle($request, $next);

        // Assert
        $this->assertEquals('light', View::shared('appearance'));
    }

    public function test_passes_request_to_next_middleware(): void
    {
        // Arrange
        $middleware = new HandleAppearance();
        $request = Request::create('/test');
        $passedRequest = null;

        $next = function ($req) use (&$passedRequest) {
            $passedRequest = $req;

            return new Response('test');
        };

        // Act
        $middleware->handle($request, $next);

        // Assert
        $this->assertSame($request, $passedRequest);
    }

    public function test_returns_response_from_next_middleware(): void
    {
        // Arrange
        $middleware = new HandleAppearance();
        $request = Request::create('/test');
        $expectedResponse = new Response('expected content', 201);

        $next = function ($req) use ($expectedResponse) {
            return $expectedResponse;
        };

        // Act
        $response = $middleware->handle($request, $next);

        // Assert
        $this->assertSame($expectedResponse, $response);
        $this->assertEquals('expected content', $response->getContent());
        $this->assertEquals(201, $response->getStatusCode());
    }
}

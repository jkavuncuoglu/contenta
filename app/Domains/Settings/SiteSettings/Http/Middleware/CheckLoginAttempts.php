<?php

namespace App\Domains\Settings\SiteSettings\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to check login attempts
 * TODO: Implement LoginAttemptService for production use
 */
class CheckLoginAttempts
{
    public function __construct()
    {
        // LoginAttemptService dependency removed - to be implemented
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check login-related routes
        if (!$this->isLoginRequest($request)) {
            return $next($request);
        }

        // TODO: Implement LoginAttemptService logic
        // For now, pass through all login requests
        return $next($request);
    }

    /**
     * Check if this is a login request
     */
    private function isLoginRequest(Request $request): bool
    {
        // Check common login routes and methods
        $loginRoutes = [
            'login',
            'api/login',
            'api/auth/login',
            'auth/login',
            'api/v1/auth/login',
        ];

        $path = trim($request->getPathInfo(), '/');

        return $request->isMethod('POST') &&
               (in_array($path, $loginRoutes) || str_contains($path, 'login'));
    }
}

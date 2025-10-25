<?php

namespace App\Domains\Settings\SiteSettings\Http\Middleware;

use App\Services\LoginAttemptService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckLoginAttempts
{
    public function __construct(
        private LoginAttemptService $loginAttemptService
    ) {}

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

        $username = $request->input('email') ?? $request->input('username') ?? '';
        $ipAddress = $request->ip();
        $deviceFingerprint = LoginAttemptService::generateDeviceFingerprint($request);

        // Check if this login attempt should be blocked
        if ($this->loginAttemptService->isBlocked($username, $ipAddress, $deviceFingerprint)) {
            // Get blocking information for response
            $blockInfo = $this->loginAttemptService->getBlockingInfo($username)
                ?? $this->loginAttemptService->getBlockingInfo($ipAddress, 'ip')
                ?? $this->loginAttemptService->getBlockingInfo($deviceFingerprint, 'device');

            Log::warning('Login attempt blocked by middleware', [
                'username' => $username,
                'ip' => $ipAddress,
                'device_fingerprint' => substr($deviceFingerprint, 0, 8) . '...',
                'block_info' => $blockInfo
            ]);

            return $this->createBlockedResponse($blockInfo);
        }

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

    /**
     * Create blocked response
     */
    private function createBlockedResponse(?array $blockInfo): Response
    {
        $message = 'Too many failed login attempts. Access temporarily blocked.';
        $statusCode = 429; // Too Many Requests

        if (!$blockInfo) {
            return response()->json([
                'status' => 'error',
                'message' => $message,
                'code' => 'LOGIN_BLOCKED'
            ], $statusCode);
        }

        if ($blockInfo['permanent_block']) {
            $message = 'Account access has been permanently blocked due to security violations.';
            $statusCode = 403; // Forbidden
        } elseif ($blockInfo['blocked_until']) {
            $blockedUntil = $blockInfo['blocked_until'];
            if ($blockedUntil instanceof \DateTime) {
                $blockedUntil = $blockedUntil->format('Y-m-d H:i:s');
            }
            $message = "Access blocked until {$blockedUntil} due to repeated failed attempts.";
        }

        $response = [
            'status' => 'error',
            'message' => $message,
            'code' => 'LOGIN_BLOCKED',
            'block_info' => [
                'escalation_level' => $blockInfo['escalation_level'],
                'failed_attempts' => $blockInfo['failed_attempts'],
                'permanent_block' => $blockInfo['permanent_block'],
            ]
        ];

        // Add blocked_until only if not permanent
        if (!$blockInfo['permanent_block'] && $blockInfo['blocked_until']) {
            $response['block_info']['blocked_until'] = $blockInfo['blocked_until'];
        }

        return response()->json($response, $statusCode);
    }
}

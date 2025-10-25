<?php

namespace App\Domains\Settings\SiteSettings\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class LogoutOnInactivity
{
    /**
     * Handle an incoming request and logout/revoke user if inactive longer than configured timeout.
     */
    public function handle(Request $request, Closure $next)
    {
        $timeout = (int) config('auth.inactivity_timeout', 0);

        if ($timeout > 0) {
            // Handle web (session-based) authentication
            if (Auth::check()) {
                $lastActivity = $request->session()->get('last_activity_at');

                if ($lastActivity) {
                    $last = Carbon::parse($lastActivity);

                    if (now()->diffInMinutes($last) >= $timeout) {
                        Auth::logout();
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();

                        if ($request->expectsJson() || $request->is('api/*')) {
                            return response()->json([
                                'status' => 'error',
                                'message' => 'Session expired due to inactivity.',
                                'data' => null,
                            ], 401);
                        }

                        return redirect()->guest('/login');
                    }
                }

                // Update last activity timestamp for session-based users
                $request->session()->put('last_activity_at', now());
            }

            // Handle API tokens (Sanctum)
            $user = $request->user();
            if ($user && method_exists($user, 'currentAccessToken')) {
                $token = $user->currentAccessToken();

                if ($token) {
                    $lastUsedAt = $token->last_used_at ?? $token->created_at;

                    if ($lastUsedAt && now()->diffInMinutes($lastUsedAt) >= $timeout) {
                        // Revoke the token due to inactivity
                        $token->delete();

                        return response()->json([
                            'status' => 'error',
                            'message' => 'Session expired due to inactivity.',
                            'data' => null,
                        ], 401);
                    }
                }
            }
        }

        return $next($request);
    }
}

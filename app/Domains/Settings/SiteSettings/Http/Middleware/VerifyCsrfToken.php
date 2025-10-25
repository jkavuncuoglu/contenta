<?php

namespace App\Domains\Settings\SiteSettings\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/*',           // Example: Exclude all API routes
        'webhook/*',       // Example: Exclude webhook endpoints
        'stripe/webhook',
        'storage/*',       // Exclude storage files from CSRF verification
    ];
}

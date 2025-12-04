<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Throwable;

class Handler extends ExceptionHandler
{
    // Keep default properties and methods from parent; override shouldReturnJson to prevent
    // returning plain JSON for Inertia requests (which expect Inertia responses).

    /**
     * Determine if the exception handler response should be JSON.
     * We override to return false for Inertia visits (X-Inertia header) so Inertia receives
     * proper Inertia responses instead of plain JSON.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldReturnJson($request, Throwable $e)
    {
        // If request is an Inertia visit, don't return JSON
        if ($request->header('X-Inertia')) {
            return false;
        }

        return parent::shouldReturnJson($request, $e);
    }
}

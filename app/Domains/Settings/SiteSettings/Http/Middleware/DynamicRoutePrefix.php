<?php

namespace App\Domains\Settings\SiteSettings\Http\Middleware;

use App\Domains\Settings\SiteSettings\Models\SiteSettings;
use Closure;
use Illuminate\Http\Request;

class DynamicRoutePrefix
{
    /**
     * The routes that should be handled by this middleware.
     *
     * @var array<int, string>
     */
    protected $routes = [
        'blog',
        'categories',
        'tags',
        'authors',
        'search',
        'feed',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $path = $request->path();
        $segments = explode('/', $path);
        $firstSegment = $segments[0];

        // Check if the first segment is one of our blog routes
        if (in_array($firstSegment, $this->routes)) {
            // Get the blog slug from settings
            $blogSlug = SiteSettings::get('blog_slug', 'blog');

            // Only rewrite if the slug is different
            if ($blogSlug !== 'blog') {
                // Replace the first segment with the blog slug
                $segments[0] = $blogSlug;
                $newPath = implode('/', $segments);

                // Redirect to the new path if it's different
                if ($newPath !== $path) {
                    return redirect()->to($newPath, 301);
                }
            }
        }

        // For API routes, we need to handle the prefix differently
        if ($firstSegment === 'api') {
            $this->handleApiRoutes($request);
        }

        return $next($request);
    }

    /**
     * Handle API routes with dynamic prefixes
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function handleApiRoutes($request)
    {
        $path = $request->path();
        $segments = explode('/', $path);

        // The API path is typically: api/v1/blog/...
        // We need to check the third segment (index 2)
        if (count($segments) >= 3 && in_array($segments[2], $this->routes)) {
            $blogSlug = SiteSettings::get('blog_slug', 'blog');

            // Only rewrite if the slug is different
            if ($blogSlug !== 'blog') {
                $segments[2] = $blogSlug;
                $newPath = implode('/', $segments);

                // Update the request path
                $request->server->set('REQUEST_URI', '/' . $newPath);
                $request->initialize(
                    $request->query->all(),
                    $request->all(),
                    $request->attributes->all(),
                    $request->cookies->all(),
                    $request->files->all(),
                    $request->server->all(),
                    $request->getContent()
                );
            }
        }
    }
}

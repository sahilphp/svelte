<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Sahil\Http\Middleware\MiddlewareContract;
use Sahil\Http\Request;
use Sahil\Http\Response;

/**
 * Adds conservative security headers to every response.
 *
 * Content-Security-Policy is intentionally not set here: the Vite dev server
 * uses a websocket and module requests from a second origin, so a policy
 * that suits production would break development. Applications that need CSP
 * can add it in this middleware once their production asset origin is known.
 */
final class SecurityHeaders implements MiddlewareContract
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request)->withHeaders([
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
        ]);
    }
}

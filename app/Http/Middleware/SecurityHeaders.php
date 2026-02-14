<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self)');

        // CSP ヘッダー (開発時は Vite のホットリロードを許可)
        if (app()->environment('production')) {
            $response->headers->set('Content-Security-Policy',
                "default-src 'self'; "
                . "script-src 'self'; "
                . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
                . "font-src 'self' https://fonts.gstatic.com; "
                . "img-src 'self' https://*.tile.openstreetmap.org data:; "
                . "connect-src 'self' https://nominatim.openstreetmap.org; "
            );
        }

        return $response;
    }
}

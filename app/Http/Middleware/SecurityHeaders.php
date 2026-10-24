<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('Cross-Origin-Resource-Policy', 'cross-origin');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
        $response->headers->set('X-Download-Options', 'noopen');
        $response->headers->set('X-DNS-Prefetch-Control', 'off');
        $response->headers->set('Content-Security-Policy', "default-src 'self'; base-uri 'self'; frame-ancestors 'self'; form-action 'self' https://www.zarinpal.com https://sandbox.zarinpal.com; object-src 'none'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' data: blob: https:; connect-src 'self' https://www.tgju.org https://api.tgju.org https://sandbox.zarinpal.com https://payment.zarinpal.com https:; frame-src 'self' https://www.zarinpal.com https://sandbox.zarinpal.com; worker-src 'self' blob:;");

        if ($request->is('admin/*') || $request->is('login') || $request->is('register') || $request->is('checkout*')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
        }

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}

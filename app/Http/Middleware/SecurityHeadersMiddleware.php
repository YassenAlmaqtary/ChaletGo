<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Security headers to be added to all responses
     */
    protected array $headers = [
        // Prevent clickjacking attacks
        'X-Frame-Options' => 'DENY',

        // Prevent MIME type sniffing
        'X-Content-Type-Options' => 'nosniff',

        // Enable XSS protection
        'X-XSS-Protection' => '1; mode=block',

        // Referrer policy
        'Referrer-Policy' => 'strict-origin-when-cross-origin',

        // Permissions policy
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',

        // Content Security Policy
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self'; frame-ancestors 'none';",
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add security headers
        foreach ($this->headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        // Add HSTS header for HTTPS
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Remove server information
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');

        return $response;
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $maxAttempts = '60', string $decayMinutes = '1'): Response
    {
        $key = $this->resolveRequestSignature($request);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return $this->buildTooManyAttemptsResponse($key, $maxAttempts);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }

    /**
     * Resolve request signature.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $user = $request->user();

        if ($user) {
            return sha1('rate_limit:user:' . $user->id . ':' . $request->route()->getName());
        }

        return sha1('rate_limit:ip:' . $request->ip() . ':' . $request->route()->getName());
    }

    /**
     * Create a 'too many attempts' response.
     */
    protected function buildTooManyAttemptsResponse(string $key, int $maxAttempts): Response
    {
        $retryAfter = RateLimiter::availableIn($key);

        return response()->json([
            'message' => 'تم تجاوز الحد المسموح من الطلبات. يرجى المحاولة مرة أخرى بعد ' . $retryAfter . ' ثانية.',
            'retry_after' => $retryAfter,
            'max_attempts' => $maxAttempts,
        ], 429);
    }

    /**
     * Add rate limit headers to response.
     */
    protected function addHeaders(Response $response, int $maxAttempts, int $remainingAttempts): Response
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $remainingAttempts),
        ]);

        return $response;
    }

    /**
     * Calculate remaining attempts.
     */
    protected function calculateRemainingAttempts(string $key, int $maxAttempts): int
    {
        return $maxAttempts - RateLimiter::attempts($key);
    }
}

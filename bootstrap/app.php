<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'user.type' => \App\Http\Middleware\CheckUserType::class,
            'locale' => \App\Http\Middleware\SetLocale::class,
            'rate.limit' => \App\Http\Middleware\RateLimitMiddleware::class,
            'security.headers' => \App\Http\Middleware\SecurityHeadersMiddleware::class,
        ]);

        // Apply security and locale middleware globally
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
        ]);

        // Apply rate limiting to API routes
        $middleware->api(append: [
            \App\Http\Middleware\RateLimitMiddleware::class . ':120,1', // 120 requests per minute
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

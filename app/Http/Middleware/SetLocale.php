<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set locale to Arabic for all requests
        App::setLocale('ar');

        // Set Carbon locale for date formatting
        if (class_exists(\Carbon\Carbon::class)) {
            \Carbon\Carbon::setLocale('ar');
        }

        return $next($request);
    }
}

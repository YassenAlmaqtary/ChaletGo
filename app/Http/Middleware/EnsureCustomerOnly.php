<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to ensure only customers can access mobile app API routes
 * Admins and Owners should use Filament panels instead
 */
class EnsureCustomerOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول'
            ], 401);
        }

        // Only customers can access mobile app API
        if ($user->user_type !== 'customer') {
            return response()->json([
                'success' => false,
                'message' => 'هذا التطبيق مخصص للعملاء فقط. يرجى استخدام لوحة التحكم على الموقع.',
                'user_type' => $user->user_type,
                'redirect_to' => match($user->user_type) {
                    'admin' => url('/admin'),
                    'owner' => url('/owner'),
                    default => null,
                }
            ], 403);
        }

        return $next($request);
    }
}






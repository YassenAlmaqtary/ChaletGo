<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $userType
     */
    public function handle(Request $request, Closure $next, string $userType): Response
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول'
            ], 401);
        }

        if ($user->user_type !== $userType) {
            return response()->json([
                'success' => false,
                'message' => 'ليس لديك صلاحية للوصول لهذا المورد'
            ], 403);
        }

        return $next($request);
    }
}

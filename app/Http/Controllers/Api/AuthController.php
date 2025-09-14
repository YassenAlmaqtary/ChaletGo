<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    use ApiResponseTrait;
    /**
     * Create a new AuthController instance.
     */
    public function __construct()
    {
        // Middleware will be applied in routes
    }

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // Validation is handled by RegisterRequest

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'user_type' => $request->user_type,
            'is_active' => true,
        ]);

        // Generate JWT token
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => __('messages.register_success'),
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60
            ]
        ], 201);
    }

    /**
     * Login user and return JWT token
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // Validation is handled by LoginRequest

        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.login_failed')
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في إنشاء الرمز المميز'
            ], 500);
        }

        $user = auth('api')->user();

        // Check if user is active
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'حسابك غير مفعل'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60
            ]
        ]);
    }

    /**
     * Get the authenticated user
     */
    public function profile(): JsonResponse
    {
        $user = auth('api')->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user
            ]
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'password' => 'sometimes|required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only(['name', 'phone']);

        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الملف الشخصي بنجاح',
            'data' => [
                'user' => $user->fresh()
            ]
        ]);
    }

    /**
     * Refresh JWT token
     */
    public function refresh(): JsonResponse
    {
        try {
            $token = JWTAuth::refresh();

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60
                ]
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تحديث الرمز المميز'
            ], 500);
        }
    }

    /**
     * Logout user (invalidate token)
     */
    public function logout(): JsonResponse
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الخروج بنجاح'
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تسجيل الخروج'
            ], 500);
        }
    }
}

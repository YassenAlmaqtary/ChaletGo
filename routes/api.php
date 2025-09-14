<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChaletController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ChaletImageController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\RefundController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Public chalet routes (no authentication required)
Route::get('chalets', [ChaletController::class, 'index']);
Route::get('chalets/{chalet:slug}', [ChaletController::class, 'show']);
Route::get('chalets/{chalet:slug}/availability', [ChaletController::class, 'checkAvailability']);

// Protected routes
Route::middleware('auth:api')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
    
    // Chalets routes
    Route::apiResource('chalets', ChaletController::class)->except(['destroy']);
    Route::delete('chalets/{chalet:slug}', [ChaletController::class, 'destroy']);
    Route::get('chalets/{chalet:slug}/availability', [ChaletController::class, 'checkAvailability']);
    Route::get('my-chalets', [ChaletController::class, 'myCharets']);

    // Chalet Images routes
    Route::post('chalets/{chalet:slug}/images', [ChaletImageController::class, 'store']);
    Route::put('chalets/{chalet:slug}/images/{image}', [ChaletImageController::class, 'update']);
    Route::delete('chalets/{chalet:slug}/images/{image}', [ChaletImageController::class, 'destroy']);
    Route::put('chalets/{chalet:slug}/images/reorder', [ChaletImageController::class, 'reorder']);

    // Bookings routes
    Route::apiResource('bookings', BookingController::class)->except(['update', 'destroy']);
    Route::put('bookings/{booking}/status', [BookingController::class, 'update']);
    Route::put('bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    Route::get('owner/bookings', [BookingController::class, 'ownerBookings']);

    // Reviews routes
    Route::apiResource('reviews', ReviewController::class)->only(['index', 'store', 'show']);

    // Payment routes
    Route::post('bookings/{booking}/payment', [PaymentController::class, 'processPayment']);
    Route::get('payments', [PaymentController::class, 'getPayments']);
    Route::get('payments/{payment}', [PaymentController::class, 'getPayment']);

    // Refund routes
    Route::post('payments/{payment}/refund', [RefundController::class, 'processRefund']);
    Route::get('payments/{payment}/refunds', [RefundController::class, 'getRefunds']);
    Route::get('refund-policies', [RefundController::class, 'getRefundPolicies']);
});

// Webhook routes (no authentication required)
Route::prefix('webhooks')->group(function () {
    Route::post('moyasar', [WebhookController::class, 'moyasar'])->name('webhook.moyasar');
    Route::post('stc-pay', [WebhookController::class, 'stcPay'])->name('webhook.stc-pay');
});

// Payment callback routes (no authentication required)
Route::get('payment/callback', [WebhookController::class, 'paymentCallback'])->name('payment.callback');

// Test route
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'ChaletGo API is working!',
        'version' => '1.0.0',
        'timestamp' => now()
    ]);
});

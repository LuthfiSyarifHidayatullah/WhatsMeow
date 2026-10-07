<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BotController;
use App\Http\Controllers\BotResponseController;
use App\Http\Controllers\ChatSessionController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OpdController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceMenuItemController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Siap Bengkayang (Kabupaten Bengkayang)
|--------------------------------------------------------------------------
*/

// Bot webhook (from Go WhatsApp service)
Route::prefix('bot')->middleware('bot.auth')->group(function () {
    Route::post('/incoming', [BotController::class, 'incoming']);
    Route::post('/incoming-media', [BotController::class, 'incomingMedia']);
    Route::post('/message-status', [BotController::class, 'messageStatus']);
});

// Auth routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (requires Sanctum auth)
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Monitoring Dashboard
    Route::prefix('monitoring')->group(function () {
        Route::get('/dashboard', [MonitoringController::class, 'dashboard']);
        Route::get('/services', [MonitoringController::class, 'serviceStats']);
        Route::get('/officers', [MonitoringController::class, 'officerPerformance']);
        Route::get('/queue', [MonitoringController::class, 'queueStatus']);
        Route::get('/activity-logs', [MonitoringController::class, 'activityLogs']);
        Route::get('/history', [MonitoringController::class, 'sessionHistory']);
        Route::get('/ratings', [MonitoringController::class, 'ratingDetails']);
        Route::get('/export', [MonitoringController::class, 'exportReport']);
    });

    // Chat Sessions (Live Chat)
    Route::prefix('chats')->group(function () {
        Route::get('/', [ChatSessionController::class, 'index']);
        Route::get('/{sessionId}', [ChatSessionController::class, 'show']);
        Route::post('/{sessionId}/accept', [ChatSessionController::class, 'accept']);
        Route::post('/{sessionId}/transfer', [ChatSessionController::class, 'transfer']);
        Route::post('/{sessionId}/resolve', [ChatSessionController::class, 'resolve']);
        Route::post('/{sessionId}/messages', [ChatSessionController::class, 'sendMessage']);
    });

    // OPD (Instansi/Perangkat Daerah) Management
    Route::apiResource('opds', OpdController::class);

    // Services Management
    Route::apiResource('services', ServiceController::class);

    // Sub-menu (pilihan di dalam layanan) Management
    Route::apiResource('service-menu-items', ServiceMenuItemController::class)
        ->parameters(['service-menu-items' => 'serviceMenuItem']);

    // Users/Officers Management
    Route::apiResource('users', UserController::class);
    Route::post('/users/toggle-availability', [UserController::class, 'toggleAvailability']);

    // Bot Response Management
    Route::apiResource('bot-responses', BotResponseController::class);

    // Booking/Schedule Management
    Route::apiResource('bookings', BookingController::class);

    // Notifications to Visitors
    Route::prefix('notifications')->group(function () {
        Route::post('/send', [NotificationController::class, 'send']);
        Route::get('/visitors', [NotificationController::class, 'visitors']);
        Route::get('/services', [NotificationController::class, 'services']);
        Route::get('/templates', [NotificationController::class, 'templates']);
    });
});

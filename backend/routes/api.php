<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BotController;
use App\Http\Controllers\BotResponseController;
use App\Http\Controllers\ChatSessionController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - MPP Chatbot Kab. Bengkayang
|--------------------------------------------------------------------------
*/

// Bot webhook (from Go WhatsApp service)
Route::prefix('bot')->middleware('bot.auth')->group(function () {
    Route::post('/incoming', [BotController::class, 'incoming']);
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

    // Services Management
    Route::apiResource('services', ServiceController::class);

    // Users/Officers Management
    Route::apiResource('users', UserController::class);
    Route::post('/users/toggle-availability', [UserController::class, 'toggleAvailability']);

    // Bot Response Management
    Route::apiResource('bot-responses', BotResponseController::class);
});

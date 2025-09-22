<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\ModelViewerController;
use App\Http\Controllers\OcrController;

// Основные маршруты для SPA-приложения
Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('welcome');
});

Route::get('/register', function () {
    return view('welcome');
});

Route::get('/profile', function () {
    return view('welcome');
});

// Маршрут для работы Laravel Sanctum
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['csrf' => csrf_token()]);
});

// OCR маршрут (без авторизации для простоты)
Route::post('/ocr', [OcrController::class, 'analyze']);

// API-маршруты
Route::post('/api/login', [AuthController::class, 'login']);
Route::post('/api/register', [AuthController::class, 'register']);

// Защищенные API-маршруты
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/api/logout', [AuthController::class, 'logout']);
    Route::get('/api/user', [UserController::class, 'current']);
    Route::get('/api/me', [UserController::class, 'current']);
    Route::get('/api/profile', [ProfileController::class, 'show']);
    Route::post('/api/profile', [ProfileController::class, 'update']);

    // Маршруты для работы с чатами
    Route::get('/api/chats', [ChatController::class, 'index']);
    Route::post('/api/chats', [ChatController::class, 'store']);
    Route::get('/api/chats/{chat}', [ChatController::class, 'show']);
    Route::put('/api/chats/{chat}', [ChatController::class, 'update']);
    Route::delete('/api/chats/{chat}', [ChatController::class, 'destroy']);

    // Маршруты для AI чата
    Route::post('/api/ai/chat', [AiChatController::class, 'chat']);
    Route::get('/api/ai/test', [AiChatController::class, 'test']);
});

// Публичные маршруты для AI - доступны без авторизации
Route::post('/api/nvidia-chat', [AiChatController::class, 'chat']);

// Маршрут "catch-all" для SPA
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '^(?!api).*$');

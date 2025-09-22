<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\OcrController;

// Тестовый маршрут для проверки работы API
Route::get('test', function() {
    return response()->json(['status' => 'API работает!', 'time' => now(), 'environment' => app()->environment()]);
});

// Еще один тестовый маршрут - полностью публичный
Route::get('public-user', function() {
    return response()->json(['message' => 'Публичный API маршрут работает', 'time' => now()]);
});

// Публичные API-маршруты (без слеша в начале)
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Добавляем публичный маршрут user/public для тестирования
Route::get('user/public', function (Request $request) {
    return response()->json(['message' => 'Публичный маршрут работает']);
});

// Простой тестовый маршрут для чатов без авторизации
Route::get('test-chats', function() {
    return response()->json(['message' => 'Тестовый маршрут для чатов работает', 'time' => now()]);
});

// ПУБЛИЧНЫЕ маршруты для чатов - авторизация проверяется внутри контроллера
Route::get('chats', [ChatController::class, 'index']);
Route::post('chats', [ChatController::class, 'store']);
Route::get('chats/{chat}', [ChatController::class, 'show']);
Route::put('chats/{chat}', [ChatController::class, 'update']);
Route::delete('chats/{chat}', [ChatController::class, 'destroy']);

// Тестовый маршрут для AI чата, который не требует сложной логики
Route::get('ai/test', [\App\Http\Controllers\AiChatController::class, 'test']);

// Маршрут для взаимодействия с AI моделями
Route::post('ai/chat', [\App\Http\Controllers\AiChatController::class, 'chat']);
// Альтернативный маршрут для чата с NVIDIA AI
Route::post('nvidia-chat', [\App\Http\Controllers\AiChatController::class, 'chat']);

// Защищенные API-маршруты (без слеша в начале)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    // Маршрут для получения данных текущего пользователя
    Route::get('user', [UserController::class, 'current']);

    // Альтернативный маршрут для получения данных пользователя
    Route::get('me', [UserController::class, 'current']);

    // Еще один альтернативный маршрут для теста
    Route::get('auth-test', function (Request $request) {
        return response()->json([
            'message' => 'Аутентифицированный маршрут работает',
            'user_id' => $request->user()->id,
            'time' => now()
        ]);
    });

    // Маршруты для профиля
    Route::get('profile', [ProfileController::class, 'show']);

    // Поддержка обоих методов для обновления профиля
    Route::post('profile', [ProfileController::class, 'update']);
    Route::put('profile', [ProfileController::class, 'update']);

    // Альтернативный маршрут для обновления профиля
    Route::post('profile/update', [ProfileController::class, 'update']);

    // Тестовый маршрут внутри защищенной группы
    Route::get('auth-chats-test', function() {
        return response()->json(['message' => 'Тестовый защищенный маршрут для чатов работает', 'time' => now()]);
    });

    Route::post('/ocr', [OcrController::class, 'analyze']);
});

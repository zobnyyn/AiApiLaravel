<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Получить все чаты авторизованного пользователя
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Если пользователь не аутентифицирован — возвращаем пустой список (клиент ожидает массив)
        $user = Auth::user();
        if (!$user) {
            return response()->json([]);
        }

        $query = $user->chats()->orderBy('updated_at', 'desc');

        // Фильтрация по модели, если указана
        if ($request->has('model')) {
            $query->where('model', $request->model);
        }

        $chats = $query->get();
        return response()->json($chats);
    }

    /**
     * Создать новый чат
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $chat = $user->chats()->create([
            'title' => $request->title ?? 'Новый чат',
            'messages' => $request->messages ?? [],
            'model' => $request->model ?? 'default',
        ]);

        return response()->json($chat, 201);
    }

    /**
     * Получить конкретный чат
     */
    public function show(Chat $chat)
    {
        // Проверка принадлежности чата пользователю
        if ($chat->user_id !== Auth::id()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return response()->json($chat);
    }

    /**
     * Обновить чат
     */
    public function update(Request $request, Chat $chat)
    {
        // Проверка принадлежности чата пользователю
        if ($chat->user_id !== Auth::id()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $chat->update($request->only(['title', 'messages', 'model']));

        return response()->json($chat);
    }

    /**
     * Удалить чат
     */
    public function destroy(Chat $chat)
    {
        // Проверка принадлежности чата пользователю
        if ($chat->user_id !== Auth::id()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $chat->delete();

        return response()->json(null, 204);
    }
}

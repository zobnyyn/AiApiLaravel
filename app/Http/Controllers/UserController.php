<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Получить данные текущего пользователя
     */
    public function current(Request $request)
    {
        // Получаем текущего пользователя из запроса
        $user = $request->user();

        // Если пользователь не найден (не аутентифицирован)
        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        // Возвращаем данные пользователя
        return response()->json($user);
    }
}

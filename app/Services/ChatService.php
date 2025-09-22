<?php

namespace App\Services;

use App\Models\Chat;
use Illuminate\Support\Facades\Log;

class ChatService
{
    /**
     * Поиск чата по chatId, userId и модели
     */
    public function findChat($chatId, $userId, $model)
    {
        $chat = null;
        if ($chatId) {
            $chat = Chat::find($chatId);
            if ($chat && $chat->model !== $model) {
                Log::warning('Запрашиваемая модель не соответствует модели чата', [
                    'requested_model' => $model,
                    'chat_model' => $chat->model
                ]);
                $chat = Chat::where('user_id', $userId)->where('model', $model)->latest()->first();
            }
            if (!$chat && $userId) {
                $chat = Chat::where('user_id', $userId)->where('model', $model)->first();
            }
        } elseif ($userId) {
            $chat = Chat::where('user_id', $userId)->where('model', $model)->latest()->first();
        }
        return $chat;
    }

    /**
     * Формирование истории сообщений для чата
     */
    public function buildChatMessages($chat, $messageToSend)
    {
        $history = $chat->messages ?? [];
        $formattedHistory = collect($history)->map(function ($msg) {
            return [
                'role' => $msg['role'] ?? 'user',
                'content' => $msg['content'] ?? '',
            ];
        })->toArray();
        return array_merge($formattedHistory, [
            ['role' => 'user', 'content' => $messageToSend]
        ]);
    }

    /**
     * Сохранение новых сообщений в чат
     */
    public function saveChatMessage($chat, $userMessage, $assistantMessage, $reasoning = null)
    {
        $messagesToAdd = [
            ['role' => 'user', 'content' => $userMessage],
            ['role' => 'assistant', 'content' => $assistantMessage]
        ];
        if ($reasoning) {
            $messagesToAdd[1]['reasoning'] = $reasoning;
        }
        $chat->messages = array_merge($chat->messages, $messagesToAdd);
        $chat->save();
    }
}


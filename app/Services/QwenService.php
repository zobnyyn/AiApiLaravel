<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\ChatMessage;

class QwenService
{
    public function getHistory($userId, $sessionId)
    {
        return ChatMessage::where('user_id', $userId)
                          ->where('session_id', $sessionId)
                          ->orderBy('created_at')
                          ->get();
    }

    public function buildMessage($history, $message)
    {
        $messagesArr = [];
        // Добавляем системное сообщение для контекста
        $messagesArr[] = [
            'role' => 'system',
            'content' => 'Ты — Qwen2.5-Coder, языковая модель от Alibaba, специализируешься на генерации кода и технических ответах.'
        ];
        // Добавляем сообщения из истории
        foreach ($history as $msg) {
            $messagesArr[] = [
                'role' => $msg->role,
                'content' => $msg->content,
            ];
        }
        // Добавляем текущее сообщение пользователя
        $messagesArr[] = [
            'role' => 'user',
            'content' => $message,
        ];
        return $messagesArr;
    }

    public function sendToApi($messages, $stream = false)
    {
        $payload = [
            'model' => 'qwen/qwen2.5-coder-32b-instruct',
            'messages' => $messages,
            'temperature' => 0.2,
            'top_p' => 0.7,
            'max_tokens' => 1024,
            'stream' => false
        ];
        $headers = [
            'Authorization' => 'Bearer ' . config('services.qwen.api_key'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $response = Http::withHeaders($headers)
            ->post('https://integrate.api.nvidia.com/v1/chat/completions', $payload);
        return $response->json();
    }

    public function saveMessage($userMessage, $aiAnswer, $userId, $sessionId)
    {
        ChatMessage::create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'role' => 'user',
            'content' => $userMessage,
            'model' => 'qwen',
        ]);
        if ($aiAnswer) {
            ChatMessage::create([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'role' => 'assistant',
                'content' => $aiAnswer,
                'model' => 'qwen',
            ]);
        }
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\ChatMessage;

class Llama4ScoutService
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
            'content' => 'Ты - Llama-4-Scout, языковая модель от Meta, специализируешься на генерации качественного контента и творческих задачах.'
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
            'model' => 'meta/llama-4-scout-17b-16e-instruct',
            'messages' => $messages,
            'max_tokens' => 512,
            'temperature' => 1.00,
            'top_p' => 1.00,
            'frequency_penalty' => 0.00,
            'presence_penalty' => 0.00,
            'stream' => false
        ];
        $headers = [
            'Authorization' => 'Bearer ' . config('services.llama4scout.api_key'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
        $response = \Illuminate\Support\Facades\Http::withHeaders($headers)
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
            'model' => 'llama4scout',
        ]);
        if ($aiAnswer) {
            ChatMessage::create([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'role' => 'assistant',
                'content' => $aiAnswer,
                'model' => 'llama4scout',
            ]);
        }
    }
}

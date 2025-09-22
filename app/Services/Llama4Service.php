<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Log;

class Llama4Service
{
    public function getHistory($userId, $sessionId, $model = 'llama4')
    {
        // Логика для получения истории чата из базы данных, с учетом модели
        return ChatMessage::where('user_id', $userId)
                          ->where('session_id', $sessionId)
                          ->where('model', $model)
                          ->orderBy('created_at')
                          ->get();
    }

    public function buildMessage($history, $message)
    {
        // Преобразуем историю сообщений в правильный формат для API
        $messagesArr = [];

        // Добавляем системное сообщение для контекста
        $messagesArr[] = [
            'role' => 'system',
            'content' => 'Ты - Llama-4-Maverick, мощная языковая модель с архитектурой MoE (Mixture of Experts) от Meta, содержащая 128 экспертных сетей. Ты специализируешься на творческих задачах и генерации качественного контента.'
        ];

        // Добавляем сообщения из истории
        if (is_array($history) || $history instanceof \Illuminate\Support\Collection) {
            foreach ($history as $msg) {
                // Проверяем, что сообщения в правильном формате и не пустые
                if (is_array($msg) && isset($msg['role']) && isset($msg['content']) && !empty($msg['content'])) {
                    $messagesArr[] = [
                        'role' => $msg['role'],
                        'content' => $msg['content'],
                    ];
                } elseif (is_object($msg) && isset($msg->role) && isset($msg->content) && !empty($msg->content)) {
                    $messagesArr[] = [
                        'role' => $msg->role,
                        'content' => $msg->content,
                    ];
                }
            }
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
        // Журналируем сообщения для отладки
        Log::debug('Отправка сообщений в API Llama4', [
            'messages' => $messages,
            'count' => count($messages)
        ]);

        // Включаем режим отладки для временных переменных
        Log::debug('Активирован режим локальной имитации API для отладки', [
            'AI_MOCK_RESPONSE' => env('AI_MOCK_RESPONSE', false)
        ]);

        // Если включен режим имитации, возвращаем тестовый ответ
        if (env('APP_DEBUG') && env('AI_MOCK_RESPONSE', false)) {
            Log::info('Возвращаем имитационный ответ вместо вызова API NVIDIA');
            return $this->generateMockResponse();
        }

        // Проверяем и очищаем сообщения от потенциально проблемных данных
        $cleanMessages = [];
        foreach ($messages as $msg) {
            if (!isset($msg['role']) || !isset($msg['content'])) {
                continue;
            }

            // Допустимые роли для NVIDIA API
            $validRoles = ['system', 'user', 'assistant'];
            $role = in_array($msg['role'], $validRoles) ? $msg['role'] : 'user';

            // Убедимся, что контент не пустой и не содержит проблемные символы
            $content = is_string($msg['content']) ? $msg['content'] : json_encode($msg['content']);
            if (empty(trim($content))) continue;

            $cleanMessages[] = [
                'role' => $role,
                'content' => $content
            ];
        }

        // Если после очистки сообщений не осталось, добавляем хотя бы одно
        if (empty($cleanMessages)) {
            $cleanMessages[] = [
                'role' => 'user',
                'content' => 'Привет'
            ];
        }

        // Используем оригинальную структуру payload из документации NVIDIA
        $payload = [
            'model' => 'meta/llama-4-maverick-17b-128e-instruct',
            'messages' => $cleanMessages,
            'max_tokens' => 512,
            'temperature' => 1.00,
            'top_p' => 1.00,
            'frequency_penalty' => 0.00,
            'presence_penalty' => 0.00,
            'stream' => false
        ];

        $headers = [
            'Authorization' => 'Bearer ' . config('services.llama4.api_key'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        Log::debug('Payload для API Llama4', [
            'payload' => $payload,
            'headers' => array_keys($headers)
        ]);

        try {
            // Отправляем запрос к API NVIDIA
            $response = Http::withHeaders($headers)
                ->post('https://integrate.api.nvidia.com/v1/chat/completions', $payload);

            // Журналируем ответ
            Log::debug('Ответ от API Llama4', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('Ошибка при отправке запроса к Llama4 API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Генерирует имитационный ответ для локальной отладки
     */
    private function generateMockResponse()
    {
        $mockResponseContent = "Это тестовый ответ от имитации API Llama-4-Maverick для локальной отладки. " .
            "В реальной среде здесь будет ответ от NVIDIA API. " .
            "Режим имитации активирован для упрощения разработки и тестирования без использования квоты API.";

        $mockResponseData = [
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => $mockResponseContent
                    ],
                    'finish_reason' => 'stop',
                    'index' => 0
                ]
            ],
            'created' => time(),
            'id' => 'mock-chatcmpl-' . uniqid(),
            'model' => 'meta/llama-4-maverick-17b-128e-instruct',
            'object' => 'chat.completion',
            'usage' => [
                'completion_tokens' => 50,
                'prompt_tokens' => 20,
                'total_tokens' => 70
            ]
        ];

        // Создаем объект, имитирующий ответ HTTP
        return new \Illuminate\Http\Client\Response(json_encode($mockResponseData), 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    public function saveMessage($userMessage, $aiAnswer, $userId, $sessionId)
    {
        ChatMessage::create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'role' => 'user',
            'content' => $userMessage,
            'model' => 'llama4',
        ]);
        if ($aiAnswer) {
            ChatMessage::create([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'role' => 'assistant',
                'content' => $aiAnswer,
                'model' => 'llama4',
            ]);
        }
    }
}

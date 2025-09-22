<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Log;

class DeepSeekR1Service
{
    public function getHistory($userId, $sessionId, $model = 'deepseek_r1')
    {
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
            'content' => 'Ты - DeepSeek R1, мощная языковая модель, которая может объяснять свои рассуждения. Ты специализируешься на логическом мышлении и пошаговом решении задач.'
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
        // Всегда используем реальное API - принудительно отключаем имитационный режим
        Log::info('DeepSeek R1: Отправка запроса к реальному API NVIDIA');

        // Журналируем сообщения для отладки
        Log::debug('Отправка сообщений в API DeepSeek R1', [
            'messages_count' => count($messages)
        ]);

        // Проверяем и очищаем сообщения от потенциально проблемных данных
        $cleanMessages = [];
        foreach ($messages as $msg) {
            if (!isset($msg['role']) || !isset($msg['content'])) {
                continue;
            }

            // Допустимые роли для NVIDIA API
            $validRoles = ['system', 'user', 'assistant'];
            $role = in_array($msg['role'], $validRoles) ? $msg['role'] : 'user';

            // Убедимся, что контент не пустой и является строкой
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

        // Получаем ID модели из конфигурации
        $modelId = config('services.deepseekr1.model_id', 'deepseek-ai/deepseek-r1');

        // Формируем корректный формат payload согласно документации NVIDIA
        $payload = [
            'model' => $modelId,
            'messages' => $cleanMessages,
            'temperature' => 0.6,
            'top_p' => 0.7,
            'max_tokens' => 4096,
            'stream' => false
        ];

        $headers = [
            'Authorization' => 'Bearer ' . config('services.deepseekr1.api_key'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        Log::debug('Payload для API DeepSeek R1', [
            'payload_size' => strlen(json_encode($payload)),
            'messages_count' => count($cleanMessages),
            'headers' => array_keys($headers)
        ]);

        try {
            // Отправляем запрос к API NVIDIA
            $response = Http::withHeaders($headers)
                ->timeout(30) // Увеличиваем таймаут
                ->post('https://integrate.api.nvidia.com/v1/chat/completions', $payload);

            // Если ответ содержит ошибку, логируем её
            if ($response->failed()) {
                Log::error('Ошибка от API DeepSeek R1', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                // Выбрасываем исключение вместо возврата имитационного ответа
                throw new \Exception('Ошибка от API DeepSeek R1: ' . $response->body());
            }

            // Журналируем успешный ответ
            Log::debug('Успешный ответ от API DeepSeek R1', [
                'status' => $response->status()
            ]);

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';
            $reasoning = $data['choices'][0]['message']['reasoning_content'] ?? null;

            return [
                'content' => $content,
                'reasoning_content' => $reasoning,
                'raw' => $data
            ];
        } catch (\Throwable $e) {
            Log::error('Исключение при отправке запроса к DeepSeek R1 API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Пробрасываем исключение дальше, чтобы видеть реальную ошибку
            throw $e;
        }
    }

    /**
     * Генерирует имитационный ответ для локальной отладки
     */
    private function generateMockResponse()
    {
        $mockContent = "Это тестовый ответ от имитации API DeepSeek R1 для локальной отладки. " .
            "В реальной среде здесь будет ответ от NVIDIA API. Модель DeepSeek R1 отличается продвинутыми возможностями рассуждения и логического мышления.";

        $mockReasoning = "Ход моих рассуждений:\n" .
            "1. Я получил запрос от пользователя\n" .
            "2. Проанализировал контекст запроса и определил его общую тему\n" .
            "3. Сформировал логическую структуру ответа на основе принципов ясности и последовательности\n" .
            "4. Подготовил ответ с учетом возможных дополнительных вопросов\n" .
            "5. Показываю тестовый ответ для демонстрации возможностей отображения рассуждений";

        return [
            'content' => $mockContent,
            'reasoning_content' => $mockReasoning,
            'raw' => [
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => $mockContent,
                            'reasoning_content' => $mockReasoning
                        ],
                        'finish_reason' => 'stop',
                        'index' => 0
                    ]
                ],
                'created' => time(),
                'id' => 'mock-chatcmpl-' . uniqid(),
                'model' => config('services.deepseekr1.model_id', 'deepseek-ai/deepseek-r1'),
                'object' => 'chat.completion'
            ]
        ];
    }

    public function saveMessage($userMessage, $aiAnswer, $userId, $sessionId, $reasoning = null)
    {
        ChatMessage::create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'role' => 'user',
            'content' => $userMessage,
            'model' => 'deepseek_r1',
        ]);
        if ($aiAnswer) {
            $assistantMessage = [
                'user_id' => $userId,
                'session_id' => $sessionId,
                'role' => 'assistant',
                'content' => $aiAnswer,
                'model' => 'deepseek_r1',
            ];

            // Сохраняем рассуждения в JSON-поле в базе данных
            if ($reasoning) {
                // Поскольку в таблице нет отдельного поля для рассуждений,
                // сохраняем их в поле JSON 'content' вместе с основным сообщением
                $assistantMessage['reasoning'] = $reasoning;
            }

            ChatMessage::create($assistantMessage);

            // Логируем информацию о сохранении рассуждений
            Log::debug('Сохранено сообщение DeepSeek R1 с рассуждениями', [
                'has_reasoning' => !is_null($reasoning),
                'reasoning_length' => $reasoning ? strlen($reasoning) : 0
            ]);
        }
    }
}

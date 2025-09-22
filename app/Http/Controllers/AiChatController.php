<?php

namespace App\Http\Controllers;

use App\Services\AiChatServiceFactory;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Chat;

class AiChatController extends Controller
{
    protected $aiChatServiceFactory;
    protected $chatService;

    public function __construct(AiChatServiceFactory $aiChatServiceFactory, ChatService $chatService)
    {
        $this->aiChatServiceFactory = $aiChatServiceFactory;
        $this->chatService = $chatService;
    }

    /**
     * Тестовый метод для проверки работы контроллера
     */
    public function test()
    {
        Log::info('AiChatController::test вызван');
        return response()->json([
            'status' => 'success',
            'message' => 'AI Chat API работает!',
            'time' => now(),
            'controller' => 'AiChatController'
        ]);
    }

    /**
     * Обработка ответа от API
     */
    private function parseApiResponse($response, $model)
    {
        $result = [
            'answer' => null,
            'reasoning' => null,
            'error' => null
        ];
        if (is_array($response) && isset($response['error'])) {
            $result['error'] = $response['error'];
            return $result;
        }
        if ($model === 'deepseek_r1') {
            if (isset($response['reasoning_content'])) {
                $result['reasoning'] = $response['reasoning_content'];
            } elseif (isset($response['choices'][0]['message']['reasoning'])) {
                $result['reasoning'] = $response['choices'][0]['message']['reasoning'];
            }
        }
        if (isset($response['choices'][0]['message']['content'])) {
            $result['answer'] = $response['choices'][0]['message']['content'];
        } elseif (isset($response['content'])) {
            $result['answer'] = $response['content'];
        }
        return $result;
    }

    /**
     * Сохранение сообщения в чат/историю
     */
    private function saveChatMessageUnified($service, $chatService, $chat, $message, $parsed, $userId, $sessionId, $model)
    {
        if ($model === 'deepseek_r1') {
            if ($chat) {
                $chatService->saveChatMessage($chat, $message, $parsed['answer'], $parsed['reasoning']);
            } else {
                $service->saveMessage($message, $parsed['answer'], $userId, $sessionId, $parsed['reasoning']);
            }
        } else {
            if ($chat) {
                $chatService->saveChatMessage($chat, $message, $parsed['answer']);
            } else {
                $service->saveMessage($message, $parsed['answer'], $userId, $sessionId);
            }
        }
    }

    public function chat(Request $request)
    {
        Log::info('AiChatController::chat вызван', [
            'request_data' => $request->all(),
            'user_id' => Auth::id(),
        ]);
        try {
            $model = $this->validateModel($request);
            $service = $this->validateService($model);
            $message = $this->validateMessage($request);
            $messageToSend = $this->buildMessageWithImages($message, $request->input('images', []));
            $userId = Auth::id() ?: 0;
            $sessionId = $request->session()->getId();
            $chatId = $request->input('chat_id');
            Log::info('Параметры запроса:', [
                'userId' => $userId,
                'sessionId' => $sessionId,
                'model' => $model,
                'chatId' => $chatId
            ]);
            $chat = $this->chatService->findChat($chatId, $userId, $model);
            $history = $chat ? $this->chatService->buildChatMessages($chat, $messageToSend)
                             : $service->buildMessage($service->getHistory($userId, $sessionId, $model), $messageToSend);
            $response = $service->sendToApi($history);
            Log::info('API response', ['response' => $response]);
            $parsed = $this->parseApiResponse($response, $model);
            if ($parsed['error']) {
                return response()->json(['error' => $parsed['error']], 500);
            }
            $this->saveChatMessageUnified($service, $this->chatService, $chat, $message, $parsed, $userId, $sessionId, $model);
            $result = ['answer' => $parsed['answer']];
            if ($model === 'deepseek_r1') {
                $result['reasoning'] = $parsed['reasoning'];
            }
            return response()->json($result);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    // --- Приватные методы рефакторинга ---
    private function validateModel(Request $request)
    {
        $model = $request->input('model');
        if (!$model) {
            Log::warning('Модель не указана в запросе');
            throw new \InvalidArgumentException('Не указана модель');
        }
        return $model;
    }
    private function validateService($model)
    {
        $service = $this->aiChatServiceFactory->getService($model);
        if (!$service) {
            Log::warning('Неизвестная модель: ' . $model);
            throw new \InvalidArgumentException('Неизвестная модель: ' . $model);
        }
        return $service;
    }
    private function validateMessage(Request $request)
    {
        $message = $request->input('message');
        if (!$message) {
            Log::warning('Сообщение отсутствует в запросе');
            throw new \InvalidArgumentException('Нет сообщения');
        }
        return $message;
    }
    private function handleException($e)
    {
        Log::error('Ошибка в AiChatController', [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        $code = method_exists($e, 'getCode') ? $e->getCode() : 500;
        return response()->json([
            'error' => 'Внутренняя ошибка сервера',
            'details' => $e->getMessage()
        ], $code ?: 500);
    }

    private function buildMessageWithImages($message, $images)
    {
        $imagesTextParts = [];
        if (is_array($images) && count($images) > 0) {
            foreach ($images as $idx => $img) {
                $name = isset($img['originalName']) ? $img['originalName'] : (isset($img['name']) ? $img['name'] : "image_" . ($idx + 1));
                $ocr = isset($img['recognizedText']) ? $img['recognizedText'] : (isset($img['ocr']) ? $img['ocr'] : '');
                $part = "[Image: " . $name . "]";
                if (strlen(trim($ocr)) > 0) {
                    $part .= "\nOCR: " . trim($ocr);
                } else {
                    $part .= "\nOCR: (no text)";
                }
                $imagesTextParts[] = $part;
            }
        }
        if (!empty($imagesTextParts)) {
            return trim($message . "\n\n" . implode("\n\n", $imagesTextParts));
        }
        return $message;
    }
}

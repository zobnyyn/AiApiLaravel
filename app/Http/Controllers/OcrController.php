<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Support\Facades\Log;
use Exception;

class OcrController extends Controller
{
    public function analyze(Request $request)
    {
        try {
            Log::info('OCR запрос получен', [
                'has_file' => $request->hasFile('image'),
                'file_size' => $request->hasFile('image') ? $request->file('image')->getSize() : 0
            ]);

            // Валидация файла
            $request->validate([
                'image' => 'required|image|max:8192', // Увеличиваем лимит до 8MB
            ]);

            // Проверяем, доступен ли tesseract в системе
            $tesseractCheck = shell_exec('which tesseract 2>/dev/null');
            if (empty(trim($tesseractCheck))) {
                Log::error('Tesseract OCR не найден в системе');

                // Возвращаем понятное сообщение пользователю
                return response()->json([
                    'error' => 'OCR сервис временно недоступен',
                    'message' => 'Функция распознавания текста из изображений требует установки дополнительных компонентов. Пожалуйста, попробуйте позже или обратитесь к администратору.',
                    'technical_details' => 'Tesseract OCR не установлен в системе'
                ], 503); // Service Unavailable
            }

            Log::info('Tesseract найден и доступен');

            // Сохраняем загруженное изображение
            $image = $request->file('image');
            $filename = 'ocr_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

            // Убеждаемся, что директория существует
            $uploadsPath = storage_path('app/public/screenshots');
            if (!is_dir($uploadsPath)) {
                mkdir($uploadsPath, 0755, true);
            }

            $path = $image->storeAs('screenshots', $filename, 'public');
            $fullPath = storage_path('app/public/' . $path);

            Log::info('Изображение сохранено', [
                'path' => $fullPath,
                'exists' => file_exists($fullPath),
                'size' => file_exists($fullPath) ? filesize($fullPath) : 0
            ]);

            if (!file_exists($fullPath)) {
                Log::error('Файл не найден после сохранения: ' . $fullPath);
                return response()->json([
                    'error' => 'Ошибка сохранения файла'
                ], 500);
            }

            // Выполняем OCR
            Log::info('Запуск Tesseract OCR...');
            $ocr = new TesseractOCR($fullPath);

            // Настраиваем языки и параметры OCR
            $ocr->lang('eng', 'rus'); // Поддержка английского и русского языков
            $ocr->psm(6); // Assume a single uniform block of text
            $ocr->oem(3); // Default OCR Engine Mode

            $text = $ocr->run();

            Log::info('OCR выполнен успешно', [
                'text_length' => strlen($text),
                'text_preview' => substr($text, 0, 200),
                'has_text' => !empty(trim($text))
            ]);

            // Удаляем временный файл
            if (file_exists($fullPath)) {
                unlink($fullPath);
                Log::info('Временный файл удален');
            }

            // Проверяем, что текст был распознан
            $cleanText = trim($text);
            if (empty($cleanText)) {
                Log::warning('Tesseract не смог распознать текст в изображении');
                return response()->json([
                    'text' => '',
                    'success' => true,
                    'message' => 'Изображение обработано, но текст не найден. Возможно, изображение содержит только графические элементы или текст нечитаемый.'
                ]);
            }

            return response()->json([
                'text' => $cleanText,
                'success' => true,
                'message' => 'Текст успешно распознан'
            ]);

        } catch (\thiagoalessio\TesseractOCR\TesseractNotFoundException $e) {
            Log::error('TesseractNotFoundException', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Удаляем временный файл при ошибке
            if (isset($fullPath) && file_exists($fullPath)) {
                unlink($fullPath);
            }

            return response()->json([
                'error' => 'OCR сервис недоступен',
                'message' => 'Функция распознавания текста временно недоступна. Пожалуйста, попробуйте позже.',
                'technical_details' => 'Tesseract OCR не найден или неправильно настроен'
            ], 503);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Ошибка валидации OCR запроса', [
                'errors' => $e->errors()
            ]);

            return response()->json([
                'error' => 'Неверный формат файла',
                'message' => 'Пожалуйста, загрузите изображение в формате JPG, PNG или GIF размером не более 8 МБ.',
                'details' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Общая ошибка OCR', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Удаляем временный файл при ошибке
            if (isset($fullPath) && file_exists($fullPath)) {
                unlink($fullPath);
            }

            return response()->json([
                'error' => 'Ошибка обработки изображения',
                'message' => 'Произошла ошибка при обработке изображения. Пожалуйста, попробуйте еще раз.',
                'technical_details' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера'
            ], 500);
        }
    }
}

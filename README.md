# AiApilaravel — API для общения с чат-моделями ИИ

Современное Laravel-приложение, оптимизированное для общения с различными моделями искусственного интеллекта через единый API-интерфейс. Реализованы возможности чата с ИИ, управление профилями пользователей, хранение истории сообщений и расширяемая фабрика сервисов ИИ.

## Стек технологий

- **Backend**: Laravel 12, PHP 8.2
- **База данных**: MySQL 
- **API**: RESTful API с JSON-форматированием
- **Аутентификация**: Laravel Sanctum (токены доступа)
- **Контейнеризация**: Docker + Docker Compose
- **AI-сервисы**: Интеграция с Llama4, Qwen 2.5, DeepSeekR1

## Особенности
- Интеграция с несколькими чат-моделями ИИ через унифицированный интерфейс
- **OCR-функциональность**: Qwen 2.5 и DeepSeek поддерживают чтение текста с изображений
- У DeepSeekR1 есть рассуждения
- Защищенный API с использованием Laravel Sanctum
- Расширяемая архитектура для добавления новых моделей ИИ
- Профили пользователей и хранение истории сообщений

## Архитектура (кратко)
- app/Http/Controllers — контроллеры API
- app/Models — модели Eloquent (User, Profile, Chat, ChatMessage)
- app/Services — адаптеры и фабрика AI-сервисов
- app/Helpers — вспомогательные утилиты
- routes/api.php — API-маршруты
- database/migrations — миграции

## AI-сервисы
Проект уже содержит абстракции и реализации нескольких сервисов:
- **Llama4Service**, Llama4ScoutService — интеграция с Llama4
- **QwenService** — интеграция с Qwen 2.5 (включая OCR — чтение текста с изображений)
- **DeepSeekR1Service** — интеграция с DeepSeek (включая OCR — чтение текста с изображений)

---
<img width="2559" height="1326" alt="image" src="https://github.com/user-attachments/assets/73ccc859-bb95-46f3-9f2c-70d1a6c50196" />
<img width="2554" height="1440" alt="Снимок экрана от 2025-09-22 01-16-25" src="https://github.com/user-attachments/assets/ae0d60fb-d101-4ba2-afd3-4305ee74ab19" />
<img width="2560" height="1440" alt="Снимок экрана от 2025-09-22 01-13-21" src="https://github.com/user-attachments/assets/bbea59cd-1b4a-4985-a7a1-47bb81deb1b3" />
<img width="2560" height="1440" alt="Снимок экрана от 2025-09-22 01-14-10" src="https://github.com/user-attachments/assets/2831ebb2-2014-449e-8783-3aa21e5cbc5e" />
<img width="2560" height="1440" alt="Снимок экрана от 2025-09-22 01-14-46" src="https://github.com/user-attachments/assets/65ecb5ec-d5b4-4da3-9da9-a976c599c5f6" />
<img width="2560" height="1440" alt="Снимок экрана от 2025-09-22 01-15-09" src="https://github.com/user-attachments/assets/b55b2a5d-adbc-40d2-9d2b-8a7ea5a3ff04" />
<img width="2560" height="1440" alt="Снимок экрана от 2025-09-22 01-15-27" src="https://github.com/user-attachments/assets/412ba976-521e-429e-928a-9c1ed4c35c37" />





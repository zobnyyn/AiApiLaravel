<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-yellow-900 to-slate-900 text-white relative overflow-hidden flex flex-col">
    <!-- Content -->
    <div class="relative z-10 flex flex-col min-h-screen">
      <!-- Header -->
      <header class="p-6">
        <NavBar />
      </header>

      <!-- Main Content -->
      <main class="flex-1 flex p-0 sm:p-6 overflow-hidden">
        <div class="w-full max-w-6xl mx-auto flex flex-col md:flex-row gap-6 h-full">

          <!-- Left Sidebar - History -->
          <div class="w-full md:w-64 bg-white/5 backdrop-blur-lg rounded-xl p-4 block md:block overflow-y-auto z-20">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold text-gray-300">История чатов</h3>
              <button class="p-2 rounded-full hover:bg-white/10 transition-colors" @click="newChat">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 3a1 1 0 00-1 1v5H4a1 1 0 100 2h5v5a1 1 0 102 0v-5h5a1 1 0 100-2h-5V4a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>

            <!-- Chat History List -->
            <div class="space-y-2">
              <div
                v-for="(chat, index) in chatHistory"
                :key="index"
                class="p-3 rounded-lg hover:bg-white/10 cursor-pointer transition-colors flex justify-between items-center"
                :class="{'bg-white/10': currentChatIndex === index}"
                @click="loadChat(index)"
              >
                <div class="truncate flex-1">
                  <span class="text-sm">{{ chat.title || 'Новый чат' }}</span>
                </div>
                <button
                  class="p-1 text-gray-400 hover:text-white transition-colors"
                  @click.stop="deleteChat(index)"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Main Chat Area -->
          <div class="flex-1 flex flex-col h-[calc(100vh-8rem)] bg-white/5 backdrop-blur-lg rounded-xl overflow-hidden">
            <!-- Model Info Banner -->
            <div class="bg-white/10 px-4 py-3 flex items-center justify-between">
              <div class="flex items-center">
                <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center mr-3">
                  <span class="text-white font-bold text-xs">QC</span>
                </div>
                <div>
                  <h3 class="text-base font-semibold">Qwen 2.5 Coder</h3>
                  <p class="text-xs text-gray-400">Специализированная модель для кода</p>
                </div>
              </div>
              <div class="hidden md:flex items-center">
                <button class="text-sm text-gray-300 hover:text-white mr-4">
                  Параметры
                </button>
                <span class="text-xs text-green-400">● Доступна</span>
              </div>
            </div>

            <!-- Messages Container -->
            <div
              ref="messagesContainer"
              class="flex-1 overflow-y-auto p-4 space-y-4"
              :class="{'opacity-60': isLoading}"
            >
              <!-- Welcome Message if no messages -->
              <div v-if="currentChat.messages.length === 0" class="flex flex-col items-center justify-center h-full text-center px-4">
                <div class="w-16 h-16 bg-yellow-500 rounded-xl flex items-center justify-center mb-4">
                  <span class="text-white font-bold text-lg">QC</span>
                </div>
                <h2 class="text-2xl font-bold mb-2 bg-gradient-to-r from-white to-gray-300 bg-clip-text text-transparent">
                  Qwen 2.5 Coder
                </h2>
                <p class="text-gray-300 max-w-md mb-6">
                  Специализированная модель для программирования, разработки и анализа кода. Отлично разбирается в различных языках программирования и может помочь с написанием, отладкой и оптимизацией кода.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-w-2xl w-full">
                  <button
                    v-for="suggestion in suggestions"
                    :key="suggestion"
                    @click="sendMessage(suggestion)"
                    class="p-3 bg-white/5 hover:bg-white/10 rounded-lg text-left text-sm transition-colors"
                  >
                    {{ suggestion }}
                  </button>
                </div>
              </div>

              <!-- Chat Messages -->
              <div v-for="(message, index) in currentChat.messages" :key="index" class="flex flex-col">
                <!-- User Message -->
                <div v-if="message.role === 'user'" class="flex items-start mb-4">
                  <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center mr-3 flex-shrink-0">
                    <span class="text-white font-semibold">Я</span>
                  </div>
                  <div class="flex-1 bg-white/10 rounded-lg px-4 py-3 text-white">
                    <!-- Предпросмотр изображения, если есть -->
                    <div v-if="message.image" class="mb-3">
                      <div class="relative inline-block max-w-sm">
                        <img
                          :src="message.image.url"
                          :alt="message.image.originalName || 'image'"
                          class="rounded-lg max-w-full h-auto shadow-lg border border-white/20"
                          style="max-height: 300px;"
                        />
                        <div class="absolute top-2 right-2 bg-black/50 backdrop-blur-sm rounded px-2 py-1">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                          </svg>
                        </div>
                      </div>

                      <!-- Примечание: распознанный текст сохраняется в message.image.recognizedText
                           но не отображается пользователю (скрыт), чтобы изображение было прикреплёно как объект. -->
                      <!-- intentionally hidden OCR text for privacy/UI consistency -->
                    </div>

                    <!-- Текст сообщения -->
                    <p v-if="message.content" class="whitespace-pre-line">{{ message.content }}</p>
                  </div>
                </div>

                <!-- AI Message -->
                <div v-else class="flex items-start mb-4">
                  <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                    <span class="text-white font-bold text-xs">QC</span>
                  </div>
                  <div class="flex-1 bg-white/5 rounded-lg px-4 py-3 text-white">
                    <p class="whitespace-pre-line markdown-content" v-html="formatMessage(message.content)"></p>
                  </div>
                </div>
              </div>

              <!-- Loading Indicator -->
              <div v-if="isLoading" class="flex items-start">
                <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                  <span class="text-white font-bold text-xs">QC</span>
                </div>
                <div class="flex-1 bg-white/5 rounded-lg px-4 py-3 text-white">
                  <p class="animate-pulse flex items-center">
                    <span class="h-1.5 w-1.5 bg-gray-300 rounded-full mr-1"></span>
                    <span class="h-1.5 w-1.5 bg-gray-300 rounded-full mr-1"></span>
                    <span class="h-1.5 w-1.5 bg-gray-300 rounded-full"></span>
                  </p>
                </div>
              </div>

              <!-- Indicator for uploaded image (temporary state) -->
              <div v-if="uploadedImages.length > 0" class="uploaded-image-indicator">
                <div
                  v-for="(img, index) in uploadedImages"
                  :key="index"
                  class="flex items-center gap-2"
                >
                  <img :src="img.url" alt="Uploaded image preview" class="w-12 h-12 rounded-lg" />
                  <div class="meta">
                    <p class="text-sm font-semibold text-white truncate">{{ img.originalName }}</p>
                    <!-- Распознанный текст скрыт в UI для более чистого вида -->
                  </div>
                  <!-- Button to remove uploaded image -->
                  <button
                    @click="removeUploadedImage(index)"
                    class="p-1 text-gray-400 hover:text-red-400 transition-colors"
                    title="Удалить изображение"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Input Area -->
            <div class="p-4 border-t border-white/10">
              <!-- Drag & Drop Zone (показывается при перетаскивании) -->
              <div
                v-if="isDragging"
                class="absolute inset-0 bg-yellow-500/20 backdrop-blur-sm rounded-xl border-2 border-yellow-500 border-dashed flex items-center justify-center z-50"
                @dragover.prevent
                @drop.prevent="handleDrop"
                @dragleave="isDragging = false"
              >
                <div class="text-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-yellow-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <p class="text-white text-lg">Отпустите изображение для распознавания текста</p>
                </div>
              </div>

              <form @submit.prevent="sendMessage" class="relative">
                <textarea
                  v-model="userInput"
                  class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 pr-20 text-white resize-none focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                  placeholder="Отправьте сообщение или вставьте изображение (Ctrl+V)..."
                  rows="2"
                  @keydown.enter.prevent="handleEnterKey"
                  @paste="handlePasteImage"
                  @dragover.prevent="isDragging = true"
                  ref="textareaRef"
                ></textarea>

                <!-- Кнопки в правой части -->
                <div class="absolute right-3 bottom-3 flex items-center gap-1">
                  <!-- Кнопка загрузки файла -->
                  <input
                    type="file"
                    ref="fileInput"
                    @change="handleFileUpload"
                    accept="image/*"
                    class="hidden"
                  />
                  <button
                    type="button"
                    @click="$refs.fileInput.click()"
                    class="p-2 text-gray-400 hover:text-yellow-400 transition-colors"
                    title="Загрузить изображение"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                  </button>

                  <!-- Кнопка вставки из буфера -->
                  <button
                    type="button"
                    class="p-2 text-gray-400 hover:text-yellow-400 transition-colors"
                    title="Вставить скриншот из буфера обмена (Ctrl+V)"
                    @click="() => textareaRef.focus && textareaRef.focus()"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </button>

                  <!-- Кнопка отправки -->
                  <button
                    type="submit"
                    class="p-2 text-white bg-yellow-500 hover:bg-yellow-600 rounded-lg transition-colors"
                    :disabled="isLoading || (!userInput.trim() && !ocrLoading)"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </div>
              </form>

              <div class="flex items-center justify-between mt-2">
                <div class="flex items-center">
                  <button
                    class="text-xs text-yellow-400 hover:text-yellow-300 flex items-center md:hidden mr-3"
                    @click="toggleReasoningMode"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ showReasoning ? 'Скрыть рассуждения' : 'Показать рассуждения' }}
                  </button>
                  <div v-if="ocrLoading" class="text-xs text-yellow-400 animate-pulse flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-3 w-3 text-yellow-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Распознавание текста...
                  </div>
                </div>
                <p class="text-xs text-gray-400 mt-1 md:mt-0 text-right">
                  Поддерживается вставка изображений • Qwen может давать неточную информацию
                </p>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed, nextTick, watch } from 'vue';
import { marked } from 'marked';
import hljs from 'highlight.js';
import 'highlight.js/styles/atom-one-dark.css';
import NavBar from './NavBar.vue';
import axios from 'axios';

// Настройка базового URL для axios
axios.defaults.baseURL = '/';

// Настройка marked для синтаксического выделения кода
marked.setOptions({
  highlight: function(code, lang) {
    if (lang && hljs.getLanguage(lang)) {
      return hljs.highlight(code, { language: lang }).value;
    }
    return hljs.highlightAuto(code).value;
  },
  breaks: true
});

// Текстовая область ввода
const textareaRef = ref(null);
const fileInput = ref(null);
const isDragging = ref(false);
const ocrLoading = ref(false);
// Набор objectURL для последующего освобождения
const tempObjectUrls = new Set();
const messagesContainer = ref(null);

// Новое состояние: массив загруженных изображений (максимум 4)
const uploadedImages = ref([]);

// Состояние чата
const userInput = ref('');
const isLoading = ref(false);
const chatHistory = ref([
  { title: 'Новый чат', messages: [] }
]);
const currentChatIndex = ref(0);
const currentChat = computed(() => chatHistory.value[currentChatIndex.value]);

// Примеры запросов для подсказок
const suggestions = [
  'Напиши простой REST API на Express.js',
  'Объясни разницу между Promise и async/await в JavaScript',
  'Оптимизируй этот SQL запрос: SELECT * FROM users WHERE active = true',
  'Как настроить Docker для локальной разработки на React и Node.js?'
];

// Удалён код инициализации Three.js и анимации звёзд

const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

// Добавлена функция форматирования сообщений (Markdown -> HTML)
const formatMessage = (message) => {
  if (!message) return '';
  try {
    // marked.parse используется в других компонентах
    return marked.parse(typeof message === 'string' ? message : String(message));
  } catch (e) {
    console.error('formatMessage error:', e);
    return typeof message === 'string' ? message : String(message);
  }
};

// Функция для загрузки всех чатов пользователя с сервера
const loadChatsFromServer = async () => {
  try {
    isLoading.value = true;
    console.log('Загрузка чатов с сервера...');
    // Используем абсолютный путь для API-запроса с фильтрацией по модели qwen
    const response = await axios.get('/api/chats?model=qwen');
    console.log('Ответ сервера при загрузке чатов:', response.data);

    if (response.data && response.data.length > 0) {
      // Преобразуем данные из сервера в нужный формат
      chatHistory.value = response.data.map(chat => ({
        id: chat.id,
        title: chat.title || 'Новый чат',
        messages: normalizeMessagesArray(chat.messages || []),
        model: 'qwen' // Явно указываем модель
      }));
      currentChatIndex.value = 0; // Выбираем первый чат
      console.log('Чаты Qwen успешно загружены:', chatHistory.value);
    } else {
      console.log('У пользователя нет чатов Qwen, создаем новый');
      // Если у пользователя нет чатов, создаем пустой
      chatHistory.value = [{ title: 'Новый чат', messages: [], model: 'qwen' }];
      // И сохраняем его на сервере
      const chatId = await createChatOnServer(chatHistory.value[0]);
      if (chatId) {
        chatHistory.value[0].id = chatId;
        console.log('Новый чат Qwen создан с ID:', chatId);
      }
    }
  } catch (error) {
    console.error('Ошибка загрузки чатов:', error);
    if (error.response) {
      console.error('Статус ответа:', error.response.status);
      console.error('Данные ответа:', error.response.data);

      // Выводим более подробную информацию о маршруте
      console.error('URL запроса:', error.config.url);
      console.error('Метод запроса:', error.config.method);
      console.error('Базовый URL axios:', axios.defaults.baseURL || 'не установлен');
    }
    // В случае ошибки создаем пустой локальный чат
    chatHistory.value = [{ title: 'Новый чат', messages: [], model: 'qwen' }];
  } finally {
    isLoading.value = false;
  }
};

// Функция для создания нового чата на сервере
const createChatOnServer = async (chat) => {
  try {
    console.log('Создание нового чата на сервере:', chat);
    const response = await axios.post('/api/chats', {
      title: chat.title,
      messages: chat.messages || [],
      model: 'qwen' // Указываем модель qwen
    });
    console.log('Чат успешно создан на сервере:', response.data);
    // Обновляем локальный чат с ID с сервера
    return response.data.id;
  } catch (error) {
    console.error('Ошибка создания чата:', error);
    if (error.response) {
      console.error('Статус ответа:', error.response.status);
      console.error('Данные ответа:', error.response.data);
    }
    return null;
  }
};

// Функция для обновления чата на сервере
const updateChatOnServer = async (chat) => {
  try {
    if (!chat.id) {
      console.log('У чата нет ID, создаем новый чат на сервере');
      // Если у чата нет ID, значит он еще не был сохранен на сервере
      const id = await createChatOnServer(chat);
      if (id) {
        console.log('Чат успешно создан с ID:', id);
        chat.id = id;
      } else {
        console.error('Не удалось получить ID для нового чата');
      }
      return;
    }

    console.log('Обновление чата на сервере, ID:', chat.id);
    const response = await axios.put(`/api/chats/${chat.id}`, {
      title: chat.title,
      messages: chat.messages || [],
      model: 'qwen' // Используем модель qwen при обновлении
    });
    console.log('Чат успешно обновлен:', response.data);
  } catch (error) {
    console.error('Ошибка обновления чата:', error);
    if (error.response) {
      console.error('Статус ответа:', error.response.status);
      console.error('Данные ответа:', error.response.data);
    }
  }
};

// Функция для удаления чата на сервере
const deleteChatFromServer = async (chatId) => {
  try {
    if (!chatId) return;
    await axios.delete(`/api/chats/${chatId}`);
    return true;
  } catch (error) {
    console.error('Ошибка удаления чата:', error);
    return false;
  }
};

// Утилиты для очистки/нормализации сообщений (устраняет объекты в content)
const normalizeMessage = (msg) => {
  if (!msg) return { role: 'assistant', content: '' };
  const role = msg.role || 'assistant';
  let content = '';

  if (typeof msg.content === 'string') {
    content = msg.content;
  } else if (msg.content === null || typeof msg.content === 'undefined') {
    content = '';
  } else if (typeof msg.content === 'object') {
    // Если это объект, попытаемся взять поле content/text/message
    if (typeof msg.content.text === 'string') {
      content = msg.content.text;
    } else if (typeof msg.content.message === 'string') {
      content = msg.content.message;
    } else {
      try {
        content = JSON.stringify(msg.content);
      } catch (e) {
        content = String(msg.content);
      }
    }
  } else {
    content = String(msg.content);
  }

  // Ограничим длину заголовка/контента в UI при необходимости
    return { role, content, image: msg.image ?? undefined, reasoning: msg.reasoning ?? undefined };
};

const normalizeMessagesArray = (arr) => {
  if (!arr || !Array.isArray(arr)) return [];
  return arr.map(m => normalizeMessage(m));
};

// Новые функции: обработка вставки и загрузки изображений, OCR
const handlePasteImage = async (event) => {
  const items = (event.clipboardData || event.originalEvent.clipboardData).items;
  let blob = null;

  // Ищем изображение в буфере обмена
  for (const item of items) {
    if (item.type.indexOf('image') === 0) {
      blob = item.getAsFile();
      break;
    }
  }

  if (!blob) {
    console.log('В буфере обмена нет изображения');
    return;
  }

  // Ограничение: максимум 4 изображения
  if (uploadedImages.value.length >= 4) {
    console.warn('Превышен лимит превью (максимум 4)');
    return;
  }

  try {
    ocrLoading.value = true;

    // Создаем URL для превью изображения
    const imageUrl = URL.createObjectURL(blob);
    tempObjectUrls.add(imageUrl);

    // Создаем FormData для отправки изображения
    const formData = new FormData();
    formData.append('image', blob);

    // Отправляем на OCR сервер
    console.log('Отправка изображения на OCR-сервер...');
    const response = await axios.post('/ocr', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    let recognizedText = '';
    if (response.data && response.data.success && response.data.text) {
      recognizedText = response.data.text.trim();
      console.log('Текст успешно распознан');
    } else {
      console.warn('Изображение обработано, но текст не найден');
    }

    // Добавляем изображение в массив превью
    uploadedImages.value.push({
      url: imageUrl,
      originalName: 'screenshot.png',
      recognizedText: recognizedText,
      file: blob
    });

    // Не очищаем поле ввода автоматически — пользователь может отправить текст отдельно
    userInput.value = '';

    // Сохраняем метаданные на сервере при необходимости (не добавляем в историю чата)
    try {
      await updateChatOnServer(currentChat.value);
    } catch (error) {
      console.error('Ошибка при сохранении метаданных чата:', error);
    }

    // Прокручиваем вниз
    await nextTick();
    scrollToBottom();

    // НЕ отправляем автоматически изображение в AI
  } catch (error) {
    console.error('Ошибка при распознавании текста из изображения:', error);
  } finally {
    ocrLoading.value = false;
  }
};

const handleFileUpload = async (event) => {
  const file = event.target.files[0];
  if (!file) return;
  if (!file.type.startsWith('image/')) {
    console.error('Загружен не файл изображения');
    return;
  }
  await processImageFile(file);
  event.target.value = '';
};

const handleDrop = async (event) => {
  isDragging.value = false;
  const files = Array.from(event.dataTransfer.files || []);
  const imageFile = files.find(f => f.type.startsWith('image/'));
  if (!imageFile) return;
  await processImageFile(imageFile);
};

// Обновлённая функция processImageFile — теперь сохраняет изображение в uploadedImage и НЕ отправляет его в чат
const processImageFile = async (file) => {
  try {
    ocrLoading.value = true;

    // Проверяем лимит
    if (uploadedImages.value.length >= 4) {
      console.warn('Превышен лимит превью (максимум 4)');
      return;
    }

    // Создаем URL для превью изображения
    const imageUrl = URL.createObjectURL(file);
    tempObjectUrls.add(imageUrl);

    // Создаем FormData для отправки изображения
    const formData = new FormData();
    formData.append('image', file);

    // Отправляем на OCR сервер
    console.log('Отправка изображения на OCR-сервер...');
    const response = await axios.post('/ocr', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    let recognizedText = '';
    if (response.data && response.data.success && response.data.text) {
      recognizedText = response.data.text.trim();
      console.log('Текст успешно распознан');
    } else {
      console.warn('Изображение обработано, но текст не найден');
    }

    // Добавляем загруженное изображение во временное состояние (не в чат)
    uploadedImages.value.push({
      url: imageUrl,
      originalName: file.name,
      recognizedText: recognizedText,
      file: file
    });

    // Очищаем поле ввода
    userInput.value = '';

    // Если нужно, можно сохранить метаданные чата, но не само изображение в истории
    try {
      await updateChatOnServer(currentChat.value);
    } catch (error) {
      console.error('Ошибка при сохранении метаданных чата:', error);
    }

    // Прокручиваем вниз
    await nextTick();
    scrollToBottom();

  } catch (error) {
    console.error('Ошибка при обработке файла изображения:', error);
  } finally {
    ocrLoading.value = false;
  }
};

// Функция удаления загруженного превью по индексу
const removeUploadedImage = (index) => {
  const img = uploadedImages.value[index];
  if (img && img.url) {
    try { URL.revokeObjectURL(img.url); } catch (e) {}
  }
  uploadedImages.value.splice(index, 1);
};

// Очистка objectURL при размонтировании (добавляем uploadedImages)
onBeforeUnmount(() => {
  try {
    tempObjectUrls.forEach(url => { try { URL.revokeObjectURL(url); } catch (e) {} });
    tempObjectUrls.clear();
    uploadedImages.value.forEach(img => {
      if (img && img.url) {
        try { URL.revokeObjectURL(img.url); } catch (e) {}
      }
    });
  } catch (e) {
    // ignore
  }
});

// Наблюдаем за изменениями в чате для прокрутки вниз
watch(() => currentChat.value.messages.length, () => {
  nextTick(() => { scrollToBottom(); });
});

// Новая функция отправки сообщения
const sendMessage = async (text = null) => {
  // Игнорируем объекты (включая DOM-события) и используем значение из userInput
  if (text && (typeof text === 'object' || (typeof Event !== 'undefined' && text instanceof Event))) {
    try { if (text.preventDefault) text.preventDefault(); } catch (e) {}
    text = null;
  }

  const messageToSend = (typeof text === 'string' && text.trim().length > 0) ? text.trim() : userInput.value.trim();
  const hasImages = uploadedImages.value.length > 0;
  // Разрешаем отправку, если есть текст или прикреплены изображения
  if (!messageToSend && !hasImages) return;
  if (isLoading.value) return;

  console.log('Отправка сообщения:', messageToSend, 'hasImages:', hasImages);

  // Очищаем ввод
  userInput.value = '';

  // Формируем сообщение пользователя; если нет текста, показываем метку "Изображение"
  const userMessage = { role: 'user', content: messageToSend || (hasImages ? 'Изображение' : '') };
  if (hasImages) {
    userMessage.images = uploadedImages.value.map(img => ({ originalName: img.originalName, recognizedText: img.recognizedText || '' }));
  }

  // Добавляем сообщение пользователя в чат
  currentChat.value.messages.push(userMessage);

  // Если это первое сообщение в чате, устанавливаем заголовок
  if (currentChat.value.messages.length === 1) {
    if (typeof userMessage.content === 'string' && userMessage.content.trim().length > 0) {
      const title = userMessage.content.split(' ').slice(0, 3).join(' ') + '...';
      currentChat.value.title = title;
    } else {
      currentChat.value.title = 'Новый чат';
    }
  }

  // Сохраняем чат перед отправкой на модель
  try {
    await updateChatOnServer(currentChat.value);
  } catch (e) {
    console.error('Ошибка при сохранении чата перед запросом к модели:', e);
  }

  await nextTick();
  scrollToBottom();

  isLoading.value = true;
  try {
    const payload = {
      message: userMessage.content,
      model: 'qwen'
    };
    if (currentChat.value.id) payload.chat_id = currentChat.value.id;
    if (hasImages) {
      // Передаем распознанный текст и имя файлов в API, чтобы модель могла их учитывать
      payload.images = userMessage.images;
    }

    const aiResponse = await axios.post('/api/ai/chat', payload);

    console.log('Ответ от AI API:', aiResponse.data);

    if (aiResponse.data && aiResponse.data.answer) {
      currentChat.value.messages.push({ role: 'assistant', content: aiResponse.data.answer, reasoning: aiResponse.data.reasoning });
    } else if (aiResponse.data && aiResponse.data.error) {
      currentChat.value.messages.push({ role: 'assistant', content: `Ошибка: ${aiResponse.data.error}` });
    } else {
      currentChat.value.messages.push({ role: 'assistant', content: 'Не удалось получить ответ от модели.' });
    }

    // Сохраняем обновлённый чат
    try {
      await updateChatOnServer(currentChat.value);
    } catch (e) {
      console.error('Ошибка при сохранении чата после ответа модели:', e);
    }

  } catch (error) {
    console.error('Ошибка при обращении к AI API:', error);
    currentChat.value.messages.push({ role: 'assistant', content: 'Извините, произошла ошибка при обработке запроса.' });
    try { await updateChatOnServer(currentChat.value); } catch (e) {}
  } finally {
    isLoading.value = false;

    // Освобождаем objectURL'ы и очищаем превью изображений после отправки
    try {
      uploadedImages.value.forEach(img => { if (img && img.url) { try { URL.revokeObjectURL(img.url); } catch (e) {} } });
    } catch (e) {}
    uploadedImages.value = [];

    // Сохраняем изменения и скроллим вниз
    try { await updateChatOnServer(currentChat.value); } catch (e) {}
    nextTick().then(() => { scrollToBottom(); });
  }
};

// Функция загрузки выбранного чата из истории
const loadChat = (index) => {
  if (index < 0 || index >= chatHistory.value.length) return;
  currentChatIndex.value = index;
};

// Новое состояние: отображение рассуждений (как в других компонентах)
const showReasoning = ref(true);

// Функция переключения режима отображения рассуждений
const toggleReasoningMode = () => {
  showReasoning.value = !showReasoning.value;
  try { localStorage.setItem('qwen_show_reasoning', showReasoning.value); } catch (e) {}
};

// Функция создания нового чата (локально и на сервере)
const newChat = async () => {
  const newChatObj = { title: 'Новый чат', messages: [], model: 'qwen' };
  // Добавляем локально в начало массива
  chatHistory.value.unshift(newChatObj);
  currentChatIndex.value = 0;
  // Пытаемся создать на сервере
  try {
    const id = await createChatOnServer(newChatObj);
    if (id) newChatObj.id = id;
    console.log('Новый чат Qwen создан с ID:', id);
  } catch (e) {
    console.error('Ошибка создания нового чата на сервере:', e);
  }
};

// Удаление чата
const deleteChat = async (index) => {
  if (index < 0 || index >= chatHistory.value.length) return;
  const chatToDelete = chatHistory.value[index];

  // Если у чата есть ID, удаляем на сервере
  if (chatToDelete && chatToDelete.id) {
    try {
      await deleteChatFromServer(chatToDelete.id);
    } catch (e) {
      console.error('Ошибка удаления чата на сервере:', e);
    }
  }

  // Удаляем локально
  chatHistory.value.splice(index, 1);

  // Если чатов не осталось, создаём новый
  if (chatHistory.value.length === 0) {
    await newChat();
    return;
  }

  // Корректируем индекс
  if (currentChatIndex.value >= chatHistory.value.length) {
    currentChatIndex.value = Math.max(0, chatHistory.value.length - 1);
  }
};

// Обработка нажатия Enter в textarea
const handleEnterKey = (event) => {
  if (!event.shiftKey) {
    sendMessage();
  }
};

// Очистка при размонтировании компонента
onBeforeUnmount(() => {
  try {
    window.removeEventListener('resize', onWindowResize);
    if (renderer) {
      renderer.dispose();
      renderer = null;
    }
    if (scene) {
      scene.dispose();
      scene = null;
    }
    if (camera) {
      camera = null;
    }
  } catch (e) {
    // ignore
  }
});

// Автоматическая загрузка истории чатов при открытии страницы Qwen
onMounted(() => {
  loadChatsFromServer().then(() => {
    nextTick(() => {
      scrollToBottom();
    });
  });
});
</script>

<style>
/* Стили для Markdown-контента */
.markdown-content h1 {
  font-size: 1.5rem;
  font-weight: bold;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
}

.markdown-content h2 {
  font-size: 1.25rem;
  font-weight: bold;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
}

.markdown-content h3 {
  font-size: 1.125rem;
  font-weight: bold;
  margin-top: 0.75rem;
  margin-bottom: 0.5rem;
}

.markdown-content p {
  margin-bottom: 0.75rem;
}

.markdown-content ul, .markdown-content ol {
  padding-left: 1.5rem;
  margin-bottom: 0.75rem;
}

.markdown-content ul {
  list-style-type: disc;
}

.markdown-content ol {
  list-style-type: decimal;
}

.markdown-content li {
  margin-bottom: 0.25rem;
}

.markdown-content code {
  background-color: rgba(0, 0, 0, 0.2);
  padding: 0.1rem 0.3rem;
  border-radius: 0.25rem;
  font-family: monospace;
}

.markdown-content pre {
  margin-bottom: 0.75rem;
  border-radius: 0.5rem;
  overflow-x: auto;
}

.markdown-content pre code {
  display: block;
  padding: 0.75rem;
  background-color: rgba(0, 0, 0, 0.3);
  border-radius: 0.5rem;
}

.markdown-content blockquote {
  border-left: 3px solid rgba(255, 255, 255, 0.2);
  padding-left: 1rem;
  margin-left: 0;
  margin-bottom: 0.75rem;
  font-style: italic;
  color: rgba(255, 255, 255, 0.7);
}

.markdown-content table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 0.75rem;
}

.markdown-content th, .markdown-content td {
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 0.5rem;
  text-align: left;
}

.markdown-content th {
  background-color: rgba(255, 255, 255, 0.05);
}

/* Стиль для превью загруженного изображения под полем ввода */
.uploaded-image-indicator {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem;
  background: rgba(255, 255, 255, 0.03);
  border-radius: 0.5rem;
  margin-top: 0.75rem;
}
.uploaded-image-indicator img { max-height: 48px; border-radius: 0.375rem; }
.uploaded-image-indicator .meta { color: #e5e7eb; font-size: 0.875rem; }
</style>

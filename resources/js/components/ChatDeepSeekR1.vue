<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 text-white relative overflow-hidden flex flex-col">
    <!-- Three.js Background удалён -->
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
              <button class="p-2 rounded-full hover:bg-white/10 transition-colors" @click="newChat">+</button>
            </div>

            <!-- Chat History List -->
            <div class="space-y-2">
              <div
                v-for="(chat, index) in chatHistory"
                :key="index"
                class="p-3 rounded-lg hover:bg-white/10 cursor-pointer flex justify-between items-center"
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
                  ×
                </button>
              </div>
            </div>
          </div>

          <!-- Main Chat Area -->
          <div class="flex-1 flex flex-col h-[calc(100vh-8rem)] bg-white/5 backdrop-blur-lg rounded-xl overflow-hidden">
            <!-- Model Info Banner -->
            <div class="bg-white/10 px-4 py-3 flex items-center justify-between">
              <div class="flex items-center">
                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                  <span class="text-white font-bold text-xs">DSR1</span>
                </div>
                <div>
                  <h3 class="text-base font-semibold">DeepSeek R1</h3>
                  <p class="text-xs text-gray-400">Мощная модель с рассуждениями</p>
                </div>
              </div>
              <div class="hidden md:flex items-center">
                <button
                  class="text-sm text-gray-300 hover:text-white mr-4 flex items-center"
                  @click="toggleReasoningMode"
                >
                  {{ showReasoning ? 'Скрыть рассуждения' : 'Показать рассуждения' }}
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
                <div class="w-16 h-16 bg-blue-500 rounded-xl flex items-center justify-center mb-4">
                  <span class="text-white font-bold text-lg">DSR1</span>
                </div>
                <h2 class="text-2xl font-bold mb-2">
                  DeepSeek R1
                </h2>
                <p class="text-gray-300 max-w-md mb-6">
                  Продвинутая модель с объяснениями процесса рассуждения.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-w-2xl w-full">
                  <button
                    v-for="s in suggestions"
                    :key="s"
                    @click="sendMessage(s)"
                    class="p-3 bg-white/5 hover:bg-white/10 rounded-lg text-left text-sm"
                  >
                    {{ s }}
                  </button>
                </div>
              </div>

              <!-- Chat Messages -->
              <div v-for="(message, index) in currentChat.messages" :key="index" class="flex flex-col">
                <!-- User Message -->
                <div v-if="message.role === 'user'" class="flex items-start mb-4">
                  <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center mr-3">
                    <span class="text-white font-semibold">Я</span>
                  </div>
                  <div class="flex-1 bg-white/10 rounded-lg px-4 py-3">
                    <!-- Изображения, если есть (поддержка нескольких) -->
                    <div v-if="message.images && message.images.length" class="mb-3 grid gap-2">
                      <div v-for="(img, i) in message.images" :key="i" class="relative inline-block max-w-sm">
                        <img
                          :src="img.url"
                          :alt="img.originalName || 'image'"
                          class="rounded-lg max-w-full h-auto"
                          style="max-height:300px;"
                        />
                      </div>
                    </div>
                    <!-- Текст сообщения -->
                    <p v-if="message.content" class="whitespace-pre-line">{{ message.content }}</p>
                  </div>
                </div>

                <!-- AI Message -->
                <div v-else class="flex items-start mb-4">
                  <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                    <span class="text-white font-bold text-xs">DSR1</span>
                  </div>
                  <div class="flex-1 bg-white/5 rounded-lg px-4 py-3">
                    <!-- Отображение рассуждений всегда первым, если они есть -->
                    <div v-if="message.reasoning && showReasoning" class="mb-3 p-3 bg-slate-800/70 rounded-lg text-gray-300 text-sm">
                      <div class="font-semibold mb-1">Процесс рассуждения:</div>
                      <div v-html="formatMessage(message.reasoning)"></div>
                    </div>
                    <!-- Затем выводим основной ответ -->
                    <div>
                      <p class="whitespace-pre-line markdown-content" v-html="formatMessage(message.content)"></p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Loading Indicator -->
              <div v-if="isLoading" class="flex items-start">
                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                  <span class="text-white font-bold text-xs">DSR1</span>
                </div>
                <div class="flex-1 bg-white/5 rounded-lg px-4 py-3">
                  <p class="animate-pulse">Обработка...</p>
                </div>
              </div>

              <!-- Indicator for uploaded image (temporary state) -->
              <div v-if="uploadedImages.length > 0" class="uploaded-image-indicator">
                <div
                  v-for="(img, index) in uploadedImages"
                  :key="index"
                  class="flex items-center gap-2"
                >
                  <img :src="img.url" alt="preview" class="w-12 h-12 rounded-lg" />
                  <div class="meta">
                    <p class="text-sm font-semibold text-white truncate">{{ img.originalName }}</p>
                  </div>
                  <!-- Button to remove uploaded image -->
                  <button
                    @click="removeUploadedImage(index)"
                    class="p-1 text-gray-400 hover:text-red-400"
                    title="Удалить изображение"
                  >
                    ✕
                  </button>
                </div>
              </div>
            </div>

            <!-- Input Area -->
            <div class="p-4 border-t border-white/10">
              <!-- Drag & Drop Zone (показывается при перетаскивании) -->
              <div
                v-if="isDragging"
                class="absolute inset-0 bg-blue-500/20 backdrop-blur-sm rounded-xl border-2 border-blue-500 border-dashed flex items-center justify-center z-50"
                @dragover.prevent
                @drop.prevent="handleDrop"
                @dragleave="isDragging = false"
              >
                <div class="text-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-blue-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <p class="text-white text-lg">Отпустите изображение для распознавания текста</p>
                </div>
              </div>

              <form @submit.prevent="sendMessage" class="relative">
                <textarea
                  v-model="userInput"
                  ref="textareaRef"
                  rows="2"
                  @keydown.enter.prevent="handleEnterKey"
                  @paste="handlePasteImage"
                  @dragover.prevent="isDragging=true"
                  placeholder="Отправьте сообщение или вставьте изображение (Ctrl+V)..."
                  class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 resize-none pr-20"
                ></textarea>

                <!-- Кнопки в правой части -->
                <div class="absolute right-3 bottom-3 flex items-center gap-1">
                  <!-- Кнопка загрузки файла -->
                  <input
                    type="file"
                    ref="fileInput"
                    accept="image/*"
                    @change="handleFileUpload"
                    class="hidden"
                  />
                  <button
                    type="button"
                    @click="$refs.fileInput.click()"
                    class="p-2 text-gray-400 hover:text-blue-400"
                    title="Загрузить изображение"
                  >
                    📁
                  </button>

                  <!-- Кнопка вставки из буфера -->
                  <button
                    type="button"
                    @click="() => textareaRef.focus && textareaRef.focus()"
                    class="p-2 text-gray-400 hover:text-blue-400"
                    title="Вставить скриншот из буфера обмена (Ctrl+V)"
                  >
                    📋
                  </button>

                  <!-- Кнопка отправки -->
                  <button
                    type="submit"
                    class="p-2 text-white bg-blue-500 rounded-lg"
                    :disabled="isLoading || (!userInput.trim() && !ocrLoading)"
                  >
                    ✈
                  </button>
                </div>
              </form>

              <div class="flex items-center justify-between mt-2">
                <div class="flex items-center">
                  <button
                    class="text-xs text-blue-400 md:hidden mr-3"
                    @click="toggleReasoningMode"
                  >
                    {{ showReasoning ? 'Скрыть рассуждения' : 'Показать рассуждения' }}
                  </button>
                  <div v-if="ocrLoading" class="text-xs text-yellow-400">Распознавание текста...</div>
                </div>
                <p class="text-xs text-gray-400">
                  Поддерживается вставка изображений • DeepSeek R1 может давать неточную информацию
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
const messagesContainer = ref(null);
const fileInput = ref(null); // Ссылка на input file

// Переменные для работы с изображениями
const isDragging = ref(false);
const ocrLoading = ref(false);
// Набор objectURL для последующего освобождения
const tempObjectUrls = new Set();
// Новое состояние: массив загруженных изображений (максимум 4)
const uploadedImages = ref([]);

// Утилиты: прокрутка контейнера сообщений вниз
const scrollToBottom = () => {
  if (messagesContainer.value) {
    try {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    } catch (e) {
      // безопасно игнорируем ошибки доступа
      console.error('scrollToBottom error:', e);
    }
  }
};

// Утилита для форматирования сообщений (Markdown -> HTML)
const formatMessage = (message) => {
  if (!message) return '';
  try {
    return marked.parse(typeof message === 'string' ? message : String(message));
  } catch (e) {
    console.error('formatMessage error:', e);
    return typeof message === 'string' ? message : String(message);
  }
};

// Нормализация сообщений (удаляет объекты в content и сохраняет image/reasoning)
const normalizeMessage = (msg) => {
  if (!msg) return { role: 'assistant', content: '' };
  const role = msg.role || 'assistant';
  let content = '';

  if (typeof msg.content === 'string') {
    content = msg.content;
  } else if (msg.content === null || typeof msg.content === 'undefined') {
    content = '';
  } else if (typeof msg.content === 'object') {
    if (typeof msg.content.text === 'string') {
      content = msg.content.text;
    } else if (typeof msg.content.message === 'string') {
      content = msg.content.message;
    } else {
      try { content = JSON.stringify(msg.content); } catch (e) { content = String(msg.content); }
    }
  } else {
    content = String(msg.content);
  }

  return { role, content, images: msg.images ?? msg.images ?? undefined, reasoning: msg.reasoning ?? undefined };
};

const normalizeMessagesArray = (arr) => {
  if (!arr || !Array.isArray(arr)) return [];
  return arr.map(m => normalizeMessage(m));
};

// Состояние чата
const userInput = ref('');
const isLoading = ref(false);
const chatHistory = ref([
  { title: 'Новый чат', messages: [] }
]);
const currentChatIndex = ref(0);
const currentChat = computed(() => chatHistory.value[currentChatIndex.value]);
const showReasoning = ref(true); // Отображение рассуждений включено по умолчанию

// Переключение режима отображения рассуждений
const toggleReasoningMode = () => {
  showReasoning.value = !showReasoning.value;
  // Сохраняем предпочтение в localStorage
  localStorage.setItem('dsr1_show_reasoning', showReasoning.value);
};

// Примеры запросов для подсказок
const suggestions = [
  'Объясни, как работает алгоритм быстрой сортировки',
  'Реши задачу оптимизации: как распределить ресурсы между 5 проектами',
  'Проанализируй преимущества и недостатки разных архитектур нейросетей',
  'Помоги разобраться в квантовых алгоритмах и их применении'
];

// Функция для загрузки всех чатов пользователя с сервера
const loadChatsFromServer = async () => {
  try {
    isLoading.value = true;
    console.log('Загрузка чатов с сервера...');
    // Используем абсолютный путь для API-запроса с фильтрацией по модели deepseek_r1
    const response = await axios.get('/api/chats?model=deepseek_r1');
    console.log('Ответ сервера при загрузке чатов:', response.data);

    if (response.data && response.data.length > 0) {
      // Преобразуем данные из сервера в нужный формат
      chatHistory.value = response.data.map(chat => ({
        id: chat.id,
        title: chat.title || 'Новый чат',
        messages: normalizeMessagesArray(chat.messages || []),
        model: 'deepseek_r1'
      }));
      currentChatIndex.value = 0; // Выбираем первый чат
      console.log('Чаты DeepSeek R1 успешно загружены:', chatHistory.value);
    } else {
      console.log('У пользователя нет чатов DeepSeek R1, создаем новый');
      // Если у пользователя нет чатов с моделью deepseek_r1, создаем пустой
      chatHistory.value = [{ title: 'Новый чат', messages: [], model: 'deepseek_r1' }];
      // И сохраняем его на сервере
      const chatId = await createChatOnServer(chatHistory.value[0]);
      if (chatId) {
        chatHistory.value[0].id = chatId;
        console.log('Новый чат DeepSeek R1 создан с ID:', chatId);
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
    chatHistory.value = [{ title: 'Новый чат', messages: [], model: 'deepseek_r1' }];
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
      model: 'deepseek_r1' // Указываем модель deepseek_r1
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
      model: 'deepseek_r1' // Используем модель deepseek_r1 при обновлении
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

// Функция для отправки сообщения
const sendMessage = async (text = null) => {
  if (text && typeof text === 'object') {
    if (text.preventDefault) text.preventDefault();
    text = null;
  }

  const messageToSend = (typeof text === 'string' && text.trim().length > 0) ? text.trim() : userInput.value.trim();
  const hasText = !!messageToSend;
  const hasImages = uploadedImages.value.length > 0;

  if (!hasText && !hasImages) return;
  if (isLoading.value) return;

  userInput.value = '';

  // Формируем сообщение пользователя — НЕ включаем распознанный текст в content
  const userMessage = { role: 'user', content: hasText ? messageToSend : (hasImages ? 'Изображение' : '') };

  if (hasImages) {
    // Прикрепляем все загруженные превью как массив вложений (включаем recognizedText)
    userMessage.images = uploadedImages.value.map(img => ({ url: img.url, originalName: img.originalName, recognizedText: img.recognizedText || '' }));
  }

  currentChat.value.messages.push(userMessage);

  // Если это первое сообщение в чате, устанавливаем заголовок
  if (currentChat.value.messages.length === 1) {
    if (typeof userMessage.content === 'string' && userMessage.content.trim().length > 0) {
      const title = userMessage.content.split(' ').slice(0, 3).join(' ') + '...';
      currentChat.value.title = title;
    } else {
      currentChat.value.title = 'Новый чат';
    }
    // Сохраняем обновлённый заголовок на сервере
    try { await updateChatOnServer(currentChat.value); } catch (e) { console.error('Ошибка при сохранении заголовка чата:', e); }
  }

  // Сохраняем чат на сервере перед запросом
  try { await updateChatOnServer(currentChat.value); } catch (e) { console.error('Ошибка при сохранении чата перед запросом:', e); }

  await nextTick();
  scrollToBottom();

  // Подготавливаем payload для AI — передаём метаданные изображений, включая recognizedText
  const payload = {
    message: userMessage.content,
    model: 'deepseek_r1'
  };
  if (currentChat.value.id) payload.chat_id = currentChat.value.id;
  if (hasImages) {
    payload.images = userMessage.images.map(i => ({ originalName: i.originalName, recognizedText: i.recognizedText || '' }));
  }

  isLoading.value = true;
  try {
    const aiResponse = await axios.post('/api/ai/chat', payload);
    console.log('Получен ответ от AI API:', aiResponse.data);

    const answer = aiResponse.data?.answer || aiResponse.data?.response || null;
    if (answer) {
      const aiMsg = { role: 'assistant', content: answer };
      if (aiResponse.data?.reasoning) aiMsg.reasoning = aiResponse.data.reasoning;
      currentChat.value.messages.push(aiMsg);
    } else {
      currentChat.value.messages.push({ role: 'assistant', content: 'Не удалось получить ответ от модели.' });
    }
  } catch (error) {
    console.error('Ошибка при обращении к AI API:', error);
    currentChat.value.messages.push({ role: 'assistant', content: 'Ошибка при обращении к AI API.' });
  } finally {
    isLoading.value = false;

    // Очистка превью изображений и освобождение objectURL'ов
    try { uploadedImages.value.forEach(img => { if (img.url) URL.revokeObjectURL(img.url); }); } catch (e) {}
    uploadedImages.value = [];

    // Сохраняем обновлённый чат
    try { await updateChatOnServer(currentChat.value); } catch (e) { console.error('Ошибка при сохранении чата после ответа модели:', e); }

    await nextTick();
    scrollToBottom();
  }
};

// Обработка вставки изображения из буфера
const handlePasteImage = async (event) => {
  try {
    const items = (event.clipboardData || event.originalEvent?.clipboardData)?.items || [];
    let blob = null;
    for (const item of items) { if (item.type?.indexOf('image') === 0) { blob = item.getAsFile(); break; } }
    if (!blob) return;
    if (uploadedImages.value.length >= 4) return;
    ocrLoading.value = true;
    const url = URL.createObjectURL(blob);

    // Отправляем на OCR и сохраняем результат
    try {
      const fd = new FormData(); fd.append('image', blob);
      const res = await axios.post('/ocr', fd, { headers: { 'Content-Type':'multipart/form-data' } });
      const recognized = res?.data?.text ? String(res.data.text).trim() : '';
      uploadedImages.value.push({ url, originalName: 'screenshot.png', file: blob, recognizedText: recognized });
    } catch (ocrErr) {
      console.warn('OCR failed for pasted image', ocrErr);
      uploadedImages.value.push({ url, originalName: 'screenshot.png', file: blob, recognizedText: '' });
    } finally {
      ocrLoading.value = false;
    }

    // Fire OCR in background already handled above; сохранение чата
    userInput.value = '';
    try { await updateChatOnServer(currentChat.value); } catch (e) {}
    await nextTick(); scrollToBottom();
  } catch (e) { console.error(e); ocrLoading.value = false; }
};

const processImageFile = async (file) => {
  try {
    if (uploadedImages.value.length >= 4) return;
    ocrLoading.value = true;
    const url = URL.createObjectURL(file);

    // Выполняем OCR и сохраняем recognizedText
    try {
      const fd = new FormData(); fd.append('image', file);
      const res = await axios.post('/ocr', fd, { headers: { 'Content-Type':'multipart/form-data' } });
      const recognized = res?.data?.text ? String(res.data.text).trim() : '';
      uploadedImages.value.push({ url, originalName: file.name, file, recognizedText: recognized });
    } catch (ocrErr) {
      console.warn('OCR failed for uploaded file', ocrErr);
      uploadedImages.value.push({ url, originalName: file.name, file, recognizedText: '' });
    } finally {
      ocrLoading.value = false;
    }

    userInput.value = '';
    try { await updateChatOnServer(currentChat.value); } catch (e) {}
    await nextTick(); scrollToBottom();
  } catch (e) { console.error(e); ocrLoading.value = false; }
};

const handleFileUpload = async (e) => { const f = e.target.files?.[0]; if (!f) return; if (!f.type.startsWith('image/')) return; await processImageFile(f); e.target.value = ''; };
const handleDrop = async (e) => { isDragging.value = false; const files = Array.from(e.dataTransfer?.files || []); const img = files.find(f=>f.type.startsWith('image/')); if (img) await processImageFile(img); };
const removeUploadedImage = (index) => { const img = uploadedImages.value[index]; if (img?.url) try { URL.revokeObjectURL(img.url); } catch(e){}; uploadedImages.value.splice(index,1); };

onBeforeUnmount(() => { try { uploadedImages.value.forEach(i=>{ if (i.url) try { URL.revokeObjectURL(i.url); } catch(e){} }); } catch(e){} try { window.removeEventListener('resize', onWindowResize); } catch(e){} });

watch(() => currentChat.value.messages.length, () => { nextTick(() => { scrollToBottom(); }); });

const handleEnterKey = (e) => { if (!e.shiftKey) sendMessage(); };

onMounted(() => {
  loadChatsFromServer().then(() => {
    nextTick(() => {
      scrollToBottom();
    });
  });
});

// Функция для создания нового чата
const newChat = async () => {
  const newChatObj = {
    title: 'Новый чат',
    messages: [],
    model: 'deepseek_r1'
  };
  // Добавляем локально в начало массива
  chatHistory.value.unshift(newChatObj);
  currentChatIndex.value = 0;
  // Пытаемся создать на сервере
  try {
    const id = await createChatOnServer(newChatObj);
    if (id) newChatObj.id = id;
    console.log('Новый чат DeepSeek R1 создан с ID:', id);
  } catch (e) {
    console.error('Ошибка создания нового чата на сервере:', e);
  }
};

// Функция загрузки выбранного чата из истории
const loadChat = (index) => {
  if (index < 0 || index >= chatHistory.value.length) return;
  currentChatIndex.value = index;
};

// Функция удаления чата
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
</script>

<style>
/* Удалён стиль canvas для Three.js */

/* Новый стиль для индикатора загруженного изображения */
.uploaded-image-indicator {
  position: relative;
  padding: 10px;
  border-radius: 8px;
  background-color: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  margin-top: 10px;
}

/* Стили для превью изображений */
.uploaded-image-indicator img {
  max-width: 100%;
  height: auto;
  border-radius: 6px;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

/* Стили для метаинформации изображений */
.uploaded-image-indicator .meta {
  flex: 1;
  min-width: 0;
}

/* Стили для кнопки удаления изображения */
.uploaded-image-indicator button {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(255, 255, 255, 0.1);
  transition: background-color 0.3s;
}

.uploaded-image-indicator button:hover {
  background-color: rgba(255, 255, 255, 0.2);
}

/* Новый стиль для текстовых сообщений */
.text-message {
  white-space: pre-line;
  word-wrap: break-word;
}

/* Новый стиль для сообщений от AI с рассуждениями */
.ai-message {
  position: relative;
  padding: 12px 16px;
  border-radius: 8px;
  background-color: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  margin-bottom: 10px;
}

/* Иконка процесса рассуждения */
.reasoning-icon {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 20px;
  height: 20px;
  color: rgba(255, 255, 255, 0.7);
}

/* Новый стиль для кнопки "Показать рассуждения" */
.show-reasoning-button {
  display: flex;
  align-items: center;
  padding: 8px 12px;
  border-radius: 4px;
  background-color: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.9);
  font-size: 14px;
  font-weight: 500;
  transition: background-color 0.3s, color 0.3s;
}

.show-reasoning-button:hover {
  background-color: rgba(255, 255, 255, 0.2);
  color: rgba(255, 255, 255, 1);
}

/* Новый стиль для области ввода сообщений */
.input-area {
  position: relative;
  padding: 12px 16px;
  border-radius: 8px;
  background-color: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  margin-top: 10px;
}

/* Новый стиль для текстовой области ввода */
.textarea-input {
  width: 100%;
  background-color: transparent;
  border: none;
  outline: none;
  color: rgba(255, 255, 255, 1);
  font-size: 16px;
  resize: none;
  padding-right: 50px;
}

/* Новый стиль для кнопок в области ввода */
.input-button {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background-color: transparent;
  border: none;
  cursor: pointer;
  color: rgba(255, 255, 255, 0.7);
  transition: color 0.3s;
}

.input-button:hover {
  color: rgba(255, 255, 255, 1);
}

/* Новый стиль для иконки загрузки */
.upload-icon {
  left: 12px;
}

.paste-icon {
  right: 50px;
}

.send-icon {
  right: 12px;
}

/* Новый стиль для индикатора загрузки */
.loading-indicator {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  display: flex;
  align-items: center;
  justify-content: center;
  height: 24px;
  width: 24px;
  border-radius: 50%;
  background-color: rgba(255, 255, 255, 0.1);
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: translate(-50%, -50%) rotate(0deg); }
  100% { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Новый стиль для области сообщений при загрузке */
.messages-loading {
  opacity: 0.6;
}

/* Новый стиль для скрытия элементов */
.hidden {
  display: none !important;
}
</style>

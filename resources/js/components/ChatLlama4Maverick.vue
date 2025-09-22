<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-green-900 to-slate-900 text-white relative overflow-hidden flex flex-col">
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
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
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
                <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center mr-3">
                  <span class="text-white font-bold text-xs">L4M</span>
                </div>
                <div>
                  <h3 class="text-base font-semibold">Llama-4-Maverick</h3>
                  <p class="text-xs text-gray-400">Передовая архитектура (MoE 128e)</p>
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
                <div class="w-16 h-16 bg-green-500 rounded-xl flex items-center justify-center mb-4">
                  <span class="text-white font-bold text-lg">L4M</span>
                </div>
                <h2 class="text-2xl font-bold mb-2 bg-gradient-to-r from-white to-gray-300 bg-clip-text text-transparent">
                  Llama-4-Maverick
                </h2>
                <p class="text-gray-300 max-w-md mb-6">
                  Универсальная модель с исключительной креативностью и глубоким пониманием контекста. Специализируется на творческих задачах и генерации качественного контента.
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
                    <p class="whitespace-pre-line">{{ message.content }}</p>
                  </div>
                </div>

                <!-- AI Message -->
                <div v-else class="flex items-start mb-4">
                  <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                    <span class="text-white font-bold text-xs">L4M</span>
                  </div>
                  <div class="flex-1 bg-white/5 rounded-lg px-4 py-3 text-white">
                    <p class="whitespace-pre-line markdown-content" v-html="formatMessage(message.content)"></p>
                  </div>
                </div>
              </div>

              <!-- Loading Indicator -->
              <div v-if="isLoading" class="flex items-start">
                <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                  <span class="text-white font-bold text-xs">L4M</span>
                </div>
                <div class="flex-1 bg-white/5 rounded-lg px-4 py-3 text-white">
                  <p class="animate-pulse flex items-center">
                    <span class="h-1.5 w-1.5 bg-gray-300 rounded-full mr-1"></span>
                    <span class="h-1.5 w-1.5 bg-gray-300 rounded-full mr-1"></span>
                    <span class="h-1.5 w-1.5 bg-gray-300 rounded-full"></span>
                  </p>
                </div>
              </div>
            </div>

            <!-- Input Area -->
            <div class="p-4 border-t border-white/10">
              <form @submit.prevent="sendMessage" class="relative">
                <textarea
                  v-model="userInput"
                  class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 pr-12 text-white resize-none focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                  placeholder="Отправьте сообщение..."
                  rows="2"
                  @keydown.enter.prevent="handleEnterKey"
                  ref="textareaRef"
                ></textarea>
                <button
                  type="submit"
                  class="absolute right-3 bottom-3 p-2 text-white bg-green-500 hover:bg-green-600 rounded-lg transition-colors"
                  :disabled="isLoading || !userInput.trim()"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd" />
                  </svg>
                </button>
              </form>
              <p class="text-xs text-gray-400 mt-2 text-center">
                Llama-4-Maverick может давать неточную информацию. Рекомендуется проверять важные данные.
              </p>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, nextTick, watch } from 'vue';
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
  'Расскажи мне о нейронных сетях простыми словами',
  'Напиши стихотворение о звёздном небе',
  'Объясни квантовую механику так, чтобы понял ребёнок',
  'Составь план обучения программированию на Python с нуля'
];

// Функция для нормализации сообщения
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
  return { role, content };
};
const normalizeMessagesArray = (arr) => {
  if (!arr || !Array.isArray(arr)) return [];
  return arr.map(m => normalizeMessage(m));
};

// Функция для загрузки всех чатов пользователя с сервера
const loadChatsFromServer = async () => {
  try {
    isLoading.value = true;
    console.log('Загрузка чатов с сервера...');
    // Используем абсолютный путь для API-запроса с фильтрацией по модели llama4
    const response = await axios.get('/api/chats?model=llama4');
    console.log('Ответ сервера при загрузке чатов:', response.data);

    if (response.data && response.data.length > 0) {
      // Преобразуем данные из сервера в нужный формат
      chatHistory.value = response.data.map(chat => ({
        id: chat.id,
        title: chat.title || 'Новый чат',
        messages: normalizeMessagesArray(chat.messages || []),
        model: 'llama4' // Явно указываем модель
      }));
      currentChatIndex.value = 0; // Выбираем первый чат
      console.log('Чаты Llama4 успешно загружены:', chatHistory.value);
    } else {
      console.log('У пользователя нет чатов Llama4, создаем новый');
      // Если у пользователя нет чатов, создаем пустой
      chatHistory.value = [{ title: 'Новый чат', messages: [], model: 'llama4' }];
      // И сохраняем его на сервере
      const chatId = await createChatOnServer(chatHistory.value[0]);
      if (chatId) {
        chatHistory.value[0].id = chatId;
        console.log('Новый чат Llama4 создан с ID:', chatId);
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
    chatHistory.value = [{ title: 'Новый чат', messages: [], model: 'llama4' }];
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
      model: chat.model || 'llama4' // Добавляем передачу модели на сервер
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
      model: chat.model || 'llama4' // Добавляем передачу модели на сервер при обновлении
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
  // Если передано событие формы или другой объект вместо текста, обрабатываем это
  if (text && typeof text === 'object') {
    // Если у объекта есть метод preventDefault, это скорее всего событие формы
    if (text.preventDefault) {
      text.preventDefault();
    }
    // В любом случае, не используем объекты как сообщения
    text = null;
  }

  const messageToSend = text || userInput.value.trim();
  if (!messageToSend || isLoading.value) return;

  console.log('Отправка сообщения:', messageToSend);

  // Очищаем ввод
  userInput.value = '';

  // Добавляем сообщение пользователя
  currentChat.value.messages.push({
    role: 'user',
    content: messageToSend
  });

  // Если это первое сообщение в чате, устанавливаем заголовок
  if (currentChat.value.messages.length === 1) {
    // Проверяем, что messageToSend - строка
    if (typeof messageToSend === 'string') {
      // Первые несколько слов как заголовок
      const title = messageToSend.split(' ').slice(0, 3).join(' ') + '...';
      currentChat.value.title = title;
      console.log('Установлен заголовок чата:', title);
    } else {
      // Если не строка, используем заголовок по умолчанию
      currentChat.value.title = 'Новый чат';
      console.log('Установлен заголовок чата по умолчанию');
    }
  }

  console.log('Состояние чата перед сохранением:', JSON.stringify(currentChat.value));

  // Сохраняем чат на сервере
  try {
    console.log('Начинаем сохранение чата на сервере...');
    await updateChatOnServer(currentChat.value);
    console.log('Чат успешно сохранен на сервере');
  } catch (error) {
    console.error('Ошибка при сохранении чата:', error);
  }

  // Прокручиваем контейнер сообщений вниз
  await nextTick();
  scrollToBottom();

  // Включаем индикатор загрузки
  isLoading.value = true;
  console.log('Ожидание ответа от модели...');

  try {
    // Отправляем запрос к AI API
    console.log('Отправка запроса к AI API...');
    const aiResponse = await axios.post('/api/ai/chat', {
      message: messageToSend,
      model: 'llama4' // Указываем модель llama4
    });

    console.log('Получен ответ от AI API:', aiResponse.data);

    // Проверяем наличие ошибок
    if (aiResponse.data && aiResponse.data.error) {
      console.error('Ошибка от AI API:', aiResponse.data.error);
      // Добавляем сообщение об ошибке в чат
      currentChat.value.messages.push({
        role: 'assistant',
        content: `Произошла ошибка: ${aiResponse.data.error}`
      });
    } else if (aiResponse.data && aiResponse.data.answer) {
      // Добавляем ответ модели
      const aiAnswer = aiResponse.data.answer;
      currentChat.value.messages.push({
        role: 'assistant',
        content: aiAnswer
      });
    } else {
      // Если нет ни ошибки, ни ответа - добавляем сообщение-заглушку
      currentChat.value.messages.push({
        role: 'assistant',
        content: 'Не удалось получить ответ от модели.'
      });
    }

    console.log('Состояние чата после добавления ответа:', JSON.stringify(currentChat.value));

    // Сохраняем обновленный чат на сервере
    console.log('Сохраняем обновленный чат на сервере...');
    await updateChatOnServer(currentChat.value);
    console.log('Обновленный чат успешно сохранен');
  } catch (error) {
    console.error('Ошибка при обращении к AI API:', error);

    // Добавляем сообщение об ошибке в чат
    currentChat.value.messages.push({
      role: 'assistant',
      content: 'Извините, произошла ошибка при обработке запроса. Пожалуйста, попробуйте позже.'
    });

    // Сохраняем чат с сообщением об ошибке
    try {
      await updateChatOnServer(currentChat.value);
    } catch (saveError) {
      console.error('Ошибка при сохранении чата после ошибки:', saveError);
    }
  } finally {
    // Завершаем загрузку
    isLoading.value = false;

    // Прокручиваем контейнер сообщений вниз
    nextTick().then(() => {
      scrollToBottom();
    });
  }
};

// Функция для создания нового чата
const newChat = async () => {
  const newChatObj = {
    title: 'Новый чат',
    messages: [],
    model: 'llama4' // Указываем модель llama4 для нового чата
  };
  chatHistory.value.unshift(newChatObj);
  currentChatIndex.value = 0;
  const id = await createChatOnServer(newChatObj);
  if (id) newChatObj.id = id;
};

// Функция для загрузки выбранного чата
const loadChat = (index) => {
  if (index < 0 || index >= chatHistory.value.length) return;
  currentChatIndex.value = index;
};

// Функция для удаления чата
const deleteChat = async (index) => {
  if (index < 0 || index >= chatHistory.value.length) return;
  const chatToDelete = chatHistory.value[index];
  if (chatToDelete && chatToDelete.id) {
    try { await deleteChatFromServer(chatToDelete.id); } catch (e) {}
  }
  chatHistory.value.splice(index, 1);
  if (chatHistory.value.length === 0) {
    await newChat();
    return;
  }
  if (currentChatIndex.value >= chatHistory.value.length) {
    currentChatIndex.value = Math.max(0, chatHistory.value.length - 1);
  }
};

// Функция для прокрутки контейнера сообщений вниз
const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

// Функция для форматирования сообщения с поддержкой Markdown
const formatMessage = (message) => {
  return marked.parse(message);
};

// Функция для обработки нажатия клавиши Enter
const handleEnterKey = (e) => {
  // Отправляем сообщение только по Enter без Shift
  if (!e.shiftKey) {
    sendMessage();
  }
};

// Функция для имитации ответа модели (только для демонстрации)
const generateDummyResponse = (message) => {
  // Набор шаблонных ответов
  const responses = [
    `Спасибо за ваш вопрос! Вы спросили: "${message.substring(0, 50)}${message.length > 50 ? '...' : ''}"\n\nЯ, Llama-4-Maverick, - это модель с архитектурой Mixture of Experts (MoE), что позволяет мне эффективно работать с разными типами задач. Мои 128 экспертных сетей делают меня особенно сильным в творческих задачах и генерации контента.\n\nЧем еще я могу вам помочь?`,

    `Интересный вопрос! Позвольте мне рассмотреть его подробнее.\n\n${message.substring(0, 30)}${message.length > 30 ? '...' : ''} - это комплексная тема, которую можно рассматривать с разных точек зрения. Одна из ключевых особенностей - это способность адаптироваться к контексту и предоставлять релевантные ответы.\n\nВот пример кода на Python, демонстрирующий эту концепцию:\n\n\`\`\`python\ndef analyze_context(input_text):\n    # Анализируем входные данные\n    words = input_text.split()\n    context = determine_subject(words)\n    \n    # Адаптируем ответ на основе контекста\n    if context == "technical":\n        return technical_response(input_text)\n    else:\n        return creative_response(input_text)\n\`\`\`\n\nНадеюсь, это помогло прояснить вопрос! У вас есть дополнительные вопросы?`,

    `Работая над вашим запросом: "${message.substring(0, 40)}${message.length > 40 ? '...' : ''}", я хотел бы отметить несколько важных аспектов.\n\n### Ключевые моменты:\n\n1. **Понимание контекста** - основа эффективной коммуникации\n2. **Анализ информации** - позволяет выделить важные детали\n3. **Генерация релевантных ответов** - создает ценность для пользователя\n\nМоя архитектура MoE с 128 экспертами позволяет мне эффективно решать различные задачи, от творческих до аналитических. Благодаря этому я могу предоставлять разнообразные ответы, адаптированные под конкретные запросы.\n\nМогу ли я помочь вам с чем-то еще?`,

    `Спасибо за ваше сообщение! Я проанализировал ваш запрос и подготовил информацию по теме.\n\nСтоит отметить, что в данной области существует множество мнений и подходов. В моей работе я стараюсь учитывать различные перспективы и предоставлять максимально объективную информацию.\n\nЕсли у вас есть конкретные аспекты темы "${message.substring(0, 20)}${message.length > 20 ? '...' : ''}", которые вас интересуют больше всего, пожалуйста, уточните, и я смогу предоставить более детальный ответ.`
  ];

  // Специальные ответы на конкретные запросы
  if (message.toLowerCase().includes('нейрон') && message.toLowerCase().includes('сет')) {
    return `# Нейронные сети простыми словами

Представьте, что нейронная сеть — это огромная команда маленьких работников (нейронов), где каждый выполняет очень простую задачу:

1. **Получает информацию** от других работников
2. **Решает, насколько это важно** (применяет "вес" к информации)
3. **Принимает решение** на основе всей полученной информации
4. **Передает свое решение** дальше

## Как это работает на практике

Представьте, что вы хотите научить компьютер отличать фотографии собак от фотографий кошек:

- **Входной слой** получает пиксели изображения
- **Скрытые слои** обнаруживают линии, затем формы, затем особенности (уши, хвост)
- **Выходной слой** принимает итоговое решение: "собака" или "кошка"

## Обучение нейросети

Это похоже на обучение ребенка:
1. Показываем картинку и спрашиваем: "Что это?"
2. Нейросеть делает предположение
3. Мы говорим, правильно или нет
4. Нейросеть корректирует свои "веса", чтобы в следующий раз сделать лучше

С каждой картинкой она становится немного умнее, пока не научится различать животных с высокой точностью.

Современные модели вроде меня (Llama-4-Maverick) используют значительно более сложные архитектуры с миллиардами нейронов и механизмами внимания, но базовый принцип остается тем же!`;
  }

  if (message.toLowerCase().includes('стихотворени') && message.toLowerCase().includes('звезд')) {
    return `# Звездная симфония

В бескрайней тьме ночного небосвода,
Где время замедляет вечный ход,
Рассыпаны сияющие звезды —
Немой свидетель наших всех невзгод.

Они мерцают, словно разговоры
Далеких предков, что давно ушли.
Их свет летит сквозь времени просторы,
Неся истории погасшей их зари.

Плеяды, Орион, Медведиц стражи —
Созвездия как древние друзья.
Их карта в небесах веками та же,
Надежный путь для странника суля.

Под этим куполом из звездной пыли
Мы ищем смысл, вглядываясь вдаль.
И что бы звезды нам ни говорили,
В их блеске — вечности прозрачная вуаль.

*Llama-4-Maverick, 2025*`;
  }

  if (message.toLowerCase().includes('квантов') && message.toLowerCase().includes('механик')) {
    return `# Квантовая механика для детей

Представь, что у тебя есть волшебный мячик, который ведет себя очень странно!

## Особенность 1: Он может быть в нескольких местах одновременно

Обычный мячик всегда находится в каком-то одном месте, правда? А наш квантовый мячик может как бы "размазаться" и быть немного здесь, немного там и еще чуть-чуть вон там. Это называется **суперпозиция**.

## Особенность 2: Он меняется, когда ты на него смотришь

Самое удивительное — когда ты решаешь посмотреть, где находится мячик, он сразу "выбирает" одно место! То есть, пока ты не смотришь, он может быть где угодно, но стоит взглянуть — и он сразу оказывается в конкретной точке. Это называется **коллапс волновой функции**.

## Особенность 3: Он может проходить сквозь стены!

Если обычный мячик бросить в стену, он отскочит. А наш квантовый мячик иногда может оказаться по другую сторону стены, даже не проходя через нее! Это называется **квантовое туннелирование**.

## Особенность 4: Он может дружить с другими мячиками на расстоянии

Если у тебя два таких мячика, и ты их подружил (запутал), то что бы ни случилось с одним мячиком, другой мгновенно об этом "узнает" и отреагирует, даже если они на разных концах вселенной! Это называется **квантовая запутанность**.

---

Звучит как волшебство, правда? Но это настоящая наука! Ученые используют эти странные свойства, чтобы создавать суперкомпьютеры и разрабатывать новые технологии.`;
  }

  // Для остальных запросов выбираем случайный ответ из набора
  return responses[Math.floor(Math.random() * responses.length)];
};

// Автоматическая загрузка истории чатов при открытии страницы
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
</style>

<template>
  <div class="max-w-6xl mx-auto flex justify-between items-center">
    <router-link to="/" class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
      AI Chat Free
    </router-link>

    <!-- Навигация для неавторизованных пользователей -->
    <nav v-if="!isAuthenticated" class="flex gap-4">
      <router-link to="/login" class="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors">
        Войти
      </router-link>
      <router-link to="/register" class="px-4 py-2 rounded-lg bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 transition-colors">
        Регистрация
      </router-link>
    </nav>

    <!-- Навигация для авторизованных пользователей -->
    <nav v-else class="flex items-center gap-4">
      <div class="relative">
        <!-- Кнопка с именем пользователя -->
        <button
          @click="toggleDropdown"
          class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors"
        >
          <span class="text-gray-300">{{ user?.name || 'Пользователь' }}</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>

        <!-- Выпадающее меню -->
        <div
          v-if="isDropdownOpen"
          class="absolute right-0 mt-2 w-48 bg-slate-800 rounded-lg shadow-lg py-2 z-20"
          @mouseleave="isDropdownOpen = false"
        >
          <router-link to="/profile" class="block px-4 py-2 text-gray-300 hover:bg-slate-700 transition-colors">
            Профиль
          </router-link>
          <button @click="logout" class="block w-full text-left px-4 py-2 text-red-400 hover:bg-slate-700 transition-colors">
            Выйти
          </button>
        </div>
      </div>
    </nav>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const isAuthenticated = ref(false);
const user = ref(null);
const isDropdownOpen = ref(false);

// Переключение выпадающего меню
const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value;
};

// Проверка авторизации при загрузке компонента
onMounted(async () => {
  checkAuth();
});

// Функция проверки авторизации
const checkAuth = async () => {
  const token = localStorage.getItem('auth_token');

  if (token) {
    try {
      // Устанавливаем токен для авторизованных запросов
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

      // Запрос к API для получения данных пользователя
      const response = await axios.get('/api/user');
      user.value = response.data;
      isAuthenticated.value = true;
    } catch (error) {
      // Если возникла ошибка, удаляем токен
      localStorage.removeItem('auth_token');
      delete axios.defaults.headers.common['Authorization'];
      isAuthenticated.value = false;
    }
  } else {
    isAuthenticated.value = false;
  }
};

// Функция выхода
const logout = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    if (token) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      await axios.post('/api/logout');
    }
  } catch (error) {
    // Обрабатываем ошибки тихо
  } finally {
    // Удаляем токен и обновляем состояние
    localStorage.removeItem('auth_token');
    delete axios.defaults.headers.common['Authorization'];
    isAuthenticated.value = false;
    user.value = null;
    isDropdownOpen.value = false;

    // Перенаправляем на главную страницу
    router.push('/');
  }
};
</script>

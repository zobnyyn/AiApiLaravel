import { createRouter, createWebHistory } from 'vue-router';
import axios from 'axios';
import StartPage from './components/StartPage.vue';
import LoginPage from './components/LoginPage.vue';
import RegisterPage from './components/RegisterPage.vue';
import ProfilePage from './components/ProfilePage.vue';
import ChatLlama4Maverick from './components/ChatLlama4Maverick.vue';
import ChatDeepSeekR1 from './components/ChatDeepSeekR1.vue';
import ChatQwen from './components/ChatQwen.vue';
import ChatLlama4Scout from './components/ChatLlama4Scout.vue';

const routes = [
  {
    path: '/',
    name: 'home',
    component: StartPage
  },
  {
    path: '/login',
    name: 'login',
    component: LoginPage
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterPage
  },
  {
    path: '/profile',
    name: 'profile',
    component: ProfilePage,
    meta: { requiresAuth: true }
  },
  {
    path: '/chat/llama4-maverick',
    name: 'chatLlama4Maverick',
    component: ChatLlama4Maverick,
    meta: { requiresAuth: true }
  },
  {
    path: '/chat/deepseek-r1',
    name: 'chatDeepSeekR1',
    component: ChatDeepSeekR1,
    meta: { requiresAuth: true }
  },
  {
    path: '/chat/qwen',
    name: 'chatQwen',
    component: ChatQwen,
    meta: { requiresAuth: true }
  },
  {
    path: '/chat/llama4-scout',
    name: 'chatLlama4Scout',
    component: ChatLlama4Scout,
    meta: { requiresAuth: true }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Навигационный guard для защиты маршрутов
router.beforeEach((to, from, next) => {
  // Всегда (перед навигацией) обновляем заголовок Authorization из localStorage
  try {
    const token = localStorage.getItem('auth_token');
    if (token) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    } else {
      // Если токена нет — удаляем заголовок, чтобы избежать использования старых значений
      delete axios.defaults.headers.common['Authorization'];
    }
  } catch (e) {
    // ignore localStorage errors
  }

  const isAuthenticated = !!localStorage.getItem('auth_token');

  // Проверяем, требует ли маршрут авторизации
  if (to.matched.some(record => record.meta.requiresAuth) && !isAuthenticated) {
    // Если нет токена, перенаправляем на страницу логина
    next({ name: 'login' });
  } else {
    next();
  }
});

export default router;

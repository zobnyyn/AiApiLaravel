<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 text-white relative overflow-hidden">
    <!-- Three.js Background -->
    <div ref="threeContainer" class="absolute inset-0 z-0"></div>

    <!-- Content -->
    <div class="relative z-10 flex flex-col min-h-screen">
      <!-- Header -->
      <header class="p-6">
        <NavBar />
      </header>

      <!-- Main Content -->
      <main class="flex-1 flex items-center justify-center p-6">
        <div class="w-full max-w-md p-8 bg-white/10 backdrop-blur-lg rounded-xl shadow-xl">
          <h2 class="text-3xl font-bold mb-6 text-center bg-gradient-to-r from-white to-gray-300 bg-clip-text text-transparent">
            Вход в аккаунт
          </h2>

          <form @submit.prevent="login" class="space-y-6">
            <div>
              <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
              <input
                type="email"
                id="email"
                v-model="email"
                class="w-full px-4 py-3 bg-white/5 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white"
                placeholder="your@email.com"
                required
              >
            </div>

            <div>
              <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Пароль</label>
              <div class="relative">
                <input
                  :type="showPassword ? 'text' : 'password'"
                  id="password"
                  v-model="password"
                  class="w-full px-4 py-3 bg-white/5 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white"
                  placeholder="••••••••"
                  required
                >
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-white focus:outline-none"
                >
                  <span v-if="showPassword">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                      <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7s-8.268-2.943-9.542-7zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                    </svg>
                  </span>
                  <span v-else>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                      <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
                    </svg>
                  </span>
                </button>
              </div>
            </div>

            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <input
                  id="remember-me"
                  type="checkbox"
                  v-model="rememberMe"
                  class="h-4 w-4 rounded bg-white/5 border-gray-600 text-blue-600 focus:ring-blue-500"
                >
                <label for="remember-me" class="ml-2 block text-sm text-gray-300">
                  Запомнить меня
                </label>
              </div>

              <a href="#" class="text-sm text-blue-400 hover:text-blue-300">
                Забыли пароль?
              </a>
            </div>

            <button
              type="submit"
              class="w-full py-3 px-4 bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white font-medium rounded-lg transition-colors"
            >
              Войти
            </button>

            <div v-if="errorMessage" class="text-red-400 text-sm mt-4 text-center">
              {{ errorMessage }}
            </div>
          </form>

          <div class="mt-6 text-center">
            <p class="text-gray-400">
              Нет аккаунта?
              <router-link to="/register" class="text-blue-400 hover:text-blue-300">
                Зарегистрироваться
              </router-link>
            </p>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import * as THREE from 'three';
import NavBar from './NavBar.vue';

const router = useRouter();

// Данные формы
const email = ref('');
const password = ref('');
const rememberMe = ref(false);
const errorMessage = ref('');
const isLoading = ref(false);
const showPassword = ref(false); // Добавили переменную для показа/скрытия пароля

// Функция входа
const login = async () => {
  try {
    isLoading.value = true;
    errorMessage.value = '';

    // Получаем CSRF-токен
    await axios.get('/sanctum/csrf-cookie');

    // Отправляем запрос на авторизацию
    const response = await axios.post('/api/login', {
      email: email.value,
      password: password.value,
      remember: rememberMe.value
    });

    // Получаем токен и сохраняем его
    const token = response.data.token;
    if (!token) {
      errorMessage.value = 'Не удалось получить токен авторизации';
      return;
    }

    localStorage.setItem('auth_token', token);

    // Если выбрана опция "запомнить меня", сохраняем email в localStorage
    if (rememberMe.value) {
      localStorage.setItem('remembered_email', email.value);
    } else {
      localStorage.removeItem('remembered_email');
    }

    // Устанавливаем токен для последующих запросов
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

    // Перенаправляем пользователя на главную страницу
    router.push('/');
  } catch (error) {
    // Обрабатываем ошибки
    if (error.response) {
      // Ошибка от сервера
      if (error.response.data && error.response.data.message) {
        errorMessage.value = error.response.data.message;
      } else if (error.response.data && error.response.data.errors) {
        // Получаем первую ошибку из списка
        const firstError = Object.values(error.response.data.errors)[0];
        errorMessage.value = Array.isArray(firstError) ? firstError[0] : firstError;
      } else {
        errorMessage.value = 'Произошла ошибка при входе';
      }
    } else {
      // Общая ошибка
      errorMessage.value = 'Не удалось подключиться к серверу';
    }
  } finally {
    isLoading.value = false;
  }
};

// Three.js background
const threeContainer = ref(null);
let scene, camera, renderer, stars;

// Проверяем, есть ли сохранённый email при загрузке компонента
onMounted(() => {
  const rememberedEmail = localStorage.getItem('remembered_email');
  if (rememberedEmail) {
    email.value = rememberedEmail;
    rememberMe.value = true;
  }

  initThreeJS();
  animate();

  // Обработчик изменения размера окна
  window.addEventListener('resize', onWindowResize);

  // Очистка при демонтировании компонента
  return () => {
    window.removeEventListener('resize', onWindowResize);
    if (renderer) {
      renderer.dispose();
    }
  };
});

const initThreeJS = () => {
  // Настройка сцены
  scene = new THREE.Scene();

  // Настройка камеры
  camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
  camera.position.z = 5; // Уменьшаем значение с 50 до 5 как на страницах чата

  // Настройка рендерера
  renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
  renderer.setSize(window.innerWidth, window.innerHeight);
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  threeContainer.value.appendChild(renderer.domElement);

  // Создание звезд - используем один объект THREE.Points, а не массив отдельных звезд
  const starGeometry = new THREE.BufferGeometry();
  const starMaterial = new THREE.PointsMaterial({
    color: 0xffffff,
    size: 0.05,
    transparent: true
  });

  const starVertices = [];
  for (let i = 0; i < 10000; i++) {
    const x = (Math.random() - 0.5) * 2000;
    const y = (Math.random() - 0.5) * 2000;
    const z = (Math.random() - 0.5) * 2000;
    starVertices.push(x, y, z);
  }

  starGeometry.setAttribute('position', new THREE.Float32BufferAttribute(starVertices, 3));
  stars = new THREE.Points(starGeometry, starMaterial);
  scene.add(stars);
};

const onWindowResize = () => {
  if (camera && renderer) {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
  }
};

const animate = () => {
  if (!scene || !camera || !renderer) return;

  requestAnimationFrame(animate);

  // Вращаем объект stars (который теперь один объект, а не массив)
  if (stars) {
    stars.rotation.x += 0.0001;
    stars.rotation.y += 0.0001;
  }

  camera.rotation.x += 0.0002;
  camera.rotation.y += 0.0002;

  renderer.render(scene, camera);
};
</script>

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
            Создание аккаунта
          </h2>

          <form @submit.prevent="register" class="space-y-6">
            <div>
              <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Имя</label>
              <input
                type="text"
                id="name"
                v-model="name"
                class="w-full px-4 py-3 bg-white/5 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white"
                placeholder="Ваше имя"
                required
              >
            </div>

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
                  placeholder="Минимум 8 символов"
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

            <div>
              <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-2">Подтверждение пароля</label>
              <div class="relative">
                <input
                  :type="showPassword ? 'text' : 'password'"
                  id="password_confirmation"
                  v-model="passwordConfirmation"
                  class="w-full px-4 py-3 bg-white/5 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white"
                  placeholder="Повторите пароль"
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

            <div class="flex items-center">
              <input
                id="terms"
                type="checkbox"
                v-model="termsAccepted"
                class="h-4 w-4 rounded bg-white/5 border-gray-600 text-blue-600 focus:ring-blue-500"
                required
              >
              <label for="terms" class="ml-2 block text-sm text-gray-300">
                Я соглашаюсь с <a href="#" class="text-blue-400 hover:text-blue-300">условиями использования</a> и <a href="#" class="text-blue-400 hover:text-blue-300">политикой конфиденциальности</a>
              </label>
            </div>

            <div v-if="errorMessage" class="text-red-400 text-sm mt-4">
              {{ errorMessage }}
            </div>

            <button
              type="submit"
              class="w-full py-3 px-4 bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white font-medium rounded-lg transition-colors"
              :disabled="isLoading"
            >
              <span v-if="isLoading" class="animate-spin">🔄</span>
              Зарегистрироваться
            </button>
          </form>

          <div class="mt-6 text-center">
            <p class="text-gray-400">
              Уже есть аккаунт?
              <router-link to="/login" class="text-blue-400 hover:text-blue-300">
                Войти
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
const name = ref('');
const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const termsAccepted = ref(false);
const errorMessage = ref('');
const isLoading = ref(false);
const showPassword = ref(false); // Новое состояние для показа/скрытия пароля

// Функция регистрации
const register = async () => {
  try {
    // Проверка на совпадение паролей
    if (password.value !== passwordConfirmation.value) {
      errorMessage.value = 'Пароли не совпадают';
      return;
    }

    isLoading.value = true;
    errorMessage.value = '';

    // Получаем CSRF-токен
    await axios.get('/sanctum/csrf-cookie');

    // Отправляем запрос на регистрацию
    const response = await axios.post('/api/register', {
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value
    });

    console.log('Успешная регистрация:', response.data);

    // Если регистрация успешна, перенаправляем на страницу входа
    router.push('/login');
  } catch (error) {
    // Обрабатываем ошибки
    console.error('Ошибка регистрации:', error);

    if (error.response) {
      // Ошибка от сервера
      if (error.response.data && error.response.data.message) {
        errorMessage.value = error.response.data.message;
      } else if (error.response.data && error.response.data.errors) {
        // Получаем первую ошибку из списка
        const firstError = Object.values(error.response.data.errors)[0];
        errorMessage.value = Array.isArray(firstError) ? firstError[0] : firstError;
      } else {
        errorMessage.value = 'Произошла ошибка при регистрации';
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

onMounted(() => {
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

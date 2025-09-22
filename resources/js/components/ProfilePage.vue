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
        <div class="w-full max-w-2xl p-8 bg-white/10 backdrop-blur-lg rounded-xl shadow-xl">
          <h2 class="text-3xl font-bold mb-6 text-center bg-gradient-to-r from-white to-gray-300 bg-clip-text text-transparent">
            Ваш профиль
          </h2>

          <!-- Форма профиля -->
          <form @submit.prevent="updateProfile" class="space-y-6">
            <!-- Информация о пользователе -->
            <div class="flex flex-col md:flex-row gap-6 items-center mb-8">
              <div class="w-32 h-32 rounded-full bg-white/20 overflow-hidden flex items-center justify-center">
                <img v-if="profile.avatar_url" :src="profile.avatar_url" alt="Avatar" class="w-full h-full object-cover" />
                <span v-else class="text-5xl text-gray-300">{{ getInitials() }}</span>
              </div>
              <div class="flex-1">
                <div class="text-lg font-medium text-white mb-1">{{ user?.name || 'Пользователь' }}</div>
                <div class="text-sm text-gray-300 mb-3">{{ user?.email || '' }}</div>
                <div class="flex gap-2">
                  <input
                    type="text"
                    v-model="profile.avatar_url"
                    placeholder="URL аватара"
                    class="flex-1 px-3 py-1 bg-white/5 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white text-sm"
                  />
                </div>
              </div>
            </div>

            <!-- Поля формы -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="first_name" class="block text-sm font-medium text-gray-300 mb-2">Имя</label>
                <input
                  type="text"
                  id="first_name"
                  v-model="profile.first_name"
                  class="w-full px-4 py-3 bg-white/5 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white"
                  placeholder="Ваше имя"
                />
              </div>

              <div>
                <label for="last_name" class="block text-sm font-medium text-gray-300 mb-2">Фамилия</label>
                <input
                  type="text"
                  id="last_name"
                  v-model="profile.last_name"
                  class="w-full px-4 py-3 bg-white/5 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white"
                  placeholder="Ваша фамилия"
                />
              </div>
            </div>

            <div>
              <label for="bio" class="block text-sm font-medium text-gray-300 mb-2">О себе</label>
              <textarea
                id="bio"
                v-model="profile.bio"
                rows="4"
                class="w-full px-4 py-3 bg-white/5 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white resize-none"
                placeholder="Расскажите о себе..."
              ></textarea>
            </div>

            <div v-if="errorMessage" class="text-red-400 text-sm mt-4 text-center">
              {{ errorMessage }}
            </div>

            <div v-if="successMessage" class="text-green-400 text-sm mt-4 text-center">
              {{ successMessage }}
            </div>

            <div class="flex justify-end">
              <button
                type="submit"
                class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white font-medium rounded-lg transition-colors"
                :disabled="isLoading"
              >
                <span v-if="isLoading" class="animate-spin mr-2">⟳</span>
                Сохранить изменения
              </button>
            </div>
          </form>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import * as THREE from 'three';
import NavBar from './NavBar.vue';

const router = useRouter();

// Состояние
const user = ref(null);
const profile = ref({
  first_name: '',
  last_name: '',
  bio: '',
  avatar_url: ''
});
const errorMessage = ref('');
const successMessage = ref('');
const isLoading = ref(false);

// Получение инициалов пользователя для аватара
const getInitials = () => {
  if (profile.value.first_name && profile.value.last_name) {
    return `${profile.value.first_name.charAt(0)}${profile.value.last_name.charAt(0)}`;
  } else if (user.value && user.value.name) {
    // Если есть только имя пользователя, берем первую букву
    return user.value.name.charAt(0);
  }
  return '?';
};

// Загрузка данных профиля
const loadProfile = async () => {
  const token = localStorage.getItem('auth_token');
  if (!token) {
    router.push('/login');
    return;
  }

  try {
    // Устанавливаем токен для авторизованных запросов
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

    // Получаем данные профиля
    const response = await axios.get('/api/profile');
    profile.value = {
      first_name: response.data.first_name || '',
      last_name: response.data.last_name || '',
      bio: response.data.bio || '',
      avatar_url: response.data.avatar_url || ''
    };

    // Получаем данные пользователя
    const userResponse = await axios.get('/api/user');
    user.value = userResponse.data;

  } catch (error) {
    if (error.response && error.response.status === 401) {
      // Если токен недействителен, перенаправляем на страницу входа
      localStorage.removeItem('auth_token');
      router.push('/login');
    } else {
      errorMessage.value = 'Не удалось загрузить данные профиля';
    }
  }
};

// Обновление профиля
const updateProfile = async () => {
  try {
    isLoading.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    const token = localStorage.getItem('auth_token');
    if (!token) {
      router.push('/login');
      return;
    }

    // Устанавливаем токен для авторизованных запросов
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

    // Обновляем профиль
    const response = await axios.post('/api/profile', profile.value);

    successMessage.value = 'Профиль успешно обновлен';
  } catch (error) {
    if (error.response) {
      // Ошибка от сервера
      if (error.response.data && error.response.data.message) {
        errorMessage.value = error.response.data.message;
      } else if (error.response.data && error.response.data.errors) {
        // Получаем первую ошибку из списка
        const firstError = Object.values(error.response.data.errors)[0];
        errorMessage.value = Array.isArray(firstError) ? firstError[0] : firstError;
      } else {
        errorMessage.value = 'Произошла ошибка при обновлении профиля';
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
let scene, camera, renderer, stars = [];

onMounted(() => {
  // Загружаем профиль при монтировании компонента
  loadProfile();

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
  // Настройка сцены - НЕ используем ref для Three.js объектов
  scene = new THREE.Scene();

  // Настройка камеры
  camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
  camera.position.z = 50;

  // Настройка рендерера
  renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
  renderer.setSize(window.innerWidth, window.innerHeight);
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2)); // Ограничиваем pixelRatio
  threeContainer.value.appendChild(renderer.domElement);

  // Создание звезд - используем обычный массив
  stars = [];
  const geometry = new THREE.SphereGeometry(0.1, 24, 24);
  const material = new THREE.MeshBasicMaterial({ color: 0xffffff });

  for (let i = 0; i < 1000; i++) {
    const star = new THREE.Mesh(geometry, material);
    const [x, y, z] = Array(3).fill().map(() => THREE.MathUtils.randFloatSpread(100));
    star.position.set(x, y, z);
    scene.add(star);
    stars.push(star);
  }
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

  try {
    // Проверяем, что массив звезд существует и не пуст
    if (stars && stars.length > 0) {
      // Анимация звезд
      stars.forEach(star => {
        if (star && typeof star.rotation !== 'undefined') {
          star.rotation.x += 0.001;
          star.rotation.y += 0.001;
        }
      });
    }

    // Вращение камеры - с проверкой
    if (camera && typeof camera.rotation !== 'undefined') {
      camera.rotation.x += 0.0002;
      camera.rotation.y += 0.0002;
    }

    // Рендеринг с проверкой
    if (renderer && scene && camera) {
      renderer.render(scene, camera);
    }
  } catch (error) {
    console.error('Ошибка в анимации Three.js:', error);
  }
};
</script>

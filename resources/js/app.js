import './bootstrap';
import { createApp } from 'vue';
import router from './router';
import App from './components/App.vue';
import axios from 'axios';

// Если при предыдущей авторизации был сохранён токен, ставим его в заголовки axios
try {
  const token = localStorage.getItem('auth_token');
  if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  }
  // Также ожидаемый JSON-ответ
  axios.defaults.headers.common['Accept'] = 'application/json';
} catch (e) {
  // ignore
}

// Создаем приложение Vue с корневым компонентом App, который использует router-view
const app = createApp(App);
app.use(router);
app.mount('#app');

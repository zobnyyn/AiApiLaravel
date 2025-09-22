<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 text-white relative overflow-hidden">
    <!-- Content -->
    <div class="relative z-10 flex flex-col min-h-screen">
      <!-- Header -->
      <header class="p-6">
        <NavBar />
      </header>

      <!-- Main Content -->
      <main class="flex-1 flex flex-col items-center justify-center p-6">
        <div class="max-w-4xl mx-auto text-center mb-12">
          <h2 class="text-5xl font-bold mb-6 bg-gradient-to-r from-white to-gray-300 bg-clip-text text-transparent">
            Выберите AI-модель для общения
          </h2>
          <p class="text-xl text-gray-300 mb-8">
            Общайтесь с передовыми языковыми моделями. Выберите подходящую для ваших задач.
          </p>
        </div>

        <!-- Models Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6 max-w-4xl w-full">
          <!-- Llama-4-Maverick -->
          <div class="bg-white/10 backdrop-blur-lg rounded-xl p-6 hover:bg-white/15 transition-all duration-300 cursor-pointer transform hover:scale-105 flex flex-col h-full">
            <div class="flex items-center mb-4">
              <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center mr-4">
                <span class="text-white font-bold text-lg">L4M</span>
              </div>
              <div>
                <h3 class="text-xl font-semibold">Llama-4-Maverick</h3>
                <p class="text-sm text-gray-400">Передовая архитектура (MoE 128e)</p>
              </div>
            </div>
            <p class="text-gray-300 mb-4 flex-grow h-32 overflow-y-auto">
              Инновационная модель с архитектурой Mixture of Experts, обеспечивающая исключительную креативность и универсальность. Идеальна для творческих задач, генерации контента и нестандартных решений.
            </p>
            <div class="flex items-center justify-between mt-auto">
              <span class="text-sm text-green-400">● Доступна</span>
              <router-link to="/chat/llama4-maverick" class="px-4 py-2 bg-green-500 hover:bg-green-600 rounded-lg text-white transition-colors">
                Выбрать
              </router-link>
            </div>
          </div>

          <!-- Llama-4-Scout -->
          <div class="bg-white/10 backdrop-blur-lg rounded-xl p-6 hover:bg-white/15 transition-all duration-300 cursor-pointer transform hover:scale-105 flex flex-col h-full">
            <div class="flex items-center mb-4">
              <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center mr-4">
                <span class="text-white font-bold text-lg">L4S</span>
              </div>
              <div>
                <h3 class="text-xl font-semibold">Llama-4-Scout</h3>
                <p class="text-sm text-gray-400">Аналитическая модель (MoE 16e)</p>
              </div>
            </div>
            <p class="text-gray-300 mb-4 flex-grow h-32 overflow-y-auto">
              Специализированная модель для глубокого анализа информации и поиска данных. Оптимизирована для исследовательских задач, информационных запросов и критического анализа с высокой точностью.
            </p>
            <div class="flex items-center justify-between mt-auto">
              <span class="text-sm text-green-400">● Доступна</span>
              <router-link to="/chat/llama4-scout" class="px-4 py-2 bg-purple-500 hover:bg-purple-600 rounded-lg text-white transition-colors">
                Выбрать
              </router-link>
            </div>
          </div>

          <!-- DeepSeek-R1 -->
          <div class="bg-white/10 backdrop-blur-lg rounded-xl p-6 hover:bg-white/15 transition-all duration-300 cursor-pointer transform hover:scale-105 flex flex-col h-full">
            <div class="flex items-center mb-4">
              <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mr-4">
                <span class="text-white font-bold text-lg">DSR</span>
              </div>
              <div>
                <h3 class="text-xl font-semibold">DeepSeek-R1</h3>
                <p class="text-sm text-gray-400">Логическое мышление (Reasoning)</p>
              </div>
            </div>
            <p class="text-gray-300 mb-4 flex-grow h-32 overflow-y-auto">
              Модель с продвинутыми способностями логического мышления и рассуждения. Превосходно решает математические задачи, головоломки и задачи, требующие пошагового логического анализа и дедукции.
            </p>
            <div class="flex items-center justify-between mt-auto">
              <span class="text-sm text-green-400">● Доступна</span>
              <router-link to="/chat/deepseek-r1" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 rounded-lg text-white transition-colors">
                Выбрать
              </router-link>
            </div>
          </div>

          <!-- Qwen2.5-Coder -->
          <div class="bg-white/10 backdrop-blur-lg rounded-xl p-6 hover:bg-white/15 transition-all duration-300 cursor-pointer transform hover:scale-105 flex flex-col h-full">
            <div class="flex items-center mb-4">
              <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center mr-4">
                <span class="text-white font-bold text-lg">QC</span>
              </div>
              <div>
                <h3 class="text-xl font-semibold">Qwen2.5-Coder</h3>
                <p class="text-sm text-gray-400">Специализация на коде</p>
              </div>
            </div>
            <p class="text-gray-300 mb-4 flex-grow h-32 overflow-y-auto">
              Специализированная модель для программирования и разработки. Обладает глубоким пониманием различных языков программирования, отладки кода и архитектурных решений. Идеальна для разработчиков всех уровней.
            </p>
            <div class="flex items-center justify-between mt-auto">
              <span class="text-sm text-green-400">● Доступна</span>
              <router-link to="/chat/qwen" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 rounded-lg text-white transition-colors">
                Выбрать
              </router-link>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import NavBar from './NavBar.vue';

</script>

<style>
</style>

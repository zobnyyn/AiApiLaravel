import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    server: {
        host: '0.0.0.0', // Слушать на всех интерфейсах
        port: 5173,
        strictPort: true,
        hmr: {
            host: 'localhost', // HMR через localhost
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            // Следующие параметры помогут Vite правильно работать в Docker
            buildDirectory: 'build',
            publicDirectory: 'public',
        }),
        tailwindcss(),
        vue(),
    ],
    build: {
        rollupOptions: {
            external: ['@google/model-viewer']
        }
    }
});

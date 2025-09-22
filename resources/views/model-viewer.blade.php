<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр 3D модели (GLB)</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            background-color: #1a1a1a;
        }
        .instructions {
            position: absolute;
            bottom: 20px;
            left: 20px;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            font-family: Arial, sans-serif;
            z-index: 100;
        }
        #loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-family: Arial, sans-serif;
            font-size: 18px;
            color: #fff;
            z-index: 10;
        }
        #error {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-family: Arial, sans-serif;
            font-size: 18px;
            color: red;
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 5px;
            display: none;
            z-index: 10;
        }
    </style>
    <!-- Подключаем библиотеку model-viewer от Google с фиксированной версией -->
    <script type="module" src="https://unpkg.com/@google/model-viewer@3.0.2/dist/model-viewer.min.js"></script>
</head>
<body>
    <div id="loading">Загрузка 3D модели...</div>
    <div id="error"></div>
    <div class="instructions">
        <p>Управление: вращение, масштаб, перемещение камеры</p>
    </div>

    <!-- Упрощенный просмотрщик 3D модели -->
    <model-viewer
        id="model-viewer"
        src="/models/anime_girl.glb"
        alt="3D модель аниме девочки"
        camera-controls
        auto-rotate
        shadow-intensity="1"
        exposure="1.2"
        style="width: 100%; height: 100vh; background-color: #1a1a1a;"
    ></model-viewer>

    <script>
        // Получаем ссылку на элемент model-viewer
        const modelViewer = document.querySelector('#model-viewer');
        const loadingElement = document.getElementById('loading');
        const errorElement = document.getElementById('error');

        // Отслеживаем события
        modelViewer.addEventListener('load', () => {
            loadingElement.style.display = 'none';
            console.log('Модель успешно загружена');
        });

        modelViewer.addEventListener('error', (event) => {
            loadingElement.style.display = 'none';
            errorElement.style.display = 'block';
            errorElement.textContent = 'Ошибка загрузки модели: ' + (event.detail?.sourceError?.message || 'Неизвестная ошибка');
            console.error('Ошибка загрузки модели:', event);
        });

        // Проверка на готовность модели к отображению
        modelViewer.addEventListener('model-visibility', (event) => {
            console.log('Видимость модели изменилась:', event.detail.visible);
            if (event.detail.visible) {
                loadingElement.style.display = 'none';
            }
        });
    </script>
</body>
</html>

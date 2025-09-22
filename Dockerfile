FROM php:8.2-fpm

# Установка системных зависимостей и tesseract
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    git \
    unzip \
    curl \
    gnupg \
    tesseract-ocr \
    tesseract-ocr-eng \
    tesseract-ocr-rus \
    && rm -rf /var/lib/apt/lists/*

# Проверяем, что tesseract установлен корректно
RUN tesseract --version

# Установка Node.js и npm (версия 22)
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt-get install -y nodejs

# Установка расширений PHP
RUN docker-php-ext-install pdo pdo_mysql zip

# Установка Composer
COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Копируем только необходимые файлы для composer install
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Копируем остальные файлы проекта
COPY . /var/www

# Завершаем установку composer с запуском скриптов
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Создаем необходимые директории и устанавливаем права
RUN mkdir -p storage/app/public/screenshots \
    && mkdir -p storage/framework/cache \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache

# Создаем символическую ссылку для public/storage
RUN php artisan storage:link || true

# Устанавливаем правильные права доступа
RUN chown -R www-data:www-data storage/ bootstrap/cache/ \
    && chmod -R 755 storage/ bootstrap/cache/

CMD ["php-fpm"]

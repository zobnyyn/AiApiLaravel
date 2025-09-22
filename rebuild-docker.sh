#!/bin/bash

echo "Останавливаем и удаляем существующие контейнеры..."
docker-compose down

echo "Удаляем старые образы..."
docker-compose down --rmi all

echo "Пересобираем контейнеры с новыми зависимостями..."
docker-compose build --no-cache

echo "Запускаем контейнеры..."
docker-compose up -d

echo "Проверяем статус контейнеров..."
docker-compose ps

echo "Проверяем установку Tesseract в контейнере..."
docker-compose exec app tesseract --version

echo "Готово! OCR должен теперь работать."

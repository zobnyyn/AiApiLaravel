<?php

namespace App\Helpers;

class ViteHelper
{
    public static function viteProxy($path)
    {
        if (config('vite.useProxy', false)) {
            // Подключение через прокси вместо прямого обращения к localhost:5173
            return url($path);
        }

        $host = config('vite.host', 'localhost');
        $port = config('vite.port', 5173);

        return "http://{$host}:{$port}/{$path}";
    }
}

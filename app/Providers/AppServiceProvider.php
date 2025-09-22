<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Настройка для Vite
        if (config('vite.useProxy', false)) {
            // Установка атрибутов для скриптов Vite
            if (method_exists(Vite::class, 'useScriptTagAttributes')) {
                Vite::useScriptTagAttributes([
                    'crossorigin' => '',
                ]);
            }

            // Базовая директория для билда
            if (method_exists(Vite::class, 'useBuildDirectory')) {
                Vite::useBuildDirectory('build');
            }
        }
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AiChatServiceFactory;
use App\Services\Llama4Service;
use App\Services\Llama4ScoutService;
use App\Services\DeepSeekR1Service;
use App\Services\QwenService;

class AiServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Регистрация сервисов чат-моделей
        $this->app->singleton(Llama4Service::class, function ($app) {
            return new Llama4Service();
        });

        $this->app->singleton(Llama4ScoutService::class, function ($app) {
            return new Llama4ScoutService();
        });

        $this->app->singleton(DeepSeekR1Service::class, function ($app) {
            return new DeepSeekR1Service();
        });

        $this->app->singleton(QwenService::class, function ($app) {
            return new QwenService();
        });

        // Регистрация фабрики сервисов чат-моделей
        $this->app->singleton(AiChatServiceFactory::class, function ($app) {
            return new AiChatServiceFactory(
                $app->make(Llama4Service::class),
                $app->make(Llama4ScoutService::class),
                $app->make(DeepSeekR1Service::class),
                $app->make(QwenService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

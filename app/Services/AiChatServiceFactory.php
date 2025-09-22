<?php

namespace App\Services;

use App\Services\Llama4Service;
use App\Services\Llama4ScoutService;
use App\Services\DeepSeekR1Service;
use App\Services\QwenService;

class AiChatServiceFactory
{
    protected $services;

    public function __construct(Llama4Service $llama4Service, Llama4ScoutService $llama4ScoutService, DeepSeekR1Service $deepSeekR1Service, QwenService $qwenService)
    {
        $this->services = [
            'llama4' => $llama4Service,
            'llama4scout' => $llama4ScoutService,
            'deepseek_r1' => $deepSeekR1Service,
            'qwen' => $qwenService,
        ];
    }

    public function getService(string $model)
    {
        return $this->services[$model] ?? null;
    }
}

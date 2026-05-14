<?php

namespace App\Services\Ai\BotStrategies;

interface BotStrategyInterface
{
    /**
     * Phân tích tin nhắn và trả về Dữ liệu (Context) tương ứng với vai trò
     */
    public function analyzeAndGetContext($user, $userMessage): string;
}

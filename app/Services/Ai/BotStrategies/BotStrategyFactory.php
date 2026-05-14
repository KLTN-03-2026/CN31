<?php

namespace App\Services\Ai\BotStrategies;

use App\Enums\VaiTro;

class BotStrategyFactory
{
    public static function make($user): BotStrategyInterface
    {
        // Kiểm tra Enum VaiTro để trả về đúng Class logic
        return match ($user->vai_tro) {
            VaiTro::TRUONG_PHONG => new  TruongPhongStrategy(),
            VaiTro::NHAN_VIEN => new NhanVienStrategy(),
            // Thêm các vai trò khác ở đây sau...
            default => new NhanVienStrategy(), // Mặc định an toàn
        };
    }
}

<?php

namespace App\Enums;

enum TrangThaiPhieu: string
{
    case NHAP = 'nhap';        // Mới tạo, chưa gửi
    case CHO_DUYET = 'cho_duyet';  // Đã gửi, chờ sếp xem
    case DA_DUYET = 'da_duyet';   // Sếp đã gật đầu
    case TU_CHOI = 'tu_choi';    // Sếp lắc đầu
    case DA_CHI_TIEN = 'da_chi_tien'; // Kế toán đã chuyển khoản

    public function label(): string
    {
        return match ($this) {
            self::NHAP => 'Bản nháp',
            self::CHO_DUYET => 'Chờ duyệt',
            self::DA_DUYET => 'Đã duyệt',
            self::TU_CHOI => 'Từ chối',
            self::DA_CHI_TIEN => 'Đã chi tiền',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NHAP => 'gray',
            self::CHO_DUYET => 'yellow',
            self::DA_DUYET => 'blue',
            self::TU_CHOI => 'red',
            self::DA_CHI_TIEN => 'green',
        };
    }

    // ĐÂY LÀ HÀM MENTOR BỔ SUNG:
    // Dùng để ném thẳng mảng này qua Inertia sang Vue.js tạo Dropdown
    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'color' => $case->color(),
        ], self::cases());
    }
}

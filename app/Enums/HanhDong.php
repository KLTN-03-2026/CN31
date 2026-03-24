<?php

namespace App\Enums;

enum HanhDong: string
{
    case TAO_MOI = 'tao_moi';
    case TRUONG_PHONG_DUYET = 'truong_phong_duyet';
    case GIAM_DOC_DUYET = 'giam_doc_duyet';
    case TU_CHOI = 'tu_choi';
    case HUY = 'huy';
    case THANH_TOAN = 'thanh_toan';

    public function label(): string
    {
        return match($this) {
            self::TAO_MOI => 'Tạo mới phiếu',
            self::TRUONG_PHONG_DUYET => 'Trưởng phòng đã duyệt',
            self::GIAM_DOC_DUYET => 'Giám đốc đã duyệt',
            self::TU_CHOI => 'Từ chối yêu cầu',
            self::HUY => 'Hủy yêu cầu',
            self::THANH_TOAN => 'Đã thanh toán VNPAY',
        };
    }

    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}

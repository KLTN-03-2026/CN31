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
    case CAP_NHAT_BAO_GIA = 'cap_nhat_bao_gia';
    case NHAN_SU_DUYET = 'nhan_su_duyet';
    case DA_HOAN_TAT = 'da_hoan_tat';
    case NHAN_HANG = 'nhan_hang';

    public function label(): string
    {
        return match($this) {
            self::TAO_MOI => 'Tạo mới phiếu',
            self::TRUONG_PHONG_DUYET => 'Trưởng phòng đã duyệt',
            self::GIAM_DOC_DUYET => 'Giám đốc đã duyệt',
            self::TU_CHOI => 'Từ chối yêu cầu',
            self::HUY => 'Hủy yêu cầu',
            self::THANH_TOAN => 'Đã thanh toán VNPAY',
            self::CAP_NHAT_BAO_GIA => 'Phòng Mua sắm đã cập nhật giá',
            self::NHAN_SU_DUYET => 'Nhân sự duyệt',
            self::DA_HOAN_TAT => 'Đã hoàn tất',
            self::NHAN_HANG => 'Xác nhận đã nhận hàng',
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

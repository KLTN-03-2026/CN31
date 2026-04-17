<?php
namespace App\Enums;

enum TrangThaiPhieu: string
{

    case CHO_TRUONG_PHONG_DUYET = 'cho_truong_phong_duyet';
    case CHO_MUA_SAM_BAO_GIA = 'cho_mua_sam_bao_gia';
    case CHO_GIAM_DOC_DUYET = 'cho_giam_doc_duyet';
    case CHO_THANH_TOAN = 'cho_thanh_toan';
    case DA_THANH_TOAN = 'da_thanh_toan';
    case TU_CHOI = 'tu_choi';
    case DA_HUY = 'da_huy';
    case CHO_NHAN_SU_DUYET = 'cho_nhan_su_duyet';
    case DA_HOAN_TAT = 'da_hoan_tat';
    case NHAN_SU_DUYET = 'nhan_su_duyet';

    public function label(): string
    {
        return match($this) {
            self::CHO_TRUONG_PHONG_DUYET => 'Chờ TP duyệt',
            self::CHO_MUA_SAM_BAO_GIA => 'Chờ báo giá',
            self::CHO_GIAM_DOC_DUYET => 'Chờ GĐ duyệt',
            self::CHO_THANH_TOAN => 'Chờ thanh toán',
            self::DA_THANH_TOAN => 'Đã thanh toán',
            self::TU_CHOI => 'Từ chối',
            self::DA_HUY => 'Đã hủy',
            self::CHO_NHAN_SU_DUYET => 'Chờ HR xử lý',
            self::DA_HOAN_TAT => 'Hoàn tất mua sắm',
            self::NHAN_SU_DUYET => 'Hoàn tất nghĩ phép',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::CHO_TRUONG_PHONG_DUYET => 'yellow',
            self::CHO_MUA_SAM_BAO_GIA => 'purple',
            self::CHO_GIAM_DOC_DUYET => 'orange',
            self::CHO_THANH_TOAN => 'blue',
            self::DA_THANH_TOAN => 'blue',
            self::TU_CHOI => 'red',
            self::DA_HUY => 'red',
            self::CHO_NHAN_SU_DUYET => 'pink',
            self::DA_HOAN_TAT => 'green',
            self::NHAN_SU_DUYET => 'green',

        };
    }

    public static function options(): array
    {
        return array_map(fn($case) => ['value' => $case->value, 'label' => $case->label(), 'color' => $case->color()], self::cases());
    }
}

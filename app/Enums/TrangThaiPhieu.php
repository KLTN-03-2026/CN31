<?php

namespace App\Enums;

enum TrangThaiPhieu: string
{ // Các trạng thái của phiếu yêu cầu
    case NHAP = 'nhap';
    case CHO_TRUONG_PHONG_DUYET = 'cho_truong_phong_duyet';
    case CHO_GIAM_DOC_DUYET = 'cho_giam_doc_duyet';
    case CHO_THANH_TOAN = 'cho_thanh_toan';
    case DA_THANH_TOAN = 'da_thanh_toan';
    case TU_CHOI = 'tu_choi';
    case DA_HUY = 'da_huy';

    // Hàm trả về nhãn hiển thị tương ứng với từng trạng thái
    public function label(): string
    {
        return match($this) {
            self::NHAP => 'Bản nháp',
            self::CHO_TRUONG_PHONG_DUYET => 'Chờ TP duyệt',
            self::CHO_GIAM_DOC_DUYET => 'Chờ GĐ duyệt',
            self::CHO_THANH_TOAN => 'Chờ thanh toán',
            self::DA_THANH_TOAN => 'Đã thanh toán',
            self::TU_CHOI => 'Từ chối',
            self::DA_HUY => 'Đã hủy',
        };
    }
// Hàm trả về màu sắc tương ứng với từng trạng thái
    public function color(): string
    {
        return match($this) {
            self::NHAP => 'gray',
            self::CHO_TRUONG_PHONG_DUYET => 'yellow',
            self::CHO_GIAM_DOC_DUYET => 'orange',
            self::CHO_THANH_TOAN => 'blue',
            self::DA_THANH_TOAN => 'green',
            self::TU_CHOI => 'red',
            self::DA_HUY => 'slate', // Màu xám đậm
        };
    }
// Hàm trả về tất cả các trạng thái dưới dạng mảng
    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'color' => $case->color(),
        ], self::cases());
    }
}

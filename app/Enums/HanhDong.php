<?php

namespace App\Enums;

enum HanhDong: string
{
    case TAO_MOI = 'tao_moi';   // nhân viên tạo mới phiếu
    case TRUONG_PHONG_DUYET = 'truong_phong_duyet';  // trưởng phòng duyệt
    case GIAM_DOC_DUYET = 'giam_doc_duyet';    // giám đốc duyệt
    case TU_CHOI = 'tu_choi'; // trưởng phòng hoặc giám đốc từ chối, hr từ chối(đơn nghỉ phép)
    case HUY = 'huy'; // nhân viên hủy phiếu (chỉ được hủy khi phiếu đang ở trạng thái chờ trưởng phòng duyệt)
    case THANH_TOAN = 'thanh_toan'; // kế toán đã thanh toán (chỉ áp dụng cho phiếu mua sắm, sau khi giám đốc duyệt xong sẽ chuyển sang trạng thái này)
    case CAP_NHAT_BAO_GIA = 'cap_nhat_bao_gia';  // phòng mua sắm cập nhật báo giá (chỉ áp dụng cho phiếu mua sắm, sau khi nhân viên tạo phiếu xong sẽ chuyển sang trạng thái này để phòng mua sắm vào cập nhật báo giá)
    case NHAN_SU_DUYET = 'nhan_su_duyet';  // nhân sự duyệt (chỉ áp dụng cho đơn nghỉ phép, sau khi giám đốc duyệt xong sẽ chuyển sang trạng thái này để nhân sự vào duyệt)
    case DA_HOAN_TAT = 'da_hoan_tat'; // sau khi kế toán thanh toán xong, nhân viên nhấn nút xác nhận đã nhận hàng thì chuyển sang trạng thái này hoặc khi hr duyệt đơn nghỉ phép xong thì chuyển sang trạng thái này luôn, coi như đã hoàn tất quy trình
    case NHAN_HANG = 'nhan_hang'; //
    case DUYET = 'duyet'; // trạng thái tạm thời để xử lý chung cho cả trưởng phòng và giám đốc, sau khi duyệt xong sẽ chuyển sang trạng thái cụ thể là trưởng phòng duyệt hoặc giám đốc duyệt

    public function label(): string
    {
        return match ($this) {
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
            self::DUYET => 'Đã duyệt',

        };
    }

    public static function options(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}

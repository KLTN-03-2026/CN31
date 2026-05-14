<?php

namespace App\Services\Ai\BotStrategies;
use App\Models\PhieuYeuCau;
use App\Enums\TrangThaiPhieu;
use Illuminate\Support\Str;

class NhanVienStrategy implements BotStrategyInterface
{
    public function analyzeAndGetContext($user, $userMessage): string
    {

        $normalizedMessage = Str::slug($userMessage);
        $context = "";

        // 1. Nhận diện Intent: Thống kê (Mở rộng cực nhiều từ khóa đồng nghĩa, không dấu)
        if (preg_match('/(thong-ke|tong-so|bao-nhieu|phieu|tien|cao-nhat|trang-thai|da-tao)/i', $normalizedMessage) && !preg_match('/(pyc-\d+)/i', $normalizedMessage)) {

            $stats = [
                'tổng_số_phiếu_đã_tạo' => PhieuYeuCau::where('nguoi_tao_id', $user->id)->count(),
                'số_phiếu_đang_chờ_duyệt' => PhieuYeuCau::where('nguoi_tao_id', $user->id)
                    ->whereIn('trang_thai', [
                        TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET->value,
                        TrangThaiPhieu::CHO_GIAM_DOC_DUYET->value,
                        TrangThaiPhieu::CHO_NHAN_SU_DUYET->value
                    ])->count(),
                'số_phiếu_đã_thanh_toán' => PhieuYeuCau::where('nguoi_tao_id', $user->id)
                    ->where('trang_thai', TrangThaiPhieu::DA_THANH_TOAN->value)->count(),
                'số_phiếu_bị_từ_chối_hoặc_hủy' => PhieuYeuCau::where('nguoi_tao_id', $user->id)
                    ->whereIn('trang_thai', [TrangThaiPhieu::TU_CHOI->value, TrangThaiPhieu::DA_HUY->value])->count(),
                'số_phiếu_chờ_báo_giá' => PhieuYeuCau::where('nguoi_tao_id', $user->id)
                    ->where('trang_thai', TrangThaiPhieu::CHO_MUA_SAM_BAO_GIA->value)->count(),
                'số_phiếu_đã_hoàn_tất' => PhieuYeuCau::where('nguoi_tao_id', $user->id)
                    ->where('trang_thai', TrangThaiPhieu::DA_HOAN_TAT->value)->count(),
                'tổng_tiền_các_phiếu_đã_tạo' => PhieuYeuCau::where('nguoi_tao_id', $user->id)->sum('tong_tien'),
                'giá_trị_phiếu_cao_nhất_từng_tạo' => PhieuYeuCau::where('nguoi_tao_id', $user->id)->max('tong_tien') ?? 0,
            ];
            $context .= "\n\n[DỮ LIỆU HỆ THỐNG - THỐNG KÊ CÁ NHÂN]: " . json_encode($stats, JSON_UNESCAPED_UNICODE) . " (AI hãy lọc đúng thông tin user hỏi để trả lời).";
        }

        // 2. Nhận diện Intent: Mã phiếu (Giữ nguyên Regex tìm pyc-số, vì mã code thì user ít gõ sai)
        if (preg_match('/(pyc-\d+)/i', mb_strtolower($userMessage), $matches)) {
            $maPhieu = strtoupper($matches[1]);
            $phieu = PhieuYeuCau::where('ma_phieu', $maPhieu)->where('nguoi_tao_id', $user->id)->with('chiTiet')->first();

            if ($phieu) {
                $data = [
                    "ma_phieu" => $phieu->ma_phieu,
                    "trang_thai" => $phieu->trang_thai->label(),
                    "tong_tien" => $phieu->tong_tien,
                    "chi_tiet_hang" => $phieu->chiTiet->pluck('ten_san_pham')->toArray()
                ];
                $context .= "\n\n[DỮ LIỆU HỆ THỐNG - CHI TIẾT PHIẾU]: " . json_encode($data, JSON_UNESCAPED_UNICODE);
            } else {
                $context .= "\n\n[DỮ LIỆU HỆ THỐNG]: Phiếu {$maPhieu} không tồn tại hoặc user không có quyền truy cập.";
            }
        }

        return $context;
    }
}

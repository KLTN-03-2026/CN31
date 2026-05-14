<?php

namespace App\Services\Ai\BotStrategies;

use App\Models\PhieuYeuCau;
use App\Models\PhongBan;
use App\Models\User;
use App\Enums\TrangThaiPhieu;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TruongPhongStrategy implements BotStrategyInterface
{
    public function analyzeAndGetContext($user, $userMessage): string
    {
        $normalizedMessage = Str::slug($userMessage);
        $context = "";

        if (preg_match('/(ngan-sach|kinh-phi|quy-phong|tien-phong|con-bao-nhieu|tieu-het)/i', $normalizedMessage)) {
            $phongBan = PhongBan::find($user->phong_ban_id);
            if ($phongBan) {
                $context .= "\n\n[DỮ LIỆU NGÂN SÁCH PHÒNG]: " . json_encode($phongBan->only(['ngan_sach_tong', 'ngan_sach_su_dung', 'ngan_sach_con_lai']), JSON_UNESCAPED_UNICODE);
            }
        }

        if (preg_match('/(phieu|duyet|chi-tieu|thang|da-duyet|cho-duyet)/i', $normalizedMessage) && !preg_match('/(pr-[a-z0-9]{6})/i', $normalizedMessage)) {
            $thangHienTai = Carbon::now()->month;
            $namHienTai = Carbon::now()->year;

            $stats = [
                'số_phiếu_đang_chờ_tôi_duyệt' => PhieuYeuCau::where('phong_ban_id', $user->phong_ban_id)
                    ->where('trang_thai', TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET->value)->count(),
                'số_phiếu_tôi_đã_duyệt' => PhieuYeuCau::where('phong_ban_id', $user->phong_ban_id)
                    ->whereIn('trang_thai', [
                        TrangThaiPhieu::CHO_MUA_SAM_BAO_GIA->value,
                        TrangThaiPhieu::CHO_GIAM_DOC_DUYET->value,
                        TrangThaiPhieu::CHO_THANH_TOAN->value,
                        TrangThaiPhieu::DA_THANH_TOAN->value,
                        TrangThaiPhieu::DA_HOAN_TAT->value
                    ])->count(),
                'tổng_chi_tiêu_phòng_tháng_này' => PhieuYeuCau::where('phong_ban_id', $user->phong_ban_id)
                    ->whereMonth('created_at', $thangHienTai)
                    ->whereYear('created_at', $namHienTai)
                    ->sum('tong_tien'),
            ];
            $context .= "\n\n[DỮ LIỆU PHÊ DUYỆT & CHI TIÊU]: " . json_encode($stats, JSON_UNESCAPED_UNICODE);
        }

        if (preg_match('/(nhan-su|nhan-vien|nguoi|thanh-vien)/i', $normalizedMessage)) {
            $soNhanSu = User::where('phong_ban_id', $user->phong_ban_id)->count();
            $context .= "\n\n[DỮ LIỆU NHÂN SỰ]: Phòng ban hiện có tổng cộng {$soNhanSu} nhân sự.";
        }

        if (preg_match('/(pr-[a-z0-9]{6})/i', mb_strtolower($userMessage), $matches)) {
            $maPhieu = strtoupper($matches[1]);

            $phieu = PhieuYeuCau::where('ma_phieu', $maPhieu)
                        ->where('phong_ban_id', $user->phong_ban_id)
                        ->with(['nguoiTao', 'chiTiet'])
                        ->first();

            if ($phieu) {
                $urlChiTiet = route('phieu.show', $phieu->id);
                $htmlChiTiet = "<a href='{$urlChiTiet}' target='_blank' style='color: #2563eb; text-decoration: underline; font-weight: bold;'>Xem chi tiết phiếu tại đây</a>";

                $data = [
                    "mã_phiếu" => $phieu->ma_phieu,
                    "người_tạo" => $phieu->nguoiTao->name,
                    "trạng_thái" => $phieu->trang_thai->label(),
                    "tổng_tiền" => number_format($phieu->tong_tien, 0, ',', '.') . ' VNĐ',
                    "chi_tiết" => $phieu->chiTiet->map(fn($item) => $item->ten_san_pham . ' (SL: ' . $item->so_luong . ')')->toArray(),
                    "html_link_chi_tiet" => $htmlChiTiet
                ];

                if (!empty($phieu->file_bao_gia)) {
                    $urlPdf = asset('storage/' . $phieu->file_bao_gia);
                    $htmlPdf = "<a href='{$urlPdf}' target='_blank' style='color: #dc2626; text-decoration: underline; font-weight: bold;'>Xem file PDF Báo giá</a>";
                    $data["html_link_pdf"] = $htmlPdf;
                }

                $context .= "\n\n[DỮ LIỆU TÓM TẮT PHIẾU]: " . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . " (NẾU CÓ 'html_link_chi_tiet' VÀ 'html_link_pdf', BẮT BUỘC in y nguyên đoạn HTML đó ra, không được tự viết lại thẻ a).";
            } else {
                $context .= "\n\n[DỮ LIỆU TÓM TẮT PHIẾU]: Không tìm thấy phiếu {$maPhieu} hoặc không thuộc quyền quản lý.";
            }
        }

        return $context;
    }
}

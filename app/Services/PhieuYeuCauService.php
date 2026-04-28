<?php

namespace App\Services;

use App\Enums\HanhDong;
use App\Enums\TrangThaiPhieu;
use App\Models\ChiTietNghiPhep;
use App\Models\NhatKyDuyet;
use App\Models\PhieuYeuCau;
use App\Models\User;
use App\Notifications\PhieuYeuCauNotification;
use Illuminate\Support\Facades\DB;

class PhieuYeuCauService
{
    /**
     * Xử lý luồng Phê duyệt hoặc Từ chối Phiếu yêu cầu
     */
    public function xuLyPheDuyet(PhieuYeuCau $phieu, User $user, string $hanhDongInput, ?string $ghiChu)
    {
        DB::transaction(function () use ($phieu, $user, $hanhDongInput, $ghiChu) {
            $trangThaiMoi = $phieu->trang_thai;
            $hanhDongLog = HanhDong::TU_CHOI;

            // --- LUỒNG TỪ CHỐI ---
            if ($hanhDongInput === 'tu_choi') {
                $trangThaiMoi = TrangThaiPhieu::TU_CHOI;
                $phieu->nguoiTao?->notify(new PhieuYeuCauNotification($phieu, "Phiếu của bạn bị TỪ CHỐI bởi {$user->name}. Lý do: {$ghiChu}", 'error'));
            }
            // --- LUỒNG PHÊ DUYỆT ---
            else {
                if ($phieu->loai_phieu === 'nghi_phep') {
                    $this->handleLeaveApproval($phieu, $user, $trangThaiMoi, $hanhDongLog);
                } else {
                    $this->handlePurchaseApproval($phieu, $user, $trangThaiMoi, $hanhDongLog);
                }
            }

            // Lưu trạng thái và Ghi Log
            $phieu->update(['trang_thai' => $trangThaiMoi]);

            NhatKyDuyet::create([
                'phieu_yeu_cau_id' => $phieu->id,
                'nguoi_thuc_hien_id' => $user->id,
                'hanh_dong' => $hanhDongLog,
                'ghi_chu' => $ghiChu ?? ($hanhDongInput === 'duyet' ? 'Đã phê duyệt yêu cầu' : 'Từ chối yêu cầu'),
            ]);
        });
    }

    /**
     * Xử lý luồng Hủy phiếu bởi chính người tạo
     */
    public function xuLyHuyPhieu(PhieuYeuCau $phieu, User $user, ?string $ghiChu)
    {
        DB::transaction(function () use ($phieu, $user, $ghiChu) {
            $phieu->update(['trang_thai' => TrangThaiPhieu::DA_HUY]);

            NhatKyDuyet::create([
                'phieu_yeu_cau_id' => $phieu->id,
                'nguoi_thuc_hien_id' => $user->id,
                'hanh_dong' => HanhDong::HUY,
                'ghi_chu' => $ghiChu ?? 'Người tạo tự hủy phiếu',
            ]);
        });
    }

    // ==========================================
    // PRIVATE METHODS (CHỈ DÙNG NỘI BỘ TRONG SERVICE)
    // ==========================================

    private function handleLeaveApproval(PhieuYeuCau $phieu, User $user, &$trangThaiMoi, &$hanhDongLog)
    {
        if ($user->isTruongPhong() && $phieu->trang_thai === TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET) {
            $trangThaiMoi = TrangThaiPhieu::CHO_NHAN_SU_DUYET;
            $hanhDongLog = HanhDong::TRUONG_PHONG_DUYET;

            User::where('vai_tro', 'nhan_su')->first()?->notify(new PhieuYeuCauNotification($phieu, 'Có đơn xin nghỉ phép đã được Trưởng phòng duyệt. Vui lòng kiểm tra và chốt phép.', 'info'));
            $phieu->nguoiTao?->notify(new PhieuYeuCauNotification($phieu, 'Trưởng phòng đã DUYỆT đơn nghỉ phép. Đang chờ Nhân sự chốt sổ.', 'success'));

        } elseif ($user->isNhanSu() && $phieu->trang_thai === TrangThaiPhieu::CHO_NHAN_SU_DUYET) {
            $trangThaiMoi = TrangThaiPhieu::NHAN_SU_DUYET;
            $hanhDongLog = HanhDong::NHAN_SU_DUYET;

            $chiTiet = ChiTietNghiPhep::where('phieu_yeu_cau_id', $phieu->id)->first();
            if ($chiTiet && $chiTiet->loai_nghi_phep === 'nghi_phep_nam') {
                User::where('id', $phieu->nguoi_tao_id)->increment('ngay_phep_da_dung', $chiTiet->so_ngay_nghi);
            }

            $phieu->nguoiTao?->notify(new PhieuYeuCauNotification($phieu, 'Đơn xin nghỉ phép đã được Nhân sự phê duyệt hoàn tất!', 'success'));
        } else {
            abort(403, 'Bạn không có thẩm quyền duyệt bước này.');
        }
    }

    private function handlePurchaseApproval(PhieuYeuCau $phieu, User $user, &$trangThaiMoi, &$hanhDongLog)
    {
        if ($user->isTruongPhong() && $phieu->trang_thai === TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET) {
            $trangThaiMoi = TrangThaiPhieu::CHO_MUA_SAM_BAO_GIA;
            $hanhDongLog = HanhDong::TRUONG_PHONG_DUYET;

            User::where('vai_tro', 'nhan_vien_mua_sam')->first()?->notify(new PhieuYeuCauNotification($phieu, 'Trưởng phòng đã duyệt yêu cầu mua sắm. Vui lòng chốt báo giá.', 'info'));
            $phieu->nguoiTao?->notify(new PhieuYeuCauNotification($phieu, 'Trưởng phòng đã DUYỆT. Hệ thống đã chuyển sang phòng Mua Sắm.', 'success'));

        } elseif ($user->isGiamDoc() && $phieu->trang_thai === TrangThaiPhieu::CHO_GIAM_DOC_DUYET) {
            $trangThaiMoi = TrangThaiPhieu::CHO_THANH_TOAN;
            $hanhDongLog = HanhDong::GIAM_DOC_DUYET;

            User::where('vai_tro', 'ke_toan')->first()?->notify(new PhieuYeuCauNotification($phieu, 'Giám đốc đã duyệt phiếu mua sắm vượt hạn mức. Vui lòng thanh toán.', 'success'));
            $phieu->nguoiTao?->notify(new PhieuYeuCauNotification($phieu, 'Sếp lớn đã DUYỆT. Kế toán đang tiến hành thanh toán.', 'success'));
            User::where('vai_tro', 'nhan_vien_mua_sam')->first()?->notify(new PhieuYeuCauNotification($phieu, 'Giám đốc đã DUYỆT báo giá bạn vừa trình.', 'success'));

        } else {
            abort(403, 'Bạn không có thẩm quyền duyệt bước này.');
        }
    }
}

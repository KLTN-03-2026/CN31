<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PhieuYeuCau;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Api\XuLyPhieuRequest;
use Illuminate\Support\Facades\DB;
use App\Models\NhatKyDuyet;
use App\Notifications\PhieuYeuCauNotification;
use App\Enums\HanhDong;
use App\Enums\TrangThaiPhieu;

class PhieuYeuCauController extends Controller
{
    // 1. API LẤY DANH SÁCH PHIẾU (Dành cho màn hình chính của Sếp)
    public function danhSachChoDuyet(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = PhieuYeuCau::query()
            ->with(['nguoiTao:id,name,avatar'])
            ->orderBy('created_at', 'desc');

        if ($user->isTruongPhong()) {
            $query->where('phong_ban_id', $user->phong_ban_id)
                  ->where('trang_thai', TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET);
        } elseif ($user->isGiamDoc()) {
            $query->where('trang_thai', TrangThaiPhieu::CHO_GIAM_DOC_DUYET);
        } else {
            return response()->json(['success' => false, 'message' => 'Không có quyền truy cập.'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách thành công.',
            'data' => $query->get()
        ], 200);
    }

  // 2. API LẤY CHI TIẾT 1 PHIẾU (Dành cho màn hình chi tiết khi Sếp bấm vào)
    public function show($id): JsonResponse
    {
        $phieu = PhieuYeuCau::with([
            'nguoiTao:id,name',
            'nhatKy' => function ($query) {
                $query->with('nguoiThucHien:id,name')->orderBy('thoi_gian_duyet', 'desc');
            }
        ])->findOrFail($id);

        // --- Nếu là phiếu nghỉ phép ---
        if ($phieu->loai_phieu === 'nghi_phep') {
            $phieu->load('chiTietNghiPhep');
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $phieu->id,
                    'ma_phieu' => $phieu->ma_phieu,
                    'tieu_de' => $phieu->tieu_de,
                    'ly_do' => $phieu->ly_do,
                    'ngay_tao' => $phieu->created_at->format('d/m/Y H:i'),
                    'nguoi_tao' => $phieu->nguoiTao->name,
                    'trang_thai_label' => $phieu->trang_thai->label(),
                    'trang_thai_color' => $phieu->trang_thai->color(),
                    'chi_tiet_nghi_phep' => $phieu->chiTietNghiPhep ? [
                        'so_ngay_nghi' => $phieu->chiTietNghiPhep->so_ngay_nghi . ' ngày',
                        'thoi_gian' => $phieu->chiTietNghiPhep->ngay_bat_dau->format('d/m/Y') . ' - ' . $phieu->chiTietNghiPhep->ngay_ket_thuc->format('d/m/Y'),
                    ] : null,
                ]
            ], 200);
        }

        // --- Nếu là phiếu mua sắm ---
        // SỬA Ở ĐÂY: Load bảng chiTiet và danhMuc đi kèm
        $phieu->load(['chiTiet.danhMuc']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $phieu->id,
                'ma_phieu' => $phieu->ma_phieu,
                'tieu_de' => $phieu->tieu_de,
                'ly_do' => $phieu->ly_do,
                'tong_tien' => number_format($phieu->tong_tien ?: 0, 0, ',', '.') . ' VNĐ',
                'nguoi_tao' => $phieu->nguoiTao->name,
                'ngay_tao' => $phieu->created_at->format('d/m/Y H:i'),
                'trang_thai_label' => $phieu->trang_thai->label(),
                'trang_thai_color' => $phieu->trang_thai->color(),
                'file_bao_gia_url' => $phieu->file_bao_gia ? asset('storage/' . $phieu->file_bao_gia) : null,

                // SỬA Ở ĐÂY: Map đúng tên cột trong Migration của bạn
                'danh_sach_vat_tu' => $phieu->chiTiet->map(fn($item) => [
                    'ten' => $item->ten_san_pham, // Trực tiếp lấy từ bảng chi_tiet_yeu_cau
                    'danh_muc' => $item->danhMuc ? $item->danhMuc->ten_danh_muc : 'Khác',
                    'so_luong' => $item->so_luong, // Đã xóa don_vi_tinh vì DB không có
                    'thanh_tien' => number_format($item->thanh_tien ?: 0, 0, ',', '.')
                ]),

                'tong_so_lich_su' => $phieu->nhatKy->count(),
                'lich_su_duyet_preview' => $phieu->nhatKy->take(3)->map(fn($log) => [
                    'nguoi_duyet' => $log->nguoiThucHien ? $log->nguoiThucHien->name : 'Hệ thống',
                    'hanh_dong' => $log->hanh_dong->label(),
                    'thoi_gian' => \Carbon\Carbon::parse($log->thoi_gian_duyet)->format('H:i d/m')
                ])
            ]
        ], 200);
    }

    // 3. API XỬ LÝ (DUYỆT / TỪ CHỐI)
    public function xuLy(XuLyPhieuRequest $request, $id): JsonResponse
    {
        $user = $request->user();
        $phieu = PhieuYeuCau::find($id);

        if (!$phieu) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy phiếu yêu cầu.'], 404);
        }

        if ($user->isTruongPhong() && $phieu->trang_thai !== TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET) {
            return response()->json(['success' => false, 'message' => 'Phiếu này không ở trạng thái chờ Trưởng phòng duyệt.'], 400);
        }
        if ($user->isGiamDoc() && $phieu->trang_thai !== TrangThaiPhieu::CHO_GIAM_DOC_DUYET) {
            return response()->json(['success' => false, 'message' => 'Phiếu này không ở trạng thái chờ Giám đốc duyệt.'], 400);
        }

        try {
            DB::beginTransaction();

            $hanhDong = $request->hanh_dong;

            if ($hanhDong === HanhDong::TU_CHOI->value) {
                $phieu->trang_thai = TrangThaiPhieu::TU_CHOI;
            } else {
                if ($user->isTruongPhong()) {
                    $phieu->trang_thai = TrangThaiPhieu::CHO_MUA_SAM_BAO_GIA;
                } elseif ($user->isGiamDoc()) {
                    $phieu->trang_thai = TrangThaiPhieu::CHO_THANH_TOAN;
                }
            }
            $phieu->save();

            NhatKyDuyet::create([
                'phieu_yeu_cau_id' => $phieu->id,
                'nguoi_thuc_hien_id' => $user->id,
                'hanh_dong' => $hanhDong,
                'ghi_chu' => $request->ly_do,
            ]);

            DB::commit();

            if ($nguoiTao = $phieu->nguoiTao) {
                $thongBao = $hanhDong === 'tu_choi'
                    ? ($user->isTruongPhong() ? 'Trưởng phòng đã từ chối phiếu yêu cầu.' : 'Giám đốc đã từ chối phiếu yêu cầu.')
                    : ($user->isTruongPhong() ? 'Trưởng phòng đã duyệt phiếu yêu cầu.' : 'Giám đốc đã duyệt phiếu yêu cầu.');

                $nguoiTao->notify(new PhieuYeuCauNotification($phieu, $thongBao, $hanhDong === 'tu_choi' ? 'error' : 'success'));
            }

            return response()->json(['success' => true, 'message' => 'Xử lý phiếu thành công.', 'data' => $phieu], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi trong quá trình xử lý.', 'error' => $e->getMessage()], 500);
        }
    }
}

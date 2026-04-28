<?php

namespace App\Http\Controllers\NghiepVu;

use App\Enums\TrangThaiPhieu;
use App\Http\Controllers\Controller;
use App\Models\NhaCungCap;
use App\Models\PhieuYeuCau;
use App\Services\PhieuYeuCauService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia; // Bổ sung Service vào đây

class PhieuYeuCauController extends Controller
{
    /**
     * Hiển thị chi tiết phiếu (Mua sắm hoặc Nghỉ phép)
     */
    public function show($id)
    {
        $phieu = PhieuYeuCau::with([
            'nguoiTao.phongBan',
            'nhatKy' => fn ($query) => $query->with('nguoiThucHien')->orderBy('thoi_gian_duyet', 'desc'),
        ])->findOrFail($id);

        // --- View chi tiết cho Đơn Nghỉ Phép ---
        if ($phieu->loai_phieu === 'nghi_phep') {
            $phieu->load('chiTietNghiPhep.nguoiBanGiao');

            return Inertia::render('Modules/NghiPhep/ChiTiet', [
                'phieu' => [
                    'id' => $phieu->id,
                    'ma_phieu' => $phieu->ma_phieu,
                    'tieu_de' => $phieu->tieu_de,
                    'ly_do' => $phieu->ly_do,
                    'ngay_tao' => $phieu->created_at->format('d/m/Y H:i'),
                    'nguoi_tao' => $phieu->nguoiTao->name,
                    'trang_thai_label' => $phieu->trang_thai->label(),
                    'trang_thai_color' => $phieu->trang_thai->color(),
                    'chi_tiet_nghi_phep' => $phieu->chiTietNghiPhep ? [
                        'loai_nghi_phep' => $phieu->chiTietNghiPhep->loai_nghi_phep,
                        'ngay_bat_dau' => $phieu->chiTietNghiPhep->ngay_bat_dau->format('d/m/Y'),
                        'ngay_ket_thuc' => $phieu->chiTietNghiPhep->ngay_ket_thuc->format('d/m/Y'),
                        'so_ngay_nghi' => $phieu->chiTietNghiPhep->so_ngay_nghi,
                        'nguoi_ban_giao' => $phieu->chiTietNghiPhep->nguoiBanGiao?->name ?? 'Không có',
                    ] : null,
                    'nhat_ky' => $phieu->nhatKy->map(fn ($log) => [
                        'id' => $log->id,
                        'hanh_dong_label' => $log->hanh_dong->label(),
                        'nguoi_thuc_hien' => $log->nguoiThucHien->name,
                        'ghi_chu' => $log->ghi_chu,
                        'thoi_gian' => Carbon::parse($log->thoi_gian_duyet)->format('d/m/Y H:i:s'),
                    ]),
                ],
            ]);
        }

        // --- View chi tiết cho Phiếu Mua Sắm ---
        $phieu->load(['chiTiet.danhMuc', 'nhaCungCap']);

        $nhaCungCaps = (Auth::user()->isMuaSam() && $phieu->trang_thai === TrangThaiPhieu::CHO_MUA_SAM_BAO_GIA)
            ? NhaCungCap::select('id', 'ten_nha_cung_cap')->get()
            : [];

        $nganSach = null;
        if (Auth::user()->vai_tro !== 'nhan_vien' && $phieu->nguoiTao?->phongBan) {
            $pb = $phieu->nguoiTao->phongBan;
            $nganSach = [
                'ten_phong' => $pb->ten_phong_ban,
                'tong' => $pb->ngan_sach_tong,
                'da_dung' => $pb->ngan_sach_su_dung,
                'con_lai' => $pb->ngan_sach_con_lai,
                'phan_tram' => $pb->phan_tram_su_dung,
            ];
        }

        return Inertia::render('Modules/MuaSam/ChiTiet', [
            'phieu' => [
                'id' => $phieu->id,
                'ma_phieu' => $phieu->ma_phieu,
                'tieu_de' => $phieu->tieu_de,
                'ly_do' => $phieu->ly_do,
                'tong_tien' => $phieu->tong_tien,
                'file_bao_gia' => $phieu->file_bao_gia ? asset('storage/'.$phieu->file_bao_gia) : null,
                'nha_cung_cap' => $phieu->nhaCungCap?->ten_nha_cung_cap ?? 'Chưa xác định',
                'file_nhan_hang' => $phieu->file_nhan_hang ? asset('storage/'.$phieu->file_nhan_hang) : null,
                'ghi_chu_nhan_hang' => $phieu->ghi_chu_nhan_hang,
                'ngay_tao' => $phieu->created_at->format('d/m/Y H:i'),
                'nguoi_tao' => $phieu->nguoiTao->name,
                'trang_thai_label' => $phieu->trang_thai->label(),
                'trang_thai_color' => $phieu->trang_thai->color(),
                'chi_tiet' => $phieu->chiTiet,
                'nhat_ky' => $phieu->nhatKy->map(fn ($log) => [
                    'id' => $log->id,
                    'hanh_dong_label' => $log->hanh_dong->label(),
                    'nguoi_thuc_hien' => $log->nguoiThucHien->name,
                    'ghi_chu' => $log->ghi_chu,
                    'thoi_gian' => Carbon::parse($log->thoi_gian_duyet)->format('d/m/Y H:i:s'),
                ]),
            ],
            'nhaCungCaps' => $nhaCungCaps,
            'nganSach' => $nganSach,
        ]);
    }

    /**
     * Ủy quyền phê duyệt/từ chối cho Service xử lý
     */
    public function approve(Request $request, $id, PhieuYeuCauService $service)
    {
        $phieu = PhieuYeuCau::with('nguoiTao')->findOrFail($id);

        $service->xuLyPheDuyet(
            $phieu,
            Auth::user(),
            $request->input('hanh_dong'),
            $request->input('ghi_chu')
        );

        return back()->with('success', 'Đã xử lý phiếu thành công!');
    }

    /**
     * Ủy quyền hủy phiếu cho Service xử lý
     */
    public function cancel(Request $request, $id, PhieuYeuCauService $service)
    {
        $phieu = PhieuYeuCau::where('id', $id)->where('nguoi_tao_id', Auth::id())->firstOrFail();

        if ($phieu->trang_thai !== TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET) {
            return back()->withErrors(['error' => 'Không thể hủy phiếu đã xử lý!']);
        }

        $service->xuLyHuyPhieu($phieu, Auth::user(), $request->input('ghi_chu'));

        return back()->with('success', 'Đã hủy phiếu yêu cầu!');
    }

    /**
     * In file PDF
     */
    public function print($id)
    {
        $phieu = PhieuYeuCau::with(['chiTiet', 'nguoiTao', 'phongBan', 'nhaCungCap'])->findOrFail($id);
        $pdf = Pdf::loadView('pdf.phieu_yeu_cau', ['phieu' => $phieu]);

        return $pdf->stream('Phieu_'.$phieu->ma_phieu.'.pdf');
    }

    /**
     * Đánh dấu thông báo đã đọc
     */
    public function markNotificationAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $url = isset($notification->data['phieu_id'])
             ? route('phieu.show', $notification->data['phieu_id'])
             : route('dashboard');

        return response()->json(['success' => true, 'url' => $url], 200);
    }
}

<?php

namespace App\Http\Controllers\NghiepVu;

use App\Enums\HanhDong;
use App\Enums\TrangThaiPhieu;
use App\Http\Controllers\Controller;
use App\Models\ChiTietYeuCau;
use App\Models\DanhMuc;
use App\Models\NhatKyDuyet;
use App\Models\PhieuYeuCau;
use App\Models\User;
use App\Models\Setting;
use App\Notifications\PhieuYeuCauNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MuaSamController extends Controller
{
    /**
     * Show the form for creating a new Purchase Request.
     */
    public function create()
    {
        $danhMucs = DanhMuc::select('id', 'ten_danh_muc')->get();

        return Inertia::render('Modules/MuaSam/TaoMoi', ['danhMucs' => $danhMucs]);
    }

    /**
     * Store a newly created Purchase Request in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tieu_de' => 'required|string|max:255',
            'ly_do' => 'nullable|string',
            'san_pham' => 'required|array|min:1',
            'san_pham.*.ten_san_pham' => 'required|string',
            'san_pham.*.danh_muc_id' => 'required|exists:danh_muc,id',
            'san_pham.*.so_luong' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($validated, &$phieu) {
                $phieu = PhieuYeuCau::create([
                    'ma_phieu' => 'PR-'.strtoupper(Str::random(6)),
                    'loai_phieu' => 'mua_sam',
                    'tieu_de' => $validated['tieu_de'],
                    'ly_do' => $validated['ly_do'],
                    'tong_tien' => null,
                    'nguoi_tao_id' => Auth::id(),
                    'phong_ban_id' => Auth::user()->phong_ban_id,
                    'trang_thai' => TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET,
                ]);

                $chiTietData = collect($validated['san_pham'])->map(fn ($sp) => [
                    'phieu_yeu_cau_id' => $phieu->id,
                    'danh_muc_id' => $sp['danh_muc_id'],
                    'ten_san_pham' => $sp['ten_san_pham'],
                    'so_luong' => $sp['so_luong'],
                    'don_gia' => null,
                    'thanh_tien' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->toArray();

                ChiTietYeuCau::insert($chiTietData);

                NhatKyDuyet::create([
                    'phieu_yeu_cau_id' => $phieu->id,
                    'nguoi_thuc_hien_id' => Auth::id(),
                    'hanh_dong' => HanhDong::TAO_MOI,
                    'ghi_chu' => 'Nhân viên tạo yêu cầu mua sắm (Chưa có báo giá)',
                ]);
            });

            // Dispatch notification to Department Manager
            $truongPhong = User::where('phong_ban_id', Auth::user()->phong_ban_id)->where('vai_tro', 'truong_phong')->first();
            if ($truongPhong) {
                $truongPhong->notify(new PhieuYeuCauNotification($phieu, Auth::user()->name.' vừa tạo yêu cầu mua sắm mới.', 'info'));
            }

            return redirect()->route('dashboard')->with('success', 'Đã tạo phiếu yêu cầu thành công!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Lỗi hệ thống: '.$e->getMessage()]);
        }
    }

    /**
     * Update pricing and select supplier by Purchasing Department.
     */
    public function capNhatBaoGia(Request $request, $id)
    {
        $phieu = PhieuYeuCau::findOrFail($id);
        $user = Auth::user();

        if (! $user->isMuaSam() || $phieu->trang_thai !== TrangThaiPhieu::CHO_MUA_SAM_BAO_GIA) {
            abort(403, 'Chỉ phòng Mua sắm mới được thao tác lúc này.');
        }

        $validated = $request->validate([
            'nha_cung_cap_id' => 'required|exists:nha_cung_cap,id',
            'file_bao_gia' => 'nullable|file|mimes:pdf|max:5120',
            'san_pham' => 'required|array|min:1',
            'san_pham.*.id' => 'required|exists:chi_tiet_yeu_cau,id',
            'san_pham.*.don_gia' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated, $phieu, $user, $request) {
                $tongTien = 0;
                foreach ($validated['san_pham'] as $sp) {
                    $chiTiet = ChiTietYeuCau::find($sp['id']);
                    if ($chiTiet->phieu_yeu_cau_id === $phieu->id) {
                        $thanhTien = $chiTiet->so_luong * $sp['don_gia'];
                        $chiTiet->update(['don_gia' => $sp['don_gia'], 'thanh_tien' => $thanhTien]);
                        $tongTien += $thanhTien;
                    }
                }

                $phongBan = $phieu->nguoiTao->phongBan ?? null;
                if ($phongBan && $phongBan->ngan_sach_tong > 0 && $tongTien > $phongBan->ngan_sach_con_lai) {
                    throw new \Exception('VƯỢT NGÂN SÁCH! Tổng báo giá cao hơn ngân sách khả dụng.');
                }

                $filePath = $request->hasFile('file_bao_gia')
                    ? $request->file('file_bao_gia')->store('bao_gia', 'public')
                    : $phieu->file_bao_gia;

                $hanMuc = (int) Setting::get('han_muc_giam_doc_duyet', 20000000);
                $isVuotHanMuc = $tongTien >= $hanMuc;

                $trangThaiTiepTheo = $isVuotHanMuc ? TrangThaiPhieu::CHO_GIAM_DOC_DUYET : TrangThaiPhieu::CHO_THANH_TOAN;

                $phieu->update([
                    'nha_cung_cap_id' => $validated['nha_cung_cap_id'],
                    'file_bao_gia' => $filePath,
                    'tong_tien' => $tongTien,
                    'trang_thai' => $trangThaiTiepTheo,
                ]);

                NhatKyDuyet::create([
                    'phieu_yeu_cau_id' => $phieu->id,
                    'nguoi_thuc_hien_id' => $user->id,
                    'hanh_dong' => HanhDong::CAP_NHAT_BAO_GIA,
                    'ghi_chu' => $isVuotHanMuc ? 'Chốt giá (Vượt hạn mức, chờ Giám đốc)' : 'Chốt giá (Chuyển Kế toán thanh toán)',
                ]);

                // Workflow Routing Notifications
                if ($isVuotHanMuc) {
                    User::where('vai_tro', 'giam_doc')->first()?->notify(new PhieuYeuCauNotification($phieu, 'Phiếu vượt hạn mức cần phê duyệt!', 'warning'));
                } else {
                    User::where('vai_tro', 'ke_toan')->first()?->notify(new PhieuYeuCauNotification($phieu, 'Chốt giá thành công. Vui lòng thanh toán.', 'success'));
                }
                $phieu->nguoiTao?->notify(new PhieuYeuCauNotification($phieu, 'Phòng Mua sắm đã chốt giá đơn hàng của bạn.', 'info'));
            });

            return back()->with('success', 'Đã chốt giá! Hệ thống phân luồng phê duyệt thành công.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Confirm receipt of goods by the original requester.
     */
    public function xacNhanNhanHang(Request $request, $id)
    {
        $phieu = PhieuYeuCau::findOrFail($id);

        if (Auth::id() !== $phieu->nguoi_tao_id || $phieu->trang_thai !== TrangThaiPhieu::DA_THANH_TOAN) {
            abort(403, 'Bạn không có quyền hoặc phiếu chưa đến bước nhận hàng.');
        }

        $request->validate([
            'file_nhan_hang' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'ghi_chu_nhan_hang' => 'nullable|string|max:1000',
        ]);

        try {
            DB::transaction(function () use ($phieu, $request) {
                $path = $request->file('file_nhan_hang')->store('chung_tu_nhan_hang', 'public');

                $phieu->update([
                    'trang_thai' => TrangThaiPhieu::DA_HOAN_TAT,
                    'file_nhan_hang' => $path,
                    'ghi_chu_nhan_hang' => $request->ghi_chu_nhan_hang,
                ]);

                NhatKyDuyet::create([
                    'phieu_yeu_cau_id' => $phieu->id,
                    'nguoi_thuc_hien_id' => Auth::id(),
                    'hanh_dong' => HanhDong::DA_HOAN_TAT ?? HanhDong::NHAN_HANG,
                    'ghi_chu' => 'Người yêu cầu đã nghiệm thu và xác nhận nhận đủ hàng.',
                ]);

                User::where('vai_tro', 'ke_toan')->first()?->notify(new PhieuYeuCauNotification($phieu, 'Người yêu cầu đã nghiệm thu hàng hóa.', 'success'));
                User::where('vai_tro', 'nhan_vien_mua_sam')->first()?->notify(new PhieuYeuCauNotification($phieu, 'Đơn hàng do bạn phụ trách đã được nghiệm thu.', 'success'));
            });

            return back()->with('success', 'Nghiệm thu thành công! Quy trình mua sắm khép kín.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Lỗi hệ thống: '.$e->getMessage()]);
        }
    }
}

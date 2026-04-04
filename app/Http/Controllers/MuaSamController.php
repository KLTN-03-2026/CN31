<?php

namespace App\Http\Controllers;

use App\Models\PhieuYeuCau;
use App\Models\ChiTietYeuCau;
use App\Models\NhatKyDuyet;
use App\Models\DanhMuc;
use App\Models\User;
use App\Enums\TrangThaiPhieu;
use App\Enums\HanhDong;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Notifications\PhieuYeuCauNotification;


class MuaSamController extends Controller
{
    public function create()
    {
        $danhMucs = DanhMuc::select('id', 'ten_danh_muc')->get();
        return Inertia::render('PhieuYeuCau/TaoMoi', ['danhMucs' => $danhMucs]);
    }

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
                    'ma_phieu' => 'PR-' . strtoupper(Str::random(6)),
                    'loai_phieu' => 'mua_sam',
                    'tieu_de' => $validated['tieu_de'],
                    'ly_do' => $validated['ly_do'],
                    'tong_tien' => null,
                    'nguoi_tao_id' => Auth::id(),
                    'phong_ban_id' => Auth::user()->phong_ban_id,
                    'trang_thai' => TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET,
                ]);

                $chiTietData = [];
                foreach ($validated['san_pham'] as $sp) {
                    $chiTietData[] = [
                        'phieu_yeu_cau_id' => $phieu->id,
                        'danh_muc_id' => $sp['danh_muc_id'],
                        'ten_san_pham' => $sp['ten_san_pham'],
                        'so_luong' => $sp['so_luong'],
                        'don_gia' => null,
                        'thanh_tien' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                ChiTietYeuCau::insert($chiTietData);

                NhatKyDuyet::create([
                    'phieu_yeu_cau_id' => $phieu->id,
                    'nguoi_thuc_hien_id' => Auth::id(),
                    'hanh_dong' => HanhDong::TAO_MOI,
                    'ghi_chu' => 'Nhân viên tạo yêu cầu mua sắm (Chưa có báo giá)',
                ]);
            });

            // Gửi thông báo cho Trưởng phòng
            $truongPhong = User::where('phong_ban_id', Auth::user()->phong_ban_id)->where('vai_tro', 'truong_phong')->first();
            if ($truongPhong) {
                $truongPhong->notify(new PhieuYeuCauNotification($phieu, Auth::user()->name . ' vừa tạo yêu cầu mua sắm mới cần bạn duyệt.', 'info'));
            }

            return redirect()->route('dashboard')->with('success', 'Đã tạo phiếu yêu cầu thành công!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }

    // Bộ phận mua sắm cập nhật báo giá và chọn nhà cung cấp
    public function capNhatBaoGia(Request $request, $id)
    {
        $phieu = PhieuYeuCau::findOrFail($id);
        $user = Auth::user();

        if (!$user->isMuaSam() || $phieu->trang_thai !== TrangThaiPhieu::CHO_MUA_SAM_BAO_GIA) {
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

                $phieu->load('nguoiTao.phongBan');
                $phongBan = $phieu->nguoiTao->phongBan;
                if ($phongBan && $phongBan->ngan_sach_tong > 0) {
                    if ($tongTien > $phongBan->ngan_sach_con_lai) {
                        throw new \Exception('VƯỢT NGÂN SÁCH! Tổng báo giá cao hơn ngân sách khả dụng. Vui lòng thương lượng lại giá!');
                    }
                }

                $filePath = $phieu->file_bao_gia;
                if ($request->hasFile('file_bao_gia')) {
                    $filePath = $request->file('file_bao_gia')->store('bao_gia', 'public');
                }

                $isVuotHanMuc = $tongTien >= 20000000;
                $trangThaiTiepTheo = $isVuotHanMuc ? TrangThaiPhieu::CHO_GIAM_DOC_DUYET : TrangThaiPhieu::CHO_THANH_TOAN;

                $phieu->update([
                    'nha_cung_cap_id' => $validated['nha_cung_cap_id'],
                    'file_bao_gia' => $filePath,
                    'tong_tien' => $tongTien,
                    'trang_thai' => $trangThaiTiepTheo,
                ]);

                $ghiChuLog = $isVuotHanMuc ? 'Phòng Mua sắm đã chốt giá (Vượt hạn mức 20tr, đã chuyển Giám đốc duyệt)' : 'Phòng Mua sắm đã chốt giá (Dưới hạn mức 20tr, chuyển Kế toán thanh toán)';
                NhatKyDuyet::create([
                    'phieu_yeu_cau_id' => $phieu->id,
                    'nguoi_thuc_hien_id' => $user->id,
                    'hanh_dong' => HanhDong::CAP_NHAT_BAO_GIA,
                    'ghi_chu' => $ghiChuLog,
                ]);

                // PHÂN LUỒNG THÔNG BÁO TỰ ĐỘNG
                if ($isVuotHanMuc) {
                    // Báo tới Giám Đốc
                    $giamDoc = User::where('vai_tro', 'giam_doc')->first();
                    if ($giamDoc) $giamDoc->notify(new PhieuYeuCauNotification($phieu, 'Phòng Mua sắm vừa trình một phiếu VƯỢT HẠN MỨC (>= 20tr). Cần Sếp phê duyệt!', 'warning'));

                    // Báo ngược cho Nhân viên
                    if ($phieu->nguoiTao) $phieu->nguoiTao->notify(new PhieuYeuCauNotification($phieu, 'Phòng Mua sắm đã chốt giá. Vì vượt 20tr nên đang chờ Giám đốc duyệt.', 'info'));
                } else {
                    // Báo tới Kế Toán
                    $keToan = User::where('vai_tro', 'ke_toan')->first();
                    if ($keToan) $keToan->notify(new PhieuYeuCauNotification($phieu, 'Phòng Mua sắm đã chốt giá (Dưới hạn mức). Vui lòng thực hiện thanh toán.', 'success'));
                    // Báo ngược cho Nhân viên
                    if ($phieu->nguoiTao) $phieu->nguoiTao->notify(new PhieuYeuCauNotification($phieu, 'Phòng Mua sắm đã chốt giá. Kế toán đang chuẩn bị thanh toán cho NCC.', 'success'));
                }
            });

            return back()->with('success', 'Đã chốt giá! Hệ thống tự động phân luồng phê duyệt thành công.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

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

        $path = null;
        if ($request->hasFile('file_nhan_hang')) {
            $path = $request->file('file_nhan_hang')->store('chung_tu_nhan_hang', 'public');
        }

        try {
            DB::transaction(function () use ($phieu, $path, $request) {
                $phieu->update([
                    'trang_thai' => TrangThaiPhieu::DA_HOAN_TAT,
                    'file_nhan_hang' => $path,
                    'ghi_chu_nhan_hang' => $request->ghi_chu_nhan_hang,
                ]);

            NhatKyDuyet::create([
                    'phieu_yeu_cau_id' => $phieu->id,
                    'nguoi_thuc_hien_id' => Auth::id(),
                    'hanh_dong' => \App\Enums\HanhDong::DA_HOAN_TAT ?? \App\Enums\HanhDong::NHAN_HANG,
                    'ghi_chu' => 'Người yêu cầu đã nghiệm thu và xác nhận nhận đủ hàng.',
                ]);
                $keToan = User::where('vai_tro', 'ke_toan')->first();
                if ($keToan) {
                    $keToan->notify(new PhieuYeuCauNotification($phieu, 'Người yêu cầu đã nghiệm thu hàng hóa thành công. Chu trình hoàn tất!', 'success'));
                }

                // Báo cho Mua sắm đóng KPI
                $muaSam = User::where('vai_tro', 'nhan_vien_mua_sam')->first();
                if ($muaSam) {
                    $muaSam->notify(new PhieuYeuCauNotification($phieu, 'Đơn hàng do bạn phụ trách đã được giao và nghiệm thu thành công.', 'success'));
                }
            });

            return back()->with('success', 'Nghiệm thu thành công! Quy trình mua sắm khép kín.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }
}

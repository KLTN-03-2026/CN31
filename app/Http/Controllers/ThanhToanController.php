<?php

namespace App\Http\Controllers;

use App\Models\PhieuYeuCau;
use App\Models\NhatKyDuyet;
use App\Enums\TrangThaiPhieu;
use App\Enums\HanhDong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Notifications\PhieuYeuCauNotification;
use App\Models\User;

class ThanhToanController extends Controller
{
    // 1. HIỂN THỊ MÀN HÌNH QUÉT MÃ QR CHO KẾ TOÁN
    public function showQR($id)
    {
        $phieu = PhieuYeuCau::with('nhaCungCap')->findOrFail($id);

        if (!Auth::user()->isKeToan() || $phieu->trang_thai !== TrangThaiPhieu::CHO_THANH_TOAN) {
            abort(403, 'Bạn không có quyền thanh toán phiếu này.');
        }

        $ncc = $phieu->nhaCungCap;
        if (!$ncc || !$ncc->so_tai_khoan) {
           return redirect()->back()->with('error', 'Không thể thanh toán! Phiếu này chưa có thông tin Nhà cung cấp hoặc Ngân hàng.');
        }

        // Tạo nội dung chuyển khoản: Bắt buộc chứa Mã phiếu
        $noiDung = "THANH TOAN " . str_replace('-', '', $phieu->ma_phieu);

        // Gọi API của VietQR để sinh ảnh QR Code
        $qrUrl = "https://img.vietqr.io/image/{$ncc->ngan_hang}-{$ncc->so_tai_khoan}-compact2.png?amount={$phieu->tong_tien}&addInfo=" . urlencode($noiDung) . "&accountName=" . urlencode($ncc->chu_tai_khoan);

        // BẢO MẬT: Làm phẳng dữ liệu, chỉ truyền đúng những gì Vue cần
        return Inertia::render('PhieuYeuCau/ThanhToanQR', [
            'phieu' => [
                'id' => $phieu->id,
                'ma_phieu' => $phieu->ma_phieu,
                'tong_tien' => $phieu->tong_tien,
            ],
            // Chỉ trả về các trường cần thiết để hiển thị thông tin ngân hàng
            'nhaCungCap' => [
                'ten_nha_cung_cap' => $ncc->ten_nha_cung_cap,
            ],
            'qrUrl' => $qrUrl,
            'noiDungCK' => $noiDung
        ]);
    }

    // 2. MÔ PHỎNG WEBHOOK ĐỐI SOÁT TỰ ĐỘNG
    // (Trong thực tế, API Ngân hàng/Casso sẽ gọi thẳng vào hàm này, chứ người dùng không cần bấm nút)
    public function xacNhanThanhToan(Request $request, $id)
    {
        $phieu = PhieuYeuCau::findOrFail($id);

        // Validate dữ liệu Form kế toán gửi lên
        $request->validate([
            'ma_giao_dich_ngan_hang' => 'required|string|max:100',
            'hinh_anh_minh_chung' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Bắt buộc phải là ảnh, max 2MB
            'ghi_chu' => 'nullable|string'
        ], [
            'ma_giao_dich_ngan_hang.required' => 'Vui lòng nhập mã giao dịch ngân hàng.',
            'hinh_anh_minh_chung.required' => 'Vui lòng tải lên ảnh chụp màn hình chuyển khoản.',
            'hinh_anh_minh_chung.image' => 'File tải lên phải là hình ảnh (jpg, png).',
        ]);

        // Kiểm tra quyền
        if (!Auth::user()->isKeToan() || $phieu->trang_thai !== TrangThaiPhieu::CHO_THANH_TOAN) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }
        $path = null;
        if ($request->hasFile('hinh_anh_minh_chung')) {
            // Lưu ảnh vào thư mục storage/app/public/chung_tu_thanh_toan
            $path = $request->file('hinh_anh_minh_chung')->store('chung_tu_thanh_toan', 'public');
        }

        try {
            DB::transaction(function () use ($phieu, $request, $path) {

                    // CHỐNG TRỪ TIỀN 2 LẦN
                if ($phieu->trang_thai !== TrangThaiPhieu::DA_THANH_TOAN) {
                    // Cập nhật trạng thái phiếu
                    $phieu->update(['trang_thai' => TrangThaiPhieu::DA_THANH_TOAN]);

                    // Gọi Nhân viên ra nhận hàng
                if ($phieu->nguoiTao) {
                    $phieu->nguoiTao->notify(new PhieuYeuCauNotification($phieu, 'Kế toán đã thanh toán cho NCC! Hãy kiểm tra hàng hóa khi được giao và Xác nhận trên hệ thống.', 'warning'));
                }

                // Báo Mua sắm theo dõi vận đơn
                $muaSam = User::where('vai_tro', 'nhan_vien_mua_sam')->first();
                if ($muaSam) {
                    $muaSam->notify(new PhieuYeuCauNotification($phieu, 'Kế toán đã chi tiền. Vui lòng đôn đốc Nhà cung cấp giao hàng đúng hạn.', 'info'));
                }
                    $phieu->load('nguoiTao.phongBan');
                    $phongBan = $phieu->nguoiTao->phongBan ?? null;
                    if ($phongBan && $phongBan->ngan_sach_tong > 0) {
                        // Trạm gác: Check lại lần cuối trước khi ghi đè DB
                        if ($phieu->tong_tien > $phongBan->ngan_sach_con_lai) {
                            throw new \Exception('Ngân sách khả dụng của ' . $phongBan->ten_phong_ban . ' hiện chỉ còn ' . number_format($phongBan->ngan_sach_con_lai) . ' đ. Giao dịch bị hủy bỏ để tránh âm ngân sách!');
                        }

                        // Nếu đủ tiền thì trừ
                        $phongBan->increment('ngan_sach_su_dung', $phieu->tong_tien);
                    }
                    \App\Models\ChungTuThanhToan::create([
                        'phieu_yeu_cau_id' => $phieu->id,
                        'ke_toan_id' => Auth::id(),
                        'phuong_thuc' => 'vietqr',
                        // [FIXED] 2. Sửa lại đúng tên biến đã validate
                        'ma_giao_dich_ngan_hang' => $request->ma_giao_dich_ngan_hang,
                        'so_tien_thanh_toan' => $phieu->tong_tien,
                        'hinh_anh_minh_chung' => $path,
                        'ghi_chu' => $request->ghi_chu
                    ]);

                    // Ghi Audit Log
                    \App\Models\NhatKyDuyet::create([
                        'phieu_yeu_cau_id' => $phieu->id,
                        'nguoi_thuc_hien_id' => Auth::id(),
                        'hanh_dong' => \App\Enums\HanhDong::THANH_TOAN,
                        // [FIXED] 3. Sửa lại đúng tên biến đã validate
                        'ghi_chu' => 'Kế toán đã xác nhận thanh toán thủ công (Mã GD: ' . $request->ma_giao_dich_ngan_hang . ')'
                    ]);
                }
            });

            return redirect()->route('phieu.show', $phieu->id)->with('success', 'Đã lưu chứng từ và cập nhật trạng thái thanh toán thành công!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }
}

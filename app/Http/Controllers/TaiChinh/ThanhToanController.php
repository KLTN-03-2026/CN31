<?php

namespace App\Http\Controllers\TaiChinh;

use App\Enums\TrangThaiPhieu;
use App\Http\Controllers\Controller;
use App\Models\PhieuYeuCau;
use App\Services\ThanhToanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ThanhToanController extends Controller
{
    public function showQR($id)
    {
        $phieu = PhieuYeuCau::with('nhaCungCap')->findOrFail($id);

        if (! Auth::user()->isKeToan() || $phieu->trang_thai !== TrangThaiPhieu::CHO_THANH_TOAN) {
            abort(403, 'Bạn không có quyền thanh toán phiếu này.');
        }

        $ncc = $phieu->nhaCungCap;
        if (! $ncc || ! $ncc->so_tai_khoan) {
            return redirect()->back()->with('error', 'Không thể thanh toán! Nhà cung cấp chưa có thông tin Ngân hàng.');
        }

        $noiDung = 'THANH TOAN '.str_replace('-', '', $phieu->ma_phieu);
        $qrUrl = "https://img.vietqr.io/image/{$ncc->ngan_hang}-{$ncc->so_tai_khoan}-compact2.png?amount={$phieu->tong_tien}&addInfo=".urlencode($noiDung).'&accountName='.urlencode($ncc->chu_tai_khoan);

        return Inertia::render('Modules/MuaSam/ThanhToanQR', [
            'phieu' => $phieu->only(['id', 'ma_phieu', 'tong_tien']),
            'nhaCungCap' => $ncc->only(['ten_nha_cung_cap']),
            'qrUrl' => $qrUrl,
            'noiDungCK' => $noiDung,
        ]);
    }

    public function xacNhanThanhToan(Request $request, $id, ThanhToanService $service)
    {
        $phieu = PhieuYeuCau::findOrFail($id);

        if (! Auth::user()->isKeToan() || $phieu->trang_thai !== TrangThaiPhieu::CHO_THANH_TOAN) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        $validated = $request->validate([
            'ma_giao_dich_ngan_hang' => 'required|string|max:100',
            'hinh_anh_minh_chung' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'ghi_chu' => 'nullable|string',
        ]);

        $path = $request->file('hinh_anh_minh_chung')->store('chung_tu_thanh_toan', 'public');

        try {
            // Giao cho Service xử lý toàn bộ logic trừ ngân sách và lưu DB
            $service->xacNhanThuCong(
                $phieu,
                Auth::user(),
                $validated['ma_giao_dich_ngan_hang'],
                $path,
                $validated['ghi_chu'] ?? null
            );

            return redirect()->route('phieu.show', $phieu->id)->with('success', 'Đã lưu chứng từ và cập nhật trạng thái thanh toán!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Lỗi hệ thống: '.$e->getMessage()]);
        }
    }
}

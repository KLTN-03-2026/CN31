<?php

namespace App\Http\Controllers\TaiChinh;

use App\Enums\TrangThaiPhieu;
use App\Http\Controllers\Controller;
use App\Models\PhieuYeuCau;
use App\Services\ThanhToanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VnpayController extends Controller
{
    public function createPayment($id, ThanhToanService $service)
    {
        $phieu = PhieuYeuCau::findOrFail($id);

        if (! Auth::user()->isKeToan() || $phieu->trang_thai !== TrangThaiPhieu::CHO_THANH_TOAN) {
            return back()->withErrors(['error' => 'Bạn không có quyền hoặc phiếu chưa hợp lệ để thanh toán!']);
        }

        try {
            // Service sẽ kiểm tra ngân sách và tạo URL VNPAY
            $url = $service->taoUrlVnpay($phieu, request()->ip(), route('vnpay.return'));

            return inertia()->location($url);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function vnpayReturn(Request $request, ThanhToanService $service)
    {
        $inputData = [];
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == 'vnp_') {
                $inputData[$key] = $value;
            }
        }
        unset($inputData['vnp_SecureHash']);

        // 1. Kiểm tra chữ ký bảo mật
        if (! $service->xacThucChuKyVnpay($inputData, $request->vnp_SecureHash)) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Chữ ký VNPAY không hợp lệ! Phát hiện nghi vấn bảo mật.']);
        }

        // 2. Tìm phiếu gốc
        $maPhieuGoc = explode('_', $request->vnp_TxnRef)[0];
        $phieu = PhieuYeuCau::where('ma_phieu', $maPhieuGoc)->firstOrFail();

        // 3. Xử lý kết quả trả về
        if ($request->vnp_ResponseCode == '00') {
            $service->ghiNhanVnpayThanhCong($phieu, Auth::user(), $request->all());

            return redirect()->route('phieu.show', $phieu->id)->with('success', 'Thanh toán VNPAY thành công!');
        } else {
            $service->ghiNhanVnpayThatBai($phieu, Auth::user(), $request->all());

            return redirect()->route('phieu.show', $phieu->id)->withErrors(['error' => 'Giao dịch thanh toán đã bị hủy hoặc thất bại!']);
        }
    }
}

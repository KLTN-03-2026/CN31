<?php

namespace App\Http\Controllers;

use App\Models\PhieuYeuCau;
use App\Models\GiaoDichVnpay;
use App\Models\NhatKyDuyet;
use App\Enums\TrangThaiPhieu;
use App\Enums\HanhDong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VnpayController extends Controller
{
    // 1. TẠO URL VÀ ĐẨY SANG VNPAY
    public function createPayment($id)
    {
        $phieu = PhieuYeuCau::findOrFail($id);

        // Bảo mật: Chỉ Kế toán mới được thanh toán và phiếu phải ở trạng thái Chờ Thanh Toán
        if (!Auth::user()->isKeToan() || $phieu->trang_thai !== TrangThaiPhieu::CHO_THANH_TOAN) {
            return back()->withErrors(['error' => 'Bạn không có quyền hoặc phiếu chưa hợp lệ để thanh toán!']);
        }

        $vnp_TmnCode = env('VNPAY_TMN_CODE');
        $vnp_HashSecret = env('VNPAY_HASH_SECRET');
        $vnp_Url = env('VNPAY_URL');
        $vnp_Returnurl = route('vnpay.return'); // Link VNPAY sẽ trả về sau khi thanh toán xong

        $vnp_TxnRef = $phieu->ma_phieu . '_' . time(); // Mã giao dịch (Cộng thêm time để không bị trùng nếu thanh toán lại)
        $vnp_OrderInfo = "Thanh toan phieu mua sam " . $phieu->ma_phieu;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $phieu->tong_tien * 100; // VNPAY yêu cầu số tiền nhân với 100
        $vnp_Locale = 'vn';
        $vnp_IpAddr = request()->ip();

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        // Sắp xếp dữ liệu theo thứ tự a-z trước khi tạo chữ ký (BẮT BUỘC)
        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        // Chuyển hướng trình duyệt sang cổng VNPAY
        return inertia()->location($vnp_Url);
    }

    // 2. NHẬN KẾT QUẢ TỪ VNPAY TRẢ VỀ
    public function vnpayReturn(Request $request)
    {
        $vnp_SecureHash = $request->vnp_SecureHash;
        $inputData = array();
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, env('VNPAY_HASH_SECRET'));

        // Tách lấy mã phiếu gốc (Vì lúc gửi đi ta nối thêm _time())
        $maPhieuGoc = explode('_', $request->vnp_TxnRef)[0];
        $phieu = PhieuYeuCau::where('ma_phieu', $maPhieuGoc)->firstOrFail();

        // Kiểm tra chữ ký hợp lệ
        if ($secureHash == $vnp_SecureHash) {
            if ($request->vnp_ResponseCode == '00') {
                // THANH TOÁN THÀNH CÔNG
                DB::transaction(function () use ($phieu, $request) {
                    $phieu->update(['trang_thai' => TrangThaiPhieu::DA_THANH_TOAN]);

                    GiaoDichVnpay::create([
                        'ma_giao_dich_vnpay' => $request->vnp_TransactionNo,
                        'ke_toan_id' => Auth::id(),
                        'phieu_yeu_cau_id' => $phieu->id,
                        'ma_ngan_hang' => $request->vnp_BankCode,
                        'so_tien_thanh_toan' => $request->vnp_Amount / 100,
                        'thong_tin_don_hang' => $request->vnp_OrderInfo,
                        'ngay_thanh_toan' => now(),
                        'trang_thai_giao_dich' => 'thanh_cong',
                    ]);

                    NhatKyDuyet::create([
                        'phieu_yeu_cau_id' => $phieu->id,
                        'nguoi_thuc_hien_id' => Auth::id(),
                        'hanh_dong' => HanhDong::THANH_TOAN,
                        'ghi_chu' => 'Kế toán đã thanh toán qua VNPAY (Mã GD: ' . $request->vnp_TransactionNo . ')',
                    ]);
                });

                return redirect()->route('phieu.show', $phieu->id)->with('success', 'Thanh toán VNPAY thành công!');
            } else {
                // THANH TOÁN THẤT BẠI / HỦY
                GiaoDichVnpay::create([
                    'ma_giao_dich_vnpay' => $request->vnp_TransactionNo ?? 'Huy_Giao_Dich',
                    'ke_toan_id' => Auth::id(),
                    'phieu_yeu_cau_id' => $phieu->id,
                    'ma_ngan_hang' => $request->vnp_BankCode ?? 'UNKNOWN',
                    'so_tien_thanh_toan' => $phieu->tong_tien,
                    'thong_tin_don_hang' => 'Giao dịch thất bại hoặc bị hủy',
                    'ngay_thanh_toan' => now(),
                    'trang_thai_giao_dich' => 'that_bai',
                ]);
                return redirect()->route('phieu.show', $phieu->id)->withErrors(['error' => 'Giao dịch thanh toán đã bị hủy hoặc thất bại!']);
            }
        } else {
            return redirect()->route('dashboard')->withErrors(['error' => 'Chữ ký VNPAY không hợp lệ! Phát hiện nghi vấn hack.']);
        }
    }
}

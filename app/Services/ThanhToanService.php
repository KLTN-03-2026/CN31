<?php

namespace App\Services;

use App\Enums\HanhDong;
use App\Enums\TrangThaiPhieu;
use App\Models\ChungTuThanhToan;
use App\Models\GiaoDichVnpay;
use App\Models\NhatKyDuyet;
use App\Models\PhieuYeuCau;
use App\Models\User;
use App\Notifications\PhieuYeuCauNotification;
use Illuminate\Support\Facades\DB;

class ThanhToanService
{
    /**
     * 1. Xử lý logic thanh toán thủ công (Quét mã QR / Chuyển khoản ngoài)
     */
    public function xacNhanThuCong(PhieuYeuCau $phieu, User $keToan, string $maGiaoDich, string $pathChungTu, ?string $ghiChu)
    {
        DB::transaction(function () use ($phieu, $keToan, $maGiaoDich, $pathChungTu, $ghiChu) {
            if ($phieu->trang_thai === TrangThaiPhieu::DA_THANH_TOAN) {
                return;
            }

            $this->truNganSach($phieu);
            $phieu->update(['trang_thai' => TrangThaiPhieu::DA_THANH_TOAN]);

            ChungTuThanhToan::create([
                'phieu_yeu_cau_id' => $phieu->id,
                'ke_toan_id' => $keToan->id,
                'phuong_thuc' => 'vietqr',
                'ma_giao_dich_ngan_hang' => $maGiaoDich,
                'so_tien_thanh_toan' => $phieu->tong_tien,
                'hinh_anh_minh_chung' => $pathChungTu,
                'ghi_chu' => $ghiChu,
            ]);

            NhatKyDuyet::create([
                'phieu_yeu_cau_id' => $phieu->id,
                'nguoi_thuc_hien_id' => $keToan->id,
                'hanh_dong' => HanhDong::THANH_TOAN,
                'ghi_chu' => 'Kế toán xác nhận thanh toán thủ công (Mã GD: '.$maGiaoDich.')',
            ]);

            $phieu->nguoiTao?->notify(new PhieuYeuCauNotification($phieu, 'Kế toán đã thanh toán cho NCC! Hãy kiểm tra hàng hóa khi được giao.', 'warning'));
            User::where('vai_tro', 'nhan_vien_mua_sam')->first()?->notify(new PhieuYeuCauNotification($phieu, 'Kế toán đã chi tiền. Vui lòng đôn đốc Nhà cung cấp giao hàng.', 'info'));
        });
    }

    /**
     * 2. Xây dựng URL đẩy sang cổng thanh toán VNPAY
     */
    public function taoUrlVnpay(PhieuYeuCau $phieu, string $ipAddr, string $returnUrl): string
    {
        $this->kiemTraNganSach($phieu);

        $vnp_TmnCode = env('VNPAY_TMN_CODE');
        $vnp_HashSecret = env('VNPAY_HASH_SECRET');
        $vnp_Url = env('VNPAY_URL');

        $inputData = [
            'vnp_Version' => '2.1.0',
            'vnp_TmnCode' => $vnp_TmnCode,
            'vnp_Amount' => $phieu->tong_tien * 100,
            'vnp_Command' => 'pay',
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => $ipAddr,
            'vnp_Locale' => 'vn',
            'vnp_OrderInfo' => 'Thanh toan phieu mua sam '.$phieu->ma_phieu,
            'vnp_OrderType' => 'billpayment',
            'vnp_ReturnUrl' => $returnUrl,
            'vnp_TxnRef' => $phieu->ma_phieu.'_'.time(),
        ];

        ksort($inputData);
        $query = '';
        $i = 0;
        $hashdata = '';
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&'.urlencode($key).'='.urlencode($value);
            } else {
                $hashdata .= urlencode($key).'='.urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key).'='.urlencode($value).'&';
        }

        $vnp_Url = $vnp_Url.'?'.$query;
        if (isset($vnp_HashSecret)) {
            $vnp_Url .= 'vnp_SecureHash='.hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        }

        return $vnp_Url;
    }

    /**
     * 3. Xác thực chữ ký điện tử từ VNPAY trả về
     */
    public function xacThucChuKyVnpay(array $inputData, string $vnp_SecureHash): bool
    {
        ksort($inputData);
        $i = 0;
        $hashData = '';
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&'.urlencode($key).'='.urlencode($value);
            } else {
                $hashData .= urlencode($key).'='.urlencode($value);
                $i = 1;
            }
        }

        return hash_hmac('sha512', $hashData, env('VNPAY_HASH_SECRET')) === $vnp_SecureHash;
    }

    /**
     * 4. Ghi nhận Database khi VNPAY báo THÀNH CÔNG
     */
    public function ghiNhanVnpayThanhCong(PhieuYeuCau $phieu, User $keToan, array $vnpayData)
    {
        DB::transaction(function () use ($phieu, $keToan, $vnpayData) {
            if ($phieu->trang_thai === TrangThaiPhieu::DA_THANH_TOAN) {
                return;
            }

            $this->truNganSach($phieu);
            $phieu->update(['trang_thai' => TrangThaiPhieu::DA_THANH_TOAN]);

            GiaoDichVnpay::create([
                'ma_giao_dich_vnpay' => $vnpayData['vnp_TransactionNo'],
                'ke_toan_id' => $keToan->id,
                'phieu_yeu_cau_id' => $phieu->id,
                'ma_ngan_hang' => $vnpayData['vnp_BankCode'],
                'so_tien_thanh_toan' => $vnpayData['vnp_Amount'] / 100,
                'thong_tin_don_hang' => $vnpayData['vnp_OrderInfo'],
                'ngay_thanh_toan' => now(),
                'trang_thai_giao_dich' => 'thanh_cong',
            ]);

            NhatKyDuyet::create([
                'phieu_yeu_cau_id' => $phieu->id,
                'nguoi_thuc_hien_id' => $keToan->id,
                'hanh_dong' => HanhDong::THANH_TOAN,
                'ghi_chu' => 'Kế toán đã thanh toán qua VNPAY (Mã GD: '.$vnpayData['vnp_TransactionNo'].')',
            ]);
        });
    }

    /**
     * 5. Ghi nhận Database khi VNPAY báo THẤT BẠI/HỦY
     */
    public function ghiNhanVnpayThatBai(PhieuYeuCau $phieu, User $keToan, array $vnpayData)
    {
        $maGD = (! empty($vnpayData['vnp_TransactionNo']) && $vnpayData['vnp_TransactionNo'] !== '0')
            ? $vnpayData['vnp_TransactionNo']
            : 'CANCEL_'.time().'_'.$phieu->id;

        GiaoDichVnpay::create([
            'ma_giao_dich_vnpay' => $maGD,
            'ke_toan_id' => $keToan->id,
            'phieu_yeu_cau_id' => $phieu->id,
            'ma_ngan_hang' => $vnpayData['vnp_BankCode'] ?? 'UNKNOWN',
            'so_tien_thanh_toan' => $phieu->tong_tien,
            'thong_tin_don_hang' => 'Giao dịch thất bại hoặc bị hủy',
            'ngay_thanh_toan' => now(),
            'trang_thai_giao_dich' => 'that_bai',
        ]);
    }

    // ==========================================
    // CÁC HÀM PRIVATE DÙNG CHUNG TRONG SERVICE
    // ==========================================

    private function kiemTraNganSach(PhieuYeuCau $phieu)
    {
        $phieu->load('nguoiTao.phongBan');
        $phongBan = $phieu->nguoiTao->phongBan ?? null;
        if ($phongBan && $phongBan->ngan_sach_tong > 0 && $phieu->tong_tien > $phongBan->ngan_sach_con_lai) {
            throw new \Exception('GIAO DỊCH BỊ CHẶN: Ngân sách khả dụng không đủ để thanh toán phiếu này!');
        }
    }

    private function truNganSach(PhieuYeuCau $phieu)
    {
        $phieu->load('nguoiTao.phongBan');

        $phongBan = $phieu->nguoiTao->phongBan ?? null;
        if ($phongBan && $phongBan->ngan_sach_tong > 0) {
            if ($phieu->tong_tien > $phongBan->ngan_sach_con_lai) {
                throw new \Exception('Ngân sách khả dụng không đủ để thực hiện giao dịch này!');
            }
            $phongBan->increment('ngan_sach_su_dung', $phieu->tong_tien);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\PhieuYeuCau;
use App\Models\PhongBan;
use App\Enums\TrangThaiPhieu;
use App\Enums\VaiTro;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AccountantController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // hỉ Kế toán mới được vào đây
        if ($user->vai_tro !== VaiTro::KE_TOAN) {
            abort(403, 'Bạn không có quyền truy cập Sổ quỹ Kế toán.');
        }

        // LẤY NĂM TỪ URL (Mặc định là năm hiện tại)
        $selectedYear = $request->input('year', now()->year);

        // Nợ cần chi luôn là Real-time (Không phụ thuộc năm)
        $queryChoThanhToan = PhieuYeuCau::with('nguoiTao')
            ->where('trang_thai', TrangThaiPhieu::CHO_THANH_TOAN->value);

        $stats = [
            'cho_thanh_toan' => (clone $queryChoThanhToan)->count(),
            'tong_tien_cho_chi' => (clone $queryChoThanhToan)->sum('tong_tien'),
        ];

        $danhSachChoThanhToan = $queryChoThanhToan->latest('updated_at')->paginate(4)->through(function ($phieu) {
            return [
                'id'               => $phieu->id,
                'ma_phieu'         => $phieu->ma_phieu,
                'tieu_de'          => $phieu->tieu_de,
                'nguoi_tao'        => $phieu->nguoiTao->name,
                'tong_tien'        => number_format($phieu->tong_tien, 0, ',', '.') . ' VNĐ',
                'trang_thai_label' => $phieu->trang_thai->label(),
                'trang_thai_color' => $phieu->trang_thai->color(),
                'ngay_tao'         => $phieu->created_at->diffForHumans(),
            ];
        });

        // Xử lý dữ liệu Biểu đồ tài chính 12 tháng THEO NĂM ĐÃ CHỌN
        $thucChiTheoThang = array_fill(1, 12, 0);
        $phieuDaChi = PhieuYeuCau::whereIn('trang_thai', [
                TrangThaiPhieu::DA_THANH_TOAN->value,
                TrangThaiPhieu::DA_HOAN_TAT->value
            ])
            ->whereYear('updated_at', $selectedYear)
            ->selectRaw('MONTH(updated_at) as thang, SUM(tong_tien) as tong')
            ->groupBy('thang')
            ->pluck('tong', 'thang');

        foreach ($phieuDaChi as $thang => $tong) {
            $thucChiTheoThang[$thang] = (float) $tong;
        }

        $tongNganSach = PhongBan::sum('ngan_sach_tong');
        $nganSachTrungBinhThang = $tongNganSach > 0 ? ($tongNganSach / 12) : 50000000;
        $nganSachTheoThang = array_fill(1, 12, $nganSachTrungBinhThang);

        $chartData = [
            'labels' => ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
            'thucChi' => array_values($thucChiTheoThang),
            'nganSach' => array_values($nganSachTheoThang),
            'selectedYear' => (int) $selectedYear,
        ];

        return Inertia::render('Accountant/Index', [
            'roleData' => [
                'stats' => $stats,
                'danhSachChoThanhToan' => $danhSachChoThanhToan,
                'chartData' => $chartData,
            ]
        ]);
    }
}

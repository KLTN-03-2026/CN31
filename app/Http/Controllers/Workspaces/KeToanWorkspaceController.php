<?php

namespace App\Http\Controllers\Workspaces;

use App\Enums\TrangThaiPhieu;
use App\Enums\VaiTro;
use App\Exports\PhieuThanhToanExport;
use App\Http\Controllers\Controller;
use App\Models\PhieuYeuCau;
use App\Models\PhongBan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class KeToanWorkspaceController extends Controller
{
    /**
     * Display the Accountant Workspace.
     * Handles financial overview, pending payments, and annual statistics.
     */
    public function index(Request $request)
    {
        // $user = Auth::user();
        $selectedYear = $request->input('year', now()->year);

        // Core Query: Pending Payments (Real-time, ignores year filter)
        $queryChoThanhToan = PhieuYeuCau::with('nguoiTao')
            ->where('trang_thai', [
                TrangThaiPhieu::CHO_THANH_TOAN->value,
            ]);

        $tongTienChoChi = (clone $queryChoThanhToan)->sum('tong_tien');

        $danhSachChoThanhToan = $queryChoThanhToan->latest('updated_at')
            ->paginate(4)
            ->withQueryString()
            ->through(fn ($phieu) => [
                'id' => $phieu->id,
                'ma_phieu' => $phieu->ma_phieu,
                'tieu_de' => $phieu->tieu_de,
                'nguoi_tao' => $phieu->nguoiTao->name,
                'tong_tien' => number_format($phieu->tong_tien, 0, ',', '.').' VNĐ',
                'trang_thai_label' => $phieu->trang_thai->label(),
                'trang_thai_color' => $phieu->trang_thai->color(),
                'ngay_tao' => $phieu->created_at->locale('vi')->diffForHumans(),
            ]);

        // Aggregate: Monthly Expenditure for the selected year
        $thucChiTheoThang = array_fill(1, 12, 0);
        $phieuDaChi = PhieuYeuCau::whereIn('trang_thai', [
            TrangThaiPhieu::DA_THANH_TOAN->value,
            TrangThaiPhieu::DA_HOAN_TAT->value,
        ])
            ->whereYear('updated_at', $selectedYear)
            ->selectRaw('MONTH(updated_at) as thang, SUM(tong_tien) as tong')
            ->groupBy('thang')
            ->pluck('tong', 'thang');

        foreach ($phieuDaChi as $thang => $tong) {
            $thucChiTheoThang[$thang] = (float) $tong;
        }

        // Aggregate: Department Budgets
        $tongNganSach = PhongBan::sum('ngan_sach_tong');
        $nganSachTrungBinhThang = $tongNganSach > 0 ? ($tongNganSach / 12) : 50000000;
        $nganSachTheoThang = array_fill(1, 12, $nganSachTrungBinhThang);

        return Inertia::render('Portals/Accountant/Index', [
            'roleData' => [
                'stats' => [
                    'cho_thanh_toan' => $danhSachChoThanhToan->total(),
                    'tong_tien_cho_chi' => (float) $tongTienChoChi,
                ],
                'danhSachChoThanhToan' => $danhSachChoThanhToan,
                'chartData' => [
                    'labels' => ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                    'thucChi' => array_values($thucChiTheoThang),
                    'nganSach' => array_values($nganSachTheoThang),
                    'selectedYear' => (int) $selectedYear,
                ],
            ],
        ]);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $user = Auth::user();

        if ($user->vai_tro !== VaiTro::KE_TOAN) {
            abort(403, 'Bạn không có quyền xuất báo cáo kế toán.');
        }

        $selectedYear = $request->integer('year');
        $fileName = $selectedYear
            ? "bao_cao_thanh_toan_{$selectedYear}.xlsx"
            : 'bao_cao_thanh_toan.xlsx';

        return Excel::download(new PhieuThanhToanExport($selectedYear), $fileName);
    }
}

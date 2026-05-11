<?php

namespace App\Http\Controllers\Workspaces;

use App\Enums\TrangThaiPhieu;
use App\Http\Controllers\Controller;
use App\Models\PhieuYeuCau;
use App\Models\PhongBan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class GiamDocWorkspaceController extends Controller
{
    /**
     * Display the Director Workspace.
     * Handles company-wide budget overview, high-level approvals, and financial radar.
     */
    public function index(Request $request)
    {
        // $user = Auth::user();

        // Core Query: Pending High-Level Approvals
        $queryDuyet = PhieuYeuCau::with(['nguoiTao', 'phongBan'])
            ->where('trang_thai', TrangThaiPhieu::CHO_GIAM_DOC_DUYET->value);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $queryDuyet->where(function ($q) use ($search) {
                $q->where('ma_phieu', 'like', "%{$search}%")
                    ->orWhere('tieu_de', 'like', "%{$search}%");
            });
        }

        $danhSachChoDuyet = $queryDuyet->latest('updated_at')
            ->paginate(3, ['*'], 'approvals_page')
            ->onEachSide(1)
            ->withQueryString()
            ->through(fn ($phieu) => [
                'id' => $phieu->id,
                'ma_phieu' => $phieu->ma_phieu,
                'tieu_de' => $phieu->tieu_de,
                'nguoi_tao' => $phieu->nguoiTao->name,
                'ten_phong_ban' => $phieu->phongBan->ten_phong_ban ?? 'Không xác định',
                'tong_tien' => number_format($phieu->tong_tien, 0, ',', '.').' ₫',
                'trang_thai_label' => $phieu->trang_thai->label(),
                'trang_thai_color' => $phieu->trang_thai->color(),
                'ngay_tao' => $phieu->created_at->locale('vi')->diffForHumans(),
            ]);

        // Aggregate: Monthly Expenditure across all departments
        $selectedYear = $request->input('year', now()->year);
        $thucChiTheoThang = array_fill(1, 12, 0);

        $phieuDaChi = PhieuYeuCau::whereIn('trang_thai', [TrangThaiPhieu::DA_THANH_TOAN->value, TrangThaiPhieu::DA_HOAN_TAT->value])
            ->whereYear('updated_at', $selectedYear)
            ->selectRaw('MONTH(updated_at) as thang, SUM(tong_tien) as tong')
            ->groupBy('thang')
            ->pluck('tong', 'thang');

        foreach ($phieuDaChi as $thang => $tong) {
            $thucChiTheoThang[$thang] = (float) $tong;
        }

        $nganSachTong = (float) PhongBan::sum('ngan_sach_tong');
        $daChi = (float) PhongBan::sum('ngan_sach_su_dung');
        $nganSachTrungBinhThang = $nganSachTong > 0 ? ($nganSachTong / 12) : 0;

        // Radar Chart Data: Department Financial Health
        $radarNganSach = PhongBan::all()->map(fn ($phong) => [
            'id' => $phong->id,
            'ten_phong_ban' => $phong->ten_phong_ban,
            'ngan_sach_tong' => (float) $phong->ngan_sach_tong,
            'ngan_sach_su_dung' => (float) $phong->ngan_sach_su_dung,
            'ngan_sach_con_lai' => (float) $phong->ngan_sach_con_lai,
            'phan_tram_su_dung' => (float) $phong->phan_tram_su_dung,
        ])->sortByDesc('phan_tram_su_dung')->values();

        return Inertia::render('Portals/Director/Index', [
            'roleData' => [
                'stats' => [
                    'ngan_sach_tong' => $nganSachTong,
                    'da_chi' => $daChi,
                    'con_lai' => $nganSachTong - $daChi,
                    'phan_tram' => $nganSachTong > 0 ? round(($daChi / $nganSachTong) * 100, 1) : 0,
                    'so_luong_cho' => $danhSachChoDuyet->total(),
                ],
                'danhSachChoDuyet' => $danhSachChoDuyet,
                'chartData' => [
                    'bar' => [
                        'labels' => ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                        'thucChi' => array_values($thucChiTheoThang),
                        // ĐÃ FIX: Sử dụng array_fill từ index 0 để đảm bảo là mảng tuần tự chuẩn JSON
                        'nganSach' => array_fill(0, 12, $nganSachTrungBinhThang),
                    ],
                    'selectedYear' => (int) $selectedYear,
                ],
                'radarNganSach' => $radarNganSach,
                'filters' => $request->only(['search', 'tab', 'year']),
            ],
        ]);
    }
}

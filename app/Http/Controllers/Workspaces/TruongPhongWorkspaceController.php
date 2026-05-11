<?php

namespace App\Http\Controllers\Workspaces;

use App\Enums\TrangThaiPhieu;
use App\Http\Controllers\Controller;
use App\Models\PhieuYeuCau;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TruongPhongWorkspaceController extends Controller
{
    /**
     * Display the Department Manager Workspace.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $phongBanId = $user->phong_ban_id;
        $phongBan = $user->phongBan;

        // ==========================================
        // KHỐI 1: DANH SÁCH PHIẾU CHỜ DUYỆT
        // ==========================================
        // ĐÃ TỐI ƯU: Chỉ load id và name của bảng users để tiết kiệm RAM
        $queryDuyet = PhieuYeuCau::with('nguoiTao:id,name')
            ->where('phong_ban_id', $phongBanId)
            ->where('trang_thai', TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET->value);

        // Bắt sự kiện Global Search từ TopNav gửi xuống
        if ($request->filled('search')) {
            $search = $request->input('search');
            $queryDuyet->where(function ($q) use ($search) {
                $q->where('ma_phieu', 'like', "%{$search}%")
                  ->orWhere('tieu_de', 'like', "%{$search}%");
            });
        }

        $conLai = (float) ($phongBan?->ngan_sach_con_lai ?? 0);

        $danhSachChoDuyet = $queryDuyet->latest('updated_at')
            ->paginate(4, ['*'], 'approvals_page')
            ->onEachSide(1)
            ->withQueryString()
            ->through(fn ($phieu) => [
                'id'               => $phieu->id,
                'ma_phieu'         => $phieu->ma_phieu,
                'tieu_de'          => $phieu->tieu_de,
                'nguoi_tao'        => $phieu->nguoiTao->name,
                'trang_thai_label' => $phieu->trang_thai->label(),
                'trang_thai_color' => $phieu->trang_thai->color(),
                'ngay_tao'         => $phieu->created_at->locale('vi')->diffForHumans(),
                'is_over_budget'   => $phieu->tong_tien > $conLai,
            ]);

        // ==========================================
        // KHỐI 2: THỐNG KÊ BIỂU ĐỒ (CHART.JS)
        // ==========================================
        $selectedYear = $request->input('year', now()->year);
        $thucChiTheoThang = array_fill(1, 12, 0);

        $phieuDaChi = PhieuYeuCau::where('phong_ban_id', $phongBanId)
            ->whereIn('trang_thai', [TrangThaiPhieu::DA_THANH_TOAN->value, TrangThaiPhieu::DA_HOAN_TAT->value])
            ->whereYear('updated_at', $selectedYear)
            ->selectRaw('MONTH(updated_at) as thang, SUM(tong_tien) as tong')
            ->groupBy('thang')
            ->pluck('tong', 'thang');

        foreach ($phieuDaChi as $thang => $tong) {
            $thucChiTheoThang[$thang] = (float) $tong;
        }

        $nganSachTong = (float) ($phongBan?->ngan_sach_tong ?? 0);
        $nganSachTrungBinhThang = $nganSachTong > 0 ? ($nganSachTong / 12) : 0;

        // ==========================================
        // KHỐI 3: QUẢN LÝ ĐỘI NGŨ (TEAM)
        // ==========================================
        $queryNhanSu = User::where('phong_ban_id', $phongBanId)->where('id', '!=', $user->id);

        if ($request->filled('search_team')) {
            $searchTeam = $request->input('search_team');
            $queryNhanSu->where('name', 'like', "%{$searchTeam}%");
        }

        $danhSachNhanSu = $queryNhanSu->paginate(9, ['*'], 'team_page')
            ->onEachSide(1)
            ->withQueryString()
            ->through(fn ($nv) => [
                'id'                => $nv->id,
                'name'              => $nv->name,
                'vai_tro_label'     => $nv->vai_tro->label() ?? 'Nhân viên',
                'tong_ngay_phep'    => $nv->tong_ngay_phep ?? 12,
                'ngay_phep_da_dung' => $nv->ngay_phep_da_dung ?? 0,
                'ngay_phep_con_lai' => $nv->ngay_phep_con_lai ?? 12,
            ]);

        return Inertia::render('Portals/Manager/Index', [
            'roleData' => [
                'stats' => [
                    'ngan_sach_tong' => $nganSachTong,
                    'da_chi'         => (float) ($phongBan?->ngan_sach_su_dung ?? 0),
                    'con_lai'        => $conLai,
                    'phan_tram'      => (float) ($phongBan?->phan_tram_su_dung ?? 0),
                    'so_luong_cho'   => $danhSachChoDuyet->total(),
                ],
                'danhSachChoDuyet' => $danhSachChoDuyet,
                'chartData' => [
                    'bar' => [
                        'labels'   => ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                        'thucChi'  => array_values($thucChiTheoThang),
                        'nganSach' => array_fill(0, 12, $nganSachTrungBinhThang), // Vẫn giữ index 0 để fix lỗi giãn biểu đồ
                    ],
                    'selectedYear' => (int) $selectedYear,
                ],
                'danhSachNhanSu' => $danhSachNhanSu,
                'lichVangMat'    => [['ngay' => date('Y-m-15'), 'ten' => 'Nhân sự nghỉ test']],
                'filters'        => $request->only(['search', 'search_team', 'tab', 'year']),
            ],
        ]);
    }
}

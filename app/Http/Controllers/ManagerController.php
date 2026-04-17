<?php

namespace App\Http\Controllers;

use App\Models\PhieuYeuCau;
use App\Models\User;
use App\Enums\TrangThaiPhieu;
use App\Enums\VaiTro;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ManagerController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->isTruongPhong()) {
            abort(403, 'Bạn không có quyền truy cập khu vực quản lý.');
        }

        $phongBanId = $user->phong_ban_id;

        // DỮ LIỆU NGÂN SÁCH
        $nganSachTong = (float) ($user->phongBan?->ngan_sach_tong ?? 0);
        $daChi        = (float) ($user->phongBan?->ngan_sach_su_dung ?? 0);
        $conLai       = (float) ($user->phongBan?->ngan_sach_con_lai ?? 0);
        $phanTramDung = (float) ($user->phongBan?->phan_tram_su_dung ?? 0);

        // TAB: WORKSPACE - KHỐI DANH SÁCH CHỜ DUYỆT
        $queryDuyet = PhieuYeuCau::with('nguoiTao')
            ->where('phong_ban_id', $phongBanId)
            ->where('trang_thai', TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET->value);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $queryDuyet->where(function($q) use ($search) {
                $q->where('ma_phieu', 'like', "%{$search}%")
                  ->orWhere('tieu_de', 'like', "%{$search}%");
            });
        }

        $danhSachChoDuyet = $queryDuyet->latest('updated_at')
            ->paginate(5, ['*'], 'approvals_page')
            ->onEachSide(1)
            ->withQueryString()
            ->through(fn($phieu) => [
                'id'               => $phieu->id,
                'ma_phieu'         => $phieu->ma_phieu,
                'tieu_de'          => $phieu->tieu_de,
                'nguoi_tao'        => $phieu->nguoiTao->name,
                // THÊM: Trạng thái để hiển thị Badge thay cho 0đ
                'trang_thai_label' => $phieu->trang_thai->label(),
                'trang_thai_color' => $phieu->trang_thai->color(),
                'ngay_tao'         => $phieu->created_at->diffForHumans(),
                'is_over_budget'   => $phieu->tong_tien > $conLai
            ]);

        // TAB: WORKSPACE - KHỐI BIỂU ĐỒ (CÓ YEAR)
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

        $nganSachTrungBinhThang = $nganSachTong > 0 ? ($nganSachTong / 12) : 0;
        $nganSachTheoThang = array_fill(1, 12, $nganSachTrungBinhThang);

        $chartData = [
            'bar' => [
                'labels'       => ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                'thucChi'      => array_values($thucChiTheoThang),
                'nganSach'     => array_values($nganSachTheoThang),
            ],
            'selectedYear' => (int) $selectedYear,
        ];

        // TAB: TEAM - DANH SÁCH NHÂN SỰ & LỊCH
        $queryNhanSu = User::where('phong_ban_id', $phongBanId)->where('id', '!=', $user->id);

        if ($request->filled('search_team')) {
            $searchTeam = $request->input('search_team');
            $queryNhanSu->where('name', 'like', "%{$searchTeam}%");
        }

        $danhSachNhanSu = $queryNhanSu->paginate(9, ['*'], 'team_page')
            ->onEachSide(1)
            ->withQueryString()
            ->through(fn($nv) => [
                'id'                 => $nv->id,
                'name'               => $nv->name,
                'vai_tro_label'      => $nv->vai_tro->label() ?? 'Nhân viên',
                'tong_ngay_phep'     => $nv->tong_ngay_phep ?? 12,
                'ngay_phep_da_dung'  => $nv->ngay_phep_da_dung ?? 0,
                'ngay_phep_con_lai'  => $nv->ngay_phep_con_lai ?? 12,
            ]);

        $lichVangMat = [['ngay' => date('Y-m-15'), 'ten' => 'Nhân sự nghỉ test']];

        return Inertia::render('Manager/Index', [
            'roleData' => [
                'stats' => [
                    'ngan_sach_tong' => $nganSachTong,
                    'da_chi'         => $daChi,
                    'con_lai'        => $conLai,
                    'phan_tram'      => $phanTramDung,
                    'so_luong_cho'   => $danhSachChoDuyet->total(),
                ],
                'danhSachChoDuyet' => $danhSachChoDuyet,
                'chartData'        => $chartData,
                'danhSachNhanSu'   => $danhSachNhanSu,
                'lichVangMat'      => $lichVangMat,
                'filters'          => $request->only(['search', 'search_team', 'tab', 'year'])
            ]
        ]);
    }
}

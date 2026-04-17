<?php

namespace App\Http\Controllers;

use App\Models\PhieuYeuCau;
use App\Models\PhongBan;
use App\Enums\TrangThaiPhieu;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DirectorController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. BẢO MẬT: Chỉ Giám Đốc mới được truy cập
        if (!$user->isGiamDoc()) {
            abort(403, 'Khu vực dành riêng cho Ban Giám Đốc.');
        }

        // ==========================================
        // DỮ LIỆU NGÂN SÁCH TOÀN CÔNG TY
        // ==========================================
        $nganSachTong = (float) PhongBan::sum('ngan_sach_tong');
        $daChi        = (float) PhongBan::sum('ngan_sach_su_dung');
        $conLai       = $nganSachTong - $daChi;
        $phanTramDung = $nganSachTong > 0 ? round(($daChi / $nganSachTong) * 100, 1) : 0;

        // ==========================================
        // TAB 1: DANH SÁCH CHỜ DUYỆT CẤP CAO
        // ==========================================
        $queryDuyet = PhieuYeuCau::with(['nguoiTao', 'phongBan']) // Lấy thêm phòng ban
            ->where('trang_thai', TrangThaiPhieu::CHO_GIAM_DOC_DUYET->value);

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
                'ten_phong_ban'    => $phieu->phongBan->ten_phong_ban ?? 'Không xác định',
                'tong_tien'        => number_format($phieu->tong_tien, 0, ',', '.') . ' ₫',
                'trang_thai_label' => $phieu->trang_thai->label(),
                'trang_thai_color' => $phieu->trang_thai->color(),
                'ngay_tao'         => $phieu->created_at->diffForHumans(),
            ]);

        // ==========================================
        // TAB 1: BIỂU ĐỒ CHI TIÊU TOÀN CÔNG TY
        // ==========================================
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

        $nganSachTrungBinhThang = $nganSachTong > 0 ? ($nganSachTong / 12) : 0;
        $nganSachTheoThang = array_fill(1, 12, $nganSachTrungBinhThang);

        $chartData = [
            'bar' => [
                'labels'   => ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                'thucChi'  => array_values($thucChiTheoThang),
                'nganSach' => array_values($nganSachTheoThang),
            ],
            'selectedYear' => (int) $selectedYear,
        ];

        // ==========================================
        // TAB 2: RADAR NGÂN SÁCH (SỨC KHỎE TÀI CHÍNH CÁC PHÒNG)
        // ==========================================
        $radarNganSach = PhongBan::all()->map(function($phong) {
            return [
                'id'                => $phong->id,
                'ten_phong_ban'     => $phong->ten_phong_ban,
                'ngan_sach_tong'    => (float) $phong->ngan_sach_tong,
                'ngan_sach_su_dung' => (float) $phong->ngan_sach_su_dung,
                'ngan_sach_con_lai' => (float) $phong->ngan_sach_con_lai,
                'phan_tram_su_dung' => (float) $phong->phan_tram_su_dung,
            ];
        })->sortByDesc('phan_tram_su_dung')->values(); // Xếp phòng tiêu nhiều nhất lên đầu

        return Inertia::render('Director/Index', [
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
                'radarNganSach'    => $radarNganSach,
                'filters'          => $request->only(['search', 'tab', 'year'])
            ]
        ]);
    }
}

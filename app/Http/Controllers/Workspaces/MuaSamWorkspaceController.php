<?php

namespace App\Http\Controllers\Workspaces;

use App\Enums\TrangThaiPhieu;
use App\Enums\VaiTro;
use App\Http\Controllers\Controller;
use App\Models\NhaCungCap;
use App\Models\PhieuYeuCau;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MuaSamWorkspaceController extends Controller
{
    /**
     * Display the Purchasing Department Workspace.
     * Handles quotation requests, order tracking, and supplier statistics.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Core Query 1: Requests Pending Quotation
        $queryChoBaoGia = PhieuYeuCau::with('nguoiTao')
            ->where('trang_thai', TrangThaiPhieu::CHO_MUA_SAM_BAO_GIA->value);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $queryChoBaoGia->where(function ($q) use ($search) {
                $q->where('ma_phieu', 'like', "%{$search}%")
                    ->orWhere('tieu_de', 'like', "%{$search}%");
            });
        }

        $danhSachChoBaoGia = $queryChoBaoGia->latest('updated_at')
            ->paginate(4, ['*'], 'cho_bao_gia_page')
            ->onEachSide(1)
            ->withQueryString()
            ->through(fn ($phieu) => [
                'id' => $phieu->id,
                'ma_phieu' => $phieu->ma_phieu,
                'tieu_de' => $phieu->tieu_de,
                'nguoi_tao' => $phieu->nguoiTao->name,
                'trang_thai_label' => $phieu->trang_thai->label(),
                'trang_thai_color' => $phieu->trang_thai->color(),
                'ngay_tao' => $phieu->created_at->locale('vi')->diffForHumans(),
            ]);

        // Core Query 2: Monitored Orders (Processing/Completed)
        $queryTheoDoi = PhieuYeuCau::with('nguoiTao')
            ->whereNotIn('trang_thai', [
                TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET->value,
                TrangThaiPhieu::CHO_MUA_SAM_BAO_GIA->value,
                TrangThaiPhieu::CHO_NHAN_SU_DUYET->value,
                TrangThaiPhieu::TU_CHOI->value,
                TrangThaiPhieu::DA_HUY->value,
                TrangThaiPhieu::NHAN_SU_DUYET->value,
            ]);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $queryTheoDoi->where(function ($q) use ($search) {
                $q->where('ma_phieu', 'like', "%{$search}%")
                    ->orWhere('tieu_de', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $queryTheoDoi->where('trang_thai', $request->input('status'));
        }

        $danhSachTheoDoi = $queryTheoDoi->latest('updated_at')
            ->paginate(4, ['*'], 'theo_doi_page')
            ->onEachSide(1)
            ->withQueryString()
            ->through(fn ($phieu) => [
                'id' => $phieu->id,
                'ma_phieu' => $phieu->ma_phieu,
                'tieu_de' => $phieu->tieu_de,
                'nguoi_tao' => $phieu->nguoiTao->name,
                'trang_thai_label' => $phieu->trang_thai->label(),
                'trang_thai_color' => $phieu->trang_thai->color(),
                 'ngay_tao' => $phieu->updated_at->locale('vi')->diffForHumans(),
            ]);

        // Statistics
        $daXuLyThangCount = PhieuYeuCau::whereNotIn('trang_thai', [
            TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET->value,
            TrangThaiPhieu::CHO_MUA_SAM_BAO_GIA->value,
            TrangThaiPhieu::TU_CHOI->value,
            TrangThaiPhieu::DA_HUY->value,
            TrangThaiPhieu::NHAN_SU_DUYET->value,
        ])->whereMonth('created_at', now()->month)->count();

        $trangThais = collect([
            TrangThaiPhieu::CHO_GIAM_DOC_DUYET,
            TrangThaiPhieu::CHO_THANH_TOAN,
            TrangThaiPhieu::DA_THANH_TOAN,
            TrangThaiPhieu::DA_HOAN_TAT,
        ])->map(fn ($enum) => ['value' => $enum->value, 'label' => $enum->label()]);

        return Inertia::render('Portals/Purchasing/Index', [
            'roleData' => [
                'stats' => [
                    'cho_bao_gia' => $danhSachChoBaoGia->total(),
                    'da_xu_ly_thang' => $daXuLyThangCount,
                    'tong_nha_cung_cap' => NhaCungCap::count(),
                ],
                'danhSachChoBaoGia' => $danhSachChoBaoGia,
                'danhSachTheoDoi' => $danhSachTheoDoi,
                'chartData' => [
                    'labels' => ['Phong Vũ Computer', 'Tập đoàn Thiên Long (VPP)', 'Nội thất Hòa Phát', 'FPT Shop', 'Khác'],
                    'data' => [45, 25, 15, 10, 5],
                ],
                'filters' => $request->only(['search', 'status']),
                'trangThais' => $trangThais,
            ],
        ]);
    }
}

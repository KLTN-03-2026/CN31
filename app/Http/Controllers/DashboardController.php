<?php

namespace App\Http\Controllers;

use App\Models\PhieuYeuCau;
use App\Models\PhongBan;
use App\Enums\TrangThaiPhieu;
use App\Enums\VaiTro;
use App\Models\NhaCungCap;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $vaiTro = $user->vai_tro;
        // Admin
        if($vaiTro === VaiTro::ADMIN) {
            return redirect()->route('admin.users.index');
        }
        // Kế Toán
        if ($vaiTro === VaiTro::KE_TOAN) {
            return redirect()->route('accountant.index');
        }
        // Mua Sắm
        if ($vaiTro === VaiTro::NHAN_VIEN_MUA_SAM) {
            return redirect()->route('purchasing.index');
        }
        // Trưởng Phòng
        if($vaiTro===VaiTro::TRUONG_PHONG){
            return redirect()->route('manager.approvals');

        }
        // Giám Đốc
        if($vaiTro===VaiTro::GIAM_DOC){
            return redirect()->route('director.approvals');
        // Nhân sự
        }
        if($vaiTro === VaiTro::NHAN_SU) {
            return redirect()->route('hr.index');
        }
        //   NHÂN VIÊN
        $baseQuery = PhieuYeuCau::forUserAccess($user);

        $stats = [
            'total'     => (clone $baseQuery)->count(),
            'cho_xuly'  => (clone $baseQuery)->whereIn('trang_thai', [
                TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET->value,
                TrangThaiPhieu::CHO_GIAM_DOC_DUYET->value,
                TrangThaiPhieu::CHO_THANH_TOAN->value,
                TrangThaiPhieu::CHO_MUA_SAM_BAO_GIA->value,
                TrangThaiPhieu::CHO_NHAN_SU_DUYET->value
            ])->count(),
            'da_thanh_toan' => (clone $baseQuery)->where('trang_thai', TrangThaiPhieu::DA_THANH_TOAN->value)->count(),
            'hoan_tat'  => (clone $baseQuery)->where('trang_thai', TrangThaiPhieu::DA_HOAN_TAT->value)->count(),
            'that_bai'   => (clone $baseQuery)->whereIn('trang_thai', [
                TrangThaiPhieu::TU_CHOI->value,
                TrangThaiPhieu::DA_HUY->value
            ])->count(),
        ];

        $tableQuery = clone $baseQuery;

        if ($request->filled('search')) {
            $search = $request->input('search');
            $tableQuery->where(function($q) use ($search) {
                $q->where('ma_phieu', 'like', "%{$search}%")
                  ->orWhere('tieu_de', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $tableQuery->where('trang_thai', $request->input('status'));
        }

        $recentPhieus = $tableQuery
            ->with('nguoiTao')
            ->latest('created_at')
            ->paginate(5)
            ->withQueryString()
            ->through(function ($phieu) {
                return [
                    'id'               => $phieu->id,
                    'ma_phieu'         => $phieu->ma_phieu,
                    'tieu_de'          => $phieu->tieu_de,
                    'loai_phieu'       => $phieu->loai_phieu,
                    'nguoi_tao'        => $phieu->nguoiTao->name,
                    'ngay_tao'         => $phieu->created_at->format('d/m/Y H:i'),
                    'tong_tien'        => number_format($phieu->tong_tien, 0, ',', '.') . ' VNĐ',
                    'trang_thai_label' => $phieu->trang_thai->label(),
                    'trang_thai_color' => $phieu->trang_thai->color(),
                ];
            });

        $trangThais = collect(TrangThaiPhieu::cases())->map(function($enum) {
            return [
                'value' => $enum->value,
                'label' => $enum->label()
            ];
        });

        return Inertia::render('Dashboard/Dashboard', [
            'stats'          => $stats,
            'recentRequests' => $recentPhieus,
            'filters'        => $request->only(['search', 'status']),
            'trangThais'     => $trangThais
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\PhieuYeuCau;
use App\Enums\TrangThaiPhieu;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Lấy Base Query theo phân quyền
        $baseQuery = PhieuYeuCau::forUserAccess($user);

        // 2. Tính 4 ô Thống Kê (Lấy từ Base Query, không bị ảnh hưởng bởi ô tìm kiếm)
        $stats = [
            'total'     => (clone $baseQuery)->count(),
            'cho_xuly'  => (clone $baseQuery)->whereIn('trang_thai', [
                TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET,
                TrangThaiPhieu::CHO_GIAM_DOC_DUYET,
                TrangThaiPhieu::CHO_THANH_TOAN,
            ])->count(),
            'hoan_tat'  => (clone $baseQuery)->where('trang_thai', TrangThaiPhieu::DA_THANH_TOAN)->count(),
            'that_bai'   => (clone $baseQuery)->whereIn('trang_thai', [
                TrangThaiPhieu::TU_CHOI,
                TrangThaiPhieu::DA_HUY
            ])->count(),
        ];

        // 3. XỬ LÝ TÌM KIẾM VÀ LỌC (Dành riêng cho bảng danh sách)
        $tableQuery = clone $baseQuery;

        // Lọc theo Mã phiếu hoặc Tiêu đề
        if ($request->filled('search')) {
            $search = $request->input('search');
            $tableQuery->where(function($q) use ($search) {
                $q->where('ma_phieu', 'like', "%{$search}%")
                  ->orWhere('tieu_de', 'like', "%{$search}%");
            });
        }

        // Lọc theo Trạng thái
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $tableQuery->where('trang_thai', $request->input('status'));
        }

        // 4. Lấy dữ liệu phân trang cho Bảng
        $recentPhieus = $tableQuery
            ->with('nguoiTao')
            ->latest('created_at')
            ->paginate(5)
            ->withQueryString() // QUAN TRỌNG: Giữ lại tham số tìm kiếm trên URL khi qua trang 2, 3...
            ->through(function ($phieu) {
                return [           
                    'id'               => $phieu->id,
                    'ma_phieu'         => $phieu->ma_phieu,
                    'tieu_de'          => $phieu->tieu_de,
                    'nguoi_tao'        => $phieu->nguoiTao->name,
                    'ngay_tao'         => $phieu->created_at->format('d/m/Y H:i'),
                    'tong_tien'        => number_format($phieu->tong_tien, 0, ',', '.') . ' VNĐ',
                    'trang_thai_label' => $phieu->trang_thai->label(),
                    'trang_thai_color' => $phieu->trang_thai->color(),
                ];
            });

        // 5. Chuẩn bị danh sách Trạng thái để in ra Dropdown
        $trangThais = collect(TrangThaiPhieu::cases())->map(function($enum) {
            return [
                'value' => $enum->value,
                'label' => $enum->label()
            ];
        });

        // 6. Trả về Frontend
        // LƯU Ý: Đảm bảo đường dẫn này khớp với tên file Vue của bạn ('Dashboard' hoặc 'Dashboard/Dashboard')
        return Inertia::render('Dashboard/Dashboard', [
            'stats'          => $stats,
            'recentRequests' => $recentPhieus,
            'filters'        => $request->only(['search', 'status']), // Trả lại chữ vừa gõ
            'trangThais'     => $trangThais
        ]);
    }
}

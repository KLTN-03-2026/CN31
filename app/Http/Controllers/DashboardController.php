<?php

namespace App\Http\Controllers;

use App\Models\PhieuYeuCau;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Khởi tạo Query & Áp dụng ngay Scope phân quyền
        // Lúc này, Laravel sẽ tự động gọi hàm scopeForUserAccess bên Model
        $query = PhieuYeuCau::forUserAccess($user);

        // 2. Clone query để tính toán thống kê
        // Mentor nhận xét: Tư duy dùng (clone $query) của bạn ở đây là XUẤT SẮC!
        // Nó giúp tận dụng lại query gốc mà không bị lưu lại các điều kiện đếm.
        $stats = [
            'total'     => (clone $query)->count(),
            'cho_duyet' => (clone $query)->where('trang_thai', 'cho_duyet')->count(),
            'da_duyet'  => (clone $query)->where('trang_thai', 'da_duyet')->count(),
            'tu_choi'   => (clone $query)->where('trang_thai', 'tu_choi')->count(),
        ];

        // 3. Lấy danh sách 7 phiếu mới nhất
        $recentPhieus = (clone $query)->with('nguoiTao') // Eager load để tránh lỗi N+1
            ->latest('created_at')
            ->take(7)
            ->get()
            ->map(function ($phieu) {
                return [
                    'id' => $phieu->id,
                    'ma_phieu' => $phieu->ma_phieu,
                    'tieu_de' => $phieu->tieu_de,
                    'nguoi_tao' => $phieu->nguoiTao->name,
                    'ngay_tao' => $phieu->created_at->format('d/m/Y'),
                    'tong_tien' => number_format($phieu->tong_tien),
                    'trang_thai_label' => $phieu->trang_thai->label(),
                    'trang_thai_color' => $phieu->trang_thai->color(),
                ];
            });

        // 4. Trả dữ liệu về cho Frontend (Inertia/Vue)
        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentRequests' => $recentPhieus
        ]);
    }
}

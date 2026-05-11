<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhieuYeuCau;
use App\Models\DanhMuc;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PhieuAdminController extends Controller
{
    /**
     * "God View" - Hiển thị toàn bộ phiếu yêu cầu trong hệ thống
     */
    public function index(Request $request)
    {
        // 1. Eager Loading (Chống N+1 Query): Lấy sẵn thông tin Người tạo và Phòng ban
        $query = PhieuYeuCau::with([
            'nguoiTao:id,name,email,phong_ban_id',
            'nguoiTao.phongBan:id,ten_phong_ban'
        ]);

        // 2. Lọc theo Danh mục (Cross-table Filtering)
        // Nếu Admin chọn 1 danh mục, ta truy vấn xuyên sang bảng chi_tiet_yeu_cau
        if ($request->filled('danh_muc_id')) {
            $query->whereHas('chiTiet', function ($q) use ($request) {
                $q->where('danh_muc_id', $request->danh_muc_id);
            });
        }

        // 3. Tìm kiếm cơ bản theo Mã phiếu hoặc Tiêu đề
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ma_phieu', 'like', "%{$search}%")
                  ->orWhere('tieu_de', 'like', "%{$search}%");
            });
        }

        // 4. Sắp xếp theo Giá trị Phiếu (Tổng tiền)
        if ($request->filled('sort_tong_tien')) {
            // Sẽ nhận 'asc' (thấp đến cao) hoặc 'desc' (cao đến thấp) từ Vue
            $query->orderBy('tong_tien', $request->sort_tong_tien);
        } else {
            // Mặc định: Phiếu mới nhất xếp lên đầu
            $query->latest();
        }

        // 5. Trả dữ liệu về cho Vue.js (Phân trang 15 records/trang)
        return Inertia::render('Admin/PhieuYeuCau/Index', [
            'phieuYeuCaus' => $query->paginate(6)->withQueryString(),
            'danhMucs' => DanhMuc::select('id', 'ten_danh_muc')->get(),
            'filters' => $request->only(['search', 'danh_muc_id', 'sort_tong_tien']),
        ]);
    }
}

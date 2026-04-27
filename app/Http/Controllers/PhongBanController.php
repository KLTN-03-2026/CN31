<?php

namespace App\Http\Controllers;

use App\Models\PhongBan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PhongBanController extends Controller
{
    // HIỂN THỊ & TÌM KIẾM
    public function index(Request $request)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        // Kéo dữ liệu phòng ban, mang theo thông tin của Trưởng phòng
        $query = PhongBan::with('truongPhong:id,name,email')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('ten_phong_ban', 'like', "%{$search}%")
                  ->orWhere('ma_phong_ban', 'like', "%{$search}%");
            });
        }

        $phongBans = $query->paginate(10)->withQueryString();

        // Lấy danh sách các User ĐANG HOẠT ĐỘNG để Admin có thể chọn làm Trưởng phòng
        $users = User::where('trang_thai', true)
                     ->select('id', 'name', 'email')
                     ->get();

        return Inertia::render('Admin/PhongBan/Index', [
            'phongBans' => $phongBans,
            'users' => $users,
            'filters' => $request->only('search')
        ]);
    }

    // THÊM MỚI PHÒNG BAN & CẤP NGÂN SÁCH
    public function store(Request $request)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $validated = $request->validate([
            'ma_phong_ban' => 'required|string|max:50|unique:phong_ban,ma_phong_ban',
            'ten_phong_ban' => 'required|string|max:255',
            'truong_phong_id' => 'nullable|exists:users,id',
            'ngan_sach_tong' => 'required|numeric|min:0',
        ]);

        // Mặc định tạo mới thì số tiền đã tiêu phải bằng 0
        $validated['ngan_sach_su_dung'] = 0;

        PhongBan::create($validated);

        return back()->with('success', 'Đã thêm phòng ban và cấp ngân sách thành công!');
    }

    // CẬP NHẬT
    public function update(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $phongBan = PhongBan::findOrFail($id);

        $validated = $request->validate([
            'ma_phong_ban' => 'required|string|max:50|unique:phong_ban,ma_phong_ban,' . $id,
            'ten_phong_ban' => 'required|string|max:255',
            'truong_phong_id' => 'nullable|exists:users,id',
            'ngan_sach_tong' => 'required|numeric|min:0',
        ]);

        // LOGIC CHỐNG LỖI NGÂN SÁCH ÂM: Ngân sách tổng không thể nhỏ hơn số tiền đã tiêu
        if ($validated['ngan_sach_tong'] < $phongBan->ngan_sach_su_dung) {
            return back()->withErrors(['ngan_sach_tong' => 'Ngân sách tổng không thể nhỏ hơn số tiền phòng này đã sử dụng (' . number_format($phongBan->ngan_sach_su_dung) . ' VNĐ)!']);
        }

        $phongBan->update($validated);

        return back()->with('success', 'Cập nhật cơ cấu phòng ban thành công!');
    }

    // XÓA CỨNG
    public function destroy($id)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $phongBan = PhongBan::findOrFail($id);

        try {
            $phongBan->delete();
            return back()->with('success', 'Đã xóa phòng ban khỏi hệ thống.');
        } catch (\Exception $e) {
            // Sẽ bắt lỗi nếu phòng ban đang có user trực thuộc (foreign key constrained)
            return back()->withErrors(['error' => 'Từ chối thao tác: Phòng ban này đang có nhân viên trực thuộc hoặc có dữ liệu liên quan!']);
        }
    }
}

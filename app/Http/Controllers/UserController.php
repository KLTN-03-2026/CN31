<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PhongBan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Hiển thị danh sách nhân sự
    public function index()
    {
        // 1. LỚP BẢO VỆ: Chỉ Admin mới được vào trang này
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Truy cập bị từ chối. Chỉ Quản trị viên mới có quyền vào khu vực này.');
        }

        // Lấy toàn bộ User (kèm thông tin phòng ban hiện tại)
        $users = User::with('phongBan')->orderBy('created_at', 'desc')->get();
 
        // Lấy danh sách phòng ban để làm Dropdown chọn
        $phongBans = PhongBan::select('id', 'ten_phong_ban')->get();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'phongBans' => $phongBans
        ]);
    }

    // Xử lý cập nhật chức vụ & phòng ban
    public function update(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Truy cập bị từ chối.');
        }

        $validated = $request->validate([
            'vai_tro' => 'required|string',
            'phong_ban_id' => 'required|exists:phong_ban,id',
        ]);

        $user = User::findOrFail($id);

        // Không cho phép Admin tự hạ quyền của chính mình (chống "tự sát")
        if ($user->id === Auth::id() && $validated['vai_tro'] !== 'admin') {
            return back()->withErrors(['error' => 'Bạn không thể tự tước quyền Admin của chính mình!']);
        }

        $user->update($validated);

        return back()->with('success', 'Đã cập nhật chức vụ và phòng ban cho nhân viên: ' . $user->name);
    }
}

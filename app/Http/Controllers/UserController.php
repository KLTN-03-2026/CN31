<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PhongBan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    // HIỂN THỊ DANH SÁCH & XỬ LÝ TÌM KIẾM
    public function index(Request $request)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        // Khởi tạo Query cơ bản
        $query = clone User::with('phongBan')->orderBy('created_at', 'desc');

        // BỔ SUNG ĐOẠN NÀY: Bắt từ khóa search từ Vue gửi lên
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $users = $query->paginate(15)->withQueryString();

        $phongBans = PhongBan::select('id', 'ten_phong_ban')->get();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'phongBans' => $phongBans,
            'filters' => $request->only('search') // Trả về lại để Vue giữ chữ trong ô input
        ]);
    }
    // 2. THÊM USER MỚI
    public function store(Request $request)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8', // Mật khẩu Admin tự set
            'vai_tro' => 'required|string',
            'phong_ban_id' => 'nullable|exists:phong_ban,id',
            'tong_ngay_phep' => 'required|numeric|min:0', // Admin cấp ngày phép ngay từ đầu

            ], [
            // Kiểm tra xem user chuẩn bị thêm đả tồn tại trong hệ thống chưa (dựa vào email)
            'email.unique' => 'Địa chỉ email này đã tồn tại trong hệ thống. Vui lòng chọn email khác.'
            ]);


        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'vai_tro' => $validated['vai_tro'],
            'phong_ban_id' => $validated['phong_ban_id'],
            'tong_ngay_phep' => $validated['tong_ngay_phep'],
            'trang_thai' => true, // Mặc định đang hoạt động
        ]);

        return back()->with('success', 'Đã thêm nhân viên mới thành công!');
    }

    // 3. CẬP NHẬT USER (Quyền, Phòng ban, Ngày phép, Trạng thái)
    public function update(Request $request, $id)
    {
            if (!Auth::user()->isAdmin()) abort(403);

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'vai_tro' => 'required|string',
            'phong_ban_id' => 'nullable|exists:phong_ban,id',
            'tong_ngay_phep' => 'required|numeric|min:0',
            'trang_thai' => 'required|boolean', // Cập nhật trạng thái: Đang làm / Đã nghỉ
        ],
        [
            // Kiểm tra xem user chuẩn bị cập nhật đả tồn tại trong hệ thống chưa (dựa vào email)
            'email.unique' => 'Địa chỉ email này đã tồn tại trong hệ thống. Vui lòng chọn email khác.'
         ]);

        // Chống Admin tự khóa hoặc tự tước quyền của chính mình
        if ($user->id === Auth::id()) {
            $validated['vai_tro'] = 'admin'; // Ép giữ nguyên quyền admin
            $validated['trang_thai'] = true; // Ép giữ trạng thái active
        }

        // Nếu Admin có nhập mật khẩu mới thì tiến hành đổi, không thì bỏ qua
        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8']);
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return back()->with('success', 'Đã cập nhật thông tin nhân viên!');
    }

    // 4. XÓA USER CỨNG (Nếu cần thiết)
    public function destroy($id)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'Bạn không thể tự xóa chính mình!']);
        }

        try {
            $user->delete();
            return back()->with('success', 'Đã xóa tài khoản nhân viên khỏi hệ thống.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Không thể xóa nhân viên này do đang có dữ liệu (phiếu) liên quan. Vui lòng chuyển trạng thái thành "Đã nghỉ việc" thay vì xóa!']);
        }
    }
}

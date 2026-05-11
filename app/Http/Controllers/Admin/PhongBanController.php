<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhongBan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PhongBanController extends Controller
{
    /**
     * Display a listing of departments with budget tracking and manager details.
     */
    public function index(Request $request)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }

        $query = PhongBan::with('truongPhong:id,name,email')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('ten_phong_ban', 'like', "%{$search}%")
                    ->orWhere('ma_phong_ban', 'like', "%{$search}%");
            });
        }

        return Inertia::render('Admin/PhongBan/Index', [
            'phongBans' => $query->paginate(10)->withQueryString(),
            'users' => User::where('trang_thai', true)->select('id', 'name', 'email')->get(),
            'filters' => $request->only('search'),
        ]);
    }

    /**
     * Create a new department and initialize its budget.
     */
    public function store(Request $request)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'ma_phong_ban' => 'required|string|max:50|unique:phong_ban,ma_phong_ban',
            'ten_phong_ban' => 'required|string|max:255',
            'truong_phong_id' => 'nullable|exists:users,id',
            'ngan_sach_tong' => 'required|numeric|min:0',
        ]);

        $validated['ngan_sach_su_dung'] = 0;

        PhongBan::create($validated);

        return back()->with('success', 'Đã thêm phòng ban và cấp ngân sách thành công!');
    }

    /**
     * Update department details and validate budget consistency.
     */
    public function update(Request $request, $id)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }

        $phongBan = PhongBan::findOrFail($id);

        $validated = $request->validate([
            'ma_phong_ban' => 'required|string|max:50|unique:phong_ban,ma_phong_ban,'.$id,
            'ten_phong_ban' => 'required|string|max:255',
            'truong_phong_id' => 'nullable|exists:users,id',
            'ngan_sach_tong' => 'required|numeric|min:0',
        ]);

        // Logic Guard: Total budget cannot be lower than current expenditure
        if ($validated['ngan_sach_tong'] < $phongBan->ngan_sach_su_dung) {
            return back()->withErrors(['ngan_sach_tong' => 'Ngân sách tổng không thể nhỏ hơn số tiền đã sử dụng!']);
        }

        $phongBan->update($validated);

        return back()->with('success', 'Cập nhật cơ cấu phòng ban thành công!');
    }

    /**
     * Delete a department from the system.
     */
    public function destroy($id)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }

        try {
            PhongBan::findOrFail($id)->delete();

            return back()->with('success', 'Đã xóa phòng ban khỏi hệ thống.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Từ chối thao tác: Phòng ban đang có dữ liệu liên quan!']);
        }
    }
}

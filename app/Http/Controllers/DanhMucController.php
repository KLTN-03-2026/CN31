<?php

namespace App\Http\Controllers;

use App\Models\DanhMuc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DanhMucController extends Controller
{
    // HIỂN THỊ & TÌM KIẾM
    public function index(Request $request)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $query = DanhMuc::orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('ten_danh_muc', 'like', "%{$search}%");
        }

        $danhMucs = $query->paginate(10)->withQueryString();

        return Inertia::render('Admin/DanhMuc/Index', [
            'danhMucs' => $danhMucs,
            'filters' => $request->only('search')
        ]);
    }

    // THÊM MỚI
    public function store(Request $request)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $validated = $request->validate([
            'ten_danh_muc' => 'required|string|max:255|unique:danh_muc,ten_danh_muc',
            'mo_ta' => 'nullable|string',
        ], [
            'ten_danh_muc.unique' => 'Tên danh mục này đã tồn tại trong hệ thống.'
        ]);

        DanhMuc::create($validated);

        return back()->with('success', 'Đã thêm danh mục mới thành công!');
    }

    // CẬP NHẬT
    public function update(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $danhMuc = DanhMuc::findOrFail($id);

        $validated = $request->validate([
            'ten_danh_muc' => 'required|string|max:255|unique:danh_muc,ten_danh_muc,' . $id,
            'mo_ta' => 'nullable|string',
        ]);

        $danhMuc->update($validated);

        return back()->with('success', 'Cập nhật danh mục thành công!');
    }

    // XÓA
    public function destroy($id)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $danhMuc = DanhMuc::findOrFail($id);

        try {
            $danhMuc->delete();
            return back()->with('success', 'Đã xóa danh mục.');
        } catch (\Exception $e) {
            // Nếu bảng PhieuYeuCau có foreign key trỏ tới danh_muc_id, Laravel sẽ throw Exception khi cố xóa
            return back()->withErrors(['error' => 'Không thể xóa danh mục này vì đang có Phiếu yêu cầu sử dụng nó!']);
        }
    }
}

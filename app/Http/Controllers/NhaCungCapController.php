<?php

namespace App\Http\Controllers;

use App\Models\NhaCungCap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NhaCungCapController extends Controller
{
    //HIỂN THỊ & TÌM KIẾM
    public function index(Request $request)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $query = NhaCungCap::orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('ten_nha_cung_cap', 'like', "%{$search}%")
                  ->orWhere('ma_so_thue', 'like', "%{$search}%");
            });
        }

        $nhaCungCaps = $query->paginate(10)->withQueryString();

        return Inertia::render('Admin/NhaCungCap/Index', [
            'nhaCungCaps' => $nhaCungCaps,
            'filters' => $request->only('search')
        ]);
    }

    // THÊM MỚI
    public function store(Request $request)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $validated = $request->validate([
            'ten_nha_cung_cap' => 'required|string|max:255|unique:nha_cung_cap,ten_nha_cung_cap',
            'ma_so_thue' => 'nullable|string|max:50',
            'so_dien_thoai' => 'nullable|string|max:20',
            'dia_chi' => 'nullable|string',
            'ngan_hang' => 'nullable|string|max:100',
            'so_tai_khoan' => 'nullable|string|max:50',
            'chu_tai_khoan' => 'nullable|string|max:255',
        ],
        [
            // Kiểm tra xem nhà cung cấp chuẩn bị thêm đả tồn tại trong hệ thống chưa (dựa vào tên nhà cung cấp)
            'ten_nha_cung_cap.unique' => 'Tên nhà cung cấp này đã tồn tại trong hệ thống. Vui lòng chọn tên khác.'
         ]);

        NhaCungCap::create($validated);

        return back()->with('success', 'Đã thêm nhà cung cấp mới thành công!');
    }

    //  CẬP NHẬT
    public function update(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $nhaCungCap = NhaCungCap::findOrFail($id);

        $validated = $request->validate([
            'ten_nha_cung_cap' => 'required|string|max:255|unique:nha_cung_cap,ten_nha_cung_cap,' . $nhaCungCap->id,
            'ma_so_thue' => 'nullable|string|max:50',
            'so_dien_thoai' => 'nullable|string|max:20',
            'dia_chi' => 'nullable|string',
            'ngan_hang' => 'nullable|string|max:100',
            'so_tai_khoan' => 'nullable|string|max:50',
            'chu_tai_khoan' => 'nullable|string|max:255',
        ],[
            // Kiểm tra xem nhà cung cấp chuẩn bị cập nhật đả tồn tại trong hệ thống chưa (dựa vào tên nhà cung cấp)
            'ten_nha_cung_cap.unique' => 'Tên nhà cung cấp này đã tồn tại trong hệ thống. Vui lòng chọn tên khác.'
         ]);

        $nhaCungCap->update($validated);

        return back()->with('success', 'Cập nhật thông tin nhà cung cấp thành công!');
    }

    // XÓA
    public function destroy($id)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $nhaCungCap = NhaCungCap::findOrFail($id);

        try {
            $nhaCungCap->delete();
            return back()->with('success', 'Đã xóa nhà cung cấp.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Không thể xóa nhà cung cấp này vì đã có giao dịch liên quan!']);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NhaCungCap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NhaCungCapController extends Controller
{
    /**
     * Display a listing of suppliers with tax and bank details.
     */
    public function index(Request $request)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }

        $query = NhaCungCap::orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('ten_nha_cung_cap', 'like', "%{$search}%")
                    ->orWhere('ma_so_thue', 'like', "%{$search}%");
            });
        }

        return Inertia::render('Admin/NhaCungCap/Index', [
            'nhaCungCaps' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only('search'),
        ]);
    }

    /**
     * Register a new supplier in the database.
     */
    public function store(Request $request)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'ten_nha_cung_cap' => 'required|string|max:255|unique:nha_cung_cap,ten_nha_cung_cap',
            'ma_so_thue' => 'nullable|string|max:50',
            'so_dien_thoai' => 'nullable|string|max:20',
            'dia_chi' => 'nullable|string',
            'ngan_hang' => 'nullable|string|max:100',
            'so_tai_khoan' => 'nullable|string|max:50',
            'chu_tai_khoan' => 'nullable|string|max:255',
        ], [
            'ten_nha_cung_cap.unique' => 'Tên nhà cung cấp này đã tồn tại.',
        ]);

        NhaCungCap::create($validated);

        return back()->with('success', 'Đã thêm nhà cung cấp mới thành công!');
    }

    /**
     * Update supplier profile information.
     */
    public function update(Request $request, $id)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }

        $ncc = NhaCungCap::findOrFail($id);

        $validated = $request->validate([
            'ten_nha_cung_cap' => 'required|string|max:255|unique:nha_cung_cap,ten_nha_cung_cap,'.$id,
            'ma_so_thue' => 'nullable|string|max:50',
            'so_dien_thoai' => 'nullable|string|max:20',
            'dia_chi' => 'nullable|string',
            'ngan_hang' => 'nullable|string|max:100',
            'so_tai_khoan' => 'nullable|string|max:50',
            'chu_tai_khoan' => 'nullable|string|max:255',
        ], [
            'ten_nha_cung_cap.unique' => 'Tên nhà cung cấp này đã tồn tại.',
        ]);

        $ncc->update($validated);

        return back()->with('success', 'Cập nhật thông tin nhà cung cấp thành công!');
    }

    /**
     * Delete a supplier from the system.
     */
    public function destroy($id)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }

        try {
            NhaCungCap::findOrFail($id)->delete();

            return back()->with('success', 'Đã xóa nhà cung cấp.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Không thể xóa nhà cung cấp vì đã có dữ liệu giao dịch liên quan.']);
        }
    }
}

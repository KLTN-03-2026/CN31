<?php

namespace App\Http\Controllers;

use App\Models\DanhMuc;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DanhMucController extends Controller
{
    // 1. Lấy danh sách hiển thị ra giao diện
    public function index()
    {
        // Lấy tất cả danh mục, xếp cái mới nhất lên đầu
        $danhMucs = DanhMuc::latest()->get();

        return Inertia::render('DanhMuc/Index', [
            'danhMucs' => $danhMucs
        ]);
    }

    // 2. Thêm mới danh mục
    public function store(Request $request)
    {
        $request->validate([
            'ten_danh_muc' => 'required|string|max:255|unique:danh_muc,ten_danh_muc',
            'mo_ta' => 'nullable|string'
        ], [
            'ten_danh_muc.required' => 'Vui lòng nhập tên danh mục',
            'ten_danh_muc.unique' => 'Tên danh mục này đã tồn tại'
        ]);
        // Tạo mới danh mục
        DanhMuc::create($request->all());

        return redirect()->back()->with('success', 'Đã thêm danh mục mới thành công!');
    }


// 3. Cập nhật danh mục
    public function update(Request $request, $id) // Sửa tham số chỗ này
    {
        $danhMuc = DanhMuc::findOrFail($id); // Ép nó phải tìm ra đúng ID

        $request->validate([
            'ten_danh_muc' => 'required|string|max:255|unique:danh_muc,ten_danh_muc,' . $danhMuc->id,
            'mo_ta' => 'nullable|string'
        ]);

        $danhMuc->update($request->all());
        return redirect()->back()->with('success', 'Đã cập nhật danh mục!');
    }

    // 4. Xóa danh mục
    public function destroy($id) // Sửa tham số chỗ này
    {
        $danhMuc = DanhMuc::findOrFail($id); // Ép nó tìm ra đúng ID rồi mới chém
        $danhMuc->delete();

        return redirect()->back()->with('success', 'Đã xóa danh mục thành công!');
    }
}

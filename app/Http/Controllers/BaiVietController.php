<?php

namespace App\Http\Controllers;

use App\Models\BaiViet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BaiVietController extends Controller
{


    public function index(Request $request)
    {
        try {
            // Chuẩn bị truy vấn: Lấy bài đã xuất bản, KÈM người đăng (Eager Loading chống N+1)
            $query = BaiViet::with('nguoiDang:id,name,avatar,phong_ban_id')
                ->xuatBan()
                ->latest('ngay_xuat_ban');

            if ($request->filled('loai')) {
                $query->where('loai_bai_viet', $request->input('loai'));
            }

            // Phân trang 8 bài/trang để phù hợp hiển thị Card Grid
            $baiViets = $query->paginate(8)->withQueryString()->through(fn ($bai) => [
                'id' => $bai->id,
                'tieu_de' => $bai->tieu_de,
                'slug' => $bai->slug,
                'tom_tat' => $bai->tom_tat,
                'loai_bai_viet' => $bai->loai_bai_viet,
                'anh_bia' => $bai->anh_bia,
                'ngay_dang' => $bai->ngay_xuat_ban ? $bai->ngay_xuat_ban->locale('vi')->diffForHumans() : $bai->created_at->locale('vi')->diffForHumans(),
                'tac_gia' => $bai->nguoiDang->name ?? 'Ẩn danh',
                'luot_xem' => $bai->luot_xem,
            ]);

            return Inertia::render('Modules/Blog/Index', [
                'baiViets' => $baiViets,
                'filters' => $request->only('loai'),
                'isAdmin' => Auth::user()->isAdmin() || Auth::user()->isNhanSu(),
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi tải bảng tin: '.$e->getMessage());
            return back()->with('error', 'Không thể tải danh sách bài viết lúc này.');
        }
    }

    public function show($slug)
    {
        try {
            $baiViet = BaiViet::with('nguoiDang:id,name,avatar,phong_ban_id')
                ->where('slug', $slug)
                ->xuatBan()
                ->firstOrFail();

            $baiViet->increment('luot_xem');

            return Inertia::render('Modules/Blog/Show', [
                'baiViet' => [
                    'id' => $baiViet->id,
                    'tieu_de' => $baiViet->tieu_de,
                    'noi_dung' => $baiViet->noi_dung,
                    'loai_bai_viet' => $baiViet->loai_bai_viet,
                    'anh_bia' => $baiViet->anh_bia,
                    'ngay_dang' => $baiViet->ngay_xuat_ban ? $baiViet->ngay_xuat_ban->format('d/m/Y H:i') : $baiViet->created_at->format('d/m/Y H:i'),
                    'tac_gia' => $baiViet->nguoiDang->name ?? 'Ẩn danh',
                    'luot_xem' => $baiViet->luot_xem,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi xem bài viết chi tiết: '.$e->getMessage());
            abort(404, 'Bài viết không tồn tại hoặc đã bị ẩn.');
        }
    }



    public function manage(Request $request)
    {
        $this->authorizeAccess();

        $query = BaiViet::withTrashed()
            ->with('nguoiDang:id,name')
            ->orderBy('deleted_at', 'desc')
            ->latest('created_at');

        if ($request->filled('search')) {
            $query->where('tieu_de', 'like', '%' . $request->search . '%');
        }

        $baiViets = $query->paginate(5)->withQueryString()->through(fn ($bai) => [
            'id' => $bai->id,
            'tieu_de' => $bai->tieu_de,
            'loai_bai_viet' => $bai->loai_bai_viet,
            'trang_thai' => $bai->trang_thai,
            'ngay_dang' => $bai->created_at->format('d/m/Y H:i'),
            'tac_gia' => $bai->nguoiDang->name ?? 'Ẩn danh',
            'luot_xem' => $bai->luot_xem,
            'is_deleted' => $bai->trashed(), // Trả về TRUE nếu bài này bị xóa mềm (dùng để hiện UI)
        ]);

        return Inertia::render('Modules/Blog/Manage', [
            'baiViets' => $baiViets,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        $this->authorizeAccess();
        return Inertia::render('Modules/Blog/Create');
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'tieu_de' => 'required|max:255',
            'noi_dung' => 'required',
            'loai_bai_viet' => 'required',
            'anh_bia' => 'nullable|image|max:2048',
        ]);

        $pathAnhBia = null;
        if ($request->hasFile('anh_bia')) {
            $pathAnhBia = $this->uploadFile($request->file('anh_bia'), 'blog/thumbnails');
        }

        BaiViet::create([
            'tieu_de' => $validated['tieu_de'],
            'slug' => Str::slug($validated['tieu_de']).'-'.uniqid(),
            'noi_dung' => $validated['noi_dung'],
            'tom_tat' => Str::limit(strip_tags($validated['noi_dung']), 150),
            'loai_bai_viet' => $validated['loai_bai_viet'],
            'anh_bia' => $pathAnhBia,
            'nguoi_dang_id' => Auth::id(),
            'trang_thai' => 'xuat_ban',
            'ngay_xuat_ban' => now(),
        ]);

        // Trả về trang quản lý tập trung thay vì trang public
        return redirect()->route('admin.blog.manage')->with('success', 'Đăng bài viết mới thành công!');
    }

    /**
     * Hành động Xóa Mềm (Soft Delete)
     */
    public function destroy($id)
    {
        $this->authorizeAccess();

        $baiViet = BaiViet::findOrFail($id);

        // Vì Model có trait SoftDeletes, lệnh này biến thành UPDATE deleted_at = NOW()
        $baiViet->delete();

        return back()->with('success', 'Đã chuyển bài viết vào lưu trữ (ẩn khỏi bảng tin).');
    }

    /**
     * Hành động Khôi phục bài viết (Restore)
     */
    public function restore($id)
    {
        $this->authorizeAccess();

        // BẮT BUỘC phải dùng withTrashed() mới tìm được bài trong thùng rác
        $baiViet = BaiViet::withTrashed()->findOrFail($id);

        $baiViet->restore();

        return back()->with('success', 'Đã khôi phục bài viết thành công.');
    }

    private function authorizeAccess()
    {
        if (! Auth::user()->isAdmin() && ! Auth::user()->isNhanSu()) {
            abort(403, 'Bạn không có quyền quản trị nội dung hệ thống.');
        }
    }

    private function uploadFile($file, $folder = 'blog')
    {
        $filename = time().'_'.$file->getClientOriginalName();
        return $file->storeAs($folder, $filename, 'public');
    }

    public function uploadImage(Request $request)
    {
        if ($request->hasFile('file')) {
            $path = $this->uploadFile($request->file('file'), 'blog/content');
            return response()->json(['url' => asset('storage/'.$path)]);
        }
        return response()->json(['error' => 'Upload failed'], 400);
    }
}

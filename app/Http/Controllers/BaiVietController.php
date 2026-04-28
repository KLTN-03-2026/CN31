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
            $canCreate = Auth::user()->isAdmin() || Auth::user()->isNhanSu();

            $query = BaiViet::with('nguoiDang:id,name,avatar,phong_ban_id')
                ->xuatBan()
                ->latest('ngay_xuat_ban');

            if ($request->filled('loai')) {
                $query->where('loai_bai_viet', $request->input('loai'));
            }

            $baiViets = $query->paginate(6)->withQueryString()->through(fn ($bai) => [
                'id' => $bai->id,
                'tieu_de' => $bai->tieu_de,
                'slug' => $bai->slug,
                'tom_tat' => $bai->tom_tat,
                'loai_bai_viet' => $bai->loai_bai_viet,
                'anh_bia' => $bai->anh_bia,
                'ngay_dang' => $bai->ngay_xuat_ban ? $bai->ngay_xuat_ban->diffForHumans() : $bai->created_at->diffForHumans(),
                'tac_gia' => $bai->nguoiDang->name ?? 'Ẩn danh',
                'luot_xem' => $bai->luot_xem,
            ]);

            return Inertia::render('Modules/Blog/Index', [
                'baiViets' => $baiViets,
                'filters' => $request->only('loai'),
                'canCreate' => $canCreate,
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi tải bảng tin: '.$e->getMessage());

            return back()->with('error', 'Không thể tải danh sách bài viết lúc này.');
        }
    }

    // Tạo bài viết
    public function create()
    {
        // Chặn đứng người dùng cố tình gõ URL /blog/create
        if (! Auth::user()->isAdmin() && ! Auth::user()->isNhanSu()) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        return Inertia::render('Modules/Blog/Create');
    }

    // XỬ LÝ LƯU BÀI VIẾT VÀO DATABASE
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tieu_de' => 'required|max:255',
            'noi_dung' => 'required',
            'loai_bai_viet' => 'required',
            'anh_bia' => 'nullable|image|max:2048', // Giới hạn 2MB
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

        return redirect()->route('blog.index')->with('success', 'Đăng bài thành công!');
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

    private function uploadFile($file, $folder = 'blog')
    {
        $filename = time().'_'.$file->getClientOriginalName();

        return $file->storeAs($folder, $filename, 'public');
    }

    // Đã đổi tên hàm thành uploadImage để khớp với route và frontend gọi axios
    public function uploadImage(Request $request)
    {
        if ($request->hasFile('file')) {
            $path = $this->uploadFile($request->file('file'), 'blog/content');

            return response()->json(['url' => asset('storage/'.$path)]);
        }

        return response()->json(['error' => 'Upload failed'], 400);
    }
}

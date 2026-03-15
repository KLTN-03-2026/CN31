<?php

namespace App\Http\Controllers;

use App\Models\PhieuYeuCau;
use App\Models\ChiTietYeuCau;
use App\Models\NhatKyDuyet;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PhieuYeuCauController extends Controller
{
    public function create()
    {
        return Inertia::render('PhieuYeuCau/TaoMoi');
    }

    // --- LOGIC LƯU DỮ LIỆU ---
    public function store(Request $request)
    {
        // 1. Validate dữ liệu đầu vào (Cực kỳ quan trọng)
        $validated = $request->validate([
            'tieu_de' => 'required|string|max:255',
            'ly_do' => 'nullable|string',
            'san_pham' => 'required|array|min:1', // Phải có ít nhất 1 dòng
            'san_pham.*.ten_san_pham' => 'required|string',
            'san_pham.*.so_luong' => 'required|integer|min:1',
            'san_pham.*.don_gia' => 'required|numeric|min:0',
        ]);

        try {
            // 2. Dùng Transaction: Một là lưu hết, hai là không lưu gì cả
            //Giải thích Transaction: Nếu trong quá trình lưu có lỗi (VD: lỗi DB, lỗi code, v.v), thì tất cả các thao tác đã thực hiện sẽ được "rollback" về trạng thái ban đầu, đảm bảo dữ liệu không bị "nửa vời" hoặc "bị hỏng".
            DB::transaction(function () use ($validated) {

                // Tính tổng tiền ở Backend (An toàn hơn tin tưởng Frontend)
                $tongTien = collect($validated['san_pham'])->sum(function ($item) {
                    return $item['so_luong'] * $item['don_gia'];
                });

                // A. Lưu phiếu cha
                $phieu = PhieuYeuCau::create([
                    'ma_phieu' => 'PR-' . strtoupper(Str::random(6)), // Sinh mã ngẫu nhiên: PR-A1B2C3
                    'tieu_de' => $validated['tieu_de'],
                    'ly_do' => $validated['ly_do'],
                    'tong_tien' => $tongTien,
                    'nguoi_tao_id' => Auth::id(),
                    // Nếu user chưa có phòng ban, tạm để null hoặc ID mặc định
                    'phong_ban_id' => Auth::user()->phong_ban_id ?? 1,
                    'trang_thai' => 'cho_duyet', // Enum 'nhap'
                ]);

                // B. Lưu các dòng con
                foreach ($validated['san_pham'] as $sp) {
                    ChiTietYeuCau::create([
                        'phieu_yeu_cau_id' => $phieu->id,
                        'ten_san_pham' => $sp['ten_san_pham'],
                        'so_luong' => $sp['so_luong'],
                        'don_gia' => $sp['don_gia'],
                        'thanh_tien' => $sp['so_luong'] * $sp['don_gia'],
                        // 'ghi_chu' => $sp['ghi_chu'] ?? null // Nếu có trường ghi chú
                    ]);
                }
            });

            // 3. Thành công -> Về Dashboard kèm thông báo
            return redirect()->route('dashboard')->with('success', 'Đã tạo phiếu yêu cầu thành công!');
        } catch (\Exception $e) {
            // 4. Nếu lỗi -> Quay lại 2form cũ và báo lỗi
            return back()->withErrors(['error' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        // Sử dụng 'with' để lấy luôn dữ liệu bảng con (tránh lỗi N+1 query)
        // 'chiTiet': Lấy danh sách hàng hóa
        // 'nguoiTao': Lấy tên người tạo phiếu
        $phieu = PhieuYeuCau::with(['chiTiet', 'nguoiTao'])->findOrFail($id);

        // Truyền dữ liệu sang Vue
        // Anh bổ sung thêm label và color từ Enum để hiển thị đẹp
        return Inertia::render('PhieuYeuCau/ChiTiet', [
            'phieu' => [
                'id' => $phieu->id,
                'ma_phieu' => $phieu->ma_phieu,
                'tieu_de' => $phieu->tieu_de,
                'ly_do' => $phieu->ly_do,
                'tong_tien' => $phieu->tong_tien,
                'ngay_tao' => $phieu->created_at->format('d/m/Y H:i'), // Format ngày giờ Việt Nam
                'nguoi_tao' => $phieu->nguoiTao->name, // Lấy tên người dùng
                'trang_thai_label' => $phieu->trang_thai->label(),
                'trang_thai_color' => $phieu->trang_thai->color(),
                'chi_tiet' => $phieu->chiTiet, // Mảng hàng hóa
            ]
        ]);
    }
    // Import thêm model Nhật ký

    public function approve(Request $request, $id)
    {
        $phieu = PhieuYeuCau::findOrFail($id);
        $user = Auth::user();

        // 1. Kiểm tra quyền (Bảo mật backend)
        if ($user->role !== 'truong_phong' && $user->role !== 'giam_doc') {
            abort(403, 'Bạn không có quyền duyệt phiếu này.');
        }

        // 2. Xác định trạng thái mới
        $hanhDong = $request->input('hanh_dong'); // 'duyet' hoặc 'tu_choi'
        $trangThaiMoi = ($hanhDong === 'duyet') ? 'da_duyet' : 'tu_choi';

        DB::transaction(function () use ($phieu, $user, $trangThaiMoi, $hanhDong, $request) {
            // A. Cập nhật phiếu
            $phieu->update([
                'trang_thai' => $trangThaiMoi,
                // Nếu duyệt thì tăng bước, nếu từ chối thì giữ nguyên hoặc về 0 (tùy logic)
            ]);

            // B. Ghi Nhật Ký (Audit Log)
            NhatKyDuyet::create([
                'phieu_yeu_cau_id' => $phieu->id,
                'nguoi_thuc_hien_id' => $user->id,
                'hanh_dong' => $hanhDong,
                'noi_dung' => $request->input('ghi_chu', 'Đã xử lý yêu cầu'),
                'thoi_gian' => now(),
            ]);
        });

        return back()->with('success', 'Đã cập nhật trạng thái phiếu thành công!');
    }


    public function print($id)
    {
        // Eager loading lấy hết quan hệ để in ra không bị lỗi
        $phieu = PhieuYeuCau::with(['chiTiet', 'nguoiTao', 'phongBan'])->findOrFail($id);

        // Load view và render PDF
        $pdf = Pdf::loadView('pdf.phieu_yeu_cau', ['phieu' => $phieu]);

        // stream() để xem trước trên trình duyệt thay vì tải về ngay
        return $pdf->stream('Phieu_' . $phieu->ma_phieu . '.pdf');
    }
}

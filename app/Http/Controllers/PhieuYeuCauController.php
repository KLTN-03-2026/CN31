<?php

namespace App\Http\Controllers;

use App\Models\PhieuYeuCau;
use App\Models\ChiTietYeuCau;
use App\Models\NhatKyDuyet;
use App\Enums\TrangThaiPhieu;
use App\Enums\HanhDong;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ThongBaoPhieuMail; 

class PhieuYeuCauController extends Controller
{
    public function create()
    {
        $danhMucs = \App\Models\DanhMuc::select('id', 'ten_danh_muc')->get();
        return Inertia::render('PhieuYeuCau/TaoMoi', ['danhMucs' => $danhMucs]);
    }


    // --- 1. LOGIC TẠO PHIẾU ---
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tieu_de' => 'required|string|max:255',
            'ly_do' => 'nullable|string',
            'san_pham' => 'required|array|min:1',
            'san_pham.*.ten_san_pham' => 'required|string',
            'san_pham.*.danh_muc_id' => 'required|exists:danh_muc,id', // Đã bổ sung chuẩn DB
            'san_pham.*.so_luong' => 'required|integer|min:1',
            'san_pham.*.don_gia' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $tongTien = collect($validated['san_pham'])->sum(fn($item) => $item['so_luong'] * $item['don_gia']);

                // A. Lưu phiếu cha
                $phieu = PhieuYeuCau::create([
                    'ma_phieu' => 'PR-' . strtoupper(Str::random(6)),
                    'tieu_de' => $validated['tieu_de'],
                    'ly_do' => $validated['ly_do'],
                    'tong_tien' => $tongTien,
                    'nguoi_tao_id' => Auth::id(),
                    'phong_ban_id' => Auth::user()->phong_ban_id, // Lấy chuẩn phòng ban của user
                    'trang_thai' => TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET, // Gửi thẳng lên Trưởng phòng
                ]);

                // B. Lưu các dòng con
                $chiTietData = [];
                foreach ($validated['san_pham'] as $sp) {
                    $chiTietData[] = [
                        'phieu_yeu_cau_id' => $phieu->id,
                        'danh_muc_id' => $sp['danh_muc_id'],
                        'ten_san_pham' => $sp['ten_san_pham'],
                        'so_luong' => $sp['so_luong'],
                        'don_gia' => $sp['don_gia'],
                        'thanh_tien' => $sp['so_luong'] * $sp['don_gia'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                // Tối ưu: Dùng insert 1 lần thay vì create trong vòng lặp (Giảm tải DB)
                ChiTietYeuCau::insert($chiTietData);

                // C. Ghi Log Tạo mới
                NhatKyDuyet::create([
                    'phieu_yeu_cau_id' => $phieu->id,
                    'nguoi_thuc_hien_id' => Auth::id(),
                    'hanh_dong' => HanhDong::TAO_MOI,
                    'ghi_chu' => 'Nhân viên tạo yêu cầu mua sắm',
                ]);
            });

            return redirect()->route('dashboard')->with('success', 'Đã tạo phiếu yêu cầu thành công!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }

   // --- 2. XEM CHI TIẾT ---
    public function show($id)
    {
        // 1. TỐI ƯU: Load thêm nhatKy và nguoiThucHien của nhật ký đó
        // Đồng thời sắp xếp nhật ký theo thời gian mới nhất lên đầu
        $phieu = PhieuYeuCau::with([
            'chiTiet.danhMuc',
            'nguoiTao',
            'nhatKy' => function($query) {
                $query->with('nguoiThucHien')->orderBy('thoi_gian_duyet', 'desc');
            }
        ])->findOrFail($id);

        return Inertia::render('PhieuYeuCau/ChiTiet', [
            'phieu' => [
                'id' => $phieu->id,
                'ma_phieu' => $phieu->ma_phieu,
                'tieu_de' => $phieu->tieu_de,
                'ly_do' => $phieu->ly_do,
                'tong_tien' => $phieu->tong_tien,
                'ngay_tao' => $phieu->created_at->format('d/m/Y H:i'),
                'nguoi_tao' => $phieu->nguoiTao->name,
                'trang_thai_label' => $phieu->trang_thai->label(),
                'trang_thai_color' => $phieu->trang_thai->color(),
                'chi_tiet' => $phieu->chiTiet,

                // 2. BỔ SUNG: Map dữ liệu Nhật ký để gửi sang Vue
                'nhat_ky' => $phieu->nhatKy->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'hanh_dong_label' => $log->hanh_dong->label(),
                        'nguoi_thuc_hien' => $log->nguoiThucHien->name,
                        'ghi_chu' => $log->ghi_chu,
                        // Format giờ phút giây cẩn thận vì đây là log Audit
                        'thoi_gian' => \Carbon\Carbon::parse($log->thoi_gian_duyet)->format('d/m/Y H:i:s'),
                    ];
                }),
            ]
        ]);
    }

    // --- 3. LUỒNG DUYỆT ĐA CẤP (TRÙM CUỐI) ---
    public function approve(Request $request, $id)
    {
        $phieu = PhieuYeuCau::findOrFail($id);
        $user = Auth::user();

        $hanhDongInput = $request->input('hanh_dong'); // 'duyet' hoặc 'tu_choi'
        $ghiChu = $request->input('ghi_chu');

        DB::transaction(function () use ($phieu, $user, $hanhDongInput, $ghiChu) {
            $trangThaiMoi = $phieu->trang_thai;
            $hanhDongLog = HanhDong::TU_CHOI;

            if ($hanhDongInput === 'tu_choi') {
                $trangThaiMoi = TrangThaiPhieu::TU_CHOI;
            } else {
                // LOGIC NHẢY BẬC
                if ($user->isTruongPhong() && $phieu->trang_thai === TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET) {
                    $trangThaiMoi = TrangThaiPhieu::CHO_GIAM_DOC_DUYET;
                    $hanhDongLog = HanhDong::TRUONG_PHONG_DUYET;
                } elseif ($user->isGiamDoc() && $phieu->trang_thai === TrangThaiPhieu::CHO_GIAM_DOC_DUYET) {
                    $trangThaiMoi = TrangThaiPhieu::CHO_THANH_TOAN; // Đẩy sang Kế toán
                    $hanhDongLog = HanhDong::GIAM_DOC_DUYET;
                } else {
                    abort(403, 'Phiếu không ở trạng thái dành cho bạn duyệt.');
                }
            }

            // Cập nhật phiếu
            $phieu->update(['trang_thai' => $trangThaiMoi]);

            // Ghi Audit Log
            NhatKyDuyet::create([
                'phieu_yeu_cau_id' => $phieu->id,
                'nguoi_thuc_hien_id' => $user->id,
                'hanh_dong' => $hanhDongLog,
                'ghi_chu' => $ghiChu ?? ($hanhDongInput === 'duyet' ? 'Đã duyệt yêu cầu' : 'Từ chối yêu cầu'),
            ]);
        });

        $phieu = PhieuYeuCau::with('nguoiTao')->findOrFail($id);

        // Thiết lập thông điệp dựa vào hành động
        if ($request->hanh_dong === 'duyet') {
            $tieuDe = "[ProcureFlow] Tin vui! Phiếu {$phieu->ma_phieu} đã được duyệt";
            $loiNhan = "Yêu cầu mua sắm của bạn vừa được cấp trên PHÊ DUYỆT.";
        } else {
            $tieuDe = "[ProcureFlow] Phiếu {$phieu->ma_phieu} đã bị từ chối";
            $loiNhan = "Rất tiếc, yêu cầu mua sắm của bạn đã bị TỪ CHỐI. Lý do: " . $request->ghi_chu;
        }

        // Thực thi gửi Email (Sẽ mất khoảng 2-3 giây để kết nối với server Google)
        Mail::to($phieu->nguoiTao->email)->send(new ThongBaoPhieuMail($phieu, $tieuDe, $loiNhan));

        return back()->with('success', 'Đã xử lý phiếu và gửi email thông báo!');
    }

    // --- 4. NHÂN VIÊN HỦY PHIẾU ---
    public function cancel(Request $request, $id)
    {
        $phieu = PhieuYeuCau::where('id', $id)->where('nguoi_tao_id', Auth::id())->firstOrFail();

        // Chỉ cho hủy khi sếp chưa duyệt (Đang chờ TP)
        if ($phieu->trang_thai !== TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET) {
            return back()->withErrors(['error' => 'Không thể hủy phiếu đã được xử lý!']);
        }

        DB::transaction(function () use ($phieu, $request) {
            $phieu->update(['trang_thai' => TrangThaiPhieu::DA_HUY]);

            NhatKyDuyet::create([
                'phieu_yeu_cau_id' => $phieu->id,
                'nguoi_thuc_hien_id' => Auth::id(),
                'hanh_dong' => HanhDong::HUY,
                'ghi_chu' => $request->input('ghi_chu', 'Người tạo tự hủy phiếu'),
            ]);
        });

        return back()->with('success', 'Đã hủy phiếu yêu cầu!');
    }

    // --- 5. IN PDF ---
    public function print($id)
    {
        $phieu = PhieuYeuCau::with(['chiTiet', 'nguoiTao', 'phongBan'])->findOrFail($id);
        $pdf = Pdf::loadView('pdf.phieu_yeu_cau', ['phieu' => $phieu]);
        return $pdf->stream('Phieu_' . $phieu->ma_phieu . '.pdf');
    }
}

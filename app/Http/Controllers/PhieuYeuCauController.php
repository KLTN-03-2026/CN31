<?php

namespace App\Http\Controllers;

use App\Models\PhieuYeuCau;
use App\Models\NhatKyDuyet;
use App\Models\User;
use App\Enums\TrangThaiPhieu;
use App\Enums\HanhDong;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Notifications\PhieuYeuCauNotification;

class PhieuYeuCauController extends Controller
{
    public function show($id)
    {
        $phieu = PhieuYeuCau::with([
            'nguoiTao.phongBan',
            'nhatKy' => function ($query) {
                $query->with('nguoiThucHien')->orderBy('thoi_gian_duyet', 'desc');
            }
        ])->findOrFail($id);

        if ($phieu->loai_phieu === 'nghi_phep') {
            $phieu->load('chiTietNghiPhep.nguoiBanGiao');
            return Inertia::render('NghiPhep/ChiTiet', [
                'phieu' => [
                    'id' => $phieu->id,
                    'ma_phieu' => $phieu->ma_phieu,
                    'tieu_de' => $phieu->tieu_de,
                    'ly_do' => $phieu->ly_do,
                    'ngay_tao' => $phieu->created_at->format('d/m/Y H:i'),
                    'nguoi_tao' => $phieu->nguoiTao->name,
                    'trang_thai_label' => $phieu->trang_thai->label(),
                    'trang_thai_color' => $phieu->trang_thai->color(),
                    'chi_tiet_nghi_phep' => $phieu->chiTietNghiPhep ? [
                        'loai_nghi_phep' => $phieu->chiTietNghiPhep->loai_nghi_phep,
                        'ngay_bat_dau' => $phieu->chiTietNghiPhep->ngay_bat_dau->format('d/m/Y'),
                        'ngay_ket_thuc' => $phieu->chiTietNghiPhep->ngay_ket_thuc->format('d/m/Y'),
                        'so_ngay_nghi' => $phieu->chiTietNghiPhep->so_ngay_nghi,
                        'nguoi_ban_giao' => $phieu->chiTietNghiPhep->nguoiBanGiao ? $phieu->chiTietNghiPhep->nguoiBanGiao->name : 'Không có',
                    ] : null,
                    'nhat_ky' => $phieu->nhatKy->map(fn($log) => [
                        'id' => $log->id,
                        'hanh_dong_label' => $log->hanh_dong->label(),
                        'nguoi_thuc_hien' => $log->nguoiThucHien->name,
                        'ghi_chu' => $log->ghi_chu,
                        'thoi_gian' => \Carbon\Carbon::parse($log->thoi_gian_duyet)->format('d/m/Y H:i:s'),
                    ]),
                ]
            ]);
        }

        $phieu->load(['chiTiet.danhMuc', 'nhaCungCap']);
        $nhaCungCaps = (Auth::user()->isMuaSam() && $phieu->trang_thai === TrangThaiPhieu::CHO_MUA_SAM_BAO_GIA)
            ? \App\Models\NhaCungCap::select('id', 'ten_nha_cung_cap')->get() : [];

        $nganSach = null;
        if ($phieu->nguoiTao && $phieu->nguoiTao->phongBan) {
            $pb = $phieu->nguoiTao->phongBan;
            $nganSach = [
                'ten_phong' => $pb->ten_phong_ban,
                'tong' => $pb->ngan_sach_tong,
                'da_dung' => $pb->ngan_sach_su_dung,
                'con_lai' => $pb->ngan_sach_con_lai,
                'phan_tram' => $pb->phan_tram_su_dung,
            ];
        }

        return Inertia::render('PhieuYeuCau/ChiTiet', [
            'phieu' => [
                'id' => $phieu->id,
                'ma_phieu' => $phieu->ma_phieu,
                'tieu_de' => $phieu->tieu_de,
                'ly_do' => $phieu->ly_do,
                'tong_tien' => $phieu->tong_tien,
                'file_bao_gia' => $phieu->file_bao_gia ? asset('storage/' . $phieu->file_bao_gia) : null,
                'nha_cung_cap' => $phieu->nhaCungCap ? $phieu->nhaCungCap->ten_nha_cung_cap : 'Chưa xác định',
                'file_nhan_hang' => $phieu->file_nhan_hang ? asset('storage/' . $phieu->file_nhan_hang) : null,
                'ghi_chu_nhan_hang' => $phieu->ghi_chu_nhan_hang,
                'ngay_tao' => $phieu->created_at->format('d/m/Y H:i'),
                'nguoi_tao' => $phieu->nguoiTao->name,
                'trang_thai_label' => $phieu->trang_thai->label(),
                'trang_thai_color' => $phieu->trang_thai->color(),
                'chi_tiet' => $phieu->chiTiet,
                'nhat_ky' => $phieu->nhatKy->map(fn($log) => [
                    'id' => $log->id,
                    'hanh_dong_label' => $log->hanh_dong->label(),
                    'nguoi_thuc_hien' => $log->nguoiThucHien->name,
                    'ghi_chu' => $log->ghi_chu,
                    'thoi_gian' => \Carbon\Carbon::parse($log->thoi_gian_duyet)->format('d/m/Y H:i:s'),
                ]),
            ],
            'nhaCungCaps' => $nhaCungCaps,
            'nganSach' => $nganSach
        ]);
    }

  public function approve(Request $request, $id)
    {
        $phieu = PhieuYeuCau::with('nguoiTao')->findOrFail($id);
        $user = Auth::user();

        $hanhDongInput = $request->input('hanh_dong');
        $ghiChu = $request->input('ghi_chu');

        DB::transaction(function () use ($phieu, $user, $hanhDongInput, $ghiChu) {
            $trangThaiMoi = $phieu->trang_thai;
            $hanhDongLog = HanhDong::TU_CHOI;

            // ==========================================
            // LUỒNG TỪ CHỐI
            // ==========================================
            if ($hanhDongInput === 'tu_choi') {
                $trangThaiMoi = TrangThaiPhieu::TU_CHOI;

                // Báo cho người tạo biết phiếu bị từ chối
                if ($phieu->nguoiTao) {
                    $phieu->nguoiTao->notify(new PhieuYeuCauNotification($phieu, 'Phiếu yêu cầu của bạn đã bị TỪ CHỐI bởi ' . $user->name . '. Lý do: ' . $ghiChu, 'error'));
                }
            }
            // ==========================================
            // LUỒNG PHÊ DUYỆT (CHẤP NHẬN)
            // ==========================================
            else {
                // --- 1. NHÁNH NGHỈ PHÉP ---
                if ($phieu->loai_phieu === 'nghi_phep') {
                    if ($user->isTruongPhong() && $phieu->trang_thai === TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET) {
                        $trangThaiMoi = TrangThaiPhieu::CHO_NHAN_SU_DUYET;
                        $hanhDongLog = HanhDong::TRUONG_PHONG_DUYET;

                        // Báo cho HR xử lý tiếp
                        $hr = User::where('vai_tro', 'nhan_su')->first();
                        if ($hr) $hr->notify(new PhieuYeuCauNotification($phieu, 'Có đơn xin nghỉ phép đã được Trưởng phòng duyệt. Vui lòng kiểm tra và chốt phép.', 'info'));

                        // [MỚI] Báo ngược cho Nhân viên an tâm
                        if ($phieu->nguoiTao) {
                            $phieu->nguoiTao->notify(new PhieuYeuCauNotification($phieu, 'Trưởng phòng đã DUYỆT đơn nghỉ phép của bạn. Đang chờ Nhân sự chốt sổ.', 'success'));
                        }

                    } elseif ($user->isNhanSu() && $phieu->trang_thai === TrangThaiPhieu::CHO_NHAN_SU_DUYET) {
                        $trangThaiMoi = TrangThaiPhieu::DA_HOAN_TAT;
                        $hanhDongLog = HanhDong::NHAN_SU_DUYET;

                        $chiTiet = \App\Models\ChiTietNghiPhep::where('phieu_yeu_cau_id', $phieu->id)->first();
                        if ($chiTiet && $chiTiet->loai_nghi_phep === 'nghi_phep_nam') {
                            User::where('id', $phieu->nguoi_tao_id)->increment('ngay_phep_da_dung', $chiTiet->so_ngay_nghi);
                        }

                        // Báo cho người tạo: Đã hoàn tất 100%
                        if ($phieu->nguoiTao) {
                            $phieu->nguoiTao->notify(new PhieuYeuCauNotification($phieu, 'Đơn xin nghỉ phép của bạn đã được Nhân sự phê duyệt hoàn tất. Chúc bạn nghỉ ngơi vui vẻ!', 'success'));
                        }
                    } else {
                        abort(403);
                    }
                }
                // --- 2. NHÁNH MUA SẮM ---
                else {
                    if ($user->isTruongPhong() && $phieu->trang_thai === TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET) {
                        $trangThaiMoi = TrangThaiPhieu::CHO_MUA_SAM_BAO_GIA;
                        $hanhDongLog = HanhDong::TRUONG_PHONG_DUYET;

                        // Báo cho Mua sắm xử lý tiếp
                        $muaSam = User::where('vai_tro', 'nhan_vien_mua_sam')->first();
                        if ($muaSam) $muaSam->notify(new PhieuYeuCauNotification($phieu, 'Trưởng phòng đã duyệt yêu cầu mua sắm. Vui lòng tìm nhà cung cấp và chốt báo giá.', 'info'));

                        // [MỚI] Báo ngược cho Nhân viên an tâm
                        if ($phieu->nguoiTao) {
                            $phieu->nguoiTao->notify(new PhieuYeuCauNotification($phieu, 'Trưởng phòng đã DUYỆT phiếu mua sắm của bạn. Hệ thống đã chuyển sang phòng Mua Sắm để xử lý.', 'success'));
                        }

                    } elseif ($user->isGiamDoc() && $phieu->trang_thai === TrangThaiPhieu::CHO_GIAM_DOC_DUYET) {
                        $trangThaiMoi = TrangThaiPhieu::CHO_THANH_TOAN;
                        $hanhDongLog = HanhDong::GIAM_DOC_DUYET;

                        // Báo cho Kế toán xuất tiền
                        $keToan = User::where('vai_tro', 'ke_toan')->first();
                        if ($keToan) $keToan->notify(new PhieuYeuCauNotification($phieu, 'Giám đốc đã duyệt phiếu mua sắm vượt hạn mức. Vui lòng thực hiện thanh toán.', 'success'));

                        // [MỚI] Báo ngược cho Nhân viên tạo phiếu
                        if ($phieu->nguoiTao) {
                            $phieu->nguoiTao->notify(new PhieuYeuCauNotification($phieu, 'Sếp lớn đã DUYỆT phiếu mua sắm của bạn. Kế toán đang tiến hành thanh toán.', 'success'));
                        }

                        // [MỚI] Báo ngược cho Mua sắm (Vì họ là người vất vả làm báo giá trình lên)
                        $muaSam = User::where('vai_tro', 'nhan_vien_mua_sam')->first();
                        if ($muaSam) {
                            $muaSam->notify(new PhieuYeuCauNotification($phieu, 'Giám đốc đã DUYỆT báo giá bạn vừa trình lên. Hệ thống đã báo Kế toán chi tiền.', 'success'));
                        }

                    } else {
                        abort(403);
                    }
                }
            }

            // Lưu trạng thái và Ghi Log
            $phieu->update(['trang_thai' => $trangThaiMoi]);
            NhatKyDuyet::create([
                'phieu_yeu_cau_id' => $phieu->id,
                'nguoi_thuc_hien_id' => $user->id,
                'hanh_dong' => $hanhDongLog,
                'ghi_chu' => $ghiChu ?? ($hanhDongInput === 'duyet' ? 'Đã phê duyệt yêu cầu' : 'Từ chối yêu cầu'),
            ]);
        });

        return back()->with('success', 'Đã xử lý phiếu thành công!');
    }
    public function cancel(Request $request, $id)
    {
        $phieu = PhieuYeuCau::where('id', $id)->where('nguoi_tao_id', Auth::id())->firstOrFail();
        if ($phieu->trang_thai !== TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET) return back()->withErrors(['error' => 'Không thể hủy phiếu đã xử lý!']);

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

    public function print($id)
    {
        $phieu = PhieuYeuCau::with(['chiTiet', 'nguoiTao', 'phongBan', 'nhaCungCap'])->findOrFail($id);
        $pdf = Pdf::loadView('pdf.phieu_yeu_cau', ['phieu' => $phieu]);
        return $pdf->stream('Phieu_' . $phieu->ma_phieu . '.pdf');
    }
    // --- API: ĐÁNH DẤU ĐÃ ĐỌC THÔNG BÁO ---
    public function markNotificationAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);

        // Chuyển trạng thái từ "chưa đọc" (unread) sang "đã đọc" (read)
        $notification->markAsRead();

        // Trả về JSON chứa URL để Frontend (Web hoặc Mobile sau này) biết đường mà chuyển trang
        return response()->json([
            'success' => true,
            'url' => route('phieu.show', $notification->data['phieu_id'])
        ], 200);
    }
}

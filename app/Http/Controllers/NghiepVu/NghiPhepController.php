<?php

namespace App\Http\Controllers\NghiepVu;

use App\Enums\HanhDong;
use App\Enums\TrangThaiPhieu;
use App\Http\Controllers\Controller;
use App\Models\ChiTietNghiPhep;
use App\Models\NhatKyDuyet;
use App\Models\PhieuYeuCau;
use App\Models\User;
use App\Notifications\PhieuYeuCauNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class NghiPhepController extends Controller
{
    /**
     * Show the form for creating a new E-Leave request.
     */
    public function create()
    {
        $usersBanGiao = User::where('phong_ban_id', Auth::user()->phong_ban_id)
            ->where('id', '!=', Auth::id())
            ->select('id', 'name')
            ->get();

        return Inertia::render('Modules/NghiPhep/TaoMoi', [
            'usersBanGiao' => $usersBanGiao,
        ]);
    }

    /**
     * Store a newly created E-Leave request in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tieu_de' => 'required|string|max:255',
            'ly_do' => 'required|string',
            'loai_nghi_phep' => 'required|string',
            'ngay_bat_dau' => 'required|date',
            'ngay_ket_thuc' => 'required|date|after_or_equal:ngay_bat_dau',
            'so_ngay_nghi' => 'required|numeric|min:0.5',
            'nguoi_ban_giao_id' => 'nullable|exists:users,id',
        ],
        [
            'tieu_de.required' => 'Vui lòng nhập tiêu đề.',
            'ly_do.required' => 'Vui lòng nhập lý do.',
            'loai_nghi_phep.required' => 'Vui lòng chọn loại nghỉ phép.',
            'ngay_bat_dau.required' => 'Ngày bắt đầu không được để trống.',
            'ngay_ket_thuc.required' => 'Ngày kết thúc không được để trống.',
            'ngay_ket_thuc.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
            'so_ngay_nghi.required' => 'Số ngày nghỉ không được để trống.',
            'so_ngay_nghi.numeric' => 'Số ngày nghỉ phải là một số.',
            'so_ngay_nghi.min' => 'Số ngày nghỉ phải lớn hơn hoặc bằng 0.5.',
            'nguoi_ban_giao_id.exists' => 'Người bàn giao không tồn tại trong hệ thống.',
        ]
        );

        $user = Auth::user();

        // Guard: Check remaining annual leave balance
        if ($validated['loai_nghi_phep'] === 'nghi_phep_nam' && $validated['so_ngay_nghi'] > $user->ngay_phep_con_lai) {
            return back()->withErrors(['error' => 'Bạn chỉ còn '.$user->ngay_phep_con_lai.' ngày phép!']);
        }

        try {
            DB::transaction(function () use ($validated, &$phieu) {
                $phieu = PhieuYeuCau::create([
                    'ma_phieu' => 'LV-'.strtoupper(Str::random(6)),
                    'loai_phieu' => 'nghi_phep',
                    'tieu_de' => $validated['tieu_de'],
                    'ly_do' => $validated['ly_do'],
                    'nguoi_tao_id' => Auth::id(),
                    'phong_ban_id' => Auth::user()->phong_ban_id,
                    'trang_thai' => TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET,
                ]);

                ChiTietNghiPhep::create([
                    'phieu_yeu_cau_id' => $phieu->id,
                    'loai_nghi_phep' => $validated['loai_nghi_phep'],
                    'ngay_bat_dau' => $validated['ngay_bat_dau'],
                    'ngay_ket_thuc' => $validated['ngay_ket_thuc'],
                    'so_ngay_nghi' => $validated['so_ngay_nghi'],
                    'nguoi_ban_giao_id' => $validated['nguoi_ban_giao_id'],
                ]);

                NhatKyDuyet::create([
                    'phieu_yeu_cau_id' => $phieu->id,
                    'nguoi_thuc_hien_id' => Auth::id(),
                    'hanh_dong' => HanhDong::TAO_MOI,
                    'ghi_chu' => 'Nhân viên tạo Đơn xin nghỉ phép.',
                ]);
            });

            // Dispatch notification to Department Manager
            User::where('phong_ban_id', Auth::user()->phong_ban_id)
                ->where('vai_tro', 'truong_phong')
                ->first()
                ?->notify(new PhieuYeuCauNotification($phieu, Auth::user()->name.' vừa tạo đơn xin nghỉ phép.', 'info'));

            return redirect()->route('dashboard')->with('success', 'Đã gửi Đơn xin nghỉ phép!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Lỗi hệ thống: '.$e->getMessage()]);
        }
    }
}

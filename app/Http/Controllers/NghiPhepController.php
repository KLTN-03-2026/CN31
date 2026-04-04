<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PhieuYeuCau;
use App\Models\ChiTietNghiPhep;
use App\Models\NhatKyDuyet;
use App\Models\User;
use App\Enums\TrangThaiPhieu;
use App\Enums\HanhDong;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Notifications\PhieuYeuCauNotification;

class NghiPhepController extends Controller
{
    public function create()
    {
        $usersBanGiao = User::where('phong_ban_id', Auth::user()->phong_ban_id)
                            ->where('id', '!=', Auth::id())
                            ->select('id', 'name')->get();

        return Inertia::render('NghiPhep/TaoMoi', [
            'usersBanGiao' => $usersBanGiao
        ]);
    }

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
        ]);

        $user = Auth::user();

        if ($validated['loai_nghi_phep'] === 'nghi_phep_nam') {
            if ($validated['so_ngay_nghi'] > $user->ngay_phep_con_lai) {
                return back()->withErrors(['error' => 'Bạn chỉ còn ' . $user->ngay_phep_con_lai . ' ngày phép!']);
            }
        }

        try {
            DB::transaction(function () use ($validated, &$phieu) {
                $phieu = PhieuYeuCau::create([
                    'ma_phieu' => 'LV-' . strtoupper(Str::random(6)),
                    'loai_phieu' => 'nghi_phep',
                    'tieu_de' => $validated['tieu_de'],
                    'ly_do' => $validated['ly_do'],
                    'nguoi_tao_id' => Auth::id(),
                    'phong_ban_id' => Auth::user()->phong_ban_id ?? null,
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

            // Bắn chuông cho Trưởng phòng
            $truongPhong = User::where('phong_ban_id', Auth::user()->phong_ban_id)->where('vai_tro', 'truong_phong')->first();
            if ($truongPhong) {
                $truongPhong->notify(new PhieuYeuCauNotification($phieu, Auth::user()->name . ' vừa tạo đơn xin nghỉ phép.', 'info'));
            }

            return redirect()->route('dashboard')->with('success', 'Đã gửi Đơn xin nghỉ phép!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }
}

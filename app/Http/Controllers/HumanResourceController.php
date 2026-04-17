<?php

namespace App\Http\Controllers;

use App\Models\PhieuYeuCau;
use App\Models\User;
use App\Models\PhongBan;
use App\Enums\TrangThaiPhieu;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HumanResourceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        // CHỉ hr mới vào được đây
        if (!$user->isNhanSu()) {
            abort(403, 'Khu vực dành riêng cho Phòng Nhân sự.');
        }

        // TAB 1: DANH SÁCH ĐƠN XIN NGHỈ PHÉP CHỜ DUYỆT
        $queryDuyet = PhieuYeuCau::with(['nguoiTao', 'phongBan'])
            ->where('loai_phieu', 'nghi_phep')
            ->where('trang_thai', TrangThaiPhieu::CHO_NHAN_SU_DUYET->value);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $queryDuyet->where(function($q) use ($search) {
                $q->where('ma_phieu', 'like', "%{$search}%")
                  ->orWhere('tieu_de', 'like', "%{$search}%");
            });
        }

        $danhSachChoDuyet = $queryDuyet->latest('updated_at')
            ->paginate(5, ['*'], 'approvals_page')
            ->onEachSide(1)
            ->withQueryString()
            ->through(fn($phieu) => [
                'id'               => $phieu->id,
                'ma_phieu'         => $phieu->ma_phieu,
                'tieu_de'          => $phieu->tieu_de,
                'nguoi_tao'        => $phieu->nguoiTao->name,
                'ten_phong_ban'    => $phieu->phongBan->ten_phong_ban ?? 'Chưa cập nhật',
                'trang_thai_label' => $phieu->trang_thai->label(),
                'trang_thai_color' => $phieu->trang_thai->color(),
                'ngay_tao'         => $phieu->created_at->diffForHumans(),
            ]);

        // TAB 2: QUẢN LÝ QUỸ PHÉP TOÀN CÔNG TY
        $queryNhanSu = User::with('phongBan')->where('id', '!=', $user->id);

        if ($request->filled('search_team')) {
            $queryNhanSu->where('name', 'like', "%" . $request->input('search_team') . "%");
        }
        if ($request->filled('phong_ban_id')) {
            $queryNhanSu->where('phong_ban_id', $request->input('phong_ban_id'));
        }

        $danhSachNhanSu = $queryNhanSu->paginate(12, ['*'], 'team_page')
            ->onEachSide(1)
            ->withQueryString()
            ->through(fn($nv) => [
                'id'                 => $nv->id,
                'name'               => $nv->name,
                'ten_phong_ban'      => $nv->phongBan->ten_phong_ban ?? 'Chưa phân phòng',
                'vai_tro_label'      => $nv->vai_tro->label() ?? 'Nhân viên',
                'tong_ngay_phep'     => $nv->tong_ngay_phep ?? 12,
                'ngay_phep_da_dung'  => $nv->ngay_phep_da_dung ?? 0,
                'ngay_phep_con_lai'  => $nv->ngay_phep_con_lai ?? 12,
            ]);

        // DỮ LIỆU WIDGET 1: NHÂN VIÊN VẮNG MẶT HÔM NAY (REAL DATA)
        $today = Carbon::today()->toDateString();

        // Lấy phiếu nghỉ phép ĐÃ ĐƯỢC HR DUYỆT và có ngày hôm nay nằm trong khoảng từ bắt đầu đến kết thúc
        $phieuNghiHomNay = PhieuYeuCau::with(['nguoiTao.phongBan', 'chiTietNghiPhep'])
            ->where('loai_phieu', 'nghi_phep')
            ->where('trang_thai', TrangThaiPhieu::NHAN_SU_DUYET->value)
            ->whereHas('chiTietNghiPhep', function ($query) use ($today) {
                $query->whereDate('ngay_bat_dau', '<=', $today)
                      ->whereDate('ngay_ket_thuc', '>=', $today);
            })
            ->get();

        $nhanVienNghiHomNay = $phieuNghiHomNay->map(function ($phieu) {
            return [
                'ten'       => $phieu->nguoiTao->name ?? 'Không xác định',
                'phong_ban' => $phieu->nguoiTao->phongBan->ten_phong_ban ?? 'Chưa phân phòng',
                'loai'      => $phieu->chiTietNghiPhep->loai_nghi_phep ?? 'Nghỉ phép',
            ];
        })->toArray();

        // DỮ LIỆU WIDGET 2: BIỂU ĐỒ SỐ NGÀY NGHỈ THEO THÁNG (REAL DATA)
        $selectedYear = $request->input('year', now()->year);
        $soNgayNghiTheoThang = array_fill(1, 12, 0);

        // Lấy tất cả phiếu đã duyệt trong năm nay, tính tổng số ngày dựa vào tháng của "ngay_bat_dau"
        $phieuNghiPhepNamNay = PhieuYeuCau::where('loai_phieu', 'nghi_phep')
            ->where('trang_thai', TrangThaiPhieu::NHAN_SU_DUYET->value)
            ->whereHas('chiTietNghiPhep', function($query) use ($selectedYear) {
                $query->whereYear('ngay_bat_dau', $selectedYear);
            })
            ->with('chiTietNghiPhep')
            ->get();

        foreach ($phieuNghiPhepNamNay as $phieu) {
            if ($phieu->chiTietNghiPhep) {
                $thang = Carbon::parse($phieu->chiTietNghiPhep->ngay_bat_dau)->month;
                $soNgayNghiTheoThang[$thang] += (float) $phieu->chiTietNghiPhep->so_ngay_nghi;
            }
        }

        $chartData = [
            'bar' => [
                'labels' => ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                'soNgayNghi' => array_values($soNgayNghiTheoThang),
            ],
            'selectedYear' => (int) $selectedYear,
        ];

        // Lấy danh sách phòng ban cho bộ lọc
        $danhSachPhongBan = PhongBan::select('id', 'ten_phong_ban')->get();

        return Inertia::render('HR/Index', [
            'roleData' => [
                'stats' => [
                    'so_luong_cho'    => $danhSachChoDuyet->total(),
                    'so_nguoi_nghi'   => count($nhanVienNghiHomNay),
                    'tong_nhan_su'    => User::count(),
                ],
                'danhSachChoDuyet'   => $danhSachChoDuyet,
                'danhSachNhanSu'     => $danhSachNhanSu,
                'nhanVienNghiHomNay' => $nhanVienNghiHomNay,
                'danhSachPhongBan'   => $danhSachPhongBan,
                'chartData'          => $chartData,
                'filters'            => $request->only(['search', 'search_team', 'phong_ban_id', 'tab', 'year'])
            ]
        ]);
    }
}

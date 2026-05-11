<?php

namespace App\Http\Controllers;

use App\Enums\TrangThaiPhieu;
use App\Enums\VaiTro;
use App\Models\PhieuYeuCau;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $vaiTro = $user->vai_tro;

        // 1. CLEAN CODE: Sử dụng PHP 8 Match Expression để làm Dispatcher Router
        $redirectRoute = match ($vaiTro) {
            VaiTro::ADMIN                    => 'admin.users.index',
            VaiTro::KE_TOAN                  => 'accountant.index',
            VaiTro::NHAN_VIEN_MUA_SAM        => 'purchasing.index',
            VaiTro::TRUONG_PHONG             => 'manager.approvals',
            VaiTro::GIAM_DOC                 => 'director.approvals',
            VaiTro::NHAN_SU                  => 'hr.index',
            default                          => null, // Nếu là NHAN_VIEN thì đi tiếp
        };

        if ($redirectRoute) {
            return redirect()->route($redirectRoute);
        }

        // KHÔNG GIAN DÀNH RIÊNG CHO NHÂN VIÊN
        $baseQuery = PhieuYeuCau::forUserAccess($user);

        // 2. PERFORMANCE OPTIMIZATION: 1 Query đếm thay vì 5 Queries (Conditional Aggregation)
        // Kỹ thuật này ép Database duyệt qua các dòng 1 lần và tự phân loại kết quả
        $rawStats = (clone $baseQuery)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN trang_thai IN (?, ?, ?, ?, ?) THEN 1 ELSE 0 END) as cho_xuly,
                SUM(CASE WHEN trang_thai = ? THEN 1 ELSE 0 END) as da_thanh_toan,
                SUM(CASE WHEN trang_thai IN (?, ?) THEN 1 ELSE 0 END) as hoan_tat,
                SUM(CASE WHEN trang_thai IN (?, ?) THEN 1 ELSE 0 END) as that_bai
            ', [
                // Bindings cho chờ xử lý
                TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET->value,
                TrangThaiPhieu::CHO_GIAM_DOC_DUYET->value,
                TrangThaiPhieu::CHO_THANH_TOAN->value,
                TrangThaiPhieu::CHO_MUA_SAM_BAO_GIA->value,
                TrangThaiPhieu::CHO_NHAN_SU_DUYET->value,
                // Bindings cho đã thanh toán
                TrangThaiPhieu::DA_THANH_TOAN->value,
                // Bindings cho hoàn tất
                TrangThaiPhieu::DA_HOAN_TAT->value,
                TrangThaiPhieu::NHAN_SU_DUYET->value,
                // Bindings cho thất bại
                TrangThaiPhieu::TU_CHOI->value,
                TrangThaiPhieu::DA_HUY->value,
            ])
            ->first();

        // Gán lại vào mảng stats trả về frontend (Ép kiểu về int vì SUM có thể trả về string)
        $stats = [
            'total'         => (int) $rawStats->total,
            'cho_xuly'      => (int) $rawStats->cho_xuly,
            'da_thanh_toan' => (int) $rawStats->da_thanh_toan,
            'hoan_tat'      => (int) $rawStats->hoan_tat,
            'that_bai'      => (int) $rawStats->that_bai,
        ];

        // 3. XỬ LÝ LỌC VÀ TÌM KIẾM
        $tableQuery = clone $baseQuery;

        if ($request->filled('search')) {
            $search = $request->input('search');
            // Dấu % ở đầu sẽ gây ra Full Table Scan, nhưng nhờ có `forUserAccess`,
            // dữ liệu bị giới hạn chỉ trong scope của user, nên vẫn an toàn và nhanh.
            $tableQuery->where(function ($q) use ($search) {
                $q->where('ma_phieu', 'like', "%{$search}%")
                  ->orWhere('tieu_de', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $tableQuery->where('trang_thai', $request->input('status'));
        }

        // 4. LẤY DỮ LIỆU BẢNG (Kèm Eager Loading)
        $recentPhieus = $tableQuery
            ->with('nguoiTao:id,name') // Chỉ lấy id và name để tiết kiệm RAM thay vì load cả Object User khổng lồ
            ->latest('created_at')
            ->paginate(4)
            ->withQueryString()
            ->through(function ($phieu) {
                return [
                    'id'               => $phieu->id,
                    'ma_phieu'         => $phieu->ma_phieu,
                    'tieu_de'          => $phieu->tieu_de,
                    'loai_phieu'       => $phieu->loai_phieu,
                    'nguoi_tao'        => $phieu->nguoiTao->name,
                    'ngay_tao'         => $phieu->created_at->format('d/m/Y H:i'),
                    'tong_tien'        => number_format($phieu->tong_tien, 0, ',', '.') . ' VNĐ',
                    'trang_thai_label' => $phieu->trang_thai->label(),
                    'trang_thai_color' => $phieu->trang_thai->color(),
                ];
            });

        // 5. MAP ENUMS
        $trangThais = collect(TrangThaiPhieu::cases())->map(function ($enum) {
            return [
                'value' => $enum->value,
                'label' => $enum->label(),
            ];
        });

        // 6. TÍNH TOÁN NGHỈ PHÉP
        $tongNgayPhep = (float) ($user->tong_ngay_phep ?? 0);
        $ngayPhepDaDung = (float) ($user->ngay_phep_da_dung ?? 0);

        $nghiPhep = [
            'tong_ngay_phep'    => $tongNgayPhep,
            'ngay_phep_da_dung' => $ngayPhepDaDung,
            'ngay_phep_con_lai' => (float) ($user->ngay_phep_con_lai ?? 0),
            'phan_tram_su_dung' => $tongNgayPhep > 0
                ? round(($ngayPhepDaDung / $tongNgayPhep) * 100, 1)
                : 0,
        ];

        return Inertia::render('Dashboard/Dashboard', [
            'stats'          => $stats,
            'nghi_phep'      => $nghiPhep,
            'recentRequests' => $recentPhieus,
            'filters'        => $request->only(['search', 'status']),
            'trangThais'     => $trangThais,
        ]);
    }
}

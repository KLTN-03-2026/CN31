<?php

namespace App\Http\Controllers;

use App\Models\PhieuYeuCau;
use App\Models\NhaCungCap;
use App\Enums\TrangThaiPhieu;
use App\Enums\VaiTro;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class PurchasingController extends Controller
{
    public function index(Request $request)
    {
         $user = Auth::user();

        //  Chỉ Mua sắm mới được vào
        if ($user->vai_tro !== VaiTro::NHAN_VIEN_MUA_SAM) {
            abort(403, 'Bạn không có quyền truy cập khu vực Mua sắm.');
        }
            // Lấy năm từ URL
            $selectedYear = $request->input('year', now()->year);

            // LẤY SỐ LIỆU TỔNG QUAN
            $choBaoGiaCount = PhieuYeuCau::where('trang_thai', TrangThaiPhieu::CHO_MUA_SAM_BAO_GIA->value)->count();

            $daXuLyThangCount = PhieuYeuCau::whereNotIn('trang_thai', [
                    TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET->value,
                    TrangThaiPhieu::CHO_MUA_SAM_BAO_GIA->value,
                    TrangThaiPhieu::TU_CHOI->value,
                    TrangThaiPhieu::DA_HUY->value,
                    TrangThaiPhieu::NHAN_SU_DUYET->value,

                ])->whereMonth('updated_at', now()->month)->count();

            // DANH SÁCH 1: "CẦN CHỐT GIÁ" (CÓ TÌM KIẾM)
            $queryChoBaoGia = PhieuYeuCau::with('nguoiTao')
                ->where('trang_thai', TrangThaiPhieu::CHO_MUA_SAM_BAO_GIA->value);

            if ($request->filled('search')) {
                $search = $request->input('search');
                $queryChoBaoGia->where(function($q) use ($search) {
                    $q->where('ma_phieu', 'like', "%{$search}%")
                      ->orWhere('tieu_de', 'like', "%{$search}%");
                });
            }

            $danhSachChoBaoGia = $queryChoBaoGia->latest('updated_at')
                ->paginate(4, ['*'], 'cho_bao_gia_page')
                ->onEachSide(1) // Rút gọn số trang
                ->withQueryString()
                ->through(function ($phieu) {
                    return [
                        'id'               => $phieu->id,
                        'ma_phieu'         => $phieu->ma_phieu,
                        'tieu_de'          => $phieu->tieu_de,
                        'nguoi_tao'        => $phieu->nguoiTao->name,
                        'trang_thai_label' => $phieu->trang_thai->label(),
                        'trang_thai_color' => $phieu->trang_thai->color(),
                        'ngay_tao'         => $phieu->created_at->diffForHumans(),
                    ];
                });

            // DANH SÁCH 2: "ĐANG THEO DÕI" (CÓ TÌM KIẾM + LỌC TRẠNG THÁI)
            $queryTheoDoi = PhieuYeuCau::with('nguoiTao')
                ->whereNotIn('trang_thai', [
                    TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET->value,
                    TrangThaiPhieu::CHO_MUA_SAM_BAO_GIA->value,
                    TrangThaiPhieu::CHO_NHAN_SU_DUYET->value,
                    TrangThaiPhieu::TU_CHOI->value,
                    TrangThaiPhieu::DA_HUY->value,
                    TrangThaiPhieu::NHAN_SU_DUYET->value
                ]);

            if ($request->filled('search')) {
                $search = $request->input('search');
                $queryTheoDoi->where(function($q) use ($search) {
                    $q->where('ma_phieu', 'like', "%{$search}%")
                      ->orWhere('tieu_de', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status') && $request->input('status') !== 'all') {
                $queryTheoDoi->where('trang_thai', $request->input('status'));
            }

            $danhSachTheoDoi = $queryTheoDoi->latest('updated_at')
                ->paginate(4, ['*'], 'theo_doi_page')
                ->onEachSide(1) // Rút gọn số trang
                ->withQueryString()
                ->through(function ($phieu) {
                    return [
                        'id'               => $phieu->id,
                        'ma_phieu'         => $phieu->ma_phieu,
                        'tieu_de'          => $phieu->tieu_de,
                        'nguoi_tao'        => $phieu->nguoiTao->name,
                        'trang_thai_label' => $phieu->trang_thai->label(),
                        'trang_thai_color' => $phieu->trang_thai->color(),
                        'ngay_tao'         => $phieu->updated_at->diffForHumans(),
                    ];
                });

            // 4. DATA THỐNG KÊ & BIỂU ĐỒ
            $stats = [
                'cho_bao_gia' => $choBaoGiaCount,
                'da_xu_ly_thang' => $daXuLyThangCount,
                'tong_nha_cung_cap' => NhaCungCap::count(),
            ];

            $chartData = [
                'labels' => ['Phong Vũ Computer', 'Tập đoàn Thiên Long (VPP)', 'Nội thất Hòa Phát', 'FPT Shop', 'Khác'],
                'data' => [45, 25, 15, 10, 5],
            ];

            $trangThais = collect([
                TrangThaiPhieu::CHO_GIAM_DOC_DUYET,
                TrangThaiPhieu::CHO_THANH_TOAN,
                TrangThaiPhieu::DA_THANH_TOAN,
                TrangThaiPhieu::DA_HOAN_TAT,
            ])->map(function($enum) {
                return ['value' => $enum->value, 'label' => $enum->label()];
            });

            return Inertia::render('Purchasing/Index', [
                'roleData' => [
                    'stats' => $stats,
                    'danhSachChoBaoGia' => $danhSachChoBaoGia,
                    'danhSachTheoDoi' => $danhSachTheoDoi,
                    'chartData' => $chartData,
                    'filters' => $request->only(['search', 'status']),
                    'trangThais' => $trangThais
                ]
            ]);

    }
}

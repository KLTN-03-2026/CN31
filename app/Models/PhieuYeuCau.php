<?php

namespace App\Models;

use App\Enums\TrangThaiPhieu;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PhieuYeuCau extends Model
{
    protected $table = 'phieu_yeu_cau';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'tong_tien' => 'decimal:0',
            'trang_thai' => TrangThaiPhieu::class,
        ];
    }

    public function scopeForUserAccess(Builder $query, User $user): Builder
    {
        // 1. Admin hoặc Giám đốc: Thấy tất cả
        if ($user->isAdmin() || $user->isGiamDoc()) {
            return $query;
        }

        // 2. Kế toán: Thấy phiếu đã chốt giá & chờ trả tiền
        if ($user->isKeToan()) {
            return $query->whereIn('trang_thai', [
                TrangThaiPhieu::CHO_THANH_TOAN,
                TrangThaiPhieu::DA_THANH_TOAN,
            ]);
        }

        // 3. Nhân viên Mua sắm: Thấy phiếu đã được Trưởng phòng duyệt, chờ điền giá
        if ($user->isMuaSam()) {
            return $query->where('trang_thai', [
                TrangThaiPhieu::CHO_MUA_SAM_BAO_GIA,
            ]);
        }

        // 4. Trưởng phòng: Lọc ra các phiếu thuộc phòng ban của mình
        if ($user->isTruongPhong()) {
            return $query->where('phong_ban_id', $user->phong_ban_id)
                ->where('trang_thai', TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET);

        }
        // 5. Nhân sự (HR): Chỉ thấy phiếu nghỉ phép đang chờ duyệt hoặc đã hoàn tất, từ chối, hủy
        if ($user->isNhanSu()) {
            return $query->where('loai_phieu', '=', 'nghi_phep')
                ->whereIn('trang_thai', [
                    TrangThaiPhieu::CHO_NHAN_SU_DUYET,
                    TrangThaiPhieu::NHAN_SU_DUYET,
                    TrangThaiPhieu::TU_CHOI,
                    TrangThaiPhieu::DA_HUY,
                ]);
        }

        // 6. Mặc định (Nhân viên): Chỉ thấy phiếu của chính mình
        return $query->where('nguoi_tao_id', $user->id);
    }

    // QUAN HỆ CÁC BẢNG (RELATIONSHIPS)
    public function nguoiTao()
    {
        return $this->belongsTo(User::class, 'nguoi_tao_id');
    }

    public function phongBan()
    {
        return $this->belongsTo(PhongBan::class, 'phong_ban_id');
    }

    public function chiTiet()
    {
        return $this->hasMany(ChiTietYeuCau::class, 'phieu_yeu_cau_id');
    }

    public function nhatKy()
    {
        return $this->hasMany(NhatKyDuyet::class, 'phieu_yeu_cau_id');
    }

    public function giaoDich()
    {
        return $this->hasMany(GiaoDichVnpay::class, 'phieu_yeu_cau_id');
    }

    public function nhaCungCap()
    {
        return $this->belongsTo(NhaCungCap::class, 'nha_cung_cap_id');
    }

    public function chiTietNghiPhep()
    {
        return $this->hasOne(ChiTietNghiPhep::class, 'phieu_yeu_cau_id');
    }

    public function chungTuThanhToan()
    {
        return $this->hasOne(ChungTuThanhToan::class, 'phieu_yeu_cau_id');
    }
}

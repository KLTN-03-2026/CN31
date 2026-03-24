<?php

namespace App\Models;

use App\Enums\TrangThaiPhieu;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PhieuYeuCau extends Model {
    protected $table = 'phieu_yeu_cau';
    protected $guarded = [];

    protected function casts(): array {
        return [
            'tong_tien' => 'decimal:0',
            'trang_thai' => TrangThaiPhieu::class,
        ];
    }

    public function scopeForUserAccess(Builder $query, User $user): Builder
    {

        if ($user->isAdmin() || $user->isGiamDoc()) {
            return $query;
        }
        // if($user->isGiamDoc()){
        //     return $query->whereIn('trang_thai',[
        //         TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET,
        //         TrangThaiPhieu::CHO_GIAM_DOC_DUYET,
        //         TrangThaiPhieu::CHO_THANH_TOAN,
        //         TrangThaiPhieu::DA_THANH_TOAN,
        //     ]);
        // }
        if($user->isKeToan()){
            return $query->whereIn('trang_thai',[
                TrangThaiPhieu::CHO_THANH_TOAN,
                TrangThaiPhieu::DA_THANH_TOAN,
            ]);
        }

        // 2. Nếu là Trưởng phòng -> Lọc ra các phiếu thuộc phòng ban của mình
        if ($user->isTruongPhong()) {
            return $query->where('phong_ban_id', $user->phong_ban_id)
                         ->where('trang_thai',TrangThaiPhieu::CHO_TRUONG_PHONG_DUYET);
        }

        // 3. Mặc định (Nhân viên) -> Chỉ thấy các phiếu do chính mình tạo
        return $query->where('nguoi_tao_id', $user->id);
    }

    // --- QUAN HỆ CÁC BẢNG (RELATIONSHIPS) ---
    public function nguoiTao() { return $this->belongsTo(User::class, 'nguoi_tao_id'); }
    public function phongBan() { return $this->belongsTo(PhongBan::class, 'phong_ban_id'); }
    public function chiTiet() { return $this->hasMany(ChiTietYeuCau::class, 'phieu_yeu_cau_id'); }
    public function nhatKy() { return $this->hasMany(NhatKyDuyet::class, 'phieu_yeu_cau_id'); }
    public function giaoDich() { return $this->hasMany(GiaoDichVnpay::class, 'phieu_yeu_cau_id'); }
}

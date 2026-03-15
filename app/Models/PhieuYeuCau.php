<?php

namespace App\Models;

use App\Enums\TrangThaiPhieu;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PhieuYeuCau extends Model
{
    protected $table = 'phieu_yeu_cau';
    protected $guarded = [];

    // Cast trang_thai thành Enum để dễ dàng sử dụng trong code
    protected $casts = [
        'trang_thai' => TrangThaiPhieu::class,
    ];

    // 1 phiếu yêu cầu có nhiều chi tiết, liên kết qua khóa ngoại phieu_yeu_cau_id
    public function chiTiet()
    {
        return $this->hasMany(ChiTietYeuCau::class, 'phieu_yeu_cau_id');
    }

    // 1 phiếu yêu cầu do 1 người tạo, liên kết qua khóa ngoại nguoi_tao_id
    public function nguoiTao()
    {
        return $this->belongsTo(User::class, 'nguoi_tao_id');
    }

    // 1 phiếu yêu cầu có 1 phòng ban, liên kết qua khóa ngoại phong_ban_id
    public function phongBan()
    {

        return $this->belongsTo(PhongBan::class, 'phong_ban_id');
    }

    // 4. Quan hệ Nhật ký (Nếu cần)
    public function nhatKy()
    {
        return $this->hasMany(NhatKyDuyet::class, 'phieu_yeu_cau_id');
    }

    public function scopeForUserAccess(Builder $query, $user): Builder
    {

        if ($user->role === 'truong_phong' || $user->role === 'giam_doc') {
            // Sếp: Xem tất cả phiếu trong phòng ban (trừ phiếu nháp)
            return $query->where('phong_ban_id', $user->phong_ban_id)
                ->where('trang_thai', '!=', 'nhap');
        }

        // Nhân viên: Chỉ xem phiếu do chính mình tạo
        return $query->where('nguoi_tao_id', $user->id);
    }
}

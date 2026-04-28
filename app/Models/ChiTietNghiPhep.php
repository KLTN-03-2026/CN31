<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTietNghiPhep extends Model
{
    use HasFactory;

    protected $table = 'chi_tiet_nghi_phep';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'ngay_bat_dau' => 'date',
            'ngay_ket_thuc' => 'date',
            'so_ngay_nghi' => 'float',
        ];
    }

    // Quan hệ với bảng cha (PhieuYeuCau)
    public function phieu()
    {
        return $this->belongsTo(PhieuYeuCau::class, 'phieu_yeu_cau_id');
    }

    // Quan hệ với bảng User (Người nhận bàn giao)
    public function nguoiBanGiao()
    {
        return $this->belongsTo(User::class, 'nguoi_ban_giao_id');
    }
}

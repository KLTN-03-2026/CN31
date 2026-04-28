<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietYeuCau extends Model
{
    protected $table = 'chi_tiet_yeu_cau';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['so_luong' => 'integer', 'don_gia' => 'decimal:0', 'thanh_tien' => 'decimal:0'];
    }

    public function phieu()
    {
        return $this->belongsTo(PhieuYeuCau::class, 'phieu_yeu_cau_id');
    }

    public function danhMuc()
    {
        return $this->belongsTo(DanhMuc::class, 'danh_muc_id');
    }

    public function nhaCungCap()
    {
        return $this->belongsTo(NhaCungCap::class, 'nha_cung_cap_id');
    }
}

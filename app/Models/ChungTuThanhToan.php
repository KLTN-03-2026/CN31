<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChungTuThanhToan extends Model
{
    use HasFactory;

    protected $table = 'chung_tu_thanh_toan';
    protected $guarded = [];

    // Quan hệ với Phiếu yêu cầu
    public function phieu()
    {
        return $this->belongsTo(PhieuYeuCau::class, 'phieu_yeu_cau_id');
    }

    // Quan hệ với Kế toán thực hiện
    public function keToan()
    {
        return $this->belongsTo(User::class, 'ke_toan_id');
    }
}

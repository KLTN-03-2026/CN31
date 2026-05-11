<?php

namespace App\Models;

use App\Enums\HanhDong;
use Illuminate\Database\Eloquent\Model;

class NhatKyDuyet extends Model
{
    protected $table = 'nhat_ky_duyet';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'hanh_dong' => HanhDong::class,
            'thoi_gian_duyet' => 'datetime',
        ];
    }

    public function phieu()
    {
        return $this->belongsTo(PhieuYeuCau::class, 'phieu_yeu_cau_id');
    }

    public function nguoiThucHien()
    {
        return $this->belongsTo(User::class, 'nguoi_thuc_hien_id');
    }
}

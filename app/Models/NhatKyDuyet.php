<?php

namespace App\Models;

use App\Enums\HanhDong;
use Illuminate\Database\Eloquent\Model;

class NhatKyDuyet extends Model
{
    protected $table = 'nhat_ky_duyet';
    protected $fillable = [
    'phieu_yeu_cau_id',
    'nguoi_thuc_hien_id',
    'hanh_dong',
    'noi_dung', // <--- Sửa lại cho khớp với tên trong DB và Controller
    'thoi_gian'
];
    protected $guarded = [];
    public $timestamps = false; // Ta dùng cột 'thoi_gian' riêng rồi

    protected $casts = [
        'hanh_dong' => HanhDong::class,
    ];

    public function nguoiThucHien()
    {
        return $this->belongsTo(User::class, 'nguoi_thuc_hien_id');
    }
}

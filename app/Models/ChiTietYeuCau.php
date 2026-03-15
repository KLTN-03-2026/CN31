<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietYeuCau extends Model
{
    protected $table = 'chi_tiet_yeu_cau';
    protected $guarded = [];
    public $timestamps = false; // Bảng chi tiết thường không cần created_at nếu ko muốn
}

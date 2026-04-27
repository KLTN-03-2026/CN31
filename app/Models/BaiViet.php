<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaiViet extends Model
{
    use HasFactory;

    protected $table = 'bai_viet';

    // Cho phép insert hàng loạt
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'ngay_xuat_ban' => 'datetime',
        ];
    }

    // Quan hệ: Một bài viết thuộc về một người đăng (User)
    public function nguoiDang()
    {
        return $this->belongsTo(User::class, 'nguoi_dang_id');
    }

    // Scope: Chỉ lấy bài viết đã xuất bản
    public function scopeXuatBan($query)
    {
        return $query->where('trang_thai', 'xuat_ban');
    }

    // Scope: Lấy theo loại bài viết
    public function scopeLoai($query, $loai)
    {
        return $query->where('loai_bai_viet', $loai);
    }
}

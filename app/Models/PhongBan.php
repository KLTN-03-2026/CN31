<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class PhongBan extends Model
{
    protected $table = 'phong_ban'; // Bắt buộc vì tên bảng tiếng Việt
    protected $guarded = []; // Cho phép lưu mọi trường (cẩn thận khi dùng)

    public function users()
    {
        return $this->hasMany(User::class, 'phong_ban_id');
    }
}

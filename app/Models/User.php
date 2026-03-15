<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{

    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phong_ban_id',
        'role',
        'avatar',
        'trang_thai'
    ];

    // --- CÁC HÀM CHECK QUYỀN (Helper Methods) ---

    public function isNhanVien()
    {
        return $this->role === 'nhan_vien';
    }

    public function isTruongPhong()
    {
        return $this->role === 'truong_phong';
    }

    public function isGiamDoc()
    {
        return $this->role === 'giam_doc';
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    // Thêm đoạn này vào class User
    public function phongBan()
    {
        return $this->belongsTo(PhongBan::class, 'phong_ban_id');
    }
}

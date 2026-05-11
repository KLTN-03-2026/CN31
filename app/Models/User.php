<?php

namespace App\Models;

use App\Enums\VaiTro;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $guarded = [];

    protected $hidden = ['password', 'remember_token'];

    protected $appends = ['avatar_url'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'trang_thai' => 'boolean',
            'vai_tro' => VaiTro::class,
        ];
    }

    // --- CÁC HÀM CHECK QUYỀN ---
    public function isAdmin(): bool
    {
        return $this->vai_tro === VaiTro::ADMIN;
    }

    public function isNhanVien(): bool
    {
        return $this->vai_tro === VaiTro::NHAN_VIEN;
    }

    public function isTruongPhong(): bool
    {
        return $this->vai_tro === VaiTro::TRUONG_PHONG;
    }

    public function isGiamDoc(): bool
    {
        return $this->vai_tro === VaiTro::GIAM_DOC;
    }

    public function isKeToan(): bool
    {
        return $this->vai_tro === VaiTro::KE_TOAN;
    }

    public function isMuaSam(): bool
    {
        return $this->vai_tro === VaiTro::NHAN_VIEN_MUA_SAM;
    }

    public function isNhanSu(): bool
    {
        return $this->vai_tro === VaiTro::NHAN_SU;
    }

    // --- QUAN HỆ (RELATIONSHIPS) ---
    public function phongBan()
    {
        return $this->belongsTo(PhongBan::class, 'phong_ban_id');
    }

    public function phieuYeuCauTao()
    {
        return $this->hasMany(PhieuYeuCau::class, 'nguoi_tao_id');
    }

    public function nhatKyDuyet()
    {
        return $this->hasMany(NhatKyDuyet::class, 'nguoi_thuc_hien_id');
    }

    public function giaoDichThanhToan()
    {
        return $this->hasMany(GiaoDichVnpay::class, 'ke_toan_id');
    }

    public function chungTuThanhToan()
    {
        return $this->hasMany(ChungTuThanhToan::class, 'ke_toan_id');
    }

    // --- ACCESSORS ---
    public function getNgayPhepConLaiAttribute()
    {
        return $this->tong_ngay_phep - $this->ngay_phep_da_dung;
    }

    /**
     * Xử lý đường dẫn Avatar: Tự nhận diện link web ngoài (Seeder) hoặc link Storage nội bộ
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->avatar) {
                    return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=f8fafc&color=0f172a';
                }

                if (str_starts_with($this->avatar, 'http')) {
                    return $this->avatar;
                }

                return Storage::url($this->avatar);
            }
        );
    }
}

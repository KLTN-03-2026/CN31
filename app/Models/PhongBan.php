<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PhongBan extends Model {
    protected $table = 'phong_ban';
    protected $guarded = [];

    public function users() { return $this->hasMany(User::class, 'phong_ban_id'); }
    public function truongPhong() { return $this->belongsTo(User::class, 'truong_phong_id'); }
    public function phieuYeuCau() { return $this->hasMany(PhieuYeuCau::class, 'phong_ban_id'); }
    
    // Tính phần trăm ngân sách đã sử dụng
    public function getPhanTramSuDungAttribute()
    {
        if ($this->ngan_sach_tong == 0) return 0;
        return round(($this->ngan_sach_su_dung / $this->ngan_sach_tong) * 100, 1);
    }

    // Tính số tiền còn lại
    public function getNganSachConLaiAttribute()
    {
        return $this->ngan_sach_tong - $this->ngan_sach_su_dung;
    }
}

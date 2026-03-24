<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PhongBan extends Model {
    protected $table = 'phong_ban';
    protected $guarded = [];

    public function users() { return $this->hasMany(User::class, 'phong_ban_id'); }
    public function truongPhong() { return $this->belongsTo(User::class, 'truong_phong_id'); }
    public function phieuYeuCau() { return $this->hasMany(PhieuYeuCau::class, 'phong_ban_id'); }
}

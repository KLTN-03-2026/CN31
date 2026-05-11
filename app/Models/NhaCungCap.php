<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NhaCungCap extends Model
{
    protected $table = 'nha_cung_cap';

    protected $guarded = [];

    // Quan hệ với bảng PhieuYeuCau
    public function phieuYeuCau()
    {
        return $this->hasMany(PhieuYeuCau::class, 'nha_cung_cap_id');
    }
}

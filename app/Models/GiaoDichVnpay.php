<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class GiaoDichVnpay extends Model {
    protected $table = 'giao_dich_vnpay';
    protected $guarded = [];

    protected function casts(): array {
        return [
            'so_tien_thanh_toan' => 'decimal:0',
            'ngay_thanh_toan' => 'datetime',
        ];
    }

    public function phieu() { return $this->belongsTo(PhieuYeuCau::class, 'phieu_yeu_cau_id'); }
    public function keToan() { return $this->belongsTo(User::class, 'ke_toan_id'); }
}

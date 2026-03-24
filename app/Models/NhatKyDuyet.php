<?php
namespace App\Models;

use App\Enums\HanhDong; // 1. BẮT BUỘC PHẢI THÊM DÒNG USE NÀY
use Illuminate\Database\Eloquent\Model;

class NhatKyDuyet extends Model {
    protected $table = 'nhat_ky_duyet';
    protected $guarded = [];
    public $timestamps = false;

    // 2. SỬA LẠI HÀM CASTS NHƯ THẾ NÀY:
    protected function casts(): array {
        return [
            'hanh_dong' => HanhDong::class, // <--- ÉP KIỂU ENUM LÀ ĐÂY!
            'thoi_gian_duyet' => 'datetime',
        ];
    }

    public function phieu() { return $this->belongsTo(PhieuYeuCau::class, 'phieu_yeu_cau_id'); }
    public function nguoiThucHien() { return $this->belongsTo(User::class, 'nguoi_thuc_hien_id'); }
}

<?php

namespace Database\Seeders;

use App\Models\DanhMuc;
use Illuminate\Database\Seeder;

class DanhMucSeeder extends Seeder
{
    public function run(): void
    {
        $danhMucs = [
            [
                'ten_danh_muc' => 'Thiết bị CNTT & Phần mềm',
                'mo_ta' => 'Máy tính xách tay, PC, Máy chủ, Bản quyền phần mềm, Linh kiện mạng.'
            ],
            [
                'ten_danh_muc' => 'Văn phòng phẩm',
                'mo_ta' => 'Giấy in, Bút, Mực in, Bìa hồ sơ, Dụng cụ văn phòng hàng ngày.'
            ],
            [
                'ten_danh_muc' => 'Nội thất & Tài sản cố định',
                'mo_ta' => 'Bàn làm việc, Ghế công thái học, Tủ tài liệu, Máy lạnh.'
            ],
            [
                'ten_danh_muc' => 'Dịch vụ & Sự kiện',
                'mo_ta' => 'Chi phí tổ chức Teambuilding, Đào tạo nội bộ, Đặt vé máy bay công tác.'
            ],
        ];

        foreach ($danhMucs as $dm) {
            DanhMuc::create($dm);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\PhongBan;
use Illuminate\Database\Seeder;

class PhongBanSeeder extends Seeder
{
    public function run(): void
    {
        $phongBans = [
            [
                'ma_phong_ban' => 'BGD',
                'ten_phong_ban' => 'Ban Giám Đốc',
                'ngan_sach_tong' => 5000000000, // 5 Tỷ
            ],
            [
                'ma_phong_ban' => 'TCKT',
                'ten_phong_ban' => 'Phòng Tài chính - Kế toán',
                'ngan_sach_tong' => 2000000000, // 2 Tỷ
            ],
            [
                'ma_phong_ban' => 'HCNS',
                'ten_phong_ban' => 'Phòng Hành chính - Nhân sự', 
                'ngan_sach_tong' => 1500000000, // 1.5 Tỷ
            ],
            [
                'ma_phong_ban' => 'IT',
                'ten_phong_ban' => 'Phòng Công nghệ Thông tin',
                'ngan_sach_tong' => 3000000000, // 3 Tỷ
            ],
            [
                'ma_phong_ban' => 'KD',
                'ten_phong_ban' => 'Phòng Kinh doanh',
                'ngan_sach_tong' => 1000000000, // 1 Tỷ
            ],
        ];

        foreach ($phongBans as $pb) {
            PhongBan::create($pb);
        }
    }
}

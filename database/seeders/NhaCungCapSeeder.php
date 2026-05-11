<?php

namespace Database\Seeders;

use App\Models\NhaCungCap;
use Illuminate\Database\Seeder;

class NhaCungCapSeeder extends Seeder
{
    public function run(): void
    {
        $nhaCungCaps = [
            [
                'ten_nha_cung_cap' => 'Công ty CP Thương mại Dịch vụ Phong Vũ',
                'ma_so_thue' => '0304998381',
                'so_dien_thoai' => '18006867',
                'dia_chi' => '264 Nguyễn Thị Minh Khai, Phường 6, Quận 3, TP.HCM',
                'ngan_hang' => 'Vietcombank',
                'so_tai_khoan' => '0071000898989',
                'chu_tai_khoan' => 'CTY CP TMDV PHONG VU'
            ],
            [
                'ten_nha_cung_cap' => 'Công ty TNHH Hệ thống Thông tin FPT (FPT IS)',
                'ma_so_thue' => '0104128565',
                'so_dien_thoai' => '02435626000',
                'dia_chi' => 'Tòa nhà FPT, Số 10 Phạm Văn Bạch, Cầu Giấy, Hà Nội',
                'ngan_hang' => 'TPBank',
                'so_tai_khoan' => '88889999000',
                'chu_tai_khoan' => 'CTY TNHH HTTT FPT'
            ],
            [
                'ten_nha_cung_cap' => 'Công ty CP Tập đoàn Thiên Long',
                'ma_so_thue' => '0301464846',
                'so_dien_thoai' => '02837505555',
                'dia_chi' => 'Lô 6-8-10-12, Đường số 3, KCN Tân Tạo, Bình Tân, TP.HCM',
                'ngan_hang' => 'Techcombank',
                'so_tai_khoan' => '1902233445566',
                'chu_tai_khoan' => 'CTY CP TAP DOAN THIEN LONG'
            ],
            [
                'ten_nha_cung_cap' => 'Nội thất Hòa Phát (The One)',
                'ma_so_thue' => '0900188284',
                'so_dien_thoai' => '02436658498',
                'dia_chi' => 'Khu công nghiệp Phố Nối A, Huyện Văn Lâm, Hưng Yên',
                'ngan_hang' => 'MBBank',
                'so_tai_khoan' => '0987654321000',
                'chu_tai_khoan' => 'NOI THAT THE ONE'
            ]
        ];

        foreach ($nhaCungCaps as $ncc) {
            NhaCungCap::create($ncc);
        }
    }
}

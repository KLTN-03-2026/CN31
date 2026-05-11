<?php

namespace Database\Seeders;

use App\Enums\VaiTro;
use App\Models\PhongBan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Mật khẩu chuẩn để demo trước hội đồng
        $password = Hash::make('password');

        // Lấy ID các phòng ban đã tạo ở PhongBanSeeder
        $bgd = PhongBan::where('ma_phong_ban', 'BGD')->first();
        $tckt = PhongBan::where('ma_phong_ban', 'TCKT')->first();
        $hcns = PhongBan::where('ma_phong_ban', 'HCNS')->first();
        $it = PhongBan::where('ma_phong_ban', 'IT')->first();

        // 1. Tạo Giám Đốc
        $giamDoc = User::create([
            'name' => 'Lê Trần Uy Vũ',
            'email' => 'giamdoc@procureflow.vn',
            'password' => $password,
            'vai_tro' => VaiTro::GIAM_DOC,
            'phong_ban_id' => $bgd->id,
            'avatar' => 'https://ui-avatars.com/api/?name=Lê+Vũ&background=EF4444&color=fff', // Đỏ - Red
        ]);
        $bgd->update(['truong_phong_id' => $giamDoc->id]);

        // 2. Tạo Trưởng phòng IT
        $truongPhongIT = User::create([
            'name' => 'Trương Thanh Hiếu',
            'email' => 'hieutt.it@procureflow.vn',
            'password' => $password,
            'vai_tro' => VaiTro::TRUONG_PHONG,
            'phong_ban_id' => $it->id,
            'avatar' => 'https://ui-avatars.com/api/?name=Thanh+Hiếu&background=3B82F6&color=fff', // Xanh dương - Blue
        ]);
        $it->update(['truong_phong_id' => $truongPhongIT->id]);

        // 3. Tạo Kế toán trưởng
        $keToan = User::create([
            'name' => 'Nguyễn Thị Bích Ngọc',
            'email' => 'ngocntb.kt@procureflow.vn',
            'password' => $password,
            'vai_tro' => VaiTro::KE_TOAN,
            'phong_ban_id' => $tckt->id,
            'avatar' => 'https://ui-avatars.com/api/?name=Bích+Ngọc&background=10B981&color=fff', // Xanh lá - Green
        ]);
        $tckt->update(['truong_phong_id' => $keToan->id]);

        // 4. Tạo Chuyên viên Nhân sự (HR) - MỚI BỔ SUNG
        $nhanSu = User::create([
            'name' => 'Phạm Tú Anh',
            'email' => 'anhpt.hr@procureflow.vn',
            'password' => $password,
            'vai_tro' => VaiTro::NHAN_SU,
            'phong_ban_id' => $hcns->id,
            'avatar' => 'https://ui-avatars.com/api/?name=Tú+Anh&background=EC4899&color=fff', // Hồng - Pink
        ]);
        // Gán HR làm trưởng phòng Hành chính - Nhân sự
        $hcns->update(['truong_phong_id' => $nhanSu->id]);

        // 5. Tạo Chuyên viên Mua sắm (Thuộc phòng HCNS)
        $muaSam = User::create([
            'name' => 'Trần Quốc Toản',
            'email' => 'toantq.ms@procureflow.vn',
            'password' => $password,
            'vai_tro' => VaiTro::NHAN_VIEN_MUA_SAM,
            'phong_ban_id' => $hcns->id,
            'avatar' => 'https://ui-avatars.com/api/?name=Quốc+Toản&background=F59E0B&color=fff', // Cam - Orange
        ]);

        // 6. Tạo Nhân viên bình thường (Để test luồng tạo phiếu)
        User::create([
            'name' => 'Hoàng Văn Thái',
            'email' => 'thaihv.it@procureflow.vn',
            'password' => $password,
            'vai_tro' => VaiTro::NHAN_VIEN,
            'phong_ban_id' => $it->id,
            'avatar' => 'https://ui-avatars.com/api/?name=Văn+Thái&background=6B7280&color=fff', // Xám - Gray
        ]);

        // 7. Thêm tài khoản Quản trị hệ thống cấp cao (Admin)
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@procureflow.vn',
            'password' => $password,
            'vai_tro' => VaiTro::ADMIN,
            'phong_ban_id' => $it->id, // Admin thường sinh hoạt ở phòng IT
            'avatar' => 'https://ui-avatars.com/api/?name=Admin&background=8B5CF6&color=fff', // Tím - Purple
        ]);
    }
}

<?php


namespace Database\Seeders;
use App\Models\User;
use App\Models\PhongBan;
use App\Models\DanhMuc;
use App\Models\NhaCungCap;
use App\Enums\VaiTro;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo Phòng ban
        $phongIT = PhongBan::create(['ma_phong_ban' => 'IT', 'ten_phong_ban' => 'Phòng IT']);

        // 2. Tạo Danh mục & Nhà cung cấp để test
        DanhMuc::create(['ten_danh_muc' => 'Thiết bị IT', 'mo_ta' => 'Máy tính, chuột, bàn phím']);
        NhaCungCap::create(['ten_nha_cung_cap' => 'Phong Vũ Computer', 'so_dien_thoai' => '18006868']);

        // 3. Tạo 5 User đại diện cho 5 Mắt xích trong luồng
        $password = Hash::make('password'); // Mật khẩu chung cho dễ test: password
        $avatar = 'https://tintuc.dienthoaigiakho.vn/wp-content/uploads/2025/08/8.jpg'; // Avatar mẫu, bạn có thể thay đổi hoặc để trống

        // Mắt xích 1: Nhân viên
        User::create([
            'name' => 'Nhân Viên Gõ Code',
            'email' => 'nhanvien@test.com',
            'avatar' => $avatar,
            'password' => $password,
            'vai_tro' => VaiTro::NHAN_VIEN,
            'phong_ban_id' => $phongIT->id,
        ]);

        // Mắt xích 2: Trưởng phòng
        $truongPhong = User::create([
            'name' => 'Sếp IT',
            'email' => 'truongphong@test.com',
            'avatar' => $avatar,
            'password' => $password,
            'vai_tro' => VaiTro::TRUONG_PHONG,
            'phong_ban_id' => $phongIT->id,
        ]);
        // Cập nhật trưởng phòng cho phòng IT
        $phongIT->update(['truong_phong_id' => $truongPhong->id]);

        // Mắt xích 3: Mua sắm
        User::create([
            'name' => 'Chuyên viên Mua Sắm',
            'email' => 'muasam@test.com',
            'avatar' => $avatar,
            'password' => $password,
            'vai_tro' => VaiTro::NHAN_VIEN_MUA_SAM,
            'phong_ban_id' => $phongIT->id,
        ]);

        // Mắt xích 4: Giám đốc
        User::create([
            'name' => 'Sếp Tổng',
            'email' => 'giamdoc@test.com',
            'avatar' => $avatar,
            'password' => $password,
            'vai_tro' => VaiTro::GIAM_DOC,
            'phong_ban_id' => $phongIT->id,
        ]);

        // Mắt xích 5: Kế toán
        User::create([
            'name' => 'Chị Kế Toán',
            'email' => 'ketoan@test.com',
            'avatar' => $avatar,
            'password' => $password,
            'vai_tro' => VaiTro::KE_TOAN,
            'phong_ban_id' => $phongIT->id,
        ]);
    }
}

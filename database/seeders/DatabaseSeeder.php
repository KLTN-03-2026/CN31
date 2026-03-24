<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\PhongBan;
use App\Models\DanhMuc;
use App\Models\NhaCungCap;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. TẠO DANH MỤC & NHÀ CUNG CẤP (Dữ liệu gốc)
        DanhMuc::insert([
            ['ten_danh_muc' => 'Thiết bị IT (Máy tính, Màn hình)', 'created_at' => now(), 'updated_at' => now()],
            ['ten_danh_muc' => 'Văn phòng phẩm', 'created_at' => now(), 'updated_at' => now()],
        ]);
 
        NhaCungCap::insert([
            ['ten_nha_cung_cap' => 'Công ty Máy tính Phong Vũ', 'so_dien_thoai' => '18006868', 'created_at' => now(), 'updated_at' => now()],
            ['ten_nha_cung_cap' => 'Nhà sách Fahasa', 'so_dien_thoai' => '1900636467', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 2. TẠO PHÒNG BAN (Chưa có trưởng phòng)
        $phongGiamDoc = PhongBan::create(['ma_phong_ban' => 'BGD', 'ten_phong_ban' => 'Ban Giám Đốc']);
        $phongIT = PhongBan::create(['ma_phong_ban' => 'IT', 'ten_phong_ban' => 'Phòng Công Nghệ Thông Tin']);
        $phongKeToan = PhongBan::create(['ma_phong_ban' => 'KT', 'ten_phong_ban' => 'Phòng Kế Toán']);

        // 3. TẠO USERS (4 Vai trò theo đúng thiết kế của bạn)
        $password = Hash::make('password'); // Mật khẩu chung là: password
        $avatar = 'https://tintuc.dienthoaigiakho.vn/wp-content/uploads/2025/08/8.jpg'; // Avatar mẫu, bạn có thể thay đổi hoặc để trống
        // A. Giám Đốc (Duyệt cấp 2)
        User::create([
            'name' => 'Sếp Tổng (Giám Đốc)',
            'email' => 'giamdoc@procureflow.test',
            'password' => $password,
            'avatar' => $avatar,
            'vai_tro' => 'giam_doc', // Nhớ đảm bảo Enum VaiTro của bạn khớp với chữ này
            'phong_ban_id' => $phongGiamDoc->id,
        ]);

        // B. Trưởng Phòng IT (Duyệt cấp 1)
        $truongPhongIT = User::create([
            'name' => 'Sếp IT (Trưởng Phòng)',
            'email' => 'truongphong@procureflow.test',
            'password' => $password,
            'avatar' => $avatar,
            'vai_tro' => 'truong_phong',
            'phong_ban_id' => $phongIT->id,
        ]);
        // Cập nhật ngược lại: Gán ông này làm trưởng phòng IT
        $phongIT->update(['truong_phong_id' => $truongPhongIT->id]);

        // C. Nhân Viên IT (Người tạo đơn)
        User::create([
            'name' => 'Nhân Viên Gõ Code',
            'email' => 'nhanvien@procureflow.test',
            'password' => $password,
            'avatar' => $avatar,
            'vai_tro' => 'nhan_vien',
            'phong_ban_id' => $phongIT->id,
        ]);

        // D. Kế Toán (Thanh toán VNPAY & In PDF)
        User::create([
            'name' => 'Chị Kế Toán',
            'email' => 'ketoan@procureflow.test',
            'password' => $password,
            'avatar' => $avatar,
            'vai_tro' => 'ke_toan',
            'phong_ban_id' => $phongKeToan->id,
        ]);
    }
}

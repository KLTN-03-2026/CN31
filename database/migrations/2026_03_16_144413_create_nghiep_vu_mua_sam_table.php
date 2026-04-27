<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1. PHIẾU YÊU CẦU
        Schema::create('phieu_yeu_cau', function (Blueprint $table) {
            $table->id();
            $table->string('ma_phieu')->unique();
            $table->string('loai_phieu')->default('mua_sam'); // Chuẩn bị sẵn cho đơn xin nghỉ phép sau này
            $table->foreignId('nguoi_tao_id')->constrained('users');
            $table->foreignId('phong_ban_id')->constrained('phong_ban');

            // Của phòng Mua sắm điền sau:
            $table->foreignId('nha_cung_cap_id')->nullable()->constrained('nha_cung_cap')->restrictOnDelete();
            $table->string('file_bao_gia')->nullable();

            $table->string('tieu_de');
            $table->text('ly_do')->nullable();
            $table->decimal('tong_tien', 15, 0)->nullable(); // Nullable vì NV tạo phiếu không biết giá
            $table->string('trang_thai')->default('nhap');
            $table->timestamps();
        });

        // 2. CHI TIẾT YÊU CẦU
        Schema::create('chi_tiet_yeu_cau', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phieu_yeu_cau_id')->constrained('phieu_yeu_cau')->cascadeOnDelete();
            $table->foreignId('danh_muc_id')->constrained('danh_muc');

            $table->string('ten_san_pham');
            $table->integer('so_luong');
            $table->decimal('don_gia', 15, 0)->nullable(); // Nullable
            $table->decimal('thanh_tien', 15, 0)->nullable(); // Nullable
            $table->text('ghi_chu')->nullable();
            $table->timestamps();
        });

        // 3. NHẬT KÝ DUYỆT
        Schema::create('nhat_ky_duyet', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phieu_yeu_cau_id')->constrained('phieu_yeu_cau')->cascadeOnDelete();
            $table->foreignId('nguoi_thuc_hien_id')->constrained('users');
            $table->string('hanh_dong');
            $table->text('ghi_chu')->nullable();
            $table->timestamp('thoi_gian_duyet')->useCurrent(); // Đổi tên chuẩn theo ERD
        });

        // 4. GIAO DỊCH VNPAY
        Schema::create('giao_dich_vnpay', function (Blueprint $table) {
            $table->id();
            $table->string('ma_giao_dich_vnpay')->unique();
            $table->foreignId('ke_toan_id')->constrained('users');
            $table->foreignId('phieu_yeu_cau_id')->constrained('phieu_yeu_cau');
            $table->string('ma_ngan_hang')->nullable();
            $table->decimal('so_tien_thanh_toan', 15, 0);
            $table->string('thong_tin_don_hang')->nullable();
            $table->dateTime('ngay_thanh_toan')->nullable();
            $table->string('trang_thai_giao_dich'); // thanh_cong, that_bai, cho_thanh_toan
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('giao_dich_vnpay');
        Schema::dropIfExists('nhat_ky_duyet');
        Schema::dropIfExists('chi_tiet_yeu_cau');
        Schema::dropIfExists('phieu_yeu_cau');
    }
};

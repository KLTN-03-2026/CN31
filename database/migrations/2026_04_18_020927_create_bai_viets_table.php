<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bai_viet', function (Blueprint $table) {
            $table->id();

            // Thông tin cơ bản
            $table->string('tieu_de'); // Tiêu đề bài viết
            $table->string('slug')->unique(); // Đường dẫn thân thiện (vd: quy-dinh-cong-ty)
            $table->text('tom_tat')->nullable(); // Trích dẫn ngắn để hiện ở ngoài trang chủ
            $table->longText('noi_dung'); // Nơi chứa HTML sinh ra từ Tiptap
            $table->string('anh_bia')->nullable(); // Ảnh thumbnail của bài viết

            // Phân loại & Trạng thái
            $table->string('loai_bai_viet')->default('tin_tuc'); // tin_tuc, su_kien, noi_quy
            $table->string('trang_thai')->default('xuat_ban'); 

            // Người đăng (Liên kết với bảng users)
            $table->foreignId('nguoi_dang_id')->constrained('users')->cascadeOnDelete();

            // Tương tác
            $table->unsignedInteger('luot_xem')->default(0); // Đếm số lượt xem bài viết
            $table->timestamp('ngay_xuat_ban')->nullable(); // Có thể hẹn giờ đăng hoặc lưu thời gian đăng

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bai_viet');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('phieu_yeu_cau', function (Blueprint $table) {
            $table->id();
            $table->string('ma_phieu')->unique(); // VD: PR-2024-001
            $table->string('tieu_de');
            $table->text('ly_do')->nullable();

            // Tiền nong rất quan trọng, dùng decimal để chính xác tuyệt đối
            // 15 số, 0 số lẻ (vì VND không dùng xu)
            $table->decimal('tong_tien', 15, 0)->default(0);

            $table->string('trang_thai')->default('nhap');

            // Ai tạo phiếu? Thuộc phòng nào lúc tạo?
            $table->foreignId('nguoi_tao_id')->constrained('users');
            $table->foreignId('phong_ban_id')->constrained('phong_ban');

            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('phieu_yeu_caus');
    }
};

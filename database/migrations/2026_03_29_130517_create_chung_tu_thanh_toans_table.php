<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chung_tu_thanh_toan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phieu_yeu_cau_id')->constrained('phieu_yeu_cau')->cascadeOnDelete();
            $table->foreignId('ke_toan_id')->constrained('users'); // Người thực hiện chuyển tiền

            $table->string('phuong_thuc')->default('vietqr'); // vietqr, vnpay, tien_mat...
            $table->string('ma_giao_dich_ngan_hang')->nullable(); // Mã tham chiếu trên app ngân hàng
            $table->double('so_tien_thanh_toan');
            $table->string('hinh_anh_minh_chung')->nullable(); // File ảnh chụp bill chuyển tiền
            $table->text('ghi_chu')->nullable();

            $table->timestamps();
        });
    }
};

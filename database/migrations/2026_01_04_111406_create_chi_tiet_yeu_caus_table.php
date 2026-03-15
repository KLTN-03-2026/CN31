<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('chi_tiet_yeu_cau', function (Blueprint $table) {
            $table->id();
            // Nếu xóa phiếu cha, chi tiết cũng mất theo (cascade)
            $table->foreignId('phieu_yeu_cau_id')->constrained('phieu_yeu_cau')->cascadeOnDelete();

            $table->string('ten_san_pham');
            $table->integer('so_luong');
            $table->decimal('don_gia', 15, 0);
            $table->decimal('thanh_tien', 15, 0); // Lưu cứng để sau này truy xuất nhanh
            $table->text('ghi_chu')->nullable();

            $table->timestamps();
        });
    }

   
    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_yeu_caus');
    }
};

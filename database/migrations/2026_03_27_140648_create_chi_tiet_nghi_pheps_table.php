<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
  public function up(): void
    {
        Schema::create('chi_tiet_nghi_phep', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phieu_yeu_cau_id')->constrained('phieu_yeu_cau')->cascadeOnDelete();

            $table->string('loai_nghi_phep');
            $table->date('ngay_bat_dau'); // Đổi thành kiểu date cho chuẩn
            $table->date('ngay_ket_thuc');
            $table->float('so_ngay_nghi');
            $table->foreignId('nguoi_ban_giao_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }
};

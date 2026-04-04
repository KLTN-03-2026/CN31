<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phieu_yeu_cau', function (Blueprint $table) {
            // File ảnh chụp hàng hóa, biên bản bàn giao hoặc hóa đơn đỏ
            $table->string('file_nhan_hang')->nullable()->after('file_bao_gia');

            // Ghi chú về tình trạng lúc nhận (Nguyên seal, móp méo, thiếu phụ kiện...)
            $table->text('ghi_chu_nhan_hang')->nullable()->after('file_nhan_hang');
        });
    }

    public function down(): void
    {
        Schema::table('phieu_yeu_cau', function (Blueprint $table) {
            $table->dropColumn(['file_nhan_hang', 'ghi_chu_nhan_hang']);
        });
    }
};

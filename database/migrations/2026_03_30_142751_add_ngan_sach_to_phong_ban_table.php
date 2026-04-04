<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phong_ban', function (Blueprint $table) {
            // Ngân sách được cấp (Ví dụ: 500.000.000 VNĐ)
            $table->decimal('ngan_sach_tong', 15, 0)->default(0)->after('ten_phong_ban');

            // Số tiền đã chi tiêu (Sẽ cộng dồn mỗi khi 1 phiếu được thanh toán)
            $table->decimal('ngan_sach_su_dung', 15, 0)->default(0)->after('ngan_sach_tong');
        });
    }

    public function down(): void
    {
        Schema::table('phong_ban', function (Blueprint $table) {
            $table->dropColumn(['ngan_sach_tong', 'ngan_sach_su_dung']);
        });
    }
};

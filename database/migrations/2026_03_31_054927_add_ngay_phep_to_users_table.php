<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tổng số ngày phép trong năm (Mặc định Luật lao động VN là 12 ngày)
            $table->float('tong_ngay_phep')->default(12)->after('vai_tro');

            // Số ngày phép đã sử dụng
            $table->float('ngay_phep_da_dung')->default(0)->after('tong_ngay_phep');

            // Note: Dùng kiểu float để hỗ trợ nghỉ nửa ngày (0.5 ngày)
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tong_ngay_phep', 'ngay_phep_da_dung']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('nhat_ky_duyet', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phieu_yeu_cau_id')->constrained('phieu_yeu_cau')->cascadeOnDelete();
            $table->foreignId('nguoi_thuc_hien_id')->constrained('users'); // Ai duyệt/hủy

            $table->string('hanh_dong'); // duyet, tu_choi...
            $table->text('noi_dung')->nullable(); // Lý do từ chối

            $table->timestamp('thoi_gian')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nhat_ky_duyets');
    }
};

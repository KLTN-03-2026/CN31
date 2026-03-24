<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('phong_ban', function (Blueprint $table) {
            $table->id();
            $table->string('ma_phong_ban')->unique();
            $table->string('ten_phong_ban');

            // TẠO CỘT CHỜ: Lúc này bảng users chưa ra đời, nên ta chỉ tạo cột để đó.
            $table->unsignedBigInteger('truong_phong_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('phong_ban');
    }
};

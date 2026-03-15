<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('phong_ban', function (Blueprint $table) {
            $table->id();
            $table->string('ten_phong');
            // Lưu ý: Truong_phong_id liên kết với users, nhưng vì bảng users có thể chưa hoàn thiện
            // nên ta để nullable và xử lý quan hệ sau để tránh lỗi foreign key vòng tròn.
            $table->unsignedBigInteger('truong_phong_id')->nullable();
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('phong_bans');
    }
};

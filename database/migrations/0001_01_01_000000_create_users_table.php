<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. TẠO BẢNG USERS
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // SƯỚNG NHẤT LÀ ĐÂY: Vì phong_ban chạy trước nên giờ ta nối thẳng khóa ngoại luôn!
            $table->foreignId('phong_ban_id')->nullable()->constrained('phong_ban')->nullOnDelete();

            $table->string('name');
            $table->string('email')->unique();
            $table->string('avatar')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('vai_tro')->default('nhan_vien');
            $table->boolean('trang_thai')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. GIẢI QUYẾT TRƯỞNG PHÒNG: Giờ cả 2 bảng đã ra đời, ta móc khóa ngoại cho cột truong_phong_id lúc nãy
        Schema::table('phong_ban', function (Blueprint $table) {
            $table->foreign('truong_phong_id')->references('id')->on('users')->nullOnDelete();
        });

        // Bảng mặc định của Laravel
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::table('phong_ban', function (Blueprint $table) {
            $table->dropForeign(['truong_phong_id']);
        });

        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};

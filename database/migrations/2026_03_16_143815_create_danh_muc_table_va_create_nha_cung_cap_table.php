<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('danh_muc', function (Blueprint $table) {
            $table->id();
            $table->string('ten_danh_muc');
            $table->text('mo_ta')->nullable();
            $table->timestamps();
        });

        Schema::create('nha_cung_cap', function (Blueprint $table) {
            $table->id();
            $table->string('ten_nha_cung_cap');
            $table->string('ma_so_thue')->nullable();
            $table->string('so_dien_thoai')->nullable();
            $table->text('dia_chi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nha_cung_cap');
        Schema::dropIfExists('danh_muc');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nha_cung_cap', function (Blueprint $table) {
            $table->string('ngan_hang')->nullable()->after('dia_chi'); // VD: MB, VCB, TCB
            $table->string('so_tai_khoan')->nullable()->after('ngan_hang');
            $table->string('chu_tai_khoan')->nullable()->after('so_tai_khoan');
        });
    }
};

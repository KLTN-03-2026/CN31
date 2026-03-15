<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

   public function up()
{
    Schema::table('users', function (Blueprint $table) {
        // Liên kết user vào phòng ban
        $table->foreignId('phong_ban_id')->nullable()->constrained('phong_ban');
        $table->string('chuc_vu')->default('nhan_vien'); // nhan_vien, truong_phong, giam_doc
        $table->boolean('trang_thai')->default(true); // true = đang làm việc
    });
}


    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};

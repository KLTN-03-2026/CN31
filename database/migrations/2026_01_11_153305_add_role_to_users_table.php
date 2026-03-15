<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
         
            $table->enum('role', ['nhan_vien', 'truong_phong', 'giam_doc'])
                ->default('nhan_vien')
                ->after('email'); // Đặt sau cột email cho dễ nhìn
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tạo cấu trúc bảng
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text');
            $table->string('description')->nullable();
            $table->timestamps();
        });

    
        DB::table('settings')->insert([
            [
                'key' => 'vat_tax',
                'value' => '10',
                'type' => 'number',
                'description' => 'Thuế GTGT (%) mặc định cho các đơn hàng',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'key' => 'han_muc_giam_doc_duyet',
                'value' => '20000000', // <-- Mốc 20 triệu nằm ở đây
                'type' => 'number',
                'description' => 'Mức giá trị (VNĐ) tối thiểu yêu cầu Giám đốc phải duyệt sau khi Mua sắm chốt giá',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'key' => 'thong_bao_bao_tri',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Bật cờ này để hiện thông báo bảo trì toàn hệ thống',
                'created_at' => now(), 'updated_at' => now()
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

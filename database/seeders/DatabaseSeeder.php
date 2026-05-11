<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            PhongBanSeeder::class,
            DanhMucSeeder::class,
            NhaCungCapSeeder::class,
            UserSeeder::class,
        ]);

        $this->command->info('==== Đã setup xong dữ liệu Doanh nghiệp Procureflow ====');
    }
}

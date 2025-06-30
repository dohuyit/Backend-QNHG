<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy 10 order_id và 10 table_id ngẫu nhiên (giả sử đã có dữ liệu)
        $orderIds = DB::table('orders')->inRandomOrder()->limit(10)->pluck('id');
        $tableIds = DB::table('tables')->inRandomOrder()->limit(10)->pluck('id');

        foreach (range(0, 9) as $i) {
            DB::table('order_tables')->insert([
                'order_id' => $orderIds[$i % count($orderIds)],
                'table_id' => $tableIds[$i % count($tableIds)],
                'notes' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

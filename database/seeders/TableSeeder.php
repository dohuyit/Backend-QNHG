<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Table;
use App\Models\TableArea;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy tất cả khu vực bàn đã có
        $tableAreas = TableArea::all();

        // Với mỗi khu vực, tạo 5–10 bàn
        foreach ($tableAreas as $area) {
            Table::factory()
                ->count(rand(5, 10))
                ->create([
                    'table_area_id' => $area->id
                ]);
        }
    }
}

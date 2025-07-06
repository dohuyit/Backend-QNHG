<?php

namespace Database\Seeders;

use App\Models\Table;
use App\Models\TableArea;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy danh sách table areas
        $tableAreas = TableArea::all();

        $tableData = [
            [
                'table_number' => 'A1',
                'table_type' => '2_seats',
                'description' => 'Bàn gần cửa sổ, view đẹp',
                'tags' => ['gần cửa sổ', 'yên tĩnh'],
                'status' => 'available',
                'table_area_id' => $tableAreas->first()->id,
            ],
            [
                'table_number' => 'A2',
                'table_type' => '2_seats',
                'description' => 'Bàn góc riêng tư',
                'tags' => ['riêng tư', 'yên tĩnh'],
                'status' => 'occupied',
                'table_area_id' => $tableAreas->first()->id,
            ],

            // Khu vực 1 - Bàn 4 ghế
            [
                'table_number' => 'A3',
                'table_type' => '4_seats',
                'description' => 'Bàn tròn giữa khu vực',
                'tags' => ['trung tâm', 'view đẹp'],
                'status' => 'available',
                'table_area_id' => $tableAreas->first()->id,
            ],

            // Khu vực 2 - Bàn 8 ghế
            [
                'table_number' => 'B1',
                'table_type' => '8_seats',
                'description' => 'Bàn lớn cho nhóm đông',
                'tags' => ['nhóm lớn', 'VIP'],
                'status' => 'available',
                'table_area_id' => $tableAreas->get(1)->id ?? $tableAreas->first()->id,
            ],
            [
                'table_number' => 'B2',
                'table_type' => '8_seats',
                'description' => 'Bàn VIP ngoài trời',
                'tags' => ['ngoài trời', 'VIP', 'view đẹp'],
                'status' => 'cleaning',
                'table_area_id' => $tableAreas->get(1)->id ?? $tableAreas->first()->id,
            ],

            // Khu vực 3 - Bàn 4 ghế
            [
                'table_number' => 'C1',
                'table_type' => '4_seats',
                'description' => 'Bàn gần nhà bếp',
                'tags' => ['gần bếp', 'nhanh'],
                'status' => 'available',
                'table_area_id' => $tableAreas->get(2)->id ?? $tableAreas->first()->id,
            ],
            [
                'table_number' => 'C2',
                'table_type' => '4_seats',
                'description' => 'Bàn góc yên tĩnh',
                'tags' => ['yên tĩnh', 'riêng tư'],
                'status' => null, // Trạng thái trống
                'table_area_id' => $tableAreas->get(2)->id ?? $tableAreas->first()->id,
            ],
        ];

        foreach ($tableData as $data) {
            Table::create($data);
        }
    }
}

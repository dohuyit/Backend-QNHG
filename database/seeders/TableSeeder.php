<?php

namespace Database\Seeders;

use App\Models\Table;
use App\Models\TableArea;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy danh sách khu vực bàn (theo tên)
        $tableAreas = TableArea::pluck('id', 'name');

        $tables = [
            [
                'table_number' => 'VIP-01',
                'capacity' => 8,
                'min_guests' => 4,
                'max_guests' => 10,
                'description' => 'Bàn VIP số 1, view đẹp, yên tĩnh',
                'tags' => json_encode(['yên tĩnh', 'view đẹp', 'ghế sofa']),
                'status' => 'available',
                'is_active' => true,
                'table_area_id' => $tableAreas['Khu vực VIP'] ?? 1,
            ],
            [
                'table_number' => 'VIP-02',
                'capacity' => 6,
                'min_guests' => 2,
                'max_guests' => 8,
                'description' => 'Bàn VIP số 2, gần cửa sổ',
                'tags' => json_encode(['gần cửa sổ', 'yên tĩnh']),
                'status' => 'available',
                'is_active' => true,
                'table_area_id' => $tableAreas['Khu vực VIP'] ?? 1,
            ],
            [
                'table_number' => 'A-01',
                'capacity' => 4,
                'min_guests' => 2,
                'max_guests' => 6,
                'description' => 'Bàn thường số 1',
                'tags' => json_encode(['bàn tròn']),
                'status' => 'available',
                'is_active' => true,
                'table_area_id' => $tableAreas['Khu vực thường'] ?? 2,
            ],
            [
                'table_number' => 'A-02',
                'capacity' => 4,
                'min_guests' => 2,
                'max_guests' => 6,
                'description' => 'Bàn thường số 2',
                'tags' => json_encode(['bàn vuông']),
                'status' => 'occupied',
                'is_active' => true,
                'table_area_id' => $tableAreas['Khu vực thường'] ?? 2,
            ],
            [
                'table_number' => 'A-03',
                'capacity' => 2,
                'min_guests' => 1,
                'max_guests' => 3,
                'description' => 'Bàn thường số 3',
                'tags' => json_encode(['bàn đôi']),
                'status' => 'reserved',
                'is_active' => true,
                'table_area_id' => $tableAreas['Khu vực thường'] ?? 2,
            ],
            [
                'table_number' => 'OUT-01',
                'capacity' => 6,
                'min_guests' => 2,
                'max_guests' => 8,
                'description' => 'Bàn ngoài trời số 1',
                'tags' => json_encode(['ngoài trời', 'view đẹp']),
                'status' => 'available',
                'is_active' => true,
                'table_area_id' => $tableAreas['Khu vực ngoài trời'] ?? 3,
            ],
            [
                'table_number' => 'OUT-02',
                'capacity' => 4,
                'min_guests' => 2,
                'max_guests' => 6,
                'description' => 'Bàn ngoài trời số 2',
                'tags' => json_encode(['ngoài trời', 'có mái che']),
                'status' => 'cleaning',
                'is_active' => true,
                'table_area_id' => $tableAreas['Khu vực ngoài trời'] ?? 3,
            ],
            [
                'table_number' => 'OUT-03',
                'capacity' => 8,
                'min_guests' => 4,
                'max_guests' => 10,
                'description' => 'Bàn ngoài trời số 3',
                'tags' => json_encode(['ngoài trời', 'bàn lớn']),
                'status' => 'out_of_service',
                'is_active' => false,
                'table_area_id' => $tableAreas['Khu vực ngoài trời'] ?? 3,
            ],
        ];

        foreach ($tables as $table) {
            Table::updateOrCreate(
                ['table_number' => $table['table_number']],
                $table
            );
        }
    }
}

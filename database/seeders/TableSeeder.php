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
            // Khu vực 1 - Bàn 2/4 ghế
            [
                'table_number' => 'A4',
                'table_type' => '2_seats',
                'description' => 'Bàn cạnh lối đi',
                'tags' => ['dễ tiếp cận', 'gần cửa'],
                'status' => 'available',
                'table_area_id' => $tableAreas->first()->id,
            ],
            [
                'table_number' => 'A5',
                'table_type' => '4_seats',
                'description' => 'Bàn dành cho gia đình nhỏ',
                'tags' => ['gia đình', 'gần khu vui chơi'],
                'status' => 'occupied',
                'table_area_id' => $tableAreas->first()->id,
            ],
            [
                'table_number' => 'A6',
                'table_type' => '2_seats',
                'description' => 'Bàn riêng tư ở góc',
                'tags' => ['riêng tư', 'ít người'],
                'status' => 'available',
                'table_area_id' => $tableAreas->first()->id,
            ],
            [
                'table_number' => 'A7',
                'table_type' => '4_seats',
                'description' => 'Bàn cạnh quầy bar',
                'tags' => ['gần quầy bar', 'sôi động'],
                'status' => 'available',
                'table_area_id' => $tableAreas->first()->id,
            ],
            [
                'table_number' => 'A8',
                'table_type' => '4_seats',
                'description' => 'Bàn gần cửa sổ lớn',
                'tags' => ['ánh sáng tốt', 'view đẹp'],
                'status' => 'reserved',
                'table_area_id' => $tableAreas->first()->id,
            ],

            // Khu vực 2 - Bàn 6/8 ghế
            [
                'table_number' => 'B3',
                'table_type' => '6_seats',
                'description' => 'Bàn dài cho nhóm bạn',
                'tags' => ['nhóm', 'bạn bè'],
                'status' => 'available',
                'table_area_id' => $tableAreas->get(1)->id ?? $tableAreas->first()->id,
            ],
            [
                'table_number' => 'B4',
                'table_type' => '8_seats',
                'description' => 'Bàn VIP riêng biệt',
                'tags' => ['VIP', 'riêng biệt'],
                'status' => 'occupied',
                'table_area_id' => $tableAreas->get(1)->id ?? $tableAreas->first()->id,
            ],
            [
                'table_number' => 'B5',
                'table_type' => '8_seats',
                'description' => 'Bàn gần sân khấu',
                'tags' => ['gần sân khấu', 'nhóm lớn'],
                'status' => 'available',
                'table_area_id' => $tableAreas->get(1)->id ?? $tableAreas->first()->id,
            ],
            [
                'table_number' => 'B6',
                'table_type' => '6_seats',
                'description' => 'Bàn ngoài trời có mái che',
                'tags' => ['ngoài trời', 'mái che'],
                'status' => 'cleaning',
                'table_area_id' => $tableAreas->get(1)->id ?? $tableAreas->first()->id,
            ],
            [
                'table_number' => 'B7',
                'table_type' => '6_seats',
                'description' => 'Bàn gần khu trẻ em',
                'tags' => ['gia đình', 'trẻ em'],
                'status' => 'available',
                'table_area_id' => $tableAreas->get(3)->id ?? $tableAreas->first()->id,
            ],

            // Khu vực 3 - Bàn 4 ghế
            [
                'table_number' => 'C3',
                'table_type' => '4_seats',
                'description' => 'Bàn có màn chắn riêng tư',
                'tags' => ['riêng tư'],
                'status' => 'available',
                'table_area_id' => $tableAreas->get(3)->id ?? $tableAreas->first()->id,
            ],
            [
                'table_number' => 'C4',
                'table_type' => '4_seats',
                'description' => 'Bàn cạnh nhà vệ sinh',
                'tags' => ['gần WC'],
                'status' => 'available',
                'table_area_id' => $tableAreas->get(3)->id ?? $tableAreas->first()->id,
            ],
            [
                'table_number' => 'C5',
                'table_type' => '4_seats',
                'description' => 'Bàn gần lối thoát hiểm',
                'tags' => ['an toàn'],
                'status' => 'reserved',
                'table_area_id' => $tableAreas->get(3)->id ?? $tableAreas->first()->id,
            ],
            [
                'table_number' => 'C6',
                'table_type' => '4_seats',
                'description' => 'Bàn ở góc khuất',
                'tags' => ['riêng tư', 'ít ồn'],
                'status' => 'occupied',
                'table_area_id' => $tableAreas->get(3)->id ?? $tableAreas->first()->id,
            ],
            [
                'table_number' => 'C7',
                'table_type' => '4_seats',
                'description' => 'Bàn gần khu chuẩn bị món ăn',
                'tags' => ['nhanh', 'gần bếp'],
                'status' => 'available',
                'table_area_id' => $tableAreas->get(4)->id ?? $tableAreas->first()->id,
            ],
            [
                'table_number' => 'C8',
                'table_type' => '4_seats',
                'description' => 'Bàn dành cho cặp đôi',
                'tags' => ['romantic', 'góc riêng'],
                'status' => 'available',
                'table_area_id' => $tableAreas->get(4)->id ?? $tableAreas->first()->id,
            ],
            [
                'table_number' => 'C9',
                'table_type' => '4_seats',
                'description' => 'Bàn có cửa sổ riêng',
                'tags' => ['gần cửa sổ'],
                'status' => null,
                'table_area_id' => $tableAreas->get(4)->id ?? $tableAreas->first()->id,
            ],
            [
                'table_number' => 'C10',
                'table_type' => '4_seats',
                'description' => 'Bàn dành cho khách đặt trước',
                'tags' => ['đặt trước'],
                'status' => 'reserved',
                'table_area_id' => $tableAreas->get(5)->id ?? $tableAreas->first()->id,
            ],

        ];

        foreach ($tableData as &$data) {
            $data['status'] = 'available';
        }
        unset($data); // xóa tham chiếu

        foreach ($tableData as $data) {
            Table::create($data);
        }
    }
}

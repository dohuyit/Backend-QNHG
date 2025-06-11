<?php

namespace Database\Seeders;

use App\Models\TableArea;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TableAreaSeeder extends Seeder
{
    public function run(): void
    {
        $tableAreas = [
            [
                'name' => 'Khu vực VIP',
                'description' => 'Khu vực dành cho khách VIP, không gian riêng tư, sang trọng',
                'capacity' => 20,
                'status' => 'active',
            ],
            [
                'name' => 'Khu vực thường',
                'description' => 'Khu vực dành cho khách thường, không gian thoáng đãng',
                'capacity' => 50,
                'status' => 'active',
            ],
            [
                'name' => 'Khu vực ngoài trời',
                'description' => 'Khu vực dành cho khách thích không gian mở, view đẹp',
                'capacity' => 30,
                'status' => 'active',
            ],
            [
                'name' => 'Khu vực gia đình',
                'description' => 'Khu vực dành cho gia đình, có không gian cho trẻ em',
                'capacity' => 40,
                'status' => 'active',
            ],
            [
                'name' => 'Khu vực bar',
                'description' => 'Khu vực bar, phù hợp cho nhóm nhỏ',
                'capacity' => 15,
                'status' => 'active',
            ],
            [
                'name' => 'Khu vực sự kiện',
                'description' => 'Khu vực dành cho các sự kiện, tiệc tùng',
                'capacity' => 100,
                'status' => 'active',
            ]
        ];

        foreach ($tableAreas as $area) {
            TableArea::updateOrCreate(
                ['name' => $area['name']],
                [
                    'description' => $area['description'],
                    'capacity' => $area['capacity'],
                    'status' => $area['status'],
                ]
            );
        }
    }
}

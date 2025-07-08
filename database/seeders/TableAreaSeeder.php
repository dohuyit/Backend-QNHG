<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TableArea;
use Illuminate\Support\Carbon;

class TableAreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            [
                'name' => 'Tầng 1',
                'capacity' => 30,
            ],
            [
                'name' => 'Tầng 2',
                'capacity' => 25,
            ],
            [
                'name' => 'Tầng 3',
                'capacity' => 20,
            ],
            [
                'name' => 'Ngoài sân',
                'capacity' => 40,
            ],
            [
                'name' => 'Phòng VIP',
                'capacity' => 10,
            ],
        ];

        foreach ($areas as $area) {
            TableArea::create([
                'name' => $area['name'],
                'description' => 'Mô tả cho khu vực ' . $area['name'],
                'status' => 'active', // bạn có thể thay thành 'inactive' nếu cần
                'capacity' => $area['capacity'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}

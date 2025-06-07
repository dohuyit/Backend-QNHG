<?php

namespace Database\Seeders;

use App\Models\AreaTemplate;
use Illuminate\Database\Seeder;

class AreaTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Khu VIP',
                'slug' => 'khu-vip',
                'description' => 'Khu vực dành cho khách VIP',
            ],
            [
                'name' => 'Khu Thường',
                'slug' => 'khu-thuong',
                'description' => 'Khu vực dành cho khách thường',
            ],
            [
                'name' => 'Khu Ngoài Trời',
                'slug' => 'khu-ngoi-troi',
                'description' => 'Khu vực bàn ngoài trời',
            ],
            [
                'name' => 'Khu Gia Đình',
                'slug' => 'khu-gia-dinh',
                'description' => 'Khu vực dành cho gia đình',
            ],
        ];

        foreach ($templates as $template) {
            AreaTemplate::create($template);
        }
    }
}

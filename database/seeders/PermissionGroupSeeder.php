<?php

namespace Database\Seeders;

use App\Models\PermissionGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            ['group_name' => 'Người dùng', 'description' => 'Quản lý người dùng hệ thống'],
            ['group_name' => 'Danh mục', 'description' => 'Quản lý danh mục món ăn'],
            ['group_name' => 'Món ăn', 'description' => 'Quản lý món ăn'],
            ['group_name' => 'Combo', 'description' => 'Quản lý combo món ăn'],
            ['group_name' => 'Đơn hàng', 'description' => 'Quản lý đơn hàng và món trong đơn'],
            ['group_name' => 'Đặt bàn', 'description' => 'Quản lý đặt bàn'],
            ['group_name' => 'Bàn', 'description' => 'Quản lý bàn ăn'],
            ['group_name' => 'Khu vực bàn', 'description' => 'Quản lý khu vực bàn'],
            ['group_name' => 'Bếp', 'description' => 'Quản lý đơn bếp và cập nhật trạng thái món ăn'],
            ['group_name' => 'Vai trò & Quyền', 'description' => 'Phân quyền và vai trò hệ thống'],
            ['group_name' => 'Khách hàng', 'description' => 'Quản lý khách hàng'],
        ];

        foreach ($groups as $group) {
            PermissionGroup::firstOrCreate(
                ['group_name' => $group['group_name']],
                ['description' => $group['description']]
            );
        }
    }

}

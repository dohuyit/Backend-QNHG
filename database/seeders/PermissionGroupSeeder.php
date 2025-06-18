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
            ['group_name' => 'Quản lý người dùng', 'description' => 'Quản lý người dùng'],
            ['group_name' => 'Quản lý vai trò', 'description' => 'Quản lý vai trò'],
        ];

        foreach ($groups as $group) {
            PermissionGroup::create($group);
        }
    }
}
<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['role_name' => 'Admin', 'description' => 'Toàn quyền hệ thống'],
            ['role_name' => 'Nhân viên', 'description' => 'Quản lý cơ bản'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
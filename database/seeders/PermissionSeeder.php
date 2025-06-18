<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['permission_name' => 'Xem người dùng', 'slug' => 'view-users', 'permission_group_id' => 1],
            ['permission_name' => 'Thêm người dùng', 'slug' => 'create-users', 'permission_group_id' => 1],
            ['permission_name' => 'Xem vai trò', 'slug' => 'view-roles', 'permission_group_id' => 2],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}

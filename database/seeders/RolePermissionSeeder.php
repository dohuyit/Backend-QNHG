<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $adminRoleId = 1;
        $allPermissionIds = \App\Models\Permission::pluck('id');

        $adminRole = \App\Models\Role::find($adminRoleId);
        if ($adminRole) {
            $adminRole->permissions()->syncWithoutDetaching($allPermissionIds);
        }

        // Gán quyền giới hạn cho Staff như cũ
        $staffRoleId = 2;
        $permissionNames = [
            'order.view',
            'order.create',
            'order.update',
            'order.item-status.update',
            'dish.view',
            'reservation.view',
            'reservation.create',
            'reservation.update',
            'reservation.confirm',
            'table.view',
            'table-area.view',
            'combo.view',
            'category.view',
            'dashboard.view',
        ];
        $permissionIds = \App\Models\Permission::whereIn('permission_name', $permissionNames)->pluck('id');
        $staffRole = \App\Models\Role::find($staffRoleId);
        if ($staffRole) {
            $staffRole->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}
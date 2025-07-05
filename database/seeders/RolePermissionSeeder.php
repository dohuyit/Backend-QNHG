<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Gán toàn bộ quyền cho Admin (role_id = 1)
        $adminRoleId = 1;
        $allPermissionIds = Permission::pluck('id');
        $adminRole = Role::find($adminRoleId);
        if ($adminRole) {
            $adminRole->permissions()->syncWithoutDetaching($allPermissionIds);
        }

        // Gán quyền giới hạn cho Nhân viên (role_id = 2)
        $staffRoleId = 2;
        $staffPermissionNames = [
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
        $staffPermissionIds = Permission::whereIn('permission_name', $staffPermissionNames)->pluck('id');
        $staffRole = Role::find($staffRoleId);
        if ($staffRole) {
            $staffRole->permissions()->syncWithoutDetaching($staffPermissionIds);
        }

        // Gán quyền cho Quản lý bếp (role_id = 3)
        $kitchenRoleId = 3;
        $kitchenPermissionNames = [
            'dashboard.view',
            'kitchen-order.view',
            'kitchen-order.update-status',
            'kitchen-order.cancel',
            'order.item-status.update', // nếu bếp được cập nhật trạng thái món
        ];
        $kitchenPermissionIds = Permission::whereIn('permission_name', $kitchenPermissionNames)->pluck('id');
        $kitchenRole = Role::find($kitchenRoleId);
        if ($kitchenRole) {
            $kitchenRole->permissions()->syncWithoutDetaching($kitchenPermissionIds);
        }
    }
}
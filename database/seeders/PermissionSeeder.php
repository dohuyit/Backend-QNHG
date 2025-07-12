<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionGroup;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $groupMap = PermissionGroup::pluck('id', 'group_name');

        $permissions = [
            // ===== Người dùng =====
            ['permission_name' => 'user.view', 'description' => 'Xem danh sách người dùng', 'group' => 'Người dùng'],
            ['permission_name' => 'user.create', 'description' => 'Tạo người dùng', 'group' => 'Người dùng'],
            ['permission_name' => 'user.update', 'description' => 'Cập nhật người dùng', 'group' => 'Người dùng'],
            ['permission_name' => 'user.delete', 'description' => 'Xóa người dùng', 'group' => 'Người dùng'],
            ['permission_name' => 'user.block', 'description' => 'Chặn / Bỏ chặn người dùng', 'group' => 'Người dùng'],
            ['permission_name' => 'user.count-by-status', 'description' => 'Thống kê người dùng theo trạng thái', 'group' => 'Người dùng'],

            // ===== Danh mục =====
            ['permission_name' => 'category.view', 'description' => 'Xem danh mục', 'group' => 'Danh mục'],
            ['permission_name' => 'category.create', 'description' => 'Tạo danh mục', 'group' => 'Danh mục'],
            ['permission_name' => 'category.update', 'description' => 'Cập nhật danh mục', 'group' => 'Danh mục'],
            ['permission_name' => 'category.delete', 'description' => 'Xóa danh mục', 'group' => 'Danh mục'],
            ['permission_name' => 'category.count-by-status', 'description' => 'Thống kê danh mục theo trạng thái', 'group' => 'Danh mục'],

            // ===== Món ăn =====
            ['permission_name' => 'dish.view', 'description' => 'Xem món ăn', 'group' => 'Món ăn'],
            ['permission_name' => 'dish.create', 'description' => 'Tạo món ăn', 'group' => 'Món ăn'],
            ['permission_name' => 'dish.update', 'description' => 'Cập nhật món ăn', 'group' => 'Món ăn'],
            ['permission_name' => 'dish.delete', 'description' => 'Xóa món ăn', 'group' => 'Món ăn'],
            ['permission_name' => 'dish.view-by-category', 'description' => 'Xem món ăn theo danh mục', 'group' => 'Món ăn'],
            ['permission_name' => 'dish.count-by-status', 'description' => 'Thống kê món ăn theo trạng thái', 'group' => 'Món ăn'],

            // ===== Combo =====
            ['permission_name' => 'combo.view', 'description' => 'Xem combo', 'group' => 'Combo'],
            ['permission_name' => 'combo.create', 'description' => 'Tạo combo', 'group' => 'Combo'],
            ['permission_name' => 'combo.update', 'description' => 'Cập nhật combo', 'group' => 'Combo'],
            ['permission_name' => 'combo.delete', 'description' => 'Xóa combo', 'group' => 'Combo'],
            ['permission_name' => 'combo.count-by-status', 'description' => 'Thống kê combo theo trạng thái', 'group' => 'Combo'],
            ['permission_name' => 'combo.item.add', 'description' => 'Thêm món vào combo', 'group' => 'Combo'],
            ['permission_name' => 'combo.item.update-quantity', 'description' => 'Cập nhật số lượng món trong combo', 'group' => 'Combo'],

            // ===== Đơn hàng =====
            ['permission_name' => 'order.view', 'description' => 'Xem danh sách đơn hàng', 'group' => 'Đơn hàng'],
            ['permission_name' => 'order.create', 'description' => 'Tạo đơn hàng', 'group' => 'Đơn hàng'],
            ['permission_name' => 'order.update', 'description' => 'Cập nhật đơn hàng', 'group' => 'Đơn hàng'],
            ['permission_name' => 'order.delete', 'description' => 'Xóa đơn hàng', 'group' => 'Đơn hàng'],
            ['permission_name' => 'order.item-status.update', 'description' => 'Cập nhật trạng thái món', 'group' => 'Đơn hàng'],
            ['permission_name' => 'order.pay', 'description' => 'Thanh toán đơn hàng', 'group' => 'Đơn hàng'],
            ['permission_name' => 'order.count-by-status', 'description' => 'Thống kê đơn hàng theo trạng thái', 'group' => 'Đơn hàng'],

            // ===== Đặt bàn =====
            ['permission_name' => 'reservation.view', 'description' => 'Xem đặt bàn', 'group' => 'Đặt bàn'],
            ['permission_name' => 'reservation.create', 'description' => 'Tạo đặt bàn', 'group' => 'Đặt bàn'],
            ['permission_name' => 'reservation.update', 'description' => 'Cập nhật đặt bàn', 'group' => 'Đặt bàn'],
            ['permission_name' => 'reservation.confirm', 'description' => 'Xác nhận đặt bàn', 'group' => 'Đặt bàn'],
            ['permission_name' => 'reservation.count-by-status', 'description' => 'Thống kê đặt bàn theo trạng thái', 'group' => 'Đặt bàn'],

            // ===== Bàn ăn =====
            ['permission_name' => 'table.view', 'description' => 'Xem bàn ăn', 'group' => 'Bàn'],
            ['permission_name' => 'table.create', 'description' => 'Tạo bàn ăn', 'group' => 'Bàn'],
            ['permission_name' => 'table.update', 'description' => 'Cập nhật bàn ăn', 'group' => 'Bàn'],
            ['permission_name' => 'table.delete', 'description' => 'Xóa bàn ăn', 'group' => 'Bàn'],
            ['permission_name' => 'table.count-by-status', 'description' => 'Thống kê bàn ăn theo trạng thái', 'group' => 'Bàn'],

            // ===== Khu vực bàn =====
            ['permission_name' => 'table-area.view', 'description' => 'Xem khu vực bàn', 'group' => 'Khu vực bàn'],
            ['permission_name' => 'table-area.create', 'description' => 'Tạo khu vực bàn', 'group' => 'Khu vực bàn'],
            ['permission_name' => 'table-area.update', 'description' => 'Cập nhật khu vực bàn', 'group' => 'Khu vực bàn'],
            ['permission_name' => 'table-area.delete', 'description' => 'Xóa khu vực bàn', 'group' => 'Khu vực bàn'],
            ['permission_name' => 'table-area.count-by-status', 'description' => 'Thống kê khu vực bàn theo trạng thái', 'group' => 'Khu vực bàn'],

            // ===== Đơn bếp =====
            ['permission_name' => 'kitchen-order.view', 'description' => 'Xem đơn bếp', 'group' => 'Đơn hàng'],
            ['permission_name' => 'kitchen-order.update-status', 'description' => 'Cập nhật trạng thái đơn bếp', 'group' => 'Đơn hàng'],
            ['permission_name' => 'kitchen-order.cancel', 'description' => 'Hủy đơn bếp', 'group' => 'Đơn hàng'],
            ['permission_name' => 'kitchen-order.count-by-status', 'description' => 'Thống kê đơn bếp theo trạng thái', 'group' => 'Đơn hàng'],

            // ===== Vai trò & Quyền =====
            ['permission_name' => 'role.view', 'description' => 'Xem vai trò', 'group' => 'Vai trò & Quyền'],
            ['permission_name' => 'role.create', 'description' => 'Tạo vai trò', 'group' => 'Vai trò & Quyền'],
            ['permission_name' => 'role.update', 'description' => 'Cập nhật vai trò', 'group' => 'Vai trò & Quyền'],
            ['permission_name' => 'role.delete', 'description' => 'Xóa vai trò', 'group' => 'Vai trò & Quyền'],
            ['permission_name' => 'permission.view', 'description' => 'Xem quyền', 'group' => 'Vai trò & Quyền'],
            ['permission_name' => 'permission.create', 'description' => 'Tạo quyền', 'group' => 'Vai trò & Quyền'],
            ['permission_name' => 'permission.update', 'description' => 'Cập nhật quyền', 'group' => 'Vai trò & Quyền'],
            ['permission_name' => 'permission.delete', 'description' => 'Xóa quyền', 'group' => 'Vai trò & Quyền'],
            ['permission_name' => 'user-role.assign', 'description' => 'Gán vai trò cho người dùng', 'group' => 'Vai trò & Quyền'],
            ['permission_name' => 'role-permission.assign', 'description' => 'Gán quyền cho vai trò', 'group' => 'Vai trò & Quyền'],
            ['permission_name' => 'dashboard.view', 'description' => 'Xem trang Dashboard', 'group' => 'Vai trò & Quyền'],
            [
                'permission_name' => 'permission_group.view',
                'description' => 'Xem nhóm quyền',
                'group' => 'Vai trò & Quyền'
            ],
            [
                'permission_name' => 'user_role.view',
                'description' => 'Xem nhân viên (vai trò người dùng)',
                'group' => 'Vai trò & Quyền'
            ],
            // Thêm vào cuối danh sách:
            [
                'permission_name' => 'customer.view',
                'description' => 'Xem danh sách khách hàng',
                'group' => 'Khách hàng'
            ],
            [
                'permission_name' => 'customer.update',
                'description' => 'Cập nhật thông tin khách hàng',
                'group' => 'Khách hàng'
            ],
            [
                'permission_name' => 'customer.delete',
                'description' => 'Xóa khách hàng',
                'group' => 'Khách hàng'
            ],
            [
                'permission_name' => 'customer.count-by-status',
                'description' => 'Thống kê khách hàng theo trạng thái',
                'group' => 'Khách hàng'
            ],

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['permission_name' => $permission['permission_name']],
                [
                    'description' => $permission['description'],
                    'permission_group_id' => $groupMap[$permission['group']] ?? null,
                ]
            );
        }
    }
}

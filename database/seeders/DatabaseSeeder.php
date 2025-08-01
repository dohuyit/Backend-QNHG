<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Nhóm các bảng không phụ thuộc
            CategorySeeder::class,
            CustomerSeeder::class,
            TableAreaSeeder::class,
            TableSeeder::class,


            UserSeeder::class,
            // Seed dish trước combo
            DishSeeder::class,

            ComboSeeder::class,

            // combo_items cần cả combo + dish
            ComboItemSeeder::class,

            // Order có thể cần dish/customer (tùy logic bạn)
            OrderSeeder::class,
            OrderItemSeeder::class,
            // KitchenOrder cần Order + OrderItem


            // Thêm TableOrderSeeder vào đây
            OrderTableSeeder::class,
            KitchenOrderSeeder::class,
            // Reservation cần user, table, customer (phổ biến)
            ReservationSeeder::class,
            PermissionGroupSeeder::class,
            PermissionSeeder::class,

            // Role cần trước để liên kết với permission, user
            RoleSeeder::class,
            RolePermissionSeeder::class,

            // user_roles phải sau khi có user và role
            UserRoleSeeder::class,

            BillSeeder::class,
            BillPaymentSeeder::class,

            CartSeeder::class,
            CartItemSeeder::class,

            DiscountCodeSeeder::class,
        ]);
    }
}

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
<<<<<<< HEAD
        // Seed user trước để dùng cho reservation, user_roles
        User::factory(10)->create();

=======
>>>>>>> df4734f6567b16ae396c30ff2e05b39350c4ba7e
        $this->call([
            // Nhóm các bảng không phụ thuộc
            CategorySeeder::class,
            CustomerSeeder::class,
            TableAreaSeeder::class,
            TableSeeder::class,
            TableAreaSeeder::class, // nếu có

            // Seed dish trước combo
            DishSeeder::class,

            // Combo cần dish
            ComboSeeder::class,

            // combo_items cần cả combo + dish
            ComboItemSeeder::class,

            // Order có thể cần dish/customer (tùy logic bạn)
            OrderSeeder::class,

            // Reservation cần user, table, customer (phổ biến)
            ReservationSeeder::class,
<<<<<<< HEAD

            // Permissions
=======
            UserSeeder::class,
>>>>>>> df4734f6567b16ae396c30ff2e05b39350c4ba7e
            PermissionGroupSeeder::class,
            PermissionSeeder::class,

            // Role cần trước để liên kết với permission, user
            RoleSeeder::class,
            RolePermissionSeeder::class,

            // user_roles phải sau khi có user và role
            UserRoleSeeder::class,
        ]);
    }
}

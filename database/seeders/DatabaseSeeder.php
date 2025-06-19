<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\TableAreaSeeder;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            CustomerSeeder::class,
            TableAreaSeeder::class,
            TableSeeder::class,
            OrderSeeder::class,
            DishSeeder::class,
            ComboSeeder::class,
            ComboItemSeeder::class,
            ReservationSeeder::class,
            UserSeeder::class,
            PermissionGroupSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            RolePermissionSeeder::class,
            UserRoleSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Gán role Admin cho user đầu tiên
        $admin = User::where('email', 'huydonganh2005@gmail.com')->first();

        if ($admin) {
            UserRole::updateOrCreate(
                ['user_id' => $admin->id, 'role_id' => 1],
                ['user_id' => $admin->id, 'role_id' => 1]
            );
        }
    }
}
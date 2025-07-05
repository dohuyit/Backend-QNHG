<?php

namespace Database\Seeders;

use App\Models\UserRole;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userRoles = [
            ['user_id' => 1, 'role_id' => 1], // Gán role Admin cho user có id = 1
        ];

        foreach ($userRoles as $ur) {
            // Tránh tạo trùng bản ghi nếu đã tồn tại
            UserRole::updateOrCreate(
                ['user_id' => $ur['user_id'], 'role_id' => $ur['role_id']],
                $ur
            );
        }
    }
}
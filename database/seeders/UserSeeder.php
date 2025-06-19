<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo 10 user ngẫu nhiên
        User::factory()->count(10)->create();

        // Tạo 1 admin cố định nếu cần
        User::factory()->create([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'status' => User::STATUS_ACTIVE,
        ]);
    }
}

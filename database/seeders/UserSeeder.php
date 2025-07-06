<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo user 1
        User::factory()->create([
            'username' => 'Đỗ Quốc Huy',
            'email' => 'huydonganh2005@gmail.com',
            'password' => Hash::make('123456'),
            'status' => User::STATUS_ACTIVE,
        ]);
    }
}
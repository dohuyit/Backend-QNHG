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
            'username' => 'quanglam',
            'email' => 'quanglam5401@gmail.com',
            'password' => Hash::make('123456'),
            'status' => User::STATUS_ACTIVE,
        ]);
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'username' => 'admin',
                'full_name' => 'admin123',
                'password' => Hash::make('123456'),
                'status' => User::STATUS_ACTIVE,
            ]
        );
    }
}

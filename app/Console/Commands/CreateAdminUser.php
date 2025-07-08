<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'create:admin';
    protected $description = 'Tạo tài khoản admin test';

    public function handle()
    {
        $adminRole = Role::firstOrCreate([
            'role_name' => 'Admin'
        ], [
            'description' => 'Toàn quyền hệ thống',
        ]);

        $user = User::updateOrCreate([
            'email' => 'admin@gmail.com',
            'username' => 'admin', // cần phải có dòng này
        ], [
            'full_name' => 'Admin',
            'password' => Hash::make('admin123'),
        ]);



        // Gán role admin cho user
        $user->roles()->syncWithoutDetaching([$adminRole->id]);

        $this->info('✅ Đã tạo tài khoản admin:');
        $this->line('Email: admin@gmail.com');
        $this->line('Mật khẩu: admin123');
    }
}

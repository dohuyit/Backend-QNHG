<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class GenerateUsersWithRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:generate-with-role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a single random user with role ID 1 or 2';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $role1 = Role::find(1);
        $role2 = Role::find(2);

        if (!$role1 || !$role2) {
            $this->error('Roles with ID 1 and 2 do not exist. Please create them first.');
            return Command::FAILURE;
        }

        $timestamp = now()->format('YmdHis');

        $user = User::create([
            'username'   => "User_{$timestamp}",
            'full_name'  => "User Full Name",
            'email'      => "admin2025@gmail.com",
            'password'   => Hash::make('12341234'),
        ]);

        $assignedRole = rand(1, 2) === 1 ? $role1 : $role2;

        UserRole::create([
            'user_id' => $user->id,
            'role_id' => $assignedRole->id,
        ]);

        $this->info("Created user: {$user->username}, email: {$user->email}, role: {$assignedRole->name}");
        return Command::SUCCESS;
    }
}

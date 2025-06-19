<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'username' => $this->faker->unique()->userName,
            'password' =>  Hash::make('password'),
            'avatar' => null,
            'full_name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone_number' => $this->faker->phoneNumber,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
            'last_login' => $this->faker->dateTimeBetween('-30 days'),
            'remember_token' => Str::random(10),
            'deleted_at' => null,
        ];
    }
}

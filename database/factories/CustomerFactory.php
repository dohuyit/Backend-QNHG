<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name,
            'avatar' => $this->faker->imageUrl(200, 200, 'people'),
            'phone_number' => $this->faker->unique()->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => bcrypt('password'), // Hoặc dùng Hash::make nếu inject được
            'google_id' => null,
            'facebook_id' => null,
            'address' => $this->faker->address,
            'date_of_birth' => $this->faker->date(),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'city_id' => str_pad((string) rand(1, 99), 2, '0', STR_PAD_LEFT),
            'district_id' => str_pad((string) rand(1, 999), 3, '0', STR_PAD_LEFT),
            'ward_id' => str_pad((string) rand(1, 99999), 5, '0', STR_PAD_LEFT),
            'status_customer' => $this->faker->randomElement(['active', 'inactive',  'blocked']),
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

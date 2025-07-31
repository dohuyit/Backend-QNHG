<?php

namespace Database\Seeders;

use App\Models\DiscountCode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DiscountCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DiscountCode::factory()->count(10)->create();
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Table;
use App\Models\User;
use App\Models\Reservation;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        Order::factory()->count(20)->create();
    }
}

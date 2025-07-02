<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\User;
use Illuminate\Database\Seeder;

class BillPaymentSeeder extends Seeder
{
    public function run(): void
    {
        $bills = Bill::limit(15)->get(); 

        foreach ($bills as $bill) {
            $totalPaid = 0;
            $finalAmount = $bill->final_amount;
            $loopTimes = rand(1, 2);

            for ($i = 0; $i < $loopTimes; $i++) {
                $max = $finalAmount - $totalPaid;
                if ($max <= 0) break;

                $amount = fake()->randomFloat(2, 10000, $max);
                $totalPaid += $amount;

                BillPayment::create([
                    'bill_id'         => $bill->id,
                    'payment_method'  => fake()->randomElement(['cash', 'momo', 'vnpay', 'credit_card']),
                    'amount_paid'     => $amount,
                    'payment_time'    => now()->subDays(rand(0, 30)),
                    'transaction_ref' => fake()->optional()->uuid(),
                    'user_id'         => User::inRandomOrder()->value('id'),
                    'notes'           => fake()->optional()->sentence(),
                ]);
            }
        }
    }
}

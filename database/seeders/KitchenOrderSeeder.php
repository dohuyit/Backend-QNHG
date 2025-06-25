<?php
namespace Database\Seeders;

use App\Models\KitchenOrder;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;

class KitchenOrderSeeder extends Seeder
{
    public function run(): void
    {
        $availableOrderItems = OrderItem::doesntHave('kitchenOrder')
            ->with('order', 'menuItem')
            ->get();

        foreach ($availableOrderItems as $item) {
            KitchenOrder::factory()->create();
        }
    }
}

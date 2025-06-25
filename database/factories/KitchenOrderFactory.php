<?php
namespace Database\Factories;

use App\Models\KitchenOrder;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class KitchenOrderFactory extends Factory
{
    protected $model = KitchenOrder::class;
    protected static $usedOrderItemIds = [];

   public function definition(): array
{
    $orderItem = OrderItem::doesntHave('kitchenOrder')
        ->with('order', 'menuItem')
        ->whereNotIn('id', self::$usedOrderItemIds)
        ->inRandomOrder()
        ->first();

    if (!$orderItem || !$orderItem->order || !$orderItem->menuItem) {
        return [];
    }

    self::$usedOrderItemIds[] = $orderItem->id;

    return [
        'order_item_id' => $orderItem->id,
        'order_id' => $orderItem->order_id,
        'table_number' => $orderItem->order->table_number,
        'item_name' => $orderItem->menuItem->name,
        'quantity' => $orderItem->quantity,
        'notes' => $orderItem->notes,
        'status' => $this->faker->randomElement(['pending', 'preparing', 'ready', 'cancelled']),
        'is_priority' => $orderItem->is_priority,
        'received_at' => now()->subMinutes(rand(1, 20)),
        'completed_at' => null,
    ];
}

}
<?php
namespace Database\Factories;

use App\Models\KitchenOrder;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class KitchenOrderFactory extends Factory
{
    protected $model = KitchenOrder::class;

    protected ?OrderItem $customOrderItem = null;

    public function withOrderItem(OrderItem $item): static
    {
        $this->customOrderItem = $item;
        return $this;
    }

    public function definition(): array
    {
        $orderItem = $this->customOrderItem;

        // Nếu chưa truyền vào thì bỏ luôn
        if (!$orderItem || !$orderItem->order || !$orderItem->menuItem) {
            return [];
        }

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

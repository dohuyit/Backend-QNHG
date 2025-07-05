<?php

namespace Database\Factories;

use App\Models\Dish;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\MenuItem;  // Đảm bảo bạn có model MenuItem
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'menu_item_id' => Dish::factory(), // hoặc null nếu là combo
            'combo_id' => null, // nếu cần giả lập combo bạn có thể điều chỉnh
            'quantity' => $this->faker->numberBetween(1, 5),
            'unit_price' => $this->faker->randomFloat(2, 20, 100),
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
            'kitchen_status' => $this->faker->randomElement(['pending', 'preparing', 'ready', 'cancelled']),
            'is_priority' => $this->faker->boolean(20),
        ];
    }
}

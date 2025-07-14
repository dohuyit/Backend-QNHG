<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Combo;
use App\Models\Dish;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CartItem>
 */
class CartItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = CartItem::class;

    public function definition(): array
    {
        // Random chọn 0 = dish, 1 = combo
        $isCombo = rand(0, 1);

        return [
            'cart_id'   => Cart::factory(),
            'dish_id'   => $isCombo ? null : Dish::inRandomOrder()->value('id'),
            'combo_id'  => $isCombo ? Combo::inRandomOrder()->value('id') : null,
            'quantity'  => rand(1, 5),
            'price'     => $this->faker->randomFloat(2, 10, 500),
        ];
    }
}

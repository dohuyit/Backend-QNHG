<?php

namespace Database\Seeders;

use App\Models\ComboItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ComboItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ComboItem::insert([
            [
                'combo_id' => 1,
                'dish_id' => 1,
                'quantity' => 2,
            ],
            [
                'combo_id' => 1,
                'dish_id' => 5,
                'quantity' => 1,
            ],
            [
                'combo_id' => 2,
                'dish_id' => 3,
                'quantity' => 1,
            ],
            [
                'combo_id' => 2,
                'dish_id' => 4,
                'quantity' => 2,
            ],
            [
                'combo_id' => 3,
                'dish_id' => 4,
                'quantity' => 4,
            ],
        ]);
    }
}
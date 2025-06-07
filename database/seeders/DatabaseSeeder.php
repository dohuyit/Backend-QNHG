<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\TableAreaSeeder;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
            AreaTemplateSeeder::class,
            TableAreaSeeder::class,
            CategorySeeder::class
        ]);

        // $this->call(CategorySeeder::class);
        // $this->call(DishSeeder::class);
        $this->call(ComboSeeder::class);

    }
}
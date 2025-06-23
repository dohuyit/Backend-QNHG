<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TableArea;

class TableAreaSeeder extends Seeder
{
    public function run(): void
    {
        TableArea::factory()->count(5)->create();
    }
}

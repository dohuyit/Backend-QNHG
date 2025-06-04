<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\TableArea;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TableAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = Branch::all();

        // if ($branches->isEmpty()) {
        //     // Handle case where there are no branches, maybe create one or log a warning
        //     echo "No branches found. Skipping TableArea seeder.\n";

        //     return;
        // }

        foreach ($branches as $branch) {
            // Seed 5 table areas for each branch
            for ($i = 1; $i <= 5; $i++) {
                $name = 'Khu vuc '.$i.' - '.$branch->name;
                TableArea::create([
                    'branch_id' => $branch->id,
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'description' => 'Mo ta cho khu vuc '.$i.' cua '.$branch->name,
                    'status' => rand(0, 1) ? 'active' : 'inactive',
                    'created_by' => 1, // Assuming user with ID 1 exists
                    'updated_by' => 1, // Assuming user with ID 1 exists
                ]);
            }
        }
    }
}

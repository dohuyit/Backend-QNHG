<?php

namespace Database\Seeders;

use App\Models\TableArea;
use App\Models\Branch;
use App\Models\AreaTemplate;
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
        $templates = AreaTemplate::all();

        foreach ($branches as $branch) {
            foreach ($templates as $template) {
                TableArea::create([
                    'branch_id' => $branch->id,
                    'area_template_id' => $template->id,
                    'name' => $template->name . ' - ' . $branch->name,
                    'slug' => Str::slug($template->name . ' ' . $branch->name),
                    'description' => $template->description,
                    'capacity' => 10,
                    'status' => 'active',
                ]);
            }
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DealStage;

class DealStageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stages = [
            ['name' => 'Baru', 'color' => '#009EF7', 'stage_type' => 'pipeline', 'sort_order' => 1],
            ['name' => 'Follow Up', 'color' => '#FFA800', 'stage_type' => 'pipeline', 'sort_order' => 2],
            ['name' => 'Negosiasi', 'color' => '#7239EA', 'stage_type' => 'pipeline', 'sort_order' => 3],
            ['name' => 'Deal', 'color' => '#50CD89', 'stage_type' => 'won', 'sort_order' => 4],
            ['name' => 'Produksi', 'color' => '#8E33FF', 'stage_type' => 'pipeline', 'sort_order' => 5],
        ];

        foreach ($stages as $stage) {
            DealStage::updateOrCreate(['name' => $stage['name']], $stage);
        }
    }
}

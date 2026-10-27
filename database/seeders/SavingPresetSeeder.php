<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SavingPresetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => '50/30/20',
                'essential_pct' => 50,
                'non_essential_pct' => 30,
                'savings_pct' => 20,
                'is_custom' => false,
            ],
            [
                'name' => '70/20/10',
                'essential_pct' => 70,
                'non_essential_pct' => 20,
                'savings_pct' => 10,
                'is_custom' => false,
            ],
            [
                'name' => 'Personalizado',
                'essential_pct' => 60,
                'non_essential_pct' => 25,
                'savings_pct' => 15,
                'is_custom' => true,
            ],
        ];
        DB::table('saving_presets')->insert($data);
    }
}

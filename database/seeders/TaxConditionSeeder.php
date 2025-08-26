<?php

namespace Database\Seeders;

use App\Models\TaxCondition;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaxConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conditions = [
            ['id' => 1, 'name' => 'Genera crédito IVA', 'code' => '01', 'active' => 1],
            ['id' => 2, 'name' => 'Genera crédito parcial del IVA', 'code' => '02', 'active' => 1],
            ['id' => 3, 'name' => 'Bienes de Capital', 'code' => '03', 'active' => 1],
            ['id' => 4, 'name' => 'Gasto corriente no genera crédito', 'code' => '04', 'active' => 1],
            ['id' => 5, 'name' => 'Proporcionalidad', 'code' => '05', 'active' => 1],
        ];

        foreach ($conditions as $condition) {
            TaxCondition::updateOrCreate(
                ['id' => $condition['id']],
                [
                    'name' => $condition['name'],
                    'code' => $condition['code'],
                    'active' => $condition['active'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }
    }
}

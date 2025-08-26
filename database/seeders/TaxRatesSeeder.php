<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxRatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tax_rates')->insert([
            [
                'id' => 1,
                'name' => 'Tarifa 0% (Artículo 32, num 1, RLIVA)',
                'code' => '01',
                'active' => '1',
                'percent' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Tarifa reducida 1%',
                'code' => '02',
                'active' => '1',
                'percent' => 1.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Tarifa reducida 2%',
                'code' => '03',
                'active' => '1',
                'percent' => 2.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Tarifa reducida 4%',
                'code' => '04',
                'active' => '1',
                'percent' => 4.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Transitorio 0%',
                'code' => '05',
                'active' => '1',
                'percent' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'Transitorio 4%',
                'code' => '06',
                'active' => '1',
                'percent' => 4.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'name' => 'Transitorio 8%',
                'code' => '07',
                'active' => '1',
                'percent' => 8.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'name' => 'Tarifa general 13%',
                'code' => '08',
                'active' => '1',
                'percent' => 13.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id' => 9,
                'name' => 'Tarifa reducida 0.5%',
                'code' => '09',
                'active' => '1',
                'percent' => 0.5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 10,
                'name' => 'Tarifa Exenta',
                'code' => '10',
                'active' => '1',
                'percent' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 11,
                'name' => 'Tarifa 0% sin derecho a crédito',
                'code' => '11',
                'active' => '1',
                'percent' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

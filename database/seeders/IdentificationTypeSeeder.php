<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IdentificationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('identification_types')->insert([
            [
                'id' => 1,
                'name' => 'Cédula Física',
                'code' => '01',
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Cédula Jurídica',
                'code' => '02',
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'DIMEX',
                'code' => '03',
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'NITE',
                'code' => '04',
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Extranjero No Domiciliado',
                'code' => '05',
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'No Contribuyente',
                'code' => '06',
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}

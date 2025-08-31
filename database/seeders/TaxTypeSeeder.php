<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tax_types')->insert([
            [
                'id' => 1,
                'name' => 'Impuesto al Valor Agregado',
                'code' => '01',
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Impuesto Selectivo de Consumo',
                'code' => '02',
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Impuesto Único a los Combustibles',
                'code' => '03',
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Impuesto específico de Bebidas Alcohólicas',
                'code' => '04',
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Impuesto Específico sobre las bebidas envasadas sin contenido alcohólico y jabones de tocador',
                'code' => '05',
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'Impuesto a los Productos de Tabaco',
                'code' => '06',
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'name' => 'IVA (cálculo especial)',
                'code' => '07',
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'name' => 'IVA Régimen de Bienes Usados (Factor)',
                'code' => '08',
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'name' => 'Impuesto Específico al Cemento',
                'code' => '09',
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 99,
                'name' => 'Otros',
                'code' => '09',
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

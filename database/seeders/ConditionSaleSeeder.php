<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConditionSaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('condition_sales')->insert([
            [
                'id' => 1,
                'name' => 'Contado',
                'code' => '01',
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Crédito',
                'code' => '02',
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Consignación',
                'code' => '03',
                'active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Apartado',
                'code' => '04',
                'active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Arrendamiento con opción de compra',
                'code' => '05',
                'active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'Arrendamiento en función financiera',
                'code' => '06',
                'active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'name' => 'Cobro a favor de un tercero',
                'code' => '07',
                'active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'name' => 'Servicios prestados al Estado',
                'code' => '08',
                'active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'name' => 'Pago de servicios prestado al Estado',
                'code' => '09',
                'active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 10,
                'name' => 'Venta a crédito en IVA hasta 90 días (Artículo 27, LIVA)',
                'code' => '10',
                'active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 11,
                'name' => 'Pago de venta a crédito en IVA hasta 90 días (Artículo 27, LIVA)',
                'code' => '11',
                'active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 12,
                'name' => 'Venta Mercancía No Nacionalizada',
                'code' => '12',
                'active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 13,
                'name' => 'Venta Bienes Usados No Contribuyente',
                'code' => '13',
                'active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 14,
                'name' => 'Arrendamiento Operativo',
                'code' => '14',
                'active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 15,
                'name' => 'Arrendamiento Financiero',
                'code' => '15',
                'active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 99,
                'name' => 'Otros (se debe indicar la condición de la venta)',
                'code' => '99',
                'active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}

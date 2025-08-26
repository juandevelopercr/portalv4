<?php

namespace Database\Seeders;

use App\Models\FactoryLevelTax;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FactoryLevelTaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxes = [
            [
                'name' => 'Venta de bienes con IVA según el sistema especial de determinación de IVA a nivel de fábrica',
                'code' => '01'
            ],
            [
                'name' => 'Ventas exentas según el sistema especial de determinación de IVA a nivel de fábrica, mayorista y aduanas',
                'code' => '02'
            ],
        ];

        foreach ($taxes as $tax) {
            FactoryLevelTax::updateOrCreate(
                ['code' => $tax['code']],
                [
                    'name' => $tax['name'],
                    'active' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }
    }
}

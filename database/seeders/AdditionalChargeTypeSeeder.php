<?php

namespace Database\Seeders;

use App\Models\AdditionalChargeType;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdditionalChargeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $charges = [
            ['id' => 1, 'name' => 'Contribución parafiscal', 'code' => '01', 'active' => 1],
            ['id' => 2, 'name' => 'Timbre de la Cruz Roja', 'code' => '02', 'active' => 1],
            ['id' => 3, 'name' => 'Timbre de Benemérito Cuerpo de Bomberos de Costa Rica', 'code' => '03', 'active' => 1],
            ['id' => 4, 'name' => 'Cobro de un tercero', 'code' => '04', 'active' => 1],
            ['id' => 5, 'name' => 'Costos de Exportación', 'code' => '05', 'active' => 1],
            ['id' => 6, 'name' => 'Impuesto de servicio 10%', 'code' => '06', 'active' => 1],
            ['id' => 7, 'name' => 'Timbre de Colegios Profesionales', 'code' => '07', 'active' => 1],
            ['id' => 8, 'name' => 'Depósitos de Garantía', 'code' => '08', 'active' => 1],
            ['id' => 9, 'name' => 'Multas o Penalizaciones', 'code' => '09', 'active' => 1],
            ['id' => 10, 'name' => 'Intereses Moratorios', 'code' => '10', 'active' => 1],
            ['id' => 99, 'name' => 'Otros Cargos', 'code' => '99', 'active' => 1],
        ];

        foreach ($charges as $charge) {
            AdditionalChargeType::updateOrCreate(
                ['id' => $charge['id']],
                [
                    'name' => $charge['name'],
                    'code' => $charge['code'],
                    'active' => $charge['active'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }
    }
}

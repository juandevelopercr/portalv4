<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AreasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            ['id' => 1, 'name' => 'ADJUDICADOS'],
            ['id' => 2, 'name' => 'ALIVIO FINANCIERO'],
            ['id' => 3, 'name' => 'BANCA PERSONAS'],
            ['id' => 6, 'name' => 'BONO'],
            ['id' => 7, 'name' => 'COBRO / COBRO JUDICIAL'],
            ['id' => 8, 'name' => 'HIPOTECARIO'],
            ['id' => 9, 'name' => 'INCOBRABLE / COBRO JUDICIAL'],
            ['id' => 10, 'name' => 'ADMINISTRATIVO'],
            ['id' => 11, 'name' => 'CONTABILIDAD'],
            ['id' => 12, 'name' => 'VENTAS'],
            ['id' => 13, 'name' => 'GERENCIA'],
            ['id' => 14, 'name' => 'SWITCHING'],
            ['id' => 15, 'name' => 'ACTAS NOTARIALES / SORTEOS'],
            ['id' => 16, 'name' => 'EJECUTIVO'],
        ];

        foreach ($areas as $area) {
            Area::updateOrCreate(['id' => $area['id']], $area);
        }
    }
}

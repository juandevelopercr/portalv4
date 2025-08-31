<?php

namespace Database\Seeders;

use App\Models\CodigoContable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CodigoContableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CodigoContable::insert([
            ['codigo' => '04-XX-YYY-100-100', 'descrip' => 'INGRESOS POR SERVICIOS PROFESIONALES A SCOTIABANK'],
            ['codigo' => '04-XX-YYY-200-000', 'descrip' => 'INGRESOS POR SERVICIOS PROFESIONALES A TERCEROS'],
        ]);
    }
}

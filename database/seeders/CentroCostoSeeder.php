<?php

namespace Database\Seeders;

use App\Models\CentroCosto;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CentroCostoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CentroCosto::insert([
            ['codigo' => '01', 'descrip' => 'BANCA RETAIL NORMAL', 'mcorto' => 'RETAIL', 'codcont' => '04-XX-100-100-100', 'favorite' => 0],
            ['codigo' => '02', 'descrip' => 'BANCA CORPORATIVA', 'mcorto' => 'B.CORP', 'codcont' => '04-XX-100-200-000', 'favorite' => 0],
            ['codigo' => '03', 'descrip' => 'PROPIEDAD INTELECTUAL', 'mcorto' => 'P.INT', 'codcont' => '04-XX-100-200-000', 'favorite' => 0],
            ['codigo' => '04', 'descrip' => 'OTROS / RAROS', 'mcorto' => 'OTROS/RAROS', 'codcont' => '04-XX-100-200-000', 'favorite' => 0],
            ['codigo' => '05', 'descrip' => 'CORPORATIVO', 'mcorto' => 'CORP', 'codcont' => '04-XX-100-200-000', 'favorite' => 0],
            ['codigo' => '06', 'descrip' => 'REAL ESTATE', 'mcorto' => 'R.ESTATE', 'codcont' => '04-XX-100-200-000', 'favorite' => 0],
            ['codigo' => '07', 'descrip' => 'LITIGIOS', 'mcorto' => 'LIT', 'codcont' => '04-XX-100-200-000', 'favorite' => 0],
            ['codigo' => '08', 'descrip' => 'OTROS (TAX, CCC, VARIOS)', 'mcorto' => 'OTROS (TAX, CCC, VARIOS)', 'codcont' => null, 'favorite' => 0],
            ['codigo' => '10', 'descrip' => 'JUNTA DIRECTIVA DE SOCIOS', 'mcorto' => 'JDS', 'codcont' => null, 'favorite' => 0],
            ['codigo' => '14', 'descrip' => 'DAVID ARTURO CAMPOS BRENES', 'mcorto' => 'DAC', 'codcont' => null, 'favorite' => 0],
            ['codigo' => '15', 'descrip' => 'ROLANDO LACLE CASTRO', 'mcorto' => 'RLC', 'codcont' => null, 'favorite' => 0],
            ['codigo' => '16', 'descrip' => 'ROLANDO LACLE ZUÑIGA', 'mcorto' => 'RLZ', 'codcont' => null, 'favorite' => 0],
            ['codigo' => '17', 'descrip' => 'MARIO QUESADA BIANCHINNI', 'mcorto' => 'MQB', 'codcont' => null, 'favorite' => 0],
            ['codigo' => '19', 'descrip' => 'DERECHO PUBLICO', 'mcorto' => 'PUBLICO', 'codcont' => null, 'favorite' => 0],
            ['codigo' => '20', 'descrip' => 'LABORAL', 'mcorto' => 'LABORAL', 'codcont' => null, 'favorite' => 0],
            ['codigo' => '21', 'descrip' => 'BURSATIL', 'mcorto' => 'BURSATIL', 'codcont' => null, 'favorite' => 0],
            ['codigo' => '23', 'descrip' => 'RETAIL COMERCIAL', 'mcorto' => 'R.COMER', 'codcont' => null, 'favorite' => 0],
            ['codigo' => '24', 'descrip' => 'MIGRATORIO', 'mcorto' => 'MIGRA', 'codcont' => null, 'favorite' => 0],
            ['codigo' => '09', 'descrip' => 'ADMINISTRACION Y OPERACIONES', 'mcorto' => 'Adm', 'codcont' => null, 'favorite' => 0],
            ['codigo' => '13', 'descrip' => 'LAFISE', 'mcorto' => 'LAFISE', 'codcont' => null, 'favorite' => 0],
            ['codigo' => '00', 'descrip' => '--VACIO', 'mcorto' => 'VACIO', 'codcont' => null, 'favorite' => 1],
            ['codigo' => '30', 'descrip' => 'CRUCES INTERNOS', 'mcorto' => 'C.INTERNOS', 'codcont' => null, 'favorite' => 0],
        ]);
    }
}

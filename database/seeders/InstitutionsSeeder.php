<?php

namespace Database\Seeders;

use App\Models\Institution;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InstitutionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $institutions = [
            ['name' => 'Ministerio de Hacienda', 'code' => '01'],
            ['name' => 'Ministerio de Relaciones Exteriores y Culto', 'code' => '02'],
            ['name' => 'Ministerio de Agricultura y Ganadería', 'code' => '03'],
            ['name' => 'Ministerio de Economía, Industria y Comercio', 'code' => '04'],
            ['name' => 'Cruz Roja Costarricense', 'code' => '05'],
            ['name' => 'Benemérito Cuerpo de Bomberos de Costa Rica', 'code' => '06'],
            ['name' => 'Asociación Obras del Espíritu Santo', 'code' => '07'],
            ['name' => 'Federación Cruzada Nacional de protección al Anciano (Fecrunapa)', 'code' => '08'],
            ['name' => 'Escuela de Agricultura de la Región Húmeda (EARTH)', 'code' => '09'],
            ['name' => 'Instituto Centroamericano de Administración de Empresas (INCAE)', 'code' => '10'],
            ['name' => 'Junta de Protección Social (JPS)', 'code' => '11'],
            ['name' => 'Autoridad Reguladora de los Servicios Públicos (Aresep)', 'code' => '12'],
            ['name' => 'Otros', 'code' => '99'],
        ];

        foreach ($institutions as $institution) {
            Institution::create($institution);
        }
    }
}

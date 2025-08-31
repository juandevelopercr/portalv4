<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExonerationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('exoneration_types')->insert([
            [
                'id' => 1,
                'name' => 'Compras autorizadas por la Dirección General de Tributación',
                'code' => '01',
                'description' => NULL,
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Ventas exentas a diplomáticos ',
                'code' => '02',
                'description' => NULL,
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Autorizado por Ley especial',
                'code' => '03',
                'description' => NULL,
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Exenciones Dirección General de Hacienda Autorización Local Genérica',
                'code' => '04',
                'description' => NULL,
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Exenciones Dirección General de Hacienda Transitorio V (servicios de ingeniería, arquitectura, topografía obra civil)',
                'code' => '05',
                'description' => NULL,
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'Servicios turísticos inscritos ante el Instituto Costarricense de Turismo (ICT)',
                'code' => '06',
                'description' => NULL,
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'name' => 'Transitorio XVII (Recolección, Clasificación, almacenamiento de Reciclaje y reutilizable)',
                'code' => '07',
                'description' => NULL,
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'name' => 'Exoneración a Zona Franca',
                'code' => '08',
                'description' => NULL,
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'name' => 'Exoneración de servicios complementarios para la exportación articulo 11 RLIVA',
                'code' => '09',
                'description' => NULL,
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 10,
                'name' => 'Órgano de las corporaciones municipales',
                'code' => '10',
                'description' => NULL,
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 11,
                'name' => 'Exenciones Dirección General de Hacienda Autorización de Impuesto Local Concreta',
                'code' => '11',
                'description' => NULL,
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 99,
                'name' => 'Otros',
                'code' => '99',
                'description' => NULL,
                'active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}

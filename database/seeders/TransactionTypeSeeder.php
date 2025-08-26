<?php

namespace Database\Seeders;

use App\Models\TransactionType;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Venta Normal de Bienes y Servicios (Transacción General)', 'code' => '01'],
            ['name' => 'Mercancía de Autoconsumo exento', 'code' => '02'],
            ['name' => 'Mercancía de Autoconsumo gravado', 'code' => '03'],
            ['name' => 'Servicio de Autoconsumo exento', 'code' => '04'],
            ['name' => 'Servicio de Autoconsumo gravado', 'code' => '05'],
            ['name' => 'Cuota de afiliación', 'code' => '06'],
            ['name' => 'Cuota de afiliación Exenta', 'code' => '07'],
            ['name' => 'Bienes de Capital para el emisor', 'code' => '08'],
            ['name' => 'Bienes de Capital para el receptor.', 'code' => '09'],
            ['name' => 'Bienes de Capital para el emisor y el receptor.', 'code' => '10'],
            ['name' => 'Bienes de capital de autoconsumo exento para el emisor', 'code' => '11'],
            ['name' => 'Bienes de capital sin contraprestación a terceros exento para el emisor', 'code' => '12'],
            ['name' => 'Sin contraprestación a terceros', 'code' => '13'],
        ];

        foreach ($types as $type) {
            TransactionType::updateOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'active' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }
    }
}

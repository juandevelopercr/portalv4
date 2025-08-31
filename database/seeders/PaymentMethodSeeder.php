<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['id' => 1, 'name' => 'Efectivo', 'code' => '01', 'active' => 1],
            ['id' => 2, 'name' => 'Tarjeta', 'code' => '02', 'active' => 1],
            ['id' => 3, 'name' => 'Cheque', 'code' => '03', 'active' => 1],
            ['id' => 4, 'name' => 'Transferencia – depósito bancario', 'code' => '04', 'active' => 1],
            ['id' => 5, 'name' => 'Recaudado por terceros', 'code' => '05', 'active' => 1],
            ['id' => 6, 'name' => 'SINPE MOVIL', 'code' => '06', 'active' => 1],
            ['id' => 7, 'name' => 'Plataforma Digital', 'code' => '07', 'active' => 1],
            ['id' => 99, 'name' => 'Otros (se debe indicar el medio de pago)', 'code' => '99', 'active' => 1],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['id' => $method['id']],
                [
                    'name' => $method['name'],
                    'code' => $method['code'],
                    'active' => $method['active'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }
    }
}

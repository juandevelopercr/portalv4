<?php

namespace Database\Seeders;

use App\Models\DiscountType;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DiscountTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $discounts = [
            ['id' => 1, 'name' => 'Descuento por Regalía', 'code' => '01'],
            ['id' => 2, 'name' => 'Descuento por Regalía IVA Cobrado al Cliente', 'code' => '02'],
            ['id' => 3, 'name' => 'Descuento por Bonificación', 'code' => '03'],
            ['id' => 4, 'name' => 'Descuento por volumen', 'code' => '04'],
            ['id' => 5, 'name' => 'Descuento por Temporada (estacional)', 'code' => '05'],
            ['id' => 6, 'name' => 'Descuento promocional', 'code' => '06'],
            ['id' => 7, 'name' => 'Descuento Comercial', 'code' => '07'],
            ['id' => 8, 'name' => 'Descuento por frecuencia', 'code' => '08'],
            ['id' => 9, 'name' => 'Descuento sostenido', 'code' => '09'],
            ['id' => 99, 'name' => 'Otros descuentos', 'code' => '99'],
        ];

        foreach ($discounts as $discount) {
            DiscountType::updateOrCreate(
                ['code' => $discount['code']],
                [
                    'name' => $discount['name'],
                    'active' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }
    }
}

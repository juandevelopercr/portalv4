<?php

namespace Database\Seeders;

use App\Models\ProductServiceCode;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductServiceCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $codes = [
            ['id' => 1, 'name' => 'Código del producto del vendedor', 'code' => '01', 'active' => 1],
            ['id' => 2, 'name' => 'Código del producto del comprador', 'code' => '02', 'active' => 1],
            ['id' => 3, 'name' => 'Código del producto asignado por el fabricante – industriales o importadores', 'code' => '03', 'active' => 1],
            ['id' => 4, 'name' => 'Código uso interno', 'code' => '04', 'active' => 1],
            ['id' => 99, 'name' => 'Otros', 'code' => '99', 'active' => 1],
        ];

        foreach ($codes as $code) {
            ProductServiceCode::updateOrCreate(
                ['id' => $code['id']],
                [
                    'name' => $code['name'],
                    'code' => $code['code'],
                    'active' => $code['active'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }
    }
}

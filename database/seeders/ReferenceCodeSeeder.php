<?php

namespace Database\Seeders;

use App\Models\ReferenceCode;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReferenceCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $codes = [
            ['id' => 1, 'name' => 'Anula Documento de Referencia', 'code' => '01', 'active' => 1],
            ['id' => 2, 'name' => 'Corrige monto', 'code' => '02', 'active' => 1],
            ['id' => 4, 'name' => 'Referencia a otro documento', 'code' => '04', 'active' => 1],
            ['id' => 5, 'name' => 'Sustituye comprobante provisional por contingencia', 'code' => '05', 'active' => 1],
            ['id' => 6, 'name' => 'Devolución de mercancía', 'code' => '06', 'active' => 1],
            ['id' => 7, 'name' => 'Sustituye comprobante electrónico', 'code' => '07', 'active' => 1],
            ['id' => 8, 'name' => 'Factura Endosada', 'code' => '08', 'active' => 1],
            ['id' => 9, 'name' => 'Nota de crédito financiera', 'code' => '09', 'active' => 1],
            ['id' => 10, 'name' => 'Nota de débito financiera', 'code' => '10', 'active' => 1],
            ['id' => 11, 'name' => 'Proveedor No Domiciliado', 'code' => '11', 'active' => 1],
            ['id' => 12, 'name' => 'Crédito por exoneración posterior a la facturación', 'code' => '12', 'active' => 1],
            ['id' => 99, 'name' => 'Otro', 'code' => '12', 'active' => 1],
        ];

        foreach ($codes as $code) {
            ReferenceCode::updateOrCreate(
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

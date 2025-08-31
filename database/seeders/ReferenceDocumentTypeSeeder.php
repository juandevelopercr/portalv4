<?php

namespace Database\Seeders;

use App\Models\ReferenceDocumentType;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReferenceDocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documentTypes = [
            ['id' => 1, 'name' => 'Factura electrónica', 'code' => '01', 'active' => 1],
            ['id' => 2, 'name' => 'Nota de débito electrónica', 'code' => '02', 'active' => 1],
            ['id' => 3, 'name' => 'Nota de crédito electrónica', 'code' => '03', 'active' => 1],
            ['id' => 4, 'name' => 'Tiquete electrónico', 'code' => '04', 'active' => 1],
            ['id' => 5, 'name' => 'Nota de despacho', 'code' => '05', 'active' => 1],
            ['id' => 6, 'name' => 'Contrato', 'code' => '06', 'active' => 1],
            ['id' => 7, 'name' => 'Procedimiento', 'code' => '07', 'active' => 1],
            ['id' => 8, 'name' => 'Comprobante emitido en contingencia', 'code' => '08', 'active' => 1],
            ['id' => 9, 'name' => 'Devolución mercadería', 'code' => '09', 'active' => 1],
            ['id' => 10, 'name' => 'Comprobante electrónico rechazado por el Ministerio de Hacienda', 'code' => '10', 'active' => 1],
            ['id' => 11, 'name' => 'Sustituye factura rechazada por el Receptor del comprobante', 'code' => '11', 'active' => 1],
            ['id' => 12, 'name' => 'Sustituye Factura de exportación', 'code' => '12', 'active' => 1],
            ['id' => 13, 'name' => 'Facturación mes vencido', 'code' => '13', 'active' => 1],
            ['id' => 14, 'name' => 'Comprobante aportado por contribuyente de Régimen Especial', 'code' => '14', 'active' => 1],
            ['id' => 15, 'name' => 'Sustituye una Factura electrónica de Compra', 'code' => '15', 'active' => 1],
            ['id' => 16, 'name' => 'Comprobante de Proveedor No Domiciliado', 'code' => '16', 'active' => 1],
            ['id' => 17, 'name' => 'Nota de Crédito a Factura Electrónica de Compra', 'code' => '17', 'active' => 1],
            ['id' => 18, 'name' => 'Nota de Débito a Factura Electrónica de Compra ', 'code' => '18', 'active' => 1],
            ['id' => 99, 'name' => 'Otros', 'code' => '99', 'active' => 1],
        ];

        foreach ($documentTypes as $type) {
            ReferenceDocumentType::updateOrCreate(
                ['id' => $type['id']],
                [
                    'name' => $type['name'],
                    'code' => $type['code'],
                    'active' => $type['active'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }
    }
}

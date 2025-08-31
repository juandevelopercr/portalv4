<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banks = [
            ['name' => 'Scotiabank C.R.', 'iniciales' => 'Scotiabank C.R.', 'email' => 'prueba@gmail.com', 'desglosar_servicio' => 1, 'active' => 0],
            ['name' => 'TERCEROS', 'iniciales' => 'TERCEROS', 'email' => 'JORGE@GMAIL.COM', 'desglosar_servicio' => 1, 'active' => 0],
            ['name' => 'Banco Lafise', 'iniciales' => 'Banco Lafise', 'email' => 'prueba@gmail.com', 'desglosar_servicio' => 1, 'active' => 0],
            ['name' => 'Banco Davivienda', 'iniciales' => 'Banco Davivienda', 'email' => 'prueba@gmail.com', 'desglosar_servicio' => 1, 'active' => 0],
            ['name' => 'BAC San José', 'iniciales' => 'BAC', 'email' => 'jmunoz@consortiumlegal.com', 'desglosar_servicio' => 1, 'active' => 0],
            ['name' => 'Banco General', 'iniciales' => 'B.G', 'email' => 'BancoGeneral@gmail.com', 'desglosar_servicio' => 1, 'active' => 0],
            ['name' => 'First Citizens', 'iniciales' => 'FC', 'email' => 'FirstCitizens@gmail.com', 'desglosar_servicio' => 1, 'active' => 0],
            ['name' => 'BanColombia', 'iniciales' => 'B.Co', 'email' => 'BanColombia@gmail.com', 'desglosar_servicio' => 1, 'active' => 0],
            ['name' => 'BID', 'iniciales' => 'BID', 'email' => 'BID@gmail.com', 'desglosar_servicio' => 1, 'active' => 0],
            ['name' => 'CITI', 'iniciales' => 'CITI', 'email' => 'CITIl@gmail.com', 'desglosar_servicio' => 1, 'active' => 0],
            ['name' => 'Bicsa', 'iniciales' => 'Bicsa', 'email' => 'Bicsa@gmail.com', 'desglosar_servicio' => 1, 'active' => 0],
            ['name' => 'ITAU CORPBANCA', 'iniciales' => 'ITAU', 'email' => 'ITAU@gmail.com', 'desglosar_servicio' => 1, 'active' => 0],
            ['name' => 'Financiera Comeca', 'iniciales' => 'Fcomeca', 'email' => 'fcomecal@gmail.com', 'desglosar_servicio' => 1, 'active' => 0],
            ['name' => 'Mucap', 'iniciales' => 'Mucap', 'email' => 'mucap@gmaiil.com', 'desglosar_servicio' => 1, 'active' => 0],
            ['name' => 'Seficom', 'iniciales' => 'Seficom', 'email' => 'fiduciaria@gmaiil.com', 'desglosar_servicio' => 1, 'active' => 0],
            ['name' => 'Banca Costa Rica', 'iniciales' => 'BCR', 'email' => 'jorgemum01@gmail.com', 'desglosar_servicio' => 1, 'active' => 0],
            ['name' => 'Securitas', 'iniciales' => 'Securitas', 'email' => 'jorgemum01@gmail.com', 'desglosar_servicio' => 1, 'active' => 0],
            ['name' => 'Grupo CS', 'iniciales' => 'Coopeservidores', 'email' => 'jorgemum01@gmail.com', 'desglosar_servicio' => 1, 'active' => 0],
            ['name' => 'Inchape', 'iniciales' => 'Inchape', 'email' => 'jorgemum01@gmail.com', 'desglosar_servicio' => 0, 'active' => 0],
        ];

        foreach ($banks as $bank) {
            Bank::create($bank);
        }
    }
}

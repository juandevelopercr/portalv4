<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HonorariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('honorarios')->insert([
            ['id' => 1, 'name' => 'MITAD TARIFA'],
            ['id' => 2, 'name' => 'SB-HONORARIOS ARANCEL'],
            ['id' => 3, 'name' => 'SB-HONORARIOS MITAD ARANCEL '],
            ['id' => 4, 'name' => 'LAF-HONORARIOS ARANCEL'],
            ['id' => 5, 'name' => 'LAF-HONORARIOS MITAD ARANCEL'],
            ['id' => 6, 'name' => 'DAV-HONORARIOS ARANCEL'],
            ['id' => 7, 'name' => 'DAV-HONORARIOS MITAD ARANCEL'],
            ['id' => 8, 'name' => 'BAC-HONORARIOS ARANCEL'],
            ['id' => 9, 'name' => 'BAC-HONORARIOS MITAD ARANCEL'],
            ['id' => 10, 'name' => 'BAC-HONORARIOS 25% ARANCEL'],
            ['id' => 11, 'name' => 'BAC-HONORARIOS 20% ARANCEL'],
            ['id' => 12, 'name' => 'MUCAP-HON. ARANCEL'],
            ['id' => 13, 'name' => 'MUCAP-HON.MITAD  ARANCEL'],
            ['id' => 14, 'name' => 'MUCAP-HON 25%. ARANCEL'],
            ['id' => 15, 'name' => 'MUCAP-HON.20%  ARANCEL'],
            ['id' => 16, 'name' => 'FID-HON  MITAD ARANCEL'],
            ['id' => 17, 'name' => 'FID-HON  ARANCEL'],
            ['id' => 18, 'name' => 'CS-HONORARIOS ARANCEL'],
            ['id' => 19, 'name' => 'CS-HONORARIOS MITAD ARANCEL'],
            ['id' => 20, 'name' => 'CS-HONORARIOS 25% ARANCEL'],
            ['id' => 21, 'name' => 'CS-HONORARIOS 20% ARANCEL'],
        ]);
    }
}

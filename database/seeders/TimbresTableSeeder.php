<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimbresTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('timbres')->insert([
            ['id' => 1, 'base' => 250000.00000, 'porcada' => 0.00000, 'tipo' => 1, 'orden' => 1],
            ['id' => 2, 'base' => 1000000.00000, 'porcada' => 1100.00000, 'tipo' => 1, 'orden' => 2],
            ['id' => 3, 'base' => 5000000.00000, 'porcada' => 2200.00000, 'tipo' => 1, 'orden' => 3],
            ['id' => 4, 'base' => 25000000.00000, 'porcada' => 5500.00000, 'tipo' => 1, 'orden' => 4],
            ['id' => 5, 'base' => 50000000.00000, 'porcada' => 11000.00000, 'tipo' => 1, 'orden' => 5],
            ['id' => 6, 'base' => 100000000.00000, 'porcada' => 16500.00000, 'tipo' => 1, 'orden' => 6],
            ['id' => 7, 'base' => 500000000.00000, 'porcada' => 27500.00000, 'tipo' => 1, 'orden' => 7],
            ['id' => 8, 'base' => 0.00000, 'porcada' => 55000.00000, 'tipo' => 1, 'orden' => 8],
            ['id' => 9, 'base' => 250000.00000, 'porcada' => 0.00000, 'tipo' => 2, 'orden' => 1],
            ['id' => 10, 'base' => 1000000.00000, 'porcada' => 2200.00000, 'tipo' => 2, 'orden' => 2],
            ['id' => 11, 'base' => 5000000.00000, 'porcada' => 4400.00000, 'tipo' => 2, 'orden' => 3],
            ['id' => 12, 'base' => 25000000.00000, 'porcada' => 11000.00000, 'tipo' => 2, 'orden' => 4],
            ['id' => 13, 'base' => 50000000.00000, 'porcada' => 22000.00000, 'tipo' => 2, 'orden' => 5],
            ['id' => 14, 'base' => 100000000.00000, 'porcada' => 33000.00000, 'tipo' => 2, 'orden' => 6],
            ['id' => 15, 'base' => 500000000.00000, 'porcada' => 55000.00000, 'tipo' => 2, 'orden' => 7],
            ['id' => 16, 'base' => 0.00000, 'porcada' => 110000.00000, 'tipo' => 2, 'orden' => 8],
        ]);
    }
}

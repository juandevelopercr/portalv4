<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Department::insert([
            [
                'name' => 'Retail',
                'email' => '',
                'centro_costo_id' => 1,
                'codigo_contable_id' => 1,
            ],
            [
                'name' => 'Banca Corporativa',
                'email' => '',
                'centro_costo_id' => 2,
                'codigo_contable_id' => 2,
            ],
            [
                'name' => 'Terceros',
                'email' => null,
                'centro_costo_id' => null,
                'codigo_contable_id' => 2,
            ],
            [
                'name' => 'Lafise',
                'email' => null,
                'centro_costo_id' => 20,
                'codigo_contable_id' => 2,
            ],
        ]);
    }
}

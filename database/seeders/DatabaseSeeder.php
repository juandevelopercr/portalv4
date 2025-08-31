<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\CentroCosto;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Seeders\CentroCostoSeeder;
use Database\Seeders\CodigoContableSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    $this->call(UsersTableSeeder::class);
    User::factory(50)->create();

    $this->call(WorldTableSeeder::class);

    //$this->call(TaxTypeSeeder::class);

    //$this->call(TaxRatesSeeder::class);

    //$this->call(ExonerationTypeSeeder::class);

    //$this->call(ConditionSaleSeeder::class);

    //$this->call(IdentificationTypeSeeder::class);

    //$this->call(RoleSeeder::class);

    //$this->call(CentroCostoSeeder::class);

    //$this->call(CodigoContableSeeder::class);

    //$this->call(BankSeeder::class);

    //$this->call(HonorariosSeeder::class);

    $this->call(RolesAndPermissionsSeeder::class);
  }
}

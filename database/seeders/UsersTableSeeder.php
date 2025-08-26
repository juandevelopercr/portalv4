<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {

    DB::table('users')->insert([
      // admin
      [
        'name' => 'Administrador',
        //'username' => 'admin',
        'email' => 'caceresvega@gmail.com',
        'password' => Hash::make('costarica'),
        //'role'=> 'admin',
        //'status'=> 'active',
      ],
      // agent
      [
        'name' => 'Agente',
        //'username' => 'agent',
        'email' => 'agent@gmail.com',
        'password' => Hash::make('costarica'),
        //'role'=> 'agent',
        //'status'=> 'active',
      ],
      // user
      [
        'name' => 'Usuario',
        //'username' => 'usuario',
        'email' => 'usuario@gmail.com',
        'password' => Hash::make('costarica'),
        //'role'=> 'user',
        //'status'=> 'active',
      ],
    ]);
  }
}

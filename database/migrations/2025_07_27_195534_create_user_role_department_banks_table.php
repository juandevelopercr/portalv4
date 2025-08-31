<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('user_role_department_banks', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained();
      $table->foreignId('role_id')->constrained();
      $table->foreignId('department_id')->constrained();
      $table->foreignId('bank_id')->constrained();

      // Para evitar duplicados
      $table->unique([
        'user_id',
        'role_id',
        'department_id',
        'bank_id'
      ], 'user_role_dept_bank_unique');

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('user_role_department_banks');
  }
};

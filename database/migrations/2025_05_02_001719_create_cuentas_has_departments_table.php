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
    Schema::create('cuentas_has_departments', function (Blueprint $table) {
      $table->unsignedBigInteger('cuenta_id');
      $table->unsignedBigInteger('department_id');

      // Foreign Keys
      $table->foreign('cuenta_id')->references('id')->on('cuentas')->onDelete('cascade');
      $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('cuentas_has_departments');
  }
};

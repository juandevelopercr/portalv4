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
    Schema::create('catalogo_cuentas', function (Blueprint $table) {
      $table->id();
      $table->string('codigo', 20);
      $table->string('descrip', 100);
      $table->boolean('favorite')->default(false);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('catalogo_cuentas');
  }
};

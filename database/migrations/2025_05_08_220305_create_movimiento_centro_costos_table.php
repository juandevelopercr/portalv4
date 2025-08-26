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
    Schema::create('movimientos_centro_costos', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('movimiento_id');
      $table->unsignedBigInteger('centro_costo_id');
      $table->unsignedBigInteger('codigo_contable_id');
      $table->decimal('amount', 18, 5)->nullable();
      $table->timestamps();

      $table->foreign('movimiento_id', 'fk_mcc_movimiento')
        ->references('id')->on('movimientos')
        ->onDelete('cascade')
        ->onUpdate('cascade');

      $table->foreign('centro_costo_id', 'fk_mcc_centro_costo')
        ->references('id')->on('centro_costos')
        ->onUpdate('cascade');

      $table->foreign('codigo_contable_id', 'fk_mcc_codigo_contable')
        ->references('id')->on('catalogo_cuentas')
        ->onUpdate('cascade');

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('movimientos_centro_costos');
  }
};

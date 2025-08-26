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
    Schema::create('movimientos_facturas', function (Blueprint $table) {
      $table->unsignedBigInteger('movimiento_id');
      $table->unsignedBigInteger('transaction_id');

      $table->primary(['movimiento_id', 'transaction_id']);

      $table->foreign('movimiento_id', 'fk_movimientos_facturas_mov')
        ->references('id')->on('movimientos')
        ->onDelete('cascade')
        ->onUpdate('cascade');

      $table->foreign('transaction_id', 'fk_movimientos_facturas_tx')
        ->references('id')->on('transactions')
        ->onDelete('cascade')
        ->onUpdate('cascade');

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('movimientos_facturas');
  }
};

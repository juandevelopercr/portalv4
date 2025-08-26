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
    Schema::create('transactions_payments', function (Blueprint $table) {
      $table->id();
      $table->foreignId('transaction_id')->constrained()->onDelete('cascade');

      $table->string('tipo_medio_pago', 2); // Código del medio de pago (01, 02, ..., 99)
      $table->string('medio_pago_otros', 100)->nullable(); // Requerido solo si tipo = 99
      $table->decimal('total_medio_pago', 18, 5);
      $table->string('banco', 100)->nullable();
      $table->string('referencia', 100)->nullable();
      $table->string('detalle', 100)->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('transactions_payments');
  }
};

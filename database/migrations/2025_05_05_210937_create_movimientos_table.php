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
    Schema::create('movimientos', function (Blueprint $table) {
      $table->id();
      $table->foreignId('cuenta_id')->constrained('cuentas')->cascadeOnUpdate();
      $table->foreignId('moneda_id')->constrained('currencies')->cascadeOnUpdate();
      $table->enum('tipo_movimiento', ['DEPOSITO', 'ELECTRONICO', 'CHEQUE'])->nullable();
      $table->string('lugar', 150)->nullable();
      $table->date('fecha')->nullable();
      $table->decimal('monto', 18, 5);
      $table->string('monto_letras', 150);
      $table->boolean('tiene_retencion')->default(false);
      $table->decimal('saldo_cancelar', 18, 5)->nullable();
      $table->decimal('diferencia', 18, 5)->default(0.00000);
      $table->text('descripcion')->nullable();
      $table->string('numero', 100)->nullable();
      $table->string('beneficiario', 150)->nullable();
      $table->boolean('comprobante_pendiente')->default(false);
      $table->boolean('bloqueo_fondos')->default(false);
      $table->decimal('impuesto', 18, 5)->default(0.00000);
      $table->decimal('total_general', 18, 5)->default(0.00000);
      $table->enum('status', ['REVISION', 'ANULADO', 'REGISTRADO', 'RECHAZADO'])->default('REGISTRADO');
      $table->boolean('listo_para_aprobar')->default(false);
      $table->text('comentarios')->nullable();
      $table->string('concepto', 150)->nullable();
      $table->string('email_destinatario', 100)->nullable();
      $table->boolean('clonando')->default(false);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('movimientos');
  }
};

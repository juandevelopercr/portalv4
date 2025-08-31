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
    Schema::create('cuentas', function (Blueprint $table) {
      $table->id();
      $table->string('numero_cuenta', 100);
      $table->string('nombre_cuenta', 100);
      $table->unsignedBigInteger('moneda_id');
      $table->decimal('balance', 18, 5)->default(0);
      $table->decimal('saldo', 18, 5)->default(0);
      $table->string('ultimo_cheque', 100)->nullable();
      $table->boolean('mostrar_lugar')->default(false);
      $table->integer('lugar_fecha_y')->nullable();
      $table->integer('lugar_fecha_x')->nullable();
      $table->integer('beneficiario_y')->nullable();
      $table->integer('beneficiario_x')->nullable();
      $table->integer('monto_y')->nullable();
      $table->integer('monto_x')->nullable();
      $table->integer('monto_letras_y')->nullable();
      $table->integer('monto_letras_x')->nullable();
      $table->integer('detalles_y')->nullable();
      $table->integer('detalles_x')->nullable();
      $table->boolean('is_cuenta_301')->default(false);
      $table->boolean('calcular_pendiente_registro')->default(false);
      $table->boolean('calcular_traslado_gastos')->default(false);
      $table->boolean('calcular_traslado_honorarios')->default(false);
      $table->unsignedBigInteger('banco_id')->nullable();
      $table->string('perosna_sociedad', 200)->nullable();
      $table->decimal('traslados_karla', 18, 5)->default(0);
      $table->decimal('certifondo_bnfa', 18, 5)->default(0);
      $table->decimal('colchon', 18, 5)->default(0);
      $table->decimal('tipo_cambio', 18, 5)->default(0);
      $table->timestamps();

      $table->foreign('moneda_id')->references('id')->on('currencies')->onDelete('cascade');

      $table->index('moneda_id');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('cuentas');
  }
};

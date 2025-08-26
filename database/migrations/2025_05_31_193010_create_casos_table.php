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
    Schema::create('casos', function (Blueprint $table) {
      $table->id();
      $table->string('numero', 18)->nullable();
      $table->string('numero_gestion', 50)->nullable();
      $table->string('deudor', 191);
      $table->foreignId('abogado_cargo_id')->nullable()->constrained('users')->cascadeOnUpdate();
      $table->foreignId('abogado_revisor_id')->nullable()->constrained('users')->cascadeOnUpdate();
      $table->foreignId('abogado_formalizador_id')->nullable()->constrained('users')->cascadeOnUpdate();
      $table->foreignId('asistente_id')->nullable()->constrained('users')->cascadeOnUpdate();
      $table->foreignId('currency_id')->constrained('currencies')->cascadeOnUpdate();
      $table->foreignId('caratula_id')->constrained('caratulas')->cascadeOnUpdate();
      $table->foreignId('garantia_id')->nullable()->constrained('garantias')->cascadeOnUpdate();
      $table->foreignId('department_id')->constrained('departments')->cascadeOnUpdate();
      $table->foreignId('estado_id')->constrained('casos_estados')->cascadeOnUpdate();
      $table->string('numero_garantia', 200)->nullable();
      $table->string('nombre_formalizo', 100)->comment('Nombre de quien formalizó el caso');
      $table->foreignId('bank_id')->constrained('banks')->cascadeOnUpdate();
      $table->string('sucursal', 200)->nullable();
      $table->decimal('monto', 18, 5)->default(0.00000);
      $table->string('numero_tomo', 50)->nullable();
      $table->string('asiento_presentacion', 50)->nullable();
      $table->date('fecha_creacion')->nullable();
      $table->date('fecha_firma')->nullable();
      $table->date('fecha_presentacion')->nullable();
      $table->date('fecha_inscripcion')->nullable();
      $table->date('fecha_entrega')->nullable();
      $table->dateTime('fecha_caratula')->nullable();
      $table->dateTime('fecha_precaratula')->nullable();
      $table->decimal('costo_caso_retiro', 18, 5)->nullable();
      $table->text('observaciones')->nullable();
      $table->text('pendientes')->nullable();
      $table->string('desarrollador', 100)->nullable();
      $table->string('cedula', 20)->nullable();
      $table->string('num_operacion', 50)->nullable();
      $table->string('cedula_deudor', 100)->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('casos');
  }
};

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
    Schema::create('contacts_contactos', function (Blueprint $table) {
      $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT
      $table->foreignId('contact_id')->constrained('contacts')->onDelete('cascade');
      $table->string('name', 255)->nullable();
      $table->string('email', 59);
      $table->string('telefono', 14);
      $table->string('ext', 6)->nullable();
      $table->string('celular', 14)->nullable();
      $table->foreignId('grupo_empresarial_id')->nullable()->constrained('grupos_empresariales')->onUpdate('cascade');
      $table->foreignId('sector_id')->nullable()->constrained('sectores')->onUpdate('cascade');
      $table->foreignId('area_practica_id')->nullable()->constrained('areas_practicas')->onUpdate('cascade');

      // Campos ENUM actualizados
      $table->enum('clasificacion', ['RECURRENTE', 'OCASIONAL'])->nullable();
      $table->enum('tipo_cliente', ['ACTUAL', 'EXCLIENTE'])->nullable();

      $table->date('fecha_nacimiento')->nullable();
      $table->integer('anno_ingreso')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('contacts_contactos');
  }
};

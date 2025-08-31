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
    Schema::create('casos_situaciones', function (Blueprint $table) {
      $table->id();
      $table->foreignId('caso_id')->constrained('casos')->cascadeOnDelete()->cascadeOnUpdate();
      $table->string('name');
      $table->string('responsable', 150);
      $table->date('fecha')->nullable();
      $table->enum('tipo', ['PENDIENTE', 'DEFECTUOSO']);
      $table->enum('estado', ['PENDIENTE', 'CUMPLIDO']);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('casos_situaciones');
  }
};

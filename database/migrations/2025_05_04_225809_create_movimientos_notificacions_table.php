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
    Schema::create('movimientos_notificaciones', function (Blueprint $table) {
      $table->id();
      $table->string('nombre', 150);
      $table->string('email', 100);
      $table->text('copia')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->boolean('activo')->default(true);
      $table->boolean('enviar_rechazo')->default(false);
      $table->boolean('enviar_aprobado')->default(false);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('movimientos_notificaciones');
  }
};

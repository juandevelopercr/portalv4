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
    Schema::create('grupos_empresariales', function (Blueprint $table) {
      $table->id(); // Equivale a INT autoincremental y primary key
      $table->string('name', 100);
      $table->boolean('active')->default(true);
      $table->timestamps(); // Opcional pero recomendado
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('grupos_empresariales');
  }
};

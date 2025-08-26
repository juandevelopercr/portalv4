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
    Schema::create('honorarios_recetas', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('honorario_id');
      $table->decimal('desde', 18, 5);
      $table->decimal('hasta', 18, 5)->default(0.00000);
      $table->decimal('porcentaje', 10, 2);
      $table->integer('orden')->nullable();
      $table->timestamps();
      $table->foreign('honorario_id')->references('id')->on('honorarios')->onUpdate('cascade');
      //$table->foreign('bank_id')->references('id')->on('banks')->onUpdate('cascade');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('honorarios_recetas');
  }
};

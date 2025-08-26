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
    Schema::create('casos_estados_history', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('caso_id');
      $table->unsignedBigInteger('caso_estado_id');
      $table->unsignedBigInteger('user_id');

      // Foreign Keys
      $table->foreign('caso_id')->references('id')->on('casos')->onDelete('cascade');
      $table->foreign('caso_estado_id')->references('id')->on('casos_estados')->onDelete('cascade');
      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('casos_estados_history');
  }
};

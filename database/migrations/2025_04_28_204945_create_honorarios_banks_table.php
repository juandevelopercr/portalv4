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
    Schema::create('honorarios_banks', function (Blueprint $table) {
      $table->unsignedBigInteger('honorario_id');
      $table->unsignedBigInteger('bank_id');

      // Foreign Keys
      $table->foreign('honorario_id', 'fk_honorarios_banks_honorario')
        ->references('id')
        ->on('honorarios')
        ->onDelete('cascade');

      $table->foreign('bank_id', 'fk_honorarios_banks_bank')
        ->references('id')
        ->on('banks')
        ->onDelete('cascade');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('honorarios_banks');
  }
};

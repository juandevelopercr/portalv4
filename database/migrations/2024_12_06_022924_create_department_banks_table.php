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
    Schema::create('department_banks', function (Blueprint $table) {
      $table->unsignedBigInteger('bank_id');
      $table->unsignedBigInteger('department_id');

      // Define las claves foráneas
      $table->foreign('bank_id')
        ->references('id')
        ->on('banks')
        ->onDelete('cascade')
        ->onUpdate('cascade');

      $table->foreign('department_id')
        ->references('id')
        ->on('departments')
        ->onDelete('cascade')
        ->onUpdate('cascade');

      // Define índices
      $table->index('bank_id');
      $table->index('department_id');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('department_banks');
  }
};

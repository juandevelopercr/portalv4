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
    Schema::create('garantias_has_departments', function (Blueprint $table) {
      $table->unsignedBigInteger('garantia_id');
      $table->unsignedBigInteger('department_id');

      // Foreign Keys
      $table->foreign('garantia_id')->references('id')->on('garantias')->onDelete('cascade');
      $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('garantias_has_departments');
  }
};

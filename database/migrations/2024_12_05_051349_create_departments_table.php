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
    Schema::create('departments', function (Blueprint $table) {
      $table->id();
      $table->string('code', 10);
      $table->string('name', 150);
      $table->string('email', 100)->nullable();
      $table->unsignedBigInteger('centro_costo_id')->nullable();
      $table->unsignedBigInteger('codigo_contable_id')->nullable();
      $table->boolean('active')->default(true);

      $table->foreign('centro_costo_id')->references('id')->on('centro_costos')->onUpdate('cascade');
      $table->foreign('codigo_contable_id')->references('id')->on('codigo_contables')->onUpdate('cascade');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('departments');
  }
};

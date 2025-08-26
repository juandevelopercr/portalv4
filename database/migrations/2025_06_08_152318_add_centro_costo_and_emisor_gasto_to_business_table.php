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
    Schema::table('business', function (Blueprint $table) {
      $table->foreignId('centro_costo_calculo_registro_id')
        ->nullable()
        ->after('currency_id')
        ->constrained('centro_costos')
        ->nullOnDelete()
        ->cascadeOnUpdate();

      $table->unsignedBigInteger('emisor_gasto_id')->nullable()->after('centro_costo_calculo_registro_id');

      $table->foreign('emisor_gasto_id')
        ->references('id')
        ->on('business_locations')
        ->nullOnDelete()
        ->cascadeOnUpdate();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('business', function (Blueprint $table) {
      $table->dropForeign(['centro_costo_calculo_registro_id']);
      $table->dropForeign(['emisor_gasto_id']);
      $table->dropColumn(['centro_costo_calculo_registro_id', 'emisor_gasto_id']);
    });
  }
};

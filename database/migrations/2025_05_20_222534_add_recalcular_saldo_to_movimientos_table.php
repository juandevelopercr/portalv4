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
    Schema::table('movimientos', function (Blueprint $table) {
      $table->boolean('recalcular_saldo')->default(false);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('movimientos', function (Blueprint $table) {
      $table->dropColumn('recalcular_saldo');
    });
  }
};

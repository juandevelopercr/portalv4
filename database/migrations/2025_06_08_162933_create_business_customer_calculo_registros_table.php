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
    Schema::create('business_customer_calculo_registros', function (Blueprint $table) {
      $table->id();
      $table->foreignId('business_id')
        ->constrained('business')
        ->cascadeOnDelete()
        ->cascadeOnUpdate();

      $table->foreignId('contact_id')
        ->constrained('contacts')
        ->cascadeOnDelete()
        ->cascadeOnUpdate();

      $table->timestamps();

      $table->unique(['business_id', 'contact_id'], 'bc_registro_unique');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('business_customer_calculo_registros');
  }
};

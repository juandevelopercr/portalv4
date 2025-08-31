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
    Schema::create('contacts_contactos_sector', function (Blueprint $table) {
      $table->foreignId('contacto_id')->constrained('contacts_contactos')->cascadeOnDelete();
      $table->foreignId('sector_id')->constrained('sectores')->cascadeOnDelete();
      $table->primary(['contacto_id', 'sector_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('contacts_contactos_sector');
  }
};

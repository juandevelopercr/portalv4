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
    Schema::create('tokens', function (Blueprint $table) {
      $table->id();
      $table->string('issuer'); // Emisor (clientId)
      $table->text('access_token'); // Token de acceso
      $table->timestamp('access_token_expires_at'); // Fecha de expiración del access_token
      $table->text('refresh_token'); // Token de refresco
      $table->timestamp('refresh_token_expires_at'); // Fecha de expiración del refresh_token
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('tokens');
  }
};

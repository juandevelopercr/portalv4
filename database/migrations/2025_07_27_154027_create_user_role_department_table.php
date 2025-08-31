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
    // Migración user_role_department
    Schema::create('user_role_department', function (Blueprint $table) {
      $table->foreignId('user_id')->constrained();
      $table->foreignId('role_id')->constrained();
      $table->foreignId('department_id')->constrained();
      $table->primary(['user_id', 'role_id', 'department_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('user_role_department');
  }
};

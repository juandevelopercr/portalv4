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
    Schema::create('products', function (Blueprint $table) {
      $table->id();
      $table->string('name', 191);
      $table->string('code', 5);
      $table->unsignedBigInteger('business_id');
      $table->enum('type', ['single', 'variable', 'service', 'combo'])->nullable()->default('service');
      $table->unsignedBigInteger('unit_type_id');
      $table->string('cabys_code');
      $table->decimal('price', 18, 5)->nullable();

      $table->boolean('enable_quantity')->default(false);

      $table->string('sku', 20);
      $table->string('image')->nullable();
      $table->text('description')->nullable();
      $table->boolean('active')->default(true);
      $table->unsignedBigInteger('created_by');
      $table->timestamps();

      $table->primary('id');
      $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
      $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
      $table->foreign('unit_type_id')->references('id')->on('unit_types')->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('products');
  }
};

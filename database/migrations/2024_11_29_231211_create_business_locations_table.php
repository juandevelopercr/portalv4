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
    Schema::create('business_locations', function (Blueprint $table) {
      $table->id(); // Campo id con AUTO_INCREMENT y PRIMARY KEY
      $table->unsignedBigInteger('business_id'); // Relación con la tabla business
      $table->unsignedBigInteger('location_parent_id')->nullable();
      $table->string('code', 3)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->string('name', 100)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->string('commercial_name', 80)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->unsignedBigInteger('admin_invoice_layout_id');
      $table->unsignedBigInteger('pos_invoice_layout_id');
      $table->unsignedBigInteger('selling_price_group_id')->nullable(); // Cambiado a unsignedBigInteger
      $table->unsignedBigInteger('admin_quotation_layout_id')->nullable();
      $table->unsignedBigInteger('pos_quotation_layout_id')->nullable();
      $table->boolean('print_receipt_on_invoice')->default(true);
      $table->enum('receipt_printer_type', ['browser', 'printer'])->default('browser');
      $table->unsignedInteger('printer_id')->nullable();
      $table->string('phone_code', 10)->nullable();
      $table->string('phone', 191)->nullable();
      $table->string('email', 191)->nullable();
      $table->string('website', 191)->nullable();
      $table->enum('environment', ['produccion', 'prueba'])->nullable();
      $table->string('api_key', 255)->nullable();
      $table->string('password', 255)->nullable();
      $table->softDeletes(); // Campo deleted_at para Soft Deletes
      $table->timestamps();
      $table->unsignedBigInteger('identification_type_id')->nullable();
      $table->string('identification', 12)->nullable();
      $table->string('country', 100);
      $table->char('zip_code', 7);
      $table->unsignedBigInteger('province_id')->nullable();
      $table->unsignedBigInteger('canton_id')->nullable();
      $table->unsignedBigInteger('district_id')->nullable();
      $table->string('address', 255)->nullable();
      $table->string('other_signs', 255)->nullable();
      $table->string('certificate_pin', 100)->nullable();
      $table->string('api_user_hacienda', 100)->nullable();
      $table->string('api_password', 255)->nullable();
      $table->string('certificate_digital_file', 255)->nullable();
      $table->string('proveedor', 255)->nullable();
      $table->string('registrofiscal8707', 12)->nullable();

      $table->string('numero_sucursal', 3)->nullable();
      $table->string('numero_punto_venta', 5)->nullable();

      $table->boolean('active')->default(true);

      // Foreign Keys
      $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
      $table->foreign('identification_type_id')->references('id')->on('identification_types')->onDelete('cascade')->onUpdate('cascade');
      $table->foreign('admin_invoice_layout_id')->references('id')->on('invoice_layouts')->onDelete('cascade');
      $table->foreign('pos_invoice_layout_id')->references('id')->on('invoice_layouts')->onDelete('cascade');
      $table->foreign('selling_price_group_id')->references('id')->on('selling_price_groups')->onDelete('set null'); // Llave foránea corregida
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('business_locations');
  }
};

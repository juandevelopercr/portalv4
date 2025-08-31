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
    Schema::create('transactions', function (Blueprint $table) {
      // Primary Key
      $table->id(); // Unsigned BigInt AUTO_INCREMENT

      // Foreign Keys
      $table->foreignId('business_id')->constrained('business')->onDelete('cascade');
      $table->foreignId('location_id')->nullable()->constrained('business_locations');
      $table->foreignId('location_economic_activity_id')->nullable()->constrained('economic_activities')->onDelete('cascade');
      $table->foreignId('contact_id')->nullable()->constrained('contacts');
      $table->foreignId('contact_economic_activity_id')->nullable()->constrained('economic_activities')->onDelete('cascade');
      $table->foreignId('currency_id')->nullable()->constrained('currencies');
      $table->foreignId('department_id')->nullable()->constrained('departments');
      $table->foreignId('area_id')->nullable()->constrained('areas');
      $table->foreignId('bank_id')->nullable()->constrained('banks');
      $table->foreignId('codigo_contable_id')->nullable()->constrained('codigo_contables');
      $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
      $table->foreignId('caso_id')->nullable()->constrained('banks');

      // Enums
      $table->enum('document_type', ['PR', 'FE', 'TE', 'NDE', 'NCE', 'FEC', 'FEE', 'REP', 'ND', 'NC'])->comment('Document Types');
      $table->enum('proforma_type', ['HONORARIO', 'GASTO'])->nullable();
      $table->enum('proforma_status', ['PROCESO', 'SOLICITADA', 'FACTURADA', 'RECHAZADA', 'ANULADA'])->nullable();
      $table->enum('status', ['PENDIENTE', 'RECIBIDA', 'ACEPTADA', 'RECHAZADA', 'ANULADA'])->nullable();
      $table->enum('payment_status', ['paid', 'due', 'partial'])->nullable();
      $table->enum('pay_term_type', ['days', 'months'])->nullable();

      // Strings
      $table->string('customer_name', 150)->nullable();
      $table->string('customer_comercial_name', 150)->nullable();
      $table->string('customer_email', 150)->nullable();
      $table->string('proforma_no', 30)->nullable();
      $table->string('consecutivo', 20)->nullable();
      $table->string('key', 50)->nullable();
      $table->string('access_token', 191)->nullable();
      $table->string('response_xml', 191)->nullable();
      $table->string('filexml', 191)->nullable();
      $table->string('filepdf', 191)->nullable();
      $table->string('transaction_reference', 50)->nullable();
      $table->string('transaction_reference_id', 50)->nullable();
      $table->string('condition_sale', 2)->default('01');
      $table->string('condition_sale_other', 100)->nullable();
      $table->string('numero_deposito_pago', 191)->nullable();
      $table->string('numero_traslado_honorario', 20)->nullable();
      $table->string('numero_traslado_gasto', 20)->nullable();
      $table->string('contacto_banco', 100)->nullable();
      $table->string('nombre_caso', 191)->nullable();
      $table->string('socio_davivienda', 191)->nullable();

      // Numerics
      $table->integer('pay_term_number')->nullable();
      $table->decimal('proforma_change_type', 18, 5)->nullable()->default(0.00000);
      $table->decimal('factura_change_type', 18, 5)->nullable()->default(0.00000);
      $table->decimal('registro_change_type', 18, 5)->nullable()->default(0.00000);

      $table->tinyInteger('num_request_hacienda_set')->nullable()->default(0);
      $table->tinyInteger('num_request_hacienda_get')->nullable()->default(0);
      $table->boolean('comision_pagada')->default(0);
      $table->boolean('is_retencion')->default(0);


      $table->boolean('contingencia')->default(0);

      $table->string('RefTipoDoc', 2)->nullable();
      $table->string('RefTipoDocOtro', 100)->nullable();
      $table->string('RefNumero', 50)->nullable();
      $table->dateTime('RefFechaEmision');
      $table->string('RefCodigo', 2)->nullable();
      $table->string('RefCodigoOtro', 100)->nullable();
      $table->string('RefRazon', 180)->nullable();

      $table->dateTime('fecha_comision_pagada');
      $table->enum('invoice_type', ['FACTURA', 'TIQUETE'])->nullable();

      // Texts
      $table->text('message')->nullable();
      $table->text('notes')->nullable();
      $table->text('detalle_adicional')->nullable();
      $table->text('oc')->nullable();
      $table->text('migo')->nullable();
      $table->text('or')->nullable();
      $table->text('gln')->nullable();
      $table->text('prebill')->nullable();

      $table->text('email_cc')->nullable();

      // Dates
      $table->dateTime('transaction_date');
      $table->dateTime('invoice_date');
      $table->dateTime('fecha_pago')->nullable();
      $table->dateTime('fecha_deposito_pago')->nullable();
      $table->dateTime('fecha_traslado_honorario')->nullable();
      $table->dateTime('fecha_traslado_gasto')->nullable();
      $table->dateTime('fecha_solicitud_factura')->nullable();
      $table->dateTime('fecha_envio_email')->nullable();

      //Para los totales
      $table->decimal('totalHonorarios', 18, 5)->nullable()->default(0);
      $table->decimal('totalTimbres', 18, 5)->nullable()->default(0);
      $table->decimal('totalDiscount', 18, 5)->nullable()->default(0);
      $table->decimal('totalTax', 18, 5)->nullable()->default(0);
      $table->decimal('totalAditionalCharge', 18, 5)->nullable()->default(0);

      $table->decimal('totalServGravados', 18, 5)->nullable()->default(0);
      $table->decimal('totalmercGravadas', 18, 5)->nullable()->default(0);
      $table->decimal('totalImpuestoServGravados', 18, 5)->nullable()->default(0);
      $table->decimal('totalImpuestomercGravadas', 18, 5)->nullable()->default(0);
      $table->decimal('totalImpuestoServExonerados', 18, 5)->nullable()->default(0);
      $table->decimal('totalImpuestoMercanciasExoneradas', 18, 5)->nullable()->default(0);
      $table->decimal('totalImpuesto', 18, 5)->nullable()->default(0);
      $table->decimal('totalServExentos', 18, 5)->nullable()->default(0);
      $table->decimal('totalmercExentas', 18, 5)->nullable()->default(0);
      $table->decimal('totalCargoAdicional', 18, 5)->nullable()->default(0);
      $table->decimal('totalOtrosCargos', 18, 5)->nullable()->default(0);
      $table->decimal('totalGravado', 18, 5)->nullable()->default(0);
      $table->decimal('totalExento', 18, 5)->nullable()->default(0);
      $table->decimal('totalVenta', 18, 5)->nullable()->default(0);
      $table->decimal('totalVentaNeta', 18, 5)->nullable()->default(0);
      $table->decimal('totalExonerado', 18, 5)->nullable()->default(0);
      $table->decimal('totalComprobante', 18, 5)->nullable()->default(0);

      // Pagos
      $table->decimal('totalPagado', 18, 5)->nullable()->default(0);
      $table->decimal('vuelto', 18, 5)->nullable()->default(0);
      $table->decimal('pendientePorPagar', 18, 5)->nullable()->default(0);

      // Timestamps
      $table->timestamps();

      // Indexes
      $table->index(['business_id', 'transaction_date']);
      $table->index(['type', 'status']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('transactions');
  }
};

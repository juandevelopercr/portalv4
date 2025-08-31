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
    Schema::create('comprobantes', function (Blueprint $table) {
      $table->id();
      $table->foreignId('location_id')->nullable()->constrained('business_locations');
      $table->string('key', 50)->unique();
      $table->string('consecutivo', 20)->nullable();
      $table->dateTime('fecha_emision');
      $table->string('emisor_nombre', 200);
      $table->string('emisor_tipo_identificacion', 2);
      $table->string('emisor_numero_identificacion', 12);
      $table->string('receptor_nombre', 200)->nullable();
      $table->string('receptor_tipo_identificacion', 2)->nullable();
      $table->string('receptor_numero_identificacion', 20)->nullable();
      $table->decimal('tipo_cambio', 18, 5); // Total del documento (MANDATORIO)
      $table->decimal('total_impuestos', 18, 5)->default(0);
      $table->decimal('total_exento', 18, 5)->default(0);
      $table->decimal('total_gravado', 18, 5)->default(0);
      $table->decimal('total_descuentos', 18, 5)->default(0);
      $table->decimal('total_otros_cargos', 18, 5)->default(0);
      $table->decimal('total_comprobante', 18, 5);
      $table->string('moneda', 3)->default('CRC');
      $table->string('tipo_documento', 2);
      $table->string('condicion_venta', 2)->default('01');
      $table->integer('plazo_credito')->nullable();
      $table->string('medio_pago', 2)->nullable();
      $table->string('detalle')->nullable();

      $table->enum('status', ['PENDIENTE', 'RECIBIDA', 'ACEPTADA', 'RECHAZADA'])->nullable();
      $table->enum('mensajeConfirmacion', ['ACEPTADO', 'ACEPTADOPARCIAL', 'RECHAZADO'])->nullable();

      $table->text('respuesta_hacienda')->nullable();
      $table->string('xml_path');
      $table->string('xml_respuesta_path')->nullable();
      $table->string('xml_confirmacion_path')->nullable()->after('xml_respuesta_path');
      $table->string('pdf_path')->nullable();
      $table->string('clave_referencia', 50)->nullable();
      $table->string('codigo_actividad', 6);
      $table->string('situacion_comprobante', 1)->default('1');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('comprobantes');
  }
};

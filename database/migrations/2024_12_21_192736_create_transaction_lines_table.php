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
    Schema::create('transactions_lines', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('transaction_id');
      $table->unsignedBigInteger('product_id');
      $table->string('codigo', 13);
      $table->string('codigocabys', 13)->nullable();
      $table->string('detail', 200);
      $table->decimal('quantity', 16, 3);
      $table->decimal('price', 18, 5)->nullable()->comment('Precio Unitario');
      $table->decimal('discount', 18, 5)->nullable()->comment('Monto descuento');
      $table->decimal('tax', 18, 5)->nullable()->comment('Monto Impuesto');
      $table->string('fecha_reporte_gasto', 20)->nullable();
      $table->date('fecha_pago_registro')->nullable();
      $table->string('numero_pago_registro', 20)->nullable();
      $table->decimal('honorarios', 18, 5)->default(0.00000);
      $table->decimal('timbres', 18, 5)->default(0.00000);
      $table->text('desglose_timbre_formula')->nullable();
      $table->text('desglose_tabla_abogados')->nullable();
      $table->text('desglose_calculos_fijos')->nullable();
      $table->text('desglose_calculo_monto_timbre_manual')->nullable();
      $table->text('desglose_honorarios')->nullable();
      $table->text('desglose_calculo_monto_honorario_manual')->nullable();
      $table->unsignedBigInteger('registro_currency_id')->nullable();
      $table->decimal('registro_change_type', 18, 5)->nullable();
      $table->decimal('registro_monto_escritura', 18, 5)->nullable();
      $table->decimal('registro_valor_fiscal', 18, 5)->nullable();
      $table->integer('registro_cantidad')->nullable()->default(1);
      $table->decimal('monto_cargo_adicional', 18, 5)->nullable()->default(0.00000);
      $table->boolean('calculo_registro_normal')->nullable()->default(0);
      $table->boolean('calculo_registro_iva')->nullable()->default(0);
      $table->boolean('calculo_registro_no_iva')->nullable()->default(0);

      //Para los totales
      $table->decimal('exoneration', 18, 5)->nullable()->default(0);
      $table->decimal('subtotal', 18, 5)->nullable()->default(0);
      $table->decimal('total', 18, 5)->nullable()->default(0);
      $table->decimal('servGravados', 18, 5)->nullable()->default(0);
      $table->decimal('mercGravadas', 18, 5)->nullable()->default(0);
      $table->decimal('impuestoServGravados', 18, 5)->nullable()->default(0);
      $table->decimal('impuestomercGravadas', 18, 5)->nullable()->default(0);
      $table->decimal('impuestoServExonerados', 18, 5)->nullable()->default(0);
      $table->decimal('impuestoMercanciasExoneradas', 18, 5)->nullable()->default(0);
      $table->decimal('impuestoNeto', 18, 5)->nullable()->default(0);
      $table->decimal('servExentos', 18, 5)->nullable()->default(0);
      $table->decimal('mercExentas', 18, 5)->nullable()->default(0);
      $table->string('partida_arancelaria', 12)->nullable();

      $table->timestamps();

      // Foreign Keys
      $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade')->onUpdate('cascade');
      $table->foreign('product_id')->references('id')->on('products')->onUpdate('cascade');

      // Indexes
      $table->index('transaction_id');
      $table->index('product_id');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('transaction_lines');
  }
};

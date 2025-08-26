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
    Schema::create('product_honorarios_timbres', function (Blueprint $table) {
      $table->increments('id');
      $table->unsignedBigInteger('product_id');
      $table->string('description', 200);
      $table->decimal('base', 18, 5)->comment('Monto tope(cascada) o monto fijo o Monto a calcular "Por cada"');
      $table->decimal('porcada', 18, 5)->comment('Relación a obtener del monto, en el caso de cascada es porciento, grada es límite superior');
      $table->boolean('honorario_id')->default(0)->comment('Si este cálculo está relacionado con el siguiente, pasando la diferencia (cascada)');
      $table->boolean('tabla_abogado_inscripciones')->default(0)->comment('Relacionado con el siguiente, sin pasar la diferencia (gradas)');
      $table->boolean('tabla_abogado_traspasos')->default(0);
      $table->boolean('fincascada')->default(0)->comment('Marca el fin de la secuencia de la cascada (monto irrelevante, es lo que reste)');
      $table->boolean('escalonado')->default(0)->comment('Se calcula el monto por cada vez que quepa en la base');
      $table->boolean('fijo')->default(0)->comment('Si el monto es fijo (no se calcula nada)');
      $table->enum('tipo', ['GASTO', 'HONORARIO'])->nullable()->default('HONORARIO');
      $table->boolean('descuento_timbre')->default(0)->comment('Aplica 6% descuento para pago del entero (No aplica para honorarios)');
      $table->boolean('otro_cheque')->default(0)->comment('Indica si el cálculo del entero se hace en cheque aparte');
      $table->boolean('redondear')->default(0)->comment('Llevar al próximo múltiplo de 5 los céntimos');
      $table->boolean('ajustar_honorario')->default(0)->comment('Aumenta el honorario en la moneda indicada hasta llegar al monto indicado');
      $table->boolean('porciento')->default(0)->comment('Si es fórmula, se calcula en base a %');
      $table->boolean('monto_manual')->default(0);
      $table->boolean('es_impuesto')->default(0);
      $table->timestamps();

      $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade')->onUpdate('cascade');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('product_honorarios_timbres');
  }
};

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
        Schema::create('products_taxes', function (Blueprint $table) {
            $table->id(); // Auto-increment ID
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('tax_type_id');
            $table->unsignedBigInteger('tax_rate_id');
            $table->decimal('tax', 4, 2);
            $table->string('tax_type_other', 100)->nullable()->comment('Será obligatorio en caso de utilizar el código 99 de “Otros”');
            $table->decimal('factor_calculo_tax', 5, 4)->nullable()->comment('Campo obligatorio si el producto posee un factor para su cálculo.');
            $table->decimal('count_unit_type', 7, 2)->nullable()->comment('Campo obligatorio cuando se utilicen los códigos de impuesto 03, 04, 05 y 06.');
            $table->decimal('percent', 4, 2)->nullable()->comment('Campo obligatorio si se utiliza el código de impuesto 04.');
            $table->decimal('proporcion', 5, 2)->nullable()->comment('Campo obligatorio en casos específicos.');
            $table->decimal('volumen_unidad_consumo', 7, 2)->nullable()->comment('Campo obligatorio cuando se utilice el código de impuesto 05.');
            $table->decimal('impuesto_unidad', 18, 5)->nullable()->comment('Campo obligatorio para los códigos de impuesto 03, 04, 05 y 06.');

            // Exoneración
            $table->unsignedBigInteger('exoneration_type_id')->nullable();
            $table->string('exoneration_doc', 40)->nullable();
            $table->string('exoneration_doc_other', 100)->nullable()->comment('Será obligatorio en caso de utilizar el código 99 de “Otros”');
            $table->unsignedBigInteger('exoneration_article')->comment('Campo entero de 6 dígitos');
            $table->unsignedBigInteger('exoneration_inciso')->comment('Campo entero de 6 dígitos');
            $table->unsignedBigInteger('exoneration_institution_id')->nullable();
            $table->string('exoneration_institute_other', 160)->nullable()->comment('Será obligatorio en caso de utilizar el código 99 de “Otros”');
            $table->date('exoneration_date')->nullable();
            $table->decimal('exoneration_percent', 4, 2)->default(0.00);
            $table->decimal('exoneration_tarifa_iva', 4, 2)->default(0.00);

            $table->timestamps();

            // Foreign Keys
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('tax_type_id')->references('id')->on('tax_types')->onUpdate('cascade');
            $table->foreign('tax_rate_id')->references('id')->on('tax_rates')->onUpdate('cascade');
            $table->foreign('exoneration_type_id')->references('id')->on('exoneration_types')->onUpdate('cascade');
            $table->foreign('exoneration_institution_id')->references('id')->on('institutions')->onUpdate('cascade');

            // Indexes
            $table->index('product_id');
            $table->index('tax_type_id');
            $table->index('tax_rate_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products_taxes');
    }
};

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
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->id(); // Campo id con AUTO_INCREMENT y PRIMARY KEY
            $table->unsignedBigInteger('business_id'); // Relación con la tabla business
            $table->string('name', 191)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->notNullable(); // Nombre del grupo
            $table->double('amount', 5, 2)->notNullable(); // Cantidad
            $table->string('price_calculation_type', 191)
                ->default('percentage')
                ->charset('utf8mb4')
                ->collation('utf8mb4_unicode_ci'); // Tipo de cálculo del precio
            $table->unsignedBigInteger('selling_price_group_id')->nullable(); // Relación con selling_price_groups
            $table->unsignedBigInteger('created_by'); // Usuario que creó el grupo
            $table->timestamps();

            // Foreign Keys
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->foreign('selling_price_group_id')->references('id')->on('selling_price_groups')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_groups');
    }
};

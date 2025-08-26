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
        Schema::create('condition_sales', function (Blueprint $table) {
            $table->id(); // Campo id con AUTO_INCREMENT y PRIMARY KEY
            $table->string('name', 255)->nullable(); // Nombre de la condición de venta
            $table->string('code', 255)->nullable(); // Código de la condición de venta
            $table->boolean('active')->default(true); // Estado activo por defecto
            $table->timestamps(); // Campos created_at y updated_at
            $table->unique('id', 'condition_sale_pkey'); // Llave única personalizada
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('condition_sales');
    }
};

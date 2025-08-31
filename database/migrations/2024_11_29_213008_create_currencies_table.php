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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id(); // Campo id con AUTO_INCREMENT y PRIMARY KEY
            $table->string('country', 100)->charset('utf8mb4')->collation('utf8mb4_unicode_ci'); // País
            $table->string('currency', 100)->charset('utf8mb4')->collation('utf8mb4_unicode_ci'); // Nombre de la moneda
            $table->string('code', 25)->charset('utf8mb4')->collation('utf8mb4_unicode_ci'); // Código de la moneda
            $table->string('symbol', 25)->charset('utf8mb4')->collation('utf8mb4_unicode_ci'); // Símbolo de la moneda
            $table->string('thousand_separator', 10)->charset('utf8mb4')->collation('utf8mb4_unicode_ci'); // Separador de miles
            $table->string('decimal_separator', 10)->charset('utf8mb4')->collation('utf8mb4_unicode_ci'); // Separador decimal
            $table->boolean('active')->default(true); // Estado del cantón (activo por defecto)
            $table->timestamps(); // Campos created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};

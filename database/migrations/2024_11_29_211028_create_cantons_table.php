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
        Schema::create('cantons', function (Blueprint $table) {
            $table->id(); // Crea el campo id con AUTO_INCREMENT y PRIMARY KEY
            $table->string('name')->nullable(); // Nombre del cantón
            $table->string('code', 2)->nullable(); // Código del cantón (2 caracteres)
            $table->foreignId('province_id')->nullable()->constrained('provinces')->nullOnDelete(); // Relación con la tabla provincias
            $table->boolean('active')->default(true); // Estado del cantón (activo por defecto)
            $table->timestamps(); // Campos created_at y updated_at
            $table->unique('id', 'canton_pkey'); // Llave única personalizada
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cantons');
    }
};

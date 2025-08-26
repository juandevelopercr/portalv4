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
        Schema::create('provinces', function (Blueprint $table) {
            $table->id(); // Crea el campo id con AUTO_INCREMENT y PRIMARY KEY
            $table->string('name')->nullable(); // Nombre de la provincia
            $table->string('code', 2)->nullable(); // Código de la provincia (2 caracteres)
            $table->boolean('active')->default(true); // Estado del usuarios
            $table->timestamps(); // Campos created_at y updated_at
            $table->unique('id', 'province_pkey'); // Llave única personalizada
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provinces');
    }
};

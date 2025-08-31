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
        Schema::create('districts', function (Blueprint $table) {
            $table->id(); // Crea el campo id con AUTO_INCREMENT y PRIMARY KEY
            $table->string('name')->nullable(); // Nombre del distrito
            $table->string('code')->nullable(); // Código del distrito
            $table->boolean('active')->default(true); // Estado del distrito (activo por defecto)
            $table->foreignId('canton_id')->nullable()->constrained('cantons')->nullOnDelete(); // Llave foránea con canton
            $table->timestamps(); // Campos created_at y updated_at
            $table->unique('id', 'disctrict_pkey'); // Llave única personalizada
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};

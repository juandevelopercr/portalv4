<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIdentificationTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('identification_types', function (Blueprint $table) {
            $table->id(); // Crea un campo id con AUTO_INCREMENT y PRIMARY KEY
            $table->string('name', 100)->nullable(); // Longitud ajustada a 100 caracteres
            $table->string('code', 2)->nullable(); // Longitud ajustada a 2 caracteres            
            $table->boolean('active')->default(true); // Estado del usuario
            $table->timestamps(); // Crea created_at y updated_at
            $table->unique('id', 'identification_type_pkey'); // Llave única personalizada
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('identification_types');
    }
}

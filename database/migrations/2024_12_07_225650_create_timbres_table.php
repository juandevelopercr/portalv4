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
        Schema::create('timbres', function (Blueprint $table) {
            $table->id();
            $table->decimal('base', 18, 5);
            $table->decimal('porcada', 18, 5);
            $table->integer('tipo')->default(1)->comment('1: Timbre Inscripciones 2: Timbre Traspasos de Vehículos');
            $table->integer('orden')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timbres');
    }
};

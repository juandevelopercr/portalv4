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
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // Solo para PR
            $table->unsignedBigInteger('emitter_id')->nullable(); // Para otros documentos
            $table->string('document_type', 10); // PR, FE, TE, NDE, NCE, FEC, FEE, REP, NC, ND.
            $table->unsignedInteger('current_sequence')->default(0); // Entero de hasta 10 dígitos
            $table->timestamps();

            // Evitar duplicados
            $table->unique(['user_id', 'document_type']);
            $table->unique(['emitter_id', 'document_type']);

            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('emitter_id')->references('id')->on('business_locations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
    }
};

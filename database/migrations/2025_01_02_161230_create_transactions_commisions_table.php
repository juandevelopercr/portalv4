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
        Schema::create('transactions_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->foreignId('centro_costo_id')->constrained('centro_costos')->onDelete('cascade');
            $table->string('abogado_encargado', 100)->nullable();
            $table->foreignId('comisionista_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('percent', 10, 2)->default(0.00);
            $table->decimal('commission_percent', 10, 2)->default(0.00);
            $table->boolean('comision_pagada')->default(false);
            $table->date('comision_pagada_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions_commissions');
    }
};

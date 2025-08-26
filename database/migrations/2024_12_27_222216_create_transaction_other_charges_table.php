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
        Schema::create('transactions_other_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_line_id')->constrained('transactions_lines')->onDelete('cascade');
            $table->foreignId('additional_charge_type_id')->constrained('additional_charge_types')->onDelete('cascade');
            $table->string('additional_charge_other', 100)->nullable()
                ->comment('Obligatorio si el código es 99 de “Otros”');
            $table->string('third_party_identification_type', 2)->nullable()
                ->comment('Obligatorio cuando hay cédula física/jurídica/NITE/DIMEX');
            $table->string('third_party_identification', 20)->nullable()
                ->comment('Formato según tipo de documento, sin guiones ni ceros al inicio');
            $table->string('third_party_name', 100)->nullable();
            $table->string('detail', 160);
            $table->decimal('percent', 9, 5)->nullable()->default(0.00000)
                ->comment('Porcentaje o monto expresado como número entero');
            $table->bigInteger('quantity');
            $table->decimal('amount', 18, 5)
                ->comment('Monto total del cargo, debe ser mayor a cero');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions_other_charges');
    }
};

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
        Schema::create('transactions_lines_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_line_id')->constrained('transactions_lines')->onDelete('cascade');
            $table->decimal('discount_percent', 4, 2)->default(0.00);
            $table->decimal('discount_amount', 18, 5)->default(0.00000);
            $table->foreignId('discount_type_id')->constrained('discount_types')->onDelete('cascade');
            $table->string('discount_type_other', 100)->nullable();
            $table->string('nature_discount', 80)->nullable()->comment('Obligatorio para el código 99 de “Otros”');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions_lines_discounts');
    }
};

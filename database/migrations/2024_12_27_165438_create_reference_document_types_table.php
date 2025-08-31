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
        Schema::create('reference_document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->string('code', 2);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique('id', 'reference_document_type_pkey');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reference_document_types');
    }
};

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
        Schema::create('exoneration_types', function (Blueprint $table) {
            $table->id(); // AUTO_INCREMENT PRIMARY KEY
            $table->string('name', 191);
            $table->string('code', 2);
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps(); // created_at and updated_at
            $table->unique('id', 'exoneration_document_type_pkey'); // UNIQUE KEY
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exoneration_document_type');
    }
};

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
        Schema::create('selling_price_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->notNullable();
            $table->text('description')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->unsignedBigInteger('business_id');
            $table->boolean('active')->default(true);
            $table->softDeletes(); // Campo deleted_at para Soft Deletes
            $table->timestamps();

            // Foreign Key
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('selling_price_groups');
    }
};

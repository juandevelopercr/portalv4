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
        Schema::create('cabys', function (Blueprint $table) {
            $table->id();
            $table->string('category1', 191);
            $table->text('description1', 191);
            $table->string('category2', 191);
            $table->text('description2', 191);
            $table->string('category3', 191);
            $table->text('description3', 191);
            $table->string('category4', 191);
            $table->text('description4', 191);
            $table->string('category5', 191);
            $table->text('description5', 191);
            $table->string('category6', 191);
            $table->text('description6', 191);
            $table->string('category7', 191);
            $table->text('description7', 191);
            $table->string('category8', 191);
            $table->text('description8', 191);
            $table->string('code', 191);
            $table->text('description_service', 191);
            $table->string('tax', 191);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique('id', 'cabys_pkey');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cabys');
    }
};

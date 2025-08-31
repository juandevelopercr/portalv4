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
        Schema::create('invoice_layouts', function (Blueprint $table) {
            $table->id(); // ID con AUTO_INCREMENT y PRIMARY KEY
            $table->string('name', 191)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->notNullable();
            $table->text('header_text')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('invoice_no_prefix', 191)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('quotation_no_prefix', 191)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('invoice_heading', 191)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('sub_heading_line1', 191)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('quotation_heading', 191)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->text('footer_text')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->boolean('is_default')->default(false); // Predeterminado como 0
            $table->unsignedBigInteger('business_id');
            $table->string('design', 190)->default('classic')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->boolean('show_qr')->default(false);
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
        Schema::dropIfExists('invoice_layouts');
    }
};

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
    Schema::create('contacts', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('business_id');
      $table->enum('type', ['customer', 'supplier']);
      $table->string('name', 100)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->string('commercial_name', 80)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->string('email', 191)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->text('email_cc')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->string('code', 191)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->unsignedBigInteger('condition_sale_id')->nullable();
      $table->unsignedBigInteger('identification_type_id')->nullable();
      $table->string('identification', 12)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->unsignedBigInteger('country_id')->nullable();
      $table->unsignedBigInteger('province_id')->nullable();
      $table->unsignedBigInteger('canton_id')->nullable();
      $table->unsignedBigInteger('district_id')->nullable();
      $table->string('other_signs', 255)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->text('address')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->string('zip_code', 191)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->date('dob')->nullable();
      $table->string('phone', 191)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');

      $table->enum('invoice_type', ['FACURA', 'TIQUETE'])->nullable();

      $table->unsignedInteger('pay_term_number')->nullable();
      $table->enum('pay_term_type', ['days', 'months'])->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->decimal('credit_limit', 22, 4)->nullable();
      $table->decimal('balance', 22, 4)->nullable()->default('0.0000');
      $table->unsignedInteger('total_rp')->nullable()->default(0);
      $table->unsignedInteger('total_rp_used')->nullable()->default(0);
      $table->unsignedInteger('total_rp_expired')->nullable()->default(0);
      $table->text('shipping_address')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->unsignedBigInteger('customer_group_id')->nullable();
      $table->boolean('is_default')->nullable()->default(false);
      $table->boolean('active')->nullable()->default(true);
      $table->boolean('aplicarImpuesto')->nullable()->default(true);
      $table->unsignedBigInteger('created_by');
      $table->softDeletes();
      $table->timestamps();

      // Foreign Keys
      $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
      $table->foreign('condition_sale_id')->references('id')->on('condition_sales')->onDelete('set null');
      $table->foreign('identification_type_id')->references('id')->on('identification_types')->onDelete('set null');
      $table->foreign('economic_activity_id')->references('id')->on('economic_activities');
      $table->foreign('customer_group_id')->references('id')->on('customer_groups')->onDelete('set null');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('contacts');
  }
};

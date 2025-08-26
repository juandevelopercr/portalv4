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
    Schema::create('business', function (Blueprint $table) {
      $table->id();
      $table->string('name', 191)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->string('business_type', 100)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->foreignId('currency_id')->constrained('currencies');
      $table->date('start_date')->nullable();
      $table->double('default_profit_percent', 5, 2)->default(0.00);
      $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
      $table->string('time_zone', 191)->default('America/Costa_Rica')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->tinyInteger('fy_start_month')->default(1);
      $table->enum('accounting_method', ['fifo', 'lifo', 'avco'])->default('fifo');
      $table->string('logo', 191)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->string('sku_prefix', 191)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->boolean('enable_product_expiry')->default(false);
      $table->enum('expiry_type', ['add_expiry', 'add_manufacturing'])->default('add_expiry');
      $table->enum('on_product_expiry', ['keep_selling', 'stop_selling', 'auto_delete'])->default('keep_selling');
      $table->integer('stop_selling_before')->default(0);
      $table->boolean('purchase_in_diff_currency')->default(false);
      $table->unsignedBigInteger('purchase_currency_id')->nullable();
      $table->unsignedInteger('transaction_edit_days')->default(30);
      $table->unsignedInteger('stock_expiry_alert_days')->default(30);
      $table->text('keyboard_shortcuts')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->text('pos_settings')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->text('manufacturing_settings')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->text('weighing_scale_setting')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->boolean('enable_brand')->default(true);
      $table->boolean('enable_category')->default(true);
      $table->boolean('enable_purchase_status')->default(true);
      $table->boolean('enable_lot_number')->default(false);
      $table->unsignedInteger('default_unit')->nullable();
      $table->boolean('enable_sub_units')->default(false);
      $table->boolean('enable_racks')->default(false);
      $table->boolean('enable_row')->default(false);
      $table->boolean('enable_position')->default(false);
      $table->boolean('enable_editing_product_from_purchase')->default(true);
      $table->boolean('enable_inline_tax')->default(true);
      $table->boolean('enable_inline_tax_purchase')->default(true);
      $table->enum('currency_symbol_placement', ['before', 'after'])->default('before');
      $table->string('date_format', 191)->default('m/d/Y')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->enum('time_format', ['12', '24'])->default('24');
      $table->text('ref_no_prefixes')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->unsignedBigInteger('created_by')->nullable();
      $table->text('email_settings')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->text('sms_settings')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->string('host_smpt', 100)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->string('user_smtp', 100)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->string('pass_smtp', 100)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->string('puerto_smpt', 10)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->string('smtp_encryptation', 100)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->string('email_notificacion_smtp', 100)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->string('proveedorSistemas', 20)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->string('registro_medicamento', 100)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->string('forma_farmaceutica', 3)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->text('notification_email')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');

      $table->string('host_imap', 100)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->string('user_imap', 100)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->string('pass_imap', 100)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->string('puerto_imap', 10)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->string('imap_encryptation', 100)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
      $table->boolean('active')->default(true);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('business');
  }
};

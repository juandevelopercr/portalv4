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
        Schema::create('contacts_economic_activities', function (Blueprint $table) {
            $table->unsignedBigInteger('contact_id'); // Clave foránea a contacts
            $table->unsignedBigInteger('economic_activity_id'); // Clave foránea a economic_activities

            // Claves foráneas con nombres personalizados
            $table->foreign('contact_id', 'fk_contacts_economic_contact')
                ->references('id')->on('contacts')
                ->onDelete('cascade');

            $table->foreign('economic_activity_id', 'fk_contacts_economic_activity')
                ->references('id')->on('economic_activities')
                ->onDelete('cascade');

            // Índices con nombres personalizados
            $table->index('contact_id', 'idx_contact_id');
            $table->index('economic_activity_id', 'idx_economic_activity_id');

            // Clave primaria compuesta con nombre personalizado
            $table->primary(['contact_id', 'economic_activity_id'], 'pk_contact_economic_activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts_economic_activities');
    }
};

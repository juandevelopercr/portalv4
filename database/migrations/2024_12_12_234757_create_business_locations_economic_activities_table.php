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
        Schema::create('business_locations_economic_activities', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id'); // Clave foránea a business_locations
            $table->unsignedBigInteger('economic_activity_id'); // Clave foránea a economic_activities

            // Claves foráneas
            $table->foreign('location_id', 'business_locations_location_fk')
                ->references('id')->on('business_locations')
                ->onDelete('cascade');

            $table->foreign('economic_activity_id', 'economic_activities_fk')
                ->references('id')->on('economic_activities')
                ->onDelete('cascade');

            // Índices con nombres personalizados
            $table->index('location_id', 'idx_location_id');
            $table->index('economic_activity_id', 'idx_economic_activity_id');

            // Clave primaria compuesta
            $table->primary(['location_id', 'economic_activity_id'], 'pk_location_economic_activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_locations_economic_activities');
    }
};

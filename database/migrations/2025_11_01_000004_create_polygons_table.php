<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('polygons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->geometry('geometry', 'POLYGON', 4326);
            $table->foreignId('producer_id')->nullable()->constrained()->onDelete('set null'); // ← CAMBIAR
            $table->foreignId('parish_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('area_ha', 10, 2)->nullable();
            $table->boolean('is_active')->default(true); // ← FALTABA
            $table->string('detected_parish')->nullable();
            $table->string('detected_municipality')->nullable();
            $table->string('detected_state')->nullable();
            $table->decimal('centroid_lat', 10, 8)->nullable();
            $table->decimal('centroid_lng', 11, 8)->nullable();
            $table->json('location_data')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('producer_id');
            $table->index('parish_id');
            $table->spatialIndex('geometry'); // ← AGREGAR ÍNDICE ESPACIAL
        });
    }

    public function down()
    {
        Schema::dropIfExists('polygons');
    }
};
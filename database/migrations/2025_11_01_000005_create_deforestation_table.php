<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración (Crea la tabla).
     */
    public function up()
    {
        Schema::create('deforestation', function (Blueprint $table) {
            $table->id();
            // Relación con el polígono
            $table->foreignId('polygon_id')->constrained()->onDelete('cascade');
            
            // Regresamos al campo único 'year'
            $table->integer('year');
            
            // Mantenemos la alta precisión para el área (4 decimales)
            $table->decimal('deforested_area_ha', 12, 4);
            $table->decimal('percentage_loss', 5, 2);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Ajustamos los índices para el campo 'year'
            // unique: evita que un mismo polígono tenga dos registros para el mismo año
            $table->unique(['polygon_id', 'year'], 'deforestation_polygon_year_unique');
            $table->index(['polygon_id', 'year'], 'deforestation_year_index');
        });
    }

    /**
     * Revierte la migración (Elimina la tabla).
     */
    public function down()
    {
        Schema::dropIfExists('deforestation');
    }
};
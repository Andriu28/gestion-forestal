<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Creación de la Tabla de Deforestación (deforestation)
 * 
 * Almacena la serie temporal de pérdida de biomasa boscosa por Parcela y año:
 * - Vinculación en cascada con el Parcela (si se elimina definitivamente el Parcela, se purgan sus registros).
 * - Restricción de unicidad compuesta [polygon_id, year] que impide tener duplicados para un mismo año en un lote.
 * - Registro métrico exacto: superficie talada en hectáreas (4 decimales) y tasa porcentual de pérdida (2 decimales).
 */
return new class extends Migration
{
    /**
     * Ejecuta la migración (Crea la tabla).
     */
    public function up()
    {
        Schema::create('deforestation', function (Blueprint $table) {
            $table->id();
            
            // Relación foránea: si el Parcela es eliminado físicamente de la base de datos,
            // se eliminan en cascada sus análisis satelitales asociados.
            $table->foreignId('polygon_id')->constrained()->onDelete('cascade');
            
            // Año calendario de la observación satelital (ej. 2018, 2021, etc.)
            $table->integer('year');
            
            // Superficie arbórea talada o perdida en hectáreas (precisión de 4 decimales)
            $table->decimal('deforested_area_ha', 12, 4);
            
            // Porcentaje que representa la pérdida sobre la superficie total del lote (0.00% a 100.00%)
            $table->decimal('percentage_loss', 5, 2);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Restricción UNIQUE: garantiza a nivel de motor de base de datos que no existan
            // dos lecturas para el mismo año y la misma parcela
            $table->unique(['polygon_id', 'year'], 'deforestation_polygon_year_unique');
            
            // Índice compuesto para acelerar consultas analíticas y gráficas cronológicas
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
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Generalización del Tipo Geométrico a GEOMETRY
 * 
 * Modifica la columna `geometry` en la tabla `polygons`:
 * - Originalmente estaba tipificada como `POLYGON`, lo que rechazaba geometrías compuestas.
 * - Al cambiar a `GEOMETRY` (conservando el SRID 4326), se habilita la compatibilidad con
 *   `MultiPolygon` (fincas compuestas por múltiples lotes o islas no contiguas),
 *   formato muy común al importar capas desde archivos Shapefile o KML.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('polygons', function (Blueprint $table) {
            // Amplía la restricción de tipo espacial para admitir tanto Polygon como MultiPolygon
            $table->geometry('geometry', 'GEOMETRY', 4326)->change();
        });
    }

    public function down()
    {
        Schema::table('polygons', function (Blueprint $table) {
            // Revierte la restricción únicamente a Polygon simple
            $table->geometry('geometry', 'POLYGON', 4326)->change();
        });
    }
};
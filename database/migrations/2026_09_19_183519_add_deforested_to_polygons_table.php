<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migración: Incorporación del Campo Booleano Indexado `deforested`
 * 
 * Optimización de rendimiento para el filtrado en listados y vistas del mapa:
 * 1. Agrega la columna booleana `deforested` (por defecto false) con su índice B-Tree.
 * 2. Evita tener que realizar costosas subconsultas `EXISTS (SELECT 1 FROM deforestation...)`
 *    cada vez que se solicita el listado o las capas del mapa de polígonos.
 * 3. Ejecuta una sentencia SQL de actualización retroactiva inmediata:
 *    Marca como `deforested = true` a todos aquellos polígonos que ya tenían registros históricos
 *    con pérdida de bosque mayor a 0 hectáreas en la tabla `deforestation`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polygons', function (Blueprint $table) {
            // Bandera booleana rápida con índice para filtros instantáneos
            $table->boolean('deforested')->default(false)->after('is_active');
            $table->index('deforested');
        });

        // Actualización retroactiva de datos existentes:
        // Identifica polígonos con deforestación activa comprobada y actualiza su bandera
        DB::statement("
            UPDATE polygons
            SET deforested = true
            WHERE id IN (
                SELECT DISTINCT polygon_id
                FROM deforestation
                WHERE deforested_area_ha > 0
                  AND deleted_at IS NULL
            )
        ");
    }

    public function down(): void
    {
        Schema::table('polygons', function (Blueprint $table) {
            $table->dropIndex(['deforested']);
            $table->dropColumn('deforested');
        });
    }
};
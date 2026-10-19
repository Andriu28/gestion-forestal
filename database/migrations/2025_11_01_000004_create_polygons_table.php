<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Creación de la Tabla de Parcelas (polygons)
 * 
 * Define la estructura de datos espacial y alfanumérica de las parcelas o predios:
 * - Soporte geográfico nativo mediante PostGIS en el sistema de referencia espacial WGS84 (SRID 4326).
 * - Relaciones con productores agroforestales y la división político-territorial (parroquia).
 * - Precisión decimal para medición de superficies agrarias (área en hectáreas con 4 decimales).
 * - Índices espaciales tipo GiST para acelerar consultas topológicas (intersecciones, contención, centroides).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('polygons', function (Blueprint $table) {
            $table->id(); // Identificador primario único autoincremental
            
            $table->string('name'); // Nombre o denominación del lote/fundo
            $table->text('description')->nullable(); // Notas descriptivas o de campo
            
            // Columna geométrica PostGIS: tipo POLYGON en coordenadas GPS estándar WGS84 (SRID 4326)
            $table->geometry('geometry', 'POLYGON', 4326);
            
            // Relación foránea con el productor titular (set null para no borrar el lote si se da de baja al productor)
            $table->foreignId('producer_id')->nullable()->constrained()->onDelete('set null');
            
            // Relación foránea con la parroquia asignada (set null para permitir parcelas pendientes de clasificar)
            $table->foreignId('parish_id')->nullable()->constrained()->onDelete('set null');
            
            // Área superficial: hasta 12 dígitos en total y 4 decimales (ej. 99,999,999.9999 Ha)
            $table->decimal('area_ha', 12, 4)->nullable();
            
            // Estado operativo para habilitar/deshabilitar la parcela en inventario
            $table->boolean('is_active')->default(true);
            
            // Nombres detectados automáticamente por geocodificación inversa desde el mapa
            $table->string('detected_parish')->nullable();
            $table->string('detected_municipality')->nullable();
            $table->string('detected_state')->nullable();
            
            // Coordenadas del baricentro/centroide para centrar el visor de mapas sin recalcular la geometría
            $table->decimal('centroid_lat', 10, 8)->nullable(); // 8 decimales para precisión métrica GPS
            $table->decimal('centroid_lng', 11, 8)->nullable();
            
            // Metadatos adicionales de geolocalización, auditoría y propiedades originales del Shapefile
            $table->json('location_data')->nullable();
            
            $table->timestamps(); // Fechas de creación y actualización
            $table->softDeletes(); // Borrado lógico para recuperación de desastres
            
            // Índices B-Tree convencionales para acelerar búsquedas y filtros por productor o parroquia
            $table->index('producer_id');
            $table->index('parish_id');
            
            // Índice espacial GiST (Generalized Search Tree) esencial para consultas PostGIS rápidas
            $table->spatialIndex('geometry');
        });
    }

    public function down()
    {
        Schema::dropIfExists('polygons');
    }
};
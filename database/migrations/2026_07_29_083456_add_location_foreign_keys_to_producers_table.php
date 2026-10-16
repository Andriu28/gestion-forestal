<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Incorporación de Claves Foráneas de Ubicación a Productores
 * 
 * Vincula al productor con la estructura territorial formal:
 * - Agrega `state_id`, `municipality_id` y `parish_id` con `nullOnDelete` para no borrar al productor
 *   si alguna división administrativa es reclasificada en el catálogo.
 * - Índices B-Tree independientes en cada nivel para optimizar las consultas de agregación y reportes.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('producers', function (Blueprint $table) {
            // Relaciones foráneas con la división territorial
            $table->foreignId('state_id')->nullable()->after('address')->constrained()->nullOnDelete();
            $table->foreignId('municipality_id')->nullable()->after('state_id')->constrained()->nullOnDelete();
            $table->foreignId('parish_id')->nullable()->after('municipality_id')->constrained()->nullOnDelete();

            // Índices para acelerar filtros geográficos y generación de listados
            $table->index('state_id');
            $table->index('municipality_id');
            $table->index('parish_id');
        });
    }

    public function down()
    {
        Schema::table('producers', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
            $table->dropForeign(['municipality_id']);
            $table->dropForeign(['parish_id']);
            $table->dropColumn(['state_id', 'municipality_id', 'parish_id']);
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Creación de la Tabla de Estados (states)
 * 
 * Almacena el primer nivel jerárquico territorial de Venezuela:
 * - Columna `name` única (`unique`) para evitar duplicaciones en el catálogo de entidades federales.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            
            // Nombre oficial del Estado (ej. 'Sucre', 'Monagas') con restricción de unicidad
            $table->string('name', 100)->unique();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('states');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Creación de la Tabla de Productores (producers)
 * 
 * Tabla base para registrar los titulares y responsables de los predios:
 * - Soporte para personas naturales (nombre y apellido) y jurídicas (razón social en name).
 * - Borrado lógico (SoftDeletes) para preservar la trazabilidad histórica de parcelas asignadas.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('producers', function (Blueprint $table) {
            $table->id();
            
            $table->string('name');                 // Nombre del productor o razón social de la empresa
            $table->string('lastname')->nullable(); // Apellidos (nulo para empresas)
            $table->text('description')->nullable();// Información complementaria de contacto o actividad productiva
            $table->boolean('is_active')->default(true); // Control de habilitación operativa
            
            $table->timestamps();
            $table->softDeletes();                  // Permite recuperar el registro si fue desactivado por error
        });
    }

    public function down()
    {
        Schema::dropIfExists('producers');
    }
};
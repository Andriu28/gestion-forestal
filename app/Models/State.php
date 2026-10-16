<?php
// [file name]: app/Models/State.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo State (Estado / Entidad Federal)
 * 
 * Representa el primer nivel jerárquico de la división político-territorial de Venezuela
 * (ej. Sucre, Barinas, Portuguesa, Bolívar, etc.).
 * 
 * Agrupa los municipios subordinados y permite consultar en cascada todas las parroquias
 * pertenecientes al estado mediante relaciones HasManyThrough.
 */
class State extends Model
{
    use HasFactory;

    /**
     * Atributos asignables:
     * - name: Nombre oficial del Estado (ej. 'Sucre').
     */
    protected $fillable = [
        'name'
    ];

    // =========================================================================
    // Relaciones Jerárquicas
    // =========================================================================

    /**
     * Municipios que pertenecen geográficamente a este Estado (Nivel 2).
     */
    public function municipalities()
    {
        return $this->hasMany(Municipality::class);
    }

    /**
     * Parroquias que pertenecen a este Estado a través de sus municipios (Nivel 3).
     * Relación indirecta que evita recorrer los municipios uno por uno en la aplicación.
     */
    public function parishes()
    {
        return $this->hasManyThrough(Parish::class, Municipality::class);
    }
}
<?php
// [file name]: app/Models/Municipality.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo Municipality (Municipio)
 * 
 * Representa el segundo nivel de la división político-territorial venezolana.
 * Cada municipio pertenece formalmente a un Estado (`state_id`) y a su vez
 * contiene una o más Parroquias civiles (`parishes`).
 */
class Municipality extends Model
{
    use HasFactory;

    /**
     * Atributos asignables:
     * - name: Nombre del término municipal (ej. 'Andrés Mata').
     * - state_id: Identificador del Estado al que está adscrito.
     */
    protected $fillable = [
        'name',
        'state_id'
    ];

    // =========================================================================
    // Relaciones Jerárquicas
    // =========================================================================

    /**
     * Estado padre al que pertenece este Municipio (Nivel 1).
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Parroquias que conforman la jurisdicción de este Municipio (Nivel 3).
     */
    public function parishes()
    {
        return $this->hasMany(Parish::class);
    }
}
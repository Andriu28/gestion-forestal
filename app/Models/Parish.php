<?php
// [file name]: app/Models/Parish.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo Parish (Parroquia)
 * 
 * Representa el tercer nivel y la unidad territorial mínima de división político-administrativa
 * en Venezuela (ej. 'San José de Areocual', 'Río Caribe', etc.).
 * 
 * Es la entidad de enlace fundamental para los predios forestales: cada polígono y cada productor
 * se vincula directamente a una parroquia, desde la cual se infiere automáticamente el municipio y el estado.
 */
class Parish extends Model
{
    use HasFactory;

    /**
     * Atributos asignables:
     * - name: Nombre oficial de la parroquia.
     * - municipality_id: Identificador del municipio padre.
     */
    protected $fillable = [
        'name',
        'municipality_id'
    ];

    // =========================================================================
    // Relaciones del Modelo
    // =========================================================================

    /**
     * Municipio al que pertenece esta Parroquia (Nivel 2).
     */
    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    /**
     * Polígonos o parcelas ubicadas dentro de los límites territoriales de esta Parroquia.
     */
    public function polygons()
    {
        return $this->hasMany(Polygon::class);
    }

    // =========================================================================
    // Scopes (Filtros de Búsqueda Territorial)
    // =========================================================================

    /**
     * Búsqueda en cascada ascendente:
     * Permite localizar parroquias buscando por su propio nombre,
     * o por el nombre del municipio o estado al que pertenecen.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
                    ->orWhereHas('municipality', function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhereHas('state', function($q2) use ($search) {
                              $q2->where('name', 'like', "%{$search}%");
                          });
                    });
    }
}
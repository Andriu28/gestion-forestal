<?php
// [file name]: app/Models/Polygon.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class Polygon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'geometry',
        'producer_id',
        'parish_id',
        'area_ha',
        'is_active',
        'detected_parish',
        'detected_municipality', 
        'detected_state',
        'centroid_lat',
        'centroid_lng',
        'location_data'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'area_ha' => 'decimal:2',
        'location_data' => 'array',
        'centroid_lat' => 'decimal:6',
        'centroid_lng' => 'decimal:6'
    ];

    // Relaciones
    public function producer()
    {
        return $this->belongsTo(Producer::class);
    }

    public function parish()
    {
        return $this->belongsTo(Parish::class);
    }

    // Acceso a municipio y estado a través de la parroquia
    public function municipality()
    {
        return $this->hasOneThrough(Municipality::class, Parish::class, 'id', 'id', 'parish_id', 'municipality_id');
    }

    public function state()
    {
        return $this->hasOneThrough(State::class, Parish::class, 'id', 'id', 'parish_id', 'municipality_id')
                    ->through('municipality');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeWithoutProducer(Builder $query): Builder
    {
        return $query->whereNull('producer_id');
    }

    public function scopeWithProducer(Builder $query): Builder
    {
        return $query->whereNotNull('producer_id');
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('detected_parish', 'like', "%{$search}%")
                    ->orWhere('detected_municipality', 'like', "%{$search}%")
                    ->orWhere('detected_state', 'like', "%{$search}%")
                    ->orWhereHas('producer', function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('lastname', 'like', "%{$search}%");
                    })
                    ->orWhereHas('parish', function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhereHas('municipality', function($q2) use ($search) {
                              $q2->where('name', 'like', "%{$search}%")
                                 ->orWhereHas('state', function($q3) use ($search) {
                                     $q3->where('name', 'like', "%{$search}%");
                                 });
                          });
                    });
    }

    public function scopeInParish(Builder $query, int $parishId): Builder
    {
        return $query->where('parish_id', $parishId);
    }

    // Métodos de utilidad
    public function hasProducer(): bool
    {
        return !is_null($this->producer_id);
    }

    public function getProducerNameAttribute(): string
    {
        return $this->hasProducer() ? $this->producer->name : 'Sin productor';
    }

    public function getTypeAttribute(): string
    {
        return $this->hasProducer() ? 'with_producer' : 'without_producer';
    }

    public function getAreaFormattedAttribute(): string
    {
        return $this->area_ha ? number_format($this->area_ha, 2) . ' Ha' : 'N/A';
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->trashed()) {
            return '<span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Eliminado</span>';
        }
        
        return $this->is_active 
            ? '<span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Activo</span>'
            : '<span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">Inactivo</span>';
    }

    // Nuevo: obtener ubicación detectada formateada
    public function getDetectedLocationAttribute(): string
    {
        if ($this->detected_parish && $this->detected_municipality && $this->detected_state) {
            return "{$this->detected_parish}, {$this->detected_municipality}, {$this->detected_state}";
        }
        
        // Si tenemos parroquia relacionada, usar esa información
        if ($this->parish) {
            return "{$this->parish->name}, {$this->parish->municipality->name}, {$this->parish->municipality->state->name}";
        }
        
        return 'Ubicación no detectada';
    }

    // Método para buscar parroquia por nombre
    public static function findParishByName($parishName, $municipalityName = null, $stateName = null)
    {
        // Normalizar nombres
        $normalizedParish = self::normalizeName($parishName);
        $normalizedMunicipality = $municipalityName ? self::normalizeName($municipalityName) : null;
        $normalizedState = $stateName ? self::normalizeName($stateName) : null;
        
        $query = Parish::with(['municipality.state'])
                      ->where(function($q) use ($normalizedParish) {
                          // Búsqueda exacta primero
                          $q->where('name', 'like', $normalizedParish)
                            // Luego búsqueda parcial
                            ->orWhere('name', 'like', "%{$normalizedParish}%");
                      });
        
        if ($normalizedMunicipality) {
            $query->whereHas('municipality', function($q) use ($normalizedMunicipality) {
                $q->where(function($q2) use ($normalizedMunicipality) {
                    $q2->where('name', 'like', $normalizedMunicipality)
                       ->orWhere('name', 'like', "%{$normalizedMunicipality}%");
                });
            });
        }
        
        if ($normalizedState) {
            $query->whereHas('municipality.state', function($q) use ($normalizedState) {
                $q->where(function($q2) use ($normalizedState) {
                    $q2->where('name', 'like', $normalizedState)
                       ->orWhere('name', 'like', "%{$normalizedState}%");
                });
            });
        }
        
        return $query->first();
    }

    // Método auxiliar para normalizar nombres
    private static function normalizeName($name)
    {
        if (!$name) return '';
        
        // Convertir a minúsculas y eliminar espacios extras
        $name = trim(mb_strtolower($name, 'UTF-8'));
        
        // Reemplazar caracteres acentuados
        $search = array('á', 'é', 'í', 'ó', 'ú', 'ñ');
        $replace = array('a', 'e', 'i', 'o', 'u', 'n');
        $name = str_replace($search, $replace, $name);
        
        // Eliminar palabras comunes que podrían interferir
        $name = preg_replace('/\b(parroquia|municipio|estado|sector|zona)\b/i', '', $name);
        $name = trim(preg_replace('/\s+/', ' ', $name));
        
        return $name;
    }
}
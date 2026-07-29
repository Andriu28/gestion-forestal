<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity; // Añadir esta línea
use Spatie\Activitylog\LogOptions; // Añadir esta línea

class Producer extends Model
{
    use HasFactory, SoftDeletes, LogsActivity; // Añadir LogsActivity aquí

    protected $fillable = [
        'name',
        'lastname',
        'description',
        'is_active',
        'latitude',
        'longitude',
        'address',
        'state_id',
        'municipality_id',
        'parish_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ============================================
    // RELACIONES CON DIVISIÓN TERRITORIAL
    // ============================================
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function parish()
    {
        return $this->belongsTo(Parish::class);
    }

    // ============================================
    // ACTIVITY LOG
    // ============================================
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'lastname', 'description', 'is_active', 'state_id', 'municipality_id', 'parish_id'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(function(string $eventName) {
                $producerName = $this->name && $this->lastname 
                    ? "{$this->name} {$this->lastname}"
                    : ($this->name ?: 'Productor #' . $this->id);
                
                switch($eventName) {
                    case 'created':
                        return "Productor '{$producerName}' fue creado";
                        
                    case 'updated':
                        $changes = $this->getChanges();
                        unset($changes['updated_at']);
                        
                        if (count($changes) === 1 && isset($changes['is_active'])) {
                            $newStatus = $changes['is_active'] ? 'activado' : 'desactivado';
                            return "Productor '{$producerName}' fue {$newStatus}";
                        }
                        
                        $changedFields = array_keys($changes);
                        if (count($changedFields) === 1) {
                            $field = $changedFields[0];
                            $fieldNames = [
                                'name' => 'nombre',
                                'lastname' => 'apellido',
                                'description' => 'descripción',
                                'is_active' => 'estado',
                                'state_id' => 'estado',
                                'municipality_id' => 'municipio',
                                'parish_id' => 'parroquia',
                            ];
                            
                            $fieldName = $fieldNames[$field] ?? $field;
                            return "Productor '{$producerName}' - {$fieldName} actualizado";
                        }
                        
                        return "Productor '{$producerName}' fue actualizado";
                        
                    case 'deleted':
                        return "Productor '{$producerName}' fue eliminado";
                        
                    case 'restored':
                        return "Productor '{$producerName}' fue restaurado";
                        
                    default:
                        return "Productor '{$producerName}' - {$eventName}";
                }
            })
            ->dontSubmitEmptyLogs();
    }

    // ============================================
    // SCOPES
    // ============================================
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('lastname', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              // Buscar por nombre de estado, municipio o parroquia (usando relaciones)
              ->orWhereHas('state', function ($q2) use ($search) {
                  $q2->where('name', 'like', "%{$search}%");
              })
              ->orWhereHas('municipality', function ($q2) use ($search) {
                  $q2->where('name', 'like', "%{$search}%");
              })
              ->orWhereHas('parish', function ($q2) use ($search) {
                  $q2->where('name', 'like', "%{$search}%");
              });
        });
    }

    
   // ============================================
   // RELACIÓN CON POLYGONS (ya existente)
   // ============================================
    public function polygons()
    {
        return $this->hasMany(Polygon::class);
    }

    // ============================================
    // ACCESSORS (opcional, para mostrar nombres)
    // ============================================
    public function getStateNameAttribute()
    {
        return $this->state ? $this->state->name : 'Sin estado';
    }

    public function getMunicipalityNameAttribute()
    {
        return $this->municipality ? $this->municipality->name : 'Sin municipio';
    }

    public function getParishNameAttribute()
    {
        return $this->parish ? $this->parish->name : 'Sin parroquia';
    }

    // Obtener ubicación completa formateada
    public function getFullLocationAttribute()
    {
        $parts = [];
        if ($this->parish) $parts[] = $this->parish->name;
        if ($this->municipality) $parts[] = $this->municipality->name;
        if ($this->state) $parts[] = $this->state->name;
        return implode(', ', $parts) ?: 'Sin ubicación';
    }
   
}
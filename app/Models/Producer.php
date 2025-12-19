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
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // En Producer.php - actualiza el método getActivitylogOptions()
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'lastname', 'description', 'is_active'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(function(string $eventName) {
                $producerName = $this->name && $this->lastname 
                    ? "{$this->name} {$this->lastname}"
                    : ($this->name ?: 'Productor #' . $this->id);
                
                switch($eventName) {
                    case 'created':
                        return "Productor '{$producerName}' fue creado";
                        
                    case 'updated':
                        // Detectar qué campo cambió específicamente
                        $changes = $this->getChanges();
                        unset($changes['updated_at']); // Quitar updated_at del log
                        
                        if (count($changes) === 1 && isset($changes['is_active'])) {
                            $newStatus = $changes['is_active'] ? 'activado' : 'desactivado';
                            return "Productor '{$producerName}' fue {$newStatus}";
                        }
                        
                        // Para múltiples cambios o cambios no específicos
                        $changedFields = array_keys($changes);
                        if (count($changedFields) === 1) {
                            $field = $changedFields[0];
                            $fieldNames = [
                                'name' => 'nombre',
                                'lastname' => 'apellido',
                                'description' => 'descripción',
                                'is_active' => 'estado'
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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
    }

    
   // Relación con polygons
    public function polygons()
    {
        return $this->hasMany(Polygon::class);
    }

   
}
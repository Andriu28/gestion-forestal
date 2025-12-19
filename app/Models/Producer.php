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

     // Añadir método para configuración del log de actividades
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
                        return "Productor '{$producerName}' fue actualizado";
                    case 'deleted':
                        return "Productor '{$producerName}' fue eliminado";
                    default:
                        return "Productor '{$producerName}' fue {$eventName}";
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
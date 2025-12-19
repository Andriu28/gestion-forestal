<?php
// [file name]: app/Models/Polygon.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Traits\LogsActivity; // Añadir
use Spatie\Activitylog\LogOptions; // Añadir

class Polygon extends Model
{
    use HasFactory, SoftDeletes, LogsActivity; // Añadir LogsActivity

    protected $fillable = [
        'name',
        'description',
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
        'centroid_lat' => 'double',
        'centroid_lng' => 'double',
    ];

    // Añadir configuración del log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'producer_id', 'parish_id', 'area_ha', 'is_active'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Polígono {$eventName}")
            ->dontSubmitEmptyLogs()
            ->logExcept(['detected_parish', 'detected_municipality', 'detected_state', 'centroid_lat', 'centroid_lng', 'location_data']); // Excluir campos calculados
    }

    // Relaciones
    public function producer()
    {
        return $this->belongsTo(Producer::class);
    }

    public function parish()
    {
        return $this->belongsTo(Parish::class);
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

    // Accesores útiles

    public function getProducerNameAttribute(): string
    {
        return $this->producer ? $this->producer->name : 'Sin productor';
    }

    public function getTypeAttribute(): string
    {
        return $this->producer_id ? 'with_producer' : 'without_producer';
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

    public function getDetectedLocationAttribute(): string
    {
        if ($this->detected_parish && $this->detected_municipality && $this->detected_state) {
            return "{$this->detected_parish}, {$this->detected_municipality}, {$this->detected_state}";
        }

        if ($this->parish) {
            return "{$this->parish->name}, {$this->parish->municipality->name}, {$this->parish->municipality->state->name}";
        }

        return 'Ubicación no detectada';
    }

    /**
     * Obtener geometría (GeoJSON) desde la base de datos si se necesita.
     */
    public function getGeometryGeoJson(): ?array
    {
        try {
            $res = DB::selectOne("SELECT ST_AsGeoJSON(geometry) AS geojson FROM polygons WHERE id = ?", [$this->id]);
            if ($res && $res->geojson) {
                return json_decode($res->geojson, true);
            }
        } catch (\Exception $e) {
            Log::error('Error al leer geometría', ['polygon_id' => $this->id, 'error' => $e->getMessage()]);
        }
        return null;
    }
}
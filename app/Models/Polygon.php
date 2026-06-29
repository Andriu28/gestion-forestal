<?php
// [file name]: app/Models/Polygon.php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Events\Created;
use App\Traits\LogsActivityWithDescriptions;

class Polygon extends Model
{
    use HasFactory, SoftDeletes, LogsActivityWithDescriptions;

    protected $fillable = [
        'name',
        'description',
        'producer_id',
        'parish_id',
        'area_ha',
        'is_active',
        'centroid_lat',
        'centroid_lng',
        'location_data',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'area_ha'       => 'decimal:4',
        'location_data' => 'array',
        'centroid_lat'  => 'double',
        'centroid_lng'  => 'double',
    ];

    // =========================================================================
    // Actividad (Spatie)
    // =========================================================================

    protected function getActivitylogAttributes(): array
    {
        return ['name', 'description', 'producer_id', 'parish_id', 'area_ha', 'is_active'];
    }

    protected function getActivityDescriptions(): array
    {
        return [
            'name'        => 'Nombre',
            'description' => 'Descripción',
            'producer_id' => 'Productor',
            'parish_id'   => 'Parroquia',
            'area_ha'     => 'Área (ha)',
            'is_active'   => 'Estado',
        ];
    }

    protected function getActivityPriority(): array
    {
        return ['name', 'is_active', 'area_ha', 'producer_id', 'parish_id', 'description'];
    }

    protected function getActivityLabel(): ?string
    {
        return $this->name;
    }

    // =========================================================================
    // Relaciones
    // =========================================================================

    public function producer()
    {
        return $this->belongsTo(Producer::class);
    }

    public function parish()
    {
        return $this->belongsTo(Parish::class);
    }

    public function deforestationAnalyses()
    {
        return $this->hasMany(Deforestation::class, 'polygon_id');
    }

    /** Alias de deforestationAnalyses para compatibilidad. */
    public function analyses()
    {
        return $this->hasMany(Deforestation::class, 'polygon_id');
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeWithProducer(Builder $query): Builder
    {
        return $query->whereNotNull('producer_id');
    }

    public function scopeWithoutProducer(Builder $query): Builder
    {
        return $query->whereNull('producer_id');
    }

    /**
     * Búsqueda por nombre, descripción, productor o jerarquía geográfica.
     * Envuelto en un grupo para que los OR no contaminen condiciones externas.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $q) use ($search) {
            $like = "%{$search}%";

            $q->where('name', 'like', $like)
              ->orWhere('description', 'like', $like)
              ->orWhereHas('producer', fn (Builder $pq) =>
                  $pq->where('name', 'like', $like)
                     ->orWhere('lastname', 'like', $like)
              )
              ->orWhereHas('parish', fn (Builder $rq) =>
                  $rq->where('name', 'like', $like)
                     ->orWhereHas('municipality', fn (Builder $mq) =>
                         $mq->where('name', 'like', $like)
                            ->orWhereHas('state', fn (Builder $sq) =>
                                $sq->where('name', 'like', $like)
                            )
                     )
              );
        });
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    public function getProducerNameAttribute(): string
    {
        return $this->producer
            ? "{$this->producer->name} {$this->producer->lastname}"
            : 'Sin productor';
    }

    public function getTypeAttribute(): string
    {
        return $this->producer_id ? 'with_producer' : 'without_producer';
    }

    public function getAreaFormattedAttribute(): string
    {
        return $this->area_ha
            ? number_format((float) $this->area_ha, 4) . ' Ha'
            : 'N/A';
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->trashed()) {
            return '<span class="inline-block px-3 py-1 text-xs font-semibold bg-red-600 text-white rounded-full">Eliminado</span>';
        }

        [$bg, $text] = $this->is_active
            ? ['bg-green-600', 'Activo']
            : ['bg-yellow-500', 'Inactivo'];

        return "<span class=\"inline-block px-3 py-1 text-xs font-semibold {$bg} text-white rounded-full\">{$text}</span>";
    }

    public function getFullLocationAttribute(): string
    {
        if ($this->parish) {
            $this->loadMissing('parish.municipality.state');
            $p = $this->parish;
            return "{$p->name}, {$p->municipality->name}, {$p->municipality->state->name}";
        }

        return 'Ubicación no asignada';
    }

    // ---- Campos detectados (leídos desde location_data) ---------------------

    public function getDetectedParishAttribute(): ?string
    {
        return $this->getFromLocationData('detected_parish');
    }

    public function getDetectedMunicipalityAttribute(): ?string
    {
        return $this->getFromLocationData('detected_municipality');
    }

    public function getDetectedStateAttribute(): ?string
    {
        return $this->getFromLocationData('detected_state');
    }

    // =========================================================================
    // Persistencia con geometría PostGIS (lógica que pertenece al modelo)
    // =========================================================================

    /**
     * Crea el registro incluyendo la geometría PostGIS.
     * Usa SQL solo para el INSERT con ST_GeomFromGeoJSON; después carga el
     * modelo con Eloquent para que Spatie registre el evento 'created'.
     *
     * @param  array  $data         Campos fillable del polígono.
     * @param  string $geoJsonGeometry  GeoJSON de la geometría (ya normalizado).
     * @return static
     * @throws \RuntimeException
     */
    public static function createWithGeometry(array $data, string $geoJsonGeometry): static
    {
        $now = now();

        $row = DB::selectOne(
            "INSERT INTO polygons
                (name, description, producer_id, parish_id, area_ha, is_active,
                centroid_lat, centroid_lng, location_data,
                geometry, created_at, updated_at)
            VALUES
                (?, ?, ?, ?, ?, ?,
                ?, ?, ?,
                ST_SetSRID(ST_GeomFromGeoJSON(?), 4326), ?, ?)
            RETURNING id",
            [
                $data['name'],
                $data['description'] ?? null,
                $data['producer_id'] ?? null,
                $data['parish_id'] ?? null,
                $data['area_ha'] ?? null,
                $data['is_active'] ?? true,
                $data['centroid_lat'] ?? null,
                $data['centroid_lng'] ?? null,
                isset($data['location_data']) ? json_encode($data['location_data'], JSON_UNESCAPED_UNICODE) : null,
                $geoJsonGeometry,
                $now,
                $now,
            ]
        );

        if (! isset($row->id)) {
            throw new \RuntimeException('No se pudo insertar el polígono (RETURNING id vacío).');
        }

        $polygon = static::with('parish.municipality.state')->findOrFail($row->id);

        // ===== CREACIÓN MANUAL DEL LOG =====
        activity()
            ->performedOn($polygon)
            ->causedBy(auth()->user())
            ->withProperties([
                'attributes' => [
                    'name'        => $polygon->name,
                    'description' => $polygon->description,
                    'producer_id' => $polygon->producer_id,
                    'parish_id'   => $polygon->parish_id,
                    'is_active'   => $polygon->is_active,
                ],
                'old' => null, // No hay valores antiguos en creación
            ])
            ->event('created')
            ->log( $polygon->getActivityLabel() . ' fue creado' );

        return $polygon;
    }

    /**
     * Actualiza los campos del polígono incluyendo la geometría PostGIS.
     * Separa el UPDATE de geometría (SQL crudo, necesario para PostGIS)
     * del UPDATE de campos normales (Eloquent, necesario para Spatie).
     *
     * @param  array  $data             Campos fillable a actualizar.
     * @param  string $geoJsonGeometry  GeoJSON de la geometría (ya normalizado).
     * @return bool
     */
    public function updateWithGeometry(array $data, string $geoJsonGeometry): bool
    {
        // 1. Actualizar solo la geometría con SQL (PostGIS no lo soporta Eloquent)
        DB::statement(
            'UPDATE polygons SET geometry = ST_SetSRID(ST_GeomFromGeoJSON(?), 4326) WHERE id = ?',
            [$geoJsonGeometry, $this->id]
        );

        // 2. Actualizar el resto con Eloquent → dispara el evento 'updated' → Spatie lo captura
        return $this->fill($data)->save();
    }

    /**
     * Recalcula área (ha) y centroide desde PostGIS y los persiste.
     * Usa DB::table para no volver a disparar eventos de Eloquent.
     */
    public function recalculateGeometryStats(): bool
    {
        try {
            $row = DB::selectOne(
                "SELECT
                     ST_Area(geometry::geography) / 10000  AS area_ha,
                     ST_AsGeoJSON(ST_Centroid(geometry))   AS centroid_geojson
                 FROM polygons
                 WHERE id = ?",
                [$this->id]
            );

            if (! $row) {
                return false;
            }

            $centroid = $row->centroid_geojson
                ? json_decode($row->centroid_geojson, true)
                : null;

            DB::table('polygons')->where('id', $this->id)->update([
                'area_ha'      => isset($row->area_ha) ? (float) $row->area_ha : null,
                'centroid_lat' => $centroid['coordinates'][1] ?? null,
                'centroid_lng' => $centroid['coordinates'][0] ?? null,
                'updated_at'   => now(),
            ]);

            $this->refresh();
            return true;

        } catch (\Throwable $e) {
            Log::error('Error al recalcular stats del polígono', [
                'polygon_id' => $this->id,
                'error'      => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Obtiene la geometría como array GeoJSON consultando PostGIS.
     * Útil para pre-cargar el mapa en la vista de edición.
     */
    public function getGeometryGeoJson(): ?array
    {
        try {
            $row = DB::selectOne(
                'SELECT ST_AsGeoJSON(geometry) AS geojson FROM polygons WHERE id = ?',
                [$this->id]
            );

            return ($row && $row->geojson)
                ? json_decode($row->geojson, true)
                : null;

        } catch (\Throwable $e) {
            Log::error('Error al leer geometría del polígono', [
                'polygon_id' => $this->id,
                'error'      => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Construye el array location_data enriquecido con los campos detectados
     * y una auditoría del momento de creación.
     */
    public function buildLocationDataForCreate(array $rawLocationData, array $detected, ?int $parishId): array
    {
        return array_merge($rawLocationData, [
            'detected_parish'       => $detected['parish'] ?? null,
            'detected_municipality' => $detected['municipality'] ?? null,
            'detected_state'        => $detected['state'] ?? null,
            'created_info'          => [
                'assigned_parish_id' => $parishId,
                'created_at'         => now()->toISOString(),
            ],
        ]);
    }

    /**
     * Fusiona los datos de detección entrantes con el location_data actual
     * del polígono, agregando una auditoría del update.
     */
    public function mergeLocationDataForUpdate(array $newRaw, array $detected, ?int $userId): array
    {
        $base = is_array($this->location_data) ? $this->location_data : [];

        // Sobreescribir con datos nuevos si vienen del request
        if (! empty($newRaw)) {
            $base = array_merge($base, $newRaw);
        }

        $base['detected_parish']       = $detected['parish'] ?? null;
        $base['detected_municipality'] = $detected['municipality'] ?? null;
        $base['detected_state']        = $detected['state'] ?? null;

        if (! empty($detected['parish'])) {
            $base['detected_info'] = [
                'detected_parish'       => $detected['parish'],
                'detected_municipality' => $detected['municipality'] ?? null,
                'detected_state'        => $detected['state'] ?? null,
                'updated_at'            => now()->toISOString(),
                'updated_by'            => $userId,
            ];
        }

        return $base;
    }

    // =========================================================================
    // Helpers privados
    // =========================================================================

    /**
     * Lee un campo desde el JSON location_data (soporta array o string).
     */
    private function getFromLocationData(string $key): ?string
    {
        $data = $this->location_data;

        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }

        return is_array($data) ? ($data[$key] ?? null) : null;
    }
}
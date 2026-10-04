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
        'deforested', 
        'centroid_lat',
        'centroid_lng',
        'location_data',
        'external_id',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'deforested'    => 'boolean',
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

    public function deforestations()
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

    public function scopeDeforested(Builder $query): Builder
    {
        return $query->where('deforested', true);
    }

    public function scopeWithoutDeforestation(Builder $query): Builder
    {
        return $query->where('deforested', false);
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

    public function getDeforestationBadgeAttribute(): string
    {
        if ($this->deforested) {
            return '<span class="inline-block px-3 py-1 text-xs font-semibold bg-red-600 text-white rounded-full">Con deforestación</span>';
        }
        return '<span class="inline-block px-3 py-1 text-xs font-semibold bg-green-600 text-white rounded-full">Sin deforestación</span>';
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
     *
     * @param array  $data
     * @param string $geoJsonGeometry
     * @param int    $srid            SRID de entrada del GeoJSON (por defecto 4326)
     * @param bool   $logActivity
     * @return static
     */
    public static function createWithGeometry(
        array $data,
        string $geoJsonGeometry,
        int $srid = 4326,
        bool $logActivity = true,
        ?\App\Models\User $causer = null,   // ← nuevo
    ): static {
        $now = now();

        $row = DB::selectOne(
            "INSERT INTO polygons
                (external_id, name, description, producer_id, parish_id, area_ha, is_active,
                centroid_lat, centroid_lng, location_data,
                geometry, created_at, updated_at)
            VALUES
                (?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?,
                ST_Transform(ST_SetSRID(ST_GeomFromGeoJSON(?), ?), 4326), ?, ?)
            RETURNING id",
            [
                $data['external_id'] ?? null,
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
                $srid,
                $now,
                $now,
            ]
        );

        if (! isset($row->id)) {
            throw new \RuntimeException('No se pudo insertar el polígono (RETURNING id vacío).');
        }

        $polygon = static::with('parish.municipality.state')->findOrFail($row->id);

        if ($logActivity) {
            activity()
                ->performedOn($polygon)
                ->causedBy($causer ?? auth()->user())
                ->withProperties([
                    'attributes' => [
                        'name'        => $polygon->name,
                        'description' => $polygon->description,
                        'producer_id' => $polygon->producer_id,
                        'parish_id'   => $polygon->parish_id,
                        'is_active'   => $polygon->is_active,
                    ],
                    'old' => null,
                ])
                ->event('created')
                ->log($polygon->getActivityLabel() . ' fue creado');
        }

        return $polygon;
    }

    /**
     * Actualiza los campos del polígono incluyendo la geometría PostGIS.
     *
     * @param array  $data
     * @param string $geoJsonGeometry
     * @param int    $srid
     * @param bool   $logActivity
     * @return bool
     */
    public function updateWithGeometry(array $data, string $geoJsonGeometry, int $srid = 4326, bool $logActivity = true): bool
    {
        DB::statement(
            'UPDATE polygons SET geometry = ST_Transform(ST_SetSRID(ST_GeomFromGeoJSON(?), ?), 4326) WHERE id = ?',
            [$geoJsonGeometry, $srid, $this->id]
        );

        $this->fill($data);

        if ($logActivity) {
            return $this->save();
        } else {
            return $this->saveQuietly();
        }
    }

    public function recalculateGeometryStats(): void
    {
        try {
            $stats = DB::selectOne("
                SELECT 
                    ST_Area(ST_Transform(geometry, 4326)::geography) / 10000 AS area_ha,
                    ST_X(ST_Centroid(geometry)) AS centroid_lng,
                    ST_Y(ST_Centroid(geometry)) AS centroid_lat
                FROM polygons
                WHERE id = ?
            ", [$this->id]);

            if ($stats) {
                $this->updateQuietly([
                    'area_ha' => $stats->area_ha ?? 0,
                    'centroid_lat' => $stats->centroid_lat ?? null,
                    'centroid_lng' => $stats->centroid_lng ?? null,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Error en recalculateGeometryStats', [
                'polygon_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            throw $e; // relanzar para que la transacción haga rollback
        }
    }

    /**
     * Obtiene el GeoJSON de la geometría almacenada en la base de datos.
     * Devuelve un string normalizado (sin espacios extra) para comparar.
     */
    public function getGeometryGeoJson(): string
    {
        // Si tu columna se llama 'geometry' y es de tipo geometry/geography en PostGIS
        // Puedes obtener el GeoJSON directamente desde la base de datos
        $geojson = DB::table('polygons')
            ->where('id', $this->id)
            ->value(DB::raw('ST_AsGeoJSON(geometry)'));
        
        // Normalizar: eliminar espacios en blanco y saltos de línea
        return $this->normalizeGeoJsonString($geojson ?: '{}');
    }

    /**
     * Normaliza un string GeoJSON para comparación (elimina espacios, saltos, etc.)
     */
    public function normalizeGeoJsonString(string $geojson): string
    {
        // Eliminar espacios en blanco, saltos de línea y tabulaciones
        return preg_replace('/\s+/', '', $geojson);
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

    
    /**
     * Crea un polígono a partir de los datos de un feature GeoJSON.
     * Internamente maneja el área (usando la proporcionada o recalculando).
     *
     * @param array $featureData Datos del feature (properties + geometry)
     * @param int   $srid       SRID del GeoJSON
     * @param array $extra      Datos adicionales (producer_id, parish_id, etc.)
     * @return static
     * @throws \Exception
     */
    public static function createFromGeoJsonFeature(array $featureData, int $srid, array $extra = []): static
    {
        $properties = $featureData['properties'] ?? [];
        $geometry = $featureData['geometry'];

        // Validar geometría (opcional, ya se hizo en el controlador)
        if (!isset($geometry['type']) || !in_array($geometry['type'], ['Polygon', 'MultiPolygon'])) {
            throw new \Exception('Geometría inválida. Solo se permiten Polygon o MultiPolygon.');
        }

        $geoJsonString = json_encode($geometry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Extraer datos del feature
        $externalId = $properties['id'] ?? null;
        $areaHa = $properties['Area_Ha'] ?? null;
        $name = $properties['name'] ?? ($extra['producer_name'] ?? 'Polígono importado');
        $description = $properties['description'] ?? null;
        $producerId = $extra['producer_id'] ?? null;
        $parishId = $extra['parish_id'] ?? null;

        // Preparar datos para el INSERT
        $data = [
            'external_id' => $externalId,
            'name' => $name,
            'description' => $description,
            'producer_id' => $producerId,
            'parish_id' => $parishId,
            'area_ha' => $areaHa,
            'is_active' => true,
            'location_data' => [
                'imported_from' => 'geojson',
                'original_properties' => $properties,
                'external_id' => $externalId,
            ],
        ];

        // Crear el polígono (esto usa createWithGeometry, que ya acepta SRID)
        $polygon = static::createWithGeometry($data, $geoJsonString, $srid, true);

        // Decidir si recalcular el área
        if (is_null($areaHa)) {
            $polygon->recalculateGeometryStats();
        } else {
            // Si se proporcionó área, actualizar sin recalcular (ya está en $data, pero por si acaso)
            $polygon->updateQuietly(['area_ha' => $areaHa]);
        }

        return $polygon;
    }

    /**
     * Recalcula y persiste el flag `deforested` según sus registros actuales.
     */
    public function refreshDeforestedFlag(): void
    {
        // Fuerza consulta fresca a la BD (no confía en relaciones ya cargadas)
        $hasDeforestation = $this->deforestations()
            ->where('deforested_area_ha', '>', 0)
            ->exists();

        if ((bool) $this->deforested !== $hasDeforestation) {
            $this->updateQuietly(['deforested' => $hasDeforestation]);
        }
    }

        /**
     * Analiza la deforestación de este polígono contra GFW en el rango dado.
     * Solo consulta los años que aún no están persistidos; los años ya
     * existentes en la tabla `deforestation` se reutilizan tal cual.
     *
     * Persiste los nuevos resultados y sincroniza el flag `deforested`.
     *
     * @return array<int, array{area__ha: float, status: string, year: int, error?: string}>
     *         Mapa [year => datos] con todos los años del rango.
     */
        public function analyzeDeforestationFromGFW(int $startYear, int $endYear, bool $persist = true): array
    {
        $geometry = json_decode($this->getGeometryGeoJson(), true);

        if (empty($geometry) || empty($geometry['type']) || empty($geometry['coordinates'])) {
            Log::warning("Polígono {$this->id} sin geometría; análisis omitido.");
            return [];
        }

        // 1. Años ya persistidos en BD
        $existing = $this->deforestations()
            ->whereBetween('year', [$startYear, $endYear])
            ->get()
            ->mapWithKeys(fn ($d) => [
                (int) $d->year => [
                    'area__ha' => (float) $d->deforested_area_ha,
                    'status'   => 'success',
                    'year'     => (int) $d->year,
                ],
            ])
            ->all();

        // 2. Años faltantes
        $requested    = range($startYear, $endYear);
        $yearsToFetch = array_values(array_diff($requested, array_keys($existing)));

        // 3. Consultar GFW solo por los faltantes
        $fetched = [];
        if (!empty($yearsToFetch)) {
            $fetched = app(\App\Services\GFWService::class)
                ->getYearlyStatsForRange($geometry, $yearsToFetch);
        }

        // 4. Persistir (opcional)
        if ($persist && !empty($fetched)) {
            $this->persistDeforestationYears($fetched);
            $this->refreshDeforestedFlag();
        }

        // 5. Combinar y devolver
        $yearly = $existing;
        foreach ($fetched as $year => $data) {
            $yearly[(int) $year] = $data;
        }
        ksort($yearly);

        return $yearly;
    }

    /**
     * Persiste los resultados de GFW en la tabla `deforestation` sin disparar
     * eventos del modelo, y refresca el flag `deforested` una sola vez.
     *
     * @param array<int, array{area__ha: float, status: string, year: int}> $yearlyData
     */
    private function persistDeforestationYears(array $yearlyData): void
    {
        $areaHa = (float) $this->area_ha;

        Deforestation::withoutEvents(function () use ($yearlyData, $areaHa) {
            foreach ($yearlyData as $year => $data) {
                if (($data['status'] ?? null) !== 'success') {
                    continue;
                }

                $currentArea = (float) ($data['area__ha'] ?? 0);
                $percentage  = $areaHa > 0 ? min(100, ($currentArea / $areaHa) * 100) : 0;

                Deforestation::updateOrCreate(
                    ['polygon_id' => $this->id, 'year' => (int) $year],
                    [
                        'deforested_area_ha' => $currentArea,
                        'percentage_loss'    => $percentage,
                    ]
                );
            }
        });
    }

        /**
     * Persiste los años que ya vienen calculados (formato del breakdown de la
     * vista: [{year, area_ha, percentage}, ...]) sin re-consultar GFW.
     *
     * Se usa cuando ya consultamos GFW antes de tener el polígono persistido
     * (polígono nuevo en el flujo del formulario).
     *
     * @param array<int, array{year: int, area_ha: float, percentage: float}> $yearlyBreakdown
     */
    public function persistYearlyResultsFromBreakdown(array $yearlyBreakdown): void
    {
        $areaHa = (float) $this->area_ha;

        Deforestation::withoutEvents(function () use ($yearlyBreakdown, $areaHa) {
            foreach ($yearlyBreakdown as $yearData) {
                $area = (float) ($yearData['area_ha'] ?? 0);

                Deforestation::updateOrCreate(
                    [
                        'polygon_id' => $this->id,
                        'year'       => (int) $yearData['year'],
                    ],
                    [
                        'deforested_area_ha' => $area,
                        'percentage_loss'    => $areaHa > 0
                            ? min(100, ($area / $areaHa) * 100)
                            : 0,
                    ]
                );
            }
        });

        $this->refreshDeforestedFlag();
    }

        /**
     * Calcula los totales de pérdida a partir de un mapa de resultados anuales.
     *
     * @param  array<int, array{area__ha?: float, status?: string}>  $yearlyResults
     *         Mapa [year => datos]. Cada entrada debe tener `area__ha` y `status`.
     * @param  int  $startYear
     * @param  int  $endYear
     * @return array{
     *     totalDeforestedArea: float,
     *     totalPercentage: float,
     *     validYears: int,
     *     totalYearsInRange: int,
     *     yearlyBreakdown: array<int, array{year:int, area_ha:float, percentage:float, status?:string}>
     * }
     */
    public function buildTotalLossStats(array $yearlyResults, int $startYear, int $endYear): array
    {
        $areaHa              = (float) $this->area_ha;
        $totalDeforestedArea = 0.0;
        $validYears          = 0;
        $yearlyBreakdown     = [];

        foreach ($yearlyResults as $year => $yearData) {
            $yearData = (array) $yearData;

            if (isset($yearData['area__ha']) && ($yearData['status'] ?? null) === 'success') {
                $currentArea          = (float) $yearData['area__ha'];
                $totalDeforestedArea += $currentArea;
                $validYears++;

                $yearlyBreakdown[$year] = [
                    'year'       => (int) $year,
                    'area_ha'    => $currentArea,
                    'percentage' => $areaHa > 0
                        ? min(100, ($currentArea / $areaHa) * 100)
                        : 0,
                ];
            } else {
                $yearlyBreakdown[$year] = [
                    'year'       => (int) $year,
                    'area_ha'    => 0.0,
                    'percentage' => 0.0,
                    'status'     => 'no_data',
                ];
            }
        }

        // Si la suma anual excede el área del polígono, usamos el total como
        // denominador para que el porcentaje nunca pase de 100 %.
        $denominator     = max($areaHa, $totalDeforestedArea);
        $totalPercentage = $denominator > 0
            ? ($totalDeforestedArea / $denominator) * 100
            : 0;

        return [
            'totalDeforestedArea' => $totalDeforestedArea,
            'totalPercentage'     => $totalPercentage,
            'validYears'          => $validYears,
            'totalYearsInRange'   => $endYear - $startYear + 1,
            'yearlyBreakdown'     => $yearlyBreakdown,
        ];
    }
    
}
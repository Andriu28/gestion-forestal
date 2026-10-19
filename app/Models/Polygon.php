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

/**
 * Modelo Polygon
 * 
 * Representa un Parcela o parcela geográfica delimitada en el sistema de gestión forestal.
 * Esta entidad es el núcleo del sistema, ya que almacena:
 * 1. La geometría espacial (mediante la extensión PostGIS en PostgreSQL).
 * 2. El área física calculada en hectáreas.
 * 3. Las coordenadas del centroide (latitud/longitud) para centrado de mapas.
 * 4. La asociación con un productor agropecuario/forestal y su ubicación político-territorial (parroquia).
 * 5. La trazabilidad histórica de alertas de deforestación asociadas.
 */
class Polygon extends Model
{
    use HasFactory, SoftDeletes, LogsActivityWithDescriptions;

    /**
     * Atributos asignables de forma masiva:
     * - name: Nombre identificativo de la parcela o lote.
     * - description: Detalles adicionales o notas de campo.
     * - producer_id: Clave foránea al productor responsable (puede ser nulo si es un Parcela comunitario o no asignado).
     * - parish_id: Parroquia asignada formalmente en la división político-territorial venezolana.
     * - area_ha: Área total de la superficie de la Parcela medida en hectáreas.
     * - is_active: Estado operativo (activo/inactivo) para control de inventario.
     * - deforested: Bandera rápida (booleana) que indica si este Parcela presenta alertas de deforestación registradas.
     * - centroid_lat: Latitud del centro geográfico de la Parcela (usada para centrar la vista en Leaflet/mapas).
     * - centroid_lng: Longitud del centro geográfico de la Parcela.
     * - location_data: Estructura JSON que guarda metadatos de geocodificación inversa detectada y auditoría de importación.
     * - external_id: Identificador alfanumérico asignado por sistemas externos (ej. códigos catastrales o IDs de shapefiles).
     */
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

    /**
     * Conversión automática de tipos de datos al interactuar con el modelo:
     * - is_active y deforested se convierten a booleanos puros.
     * - area_ha conserva 4 decimales de precisión para mediciones agrarias precisas.
     * - location_data se transforma automáticamente entre JSON y array asociativo PHP.
     * - centroid_lat y centroid_lng se convierten a float/double para operaciones de coordenadas GPS.
     */
    protected $casts = [
        'is_active'     => 'boolean',
        'deforested'    => 'boolean',
        'area_ha'       => 'decimal:4',
        'location_data' => 'array',
        'centroid_lat'  => 'double',
        'centroid_lng'  => 'double',
    ];

    // =========================================================================
    // Registro de Auditoría de Actividad (Spatie Activitylog)
    // =========================================================================

    /**
     * Define qué campos de la Parcela deben ser monitoreados para registrar cambios en el log de auditoría.
     */
    protected function getActivitylogAttributes(): array
    {
        return ['name', 'description', 'producer_id', 'parish_id', 'area_ha', 'is_active'];
    }

    /**
     * Etiquetas legibles en español asignadas a cada campo para mostrar en el historial de cambios del usuario.
     */
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

    /**
     * Orden de prioridad en el que se visualizarán los atributos modificados en las interfaces de auditoría.
     */
    protected function getActivityPriority(): array
    {
        return ['name', 'is_active', 'area_ha', 'producer_id', 'parish_id', 'description'];
    }

    /**
     * Texto descriptivo principal del registro en el log de auditoría (utiliza el nombre de la Parcela).
     */
    protected function getActivityLabel(): ?string
    {
        return $this->name;
    }

    // =========================================================================
    // Relaciones del Modelo
    // =========================================================================

    /**
     * Productor propietario o responsable del predio.
     * Es una relación opcional; existen Parcelas que pueden importarse sin productor inicial.
     */
    public function producer()
    {
        return $this->belongsTo(Producer::class);
    }

    /**
     * Parroquia en la que se ubica administrativamente el predio.
     * A través de la parroquia se asciende en cascada al municipio y al estado.
     */
    public function parish()
    {
        return $this->belongsTo(Parish::class);
    }

    /**
     * Historial de análisis anuales de deforestación asociados a este Parcela.
     */
    public function deforestationAnalyses()
    {
        return $this->hasMany(Deforestation::class, 'polygon_id');
    }

    /**
     * Alias de deforestationAnalyses para mantener compatibilidad con distintos módulos.
     */
    public function analyses()
    {
        return $this->hasMany(Deforestation::class, 'polygon_id');
    }

    /**
     * Relación directa con los registros anuales de pérdida de bosque (hectáreas y porcentajes).
     */
    public function deforestations()
    {
        return $this->hasMany(Deforestation::class, 'polygon_id');
    }

    // =========================================================================
    // Scopes (Filtros de Consulta Reutilizables)
    // =========================================================================

    /**
     * Filtra únicamente los Parcelas marcados como activos en el sistema.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Filtra los Parcelas que ya tienen un productor asignado.
     */
    public function scopeWithProducer(Builder $query): Builder
    {
        return $query->whereNotNull('producer_id');
    }

    /**
     * Filtra los Parcelas huérfanos o que aún no tienen productor asignado.
     */
    public function scopeWithoutProducer(Builder $query): Builder
    {
        return $query->whereNull('producer_id');
    }

    /**
     * Filtra Parcelas que tienen alertas o registros de deforestación confirmados.
     */
    public function scopeDeforested(Builder $query): Builder
    {
        return $query->where('deforested', true);
    }

    /**
     * Filtra Parcelas limpios de deforestación (área deforestada igual a 0).
     */
    public function scopeWithoutDeforestation(Builder $query): Builder
    {
        return $query->where('deforested', false);
    }

    /**
     * Búsqueda global por texto:
     * Busca coincidencias parciales en:
     * - Nombre de la Parcela
     * - Descripción de la parcela
     * - Nombre o apellido del productor asignado
     * - Nombre de la parroquia, municipio o estado (jerarquía geográfica completa)
     * 
     * Se encapsula en una función anónima para evitar que los operadores OR contaminen
     * otras cláusulas WHERE que el usuario haya aplicado en la consulta.
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
    // Accessors (Formateo Dinámico de Datos para Vistas y APIs)
    // =========================================================================

    /**
     * Retorna el nombre y apellido completo del productor asociado, o 'Sin productor'.
     */
    public function getProducerNameAttribute(): string
    {
        return $this->producer
            ? "{$this->producer->name} {$this->producer->lastname}"
            : 'Sin productor';
    }

    /**
     * Clasifica el Parcela según si cuenta con productor o no ('with_producer' / 'without_producer').
     * Se utiliza para filtros y badges en la interfaz de usuario.
     */
    public function getTypeAttribute(): string
    {
        return $this->producer_id ? 'with_producer' : 'without_producer';
    }

    /**
     * Devuelve el área en hectáreas formateada con 4 decimales y la unidad 'Ha' (ej. '12.4500 Ha').
     */
    public function getAreaFormattedAttribute(): string
    {
        return $this->area_ha
            ? number_format((float) $this->area_ha, 4) . ' Ha'
            : 'N/A';
    }

    /**
     * Genera una píldora visual en HTML con el estado de la Parcela:
     * - Rojo: Eliminado lógicamente (soft deleted).
     * - Verde: Activo.
     * - Amarillo: Inactivo.
     */
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

    /**
     * Devuelve la cadena territorial completa: Parroquia, Municipio, Estado.
     * Carga de forma segura las relaciones anidadas para evitar el problema de consultas N+1.
     */
    public function getFullLocationAttribute(): string
    {
        if ($this->parish) {
            $this->loadMissing('parish.municipality.state');
            $p = $this->parish;
            return "{$p->name}, {$p->municipality->name}, {$p->municipality->state->name}";
        }

        return 'Ubicación no asignada';
    }

    /**
     * Genera la píldora visual para indicar si la parcela tiene deforestación detectada:
     * - Verde: Sin deforestación.
     * - Rojo: Con deforestación.
     */
    public function getDeforestationBadgeAttribute(): string
    {
        if ($this->deforested) {
            return '<span class="inline-block px-3 py-1 text-xs font-semibold bg-red-600 text-white rounded-full">Con deforestación</span>';
        }
        return '<span class="inline-block px-3 py-1 text-xs font-semibold bg-green-600 text-white rounded-full">Sin deforestación</span>';
    }

    // ---- Campos detectados automáticamente mediante geocodificación inversa (guardados en JSON location_data) ----

    /**
     * Obtiene la parroquia sugerida por el análisis de coordenadas GPS en el mapa.
     */
    public function getDetectedParishAttribute(): ?string
    {
        return $this->getFromLocationData('detected_parish');
    }

    /**
     * Obtiene el municipio sugerido por el análisis de coordenadas GPS.
     */
    public function getDetectedMunicipalityAttribute(): ?string
    {
        return $this->getFromLocationData('detected_municipality');
    }

    /**
     * Obtiene el estado/departamento detectado a partir de las coordenadas.
     */
    public function getDetectedStateAttribute(): ?string
    {
        return $this->getFromLocationData('detected_state');
    }

    // =========================================================================
    // Persistencia Espacial con PostGIS
    // =========================================================================

    /**
     * Inserta un nuevo Parcela en la base de datos transformando la geometría GeoJSON en un objeto PostGIS nativo.
     *
     * Flujo espacial:
     * 1. `ST_GeomFromGeoJSON(?)`: Convierte la cadena GeoJSON recibida del mapa Leaflet en una geometría PostGIS binaria.
     * 2. `ST_SetSRID(..., ?)`: Asigna el sistema de referencia espacial de entrada (por defecto SRID 4326 - WGS84 coordenadas GPS).
     * 3. `ST_Transform(..., 4326)`: Garantiza que la geometría se almacene proyectada en el estándar WGS84 para compatibilidad global.
     * 4. `RETURNING id`: Captura inmediatamente el ID autoincremental generado sin necesidad de una segunda consulta.
     * 5. Opcionalmente genera la entrada en la bitácora de auditoría con los datos iniciales.
     *
     * @param array             $data             Datos convencionales (nombre, descripción, productor, parroquia, etc.)
     * @param string            $geoJsonGeometry Cadena JSON con la definición geométrica {"type":"Polygon","coordinates":...}
     * @param int               $srid             Sistema de referencia espacial de origen (4326 para GPS estándar)
     * @param bool              $logActivity      Si debe generar registro en la tabla de actividad
     * @param \App\Models\User|null $causer       Usuario que disparó la creación (útil en importaciones en segundo plano)
     * @return static
     * @throws \RuntimeException
     */
    public static function createWithGeometry(
        array $data,
        string $geoJsonGeometry,
        int $srid = 4326,
        bool $logActivity = true,
        ?\App\Models\User $causer = null,
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
            throw new \RuntimeException('No se pudo insertar el Parcela (RETURNING id vacío).');
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
     * Actualiza tanto los campos alfanuméricos como la columna geométrica PostGIS de la Parcela.
     *
     * Ejecuta una sentencia SQL directa para convertir y guardar la nueva geometría GeoJSON en el SRID 4326,
     * y luego actualiza los atributos del modelo en memoria.
     *
     * @param array  $data            Campos a actualizar (nombre, descripción, etc.)
     * @param string $geoJsonGeometry Nueva cadena GeoJSON
     * @param int    $srid            Sistema de coordenadas de origen
     * @param bool   $logActivity     Si se deben disparar observadores y logs de auditoría
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

    /**
     * Recalcula con precisión geográfica matemática el área en hectáreas y el centroide de la Parcela:
     * 
     * Funciones espaciales empleadas:
     * - `ST_Transform(geometry, 4326)::geography`: Hace un casting de la geometría a tipo GEOGRAPHY esferoidal.
     * - `ST_Area(...) / 10000`: Calcula el área en metros cuadrados considerando la curvatura de la Tierra (elipsoide WGS84)
     *   y la divide entre 10,000 para convertir metros cuadrados (m²) a hectáreas (ha).
     * - `ST_Centroid(geometry)`: Calcula el punto baricéntrico o centroide de la Parcela.
     * - `ST_X(...)` y `ST_Y(...)`: Extrae la longitud (eje X) y la latitud (eje Y) respectivamente.
     * 
     * Guarda estos valores directamente en el registro sin disparar eventos de auditoría innecesarios (`updateQuietly`).
     */
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
            throw $e; // relanzar para permitir rollback si está dentro de una transacción
        }
    }

    /**
     * Extrae la geometría binaria de la columna PostGIS y la convierte en una cadena GeoJSON válida mediante `ST_AsGeoJSON`.
     * Retorna la cadena limpia de espacios en blanco y saltos de línea para facilitar comparaciones y renderizado.
     */
    public function getGeometryGeoJson(): string
    {
        $geojson = DB::table('polygons')
            ->where('id', $this->id)
            ->value(DB::raw('ST_AsGeoJSON(geometry)'));
        
        return $this->normalizeGeoJsonString($geojson ?: '{}');
    }

    /**
     * Limpia cualquier carácter invisible, tabulaciones o saltos de línea de una cadena GeoJSON
     * para permitir comparaciones hash o comprobaciones de igualdad si la geometría fue alterada.
     */
    public function normalizeGeoJsonString(string $geojson): string
    {
        return preg_replace('/\s+/', '', $geojson);
    }

    /**
     * Prepara el campo JSON `location_data` al crear un Parcela:
     * Combina la información cruda con los datos territoriales detectados automáticamente por coordenadas
     * (parroquia, municipio, estado detectados) y agrega una marca de tiempo con la parroquia asignada formalmente.
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
     * Fusiona los datos de ubicación existentes con nuevos cambios durante una actualización:
     * Preserva propiedades previas, actualiza la detección geográfica si cambiaron las coordenadas,
     * y guarda en `detected_info` quién fue el usuario y en qué momento se realizó la modificación.
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
    // Helpers Privados de Acceso a Metadatos
    // =========================================================================

    /**
     * Extrae de forma segura una clave dentro del JSON location_data,
     * gestionando indistintamente si el atributo ya fue casteado a array o si viene como string sin deserializar.
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
     * Crea e inicializa un Parcela en la base de datos a partir de una entidad individual (Feature) de un GeoJSON o Shapefile importado:
     * 
     * 1. Valida que el tipo de geometría sea compatible ('Polygon' o 'MultiPolygon').
     * 2. Extrae atributos comunes del estándar cartográfico (id externo, nombre, área calculada 'Area_Ha', descripción).
     * 3. Registra en `location_data` la fuente de importación y las propiedades originales del archivo GIS.
     * 4. Almacena la geometría mediante `createWithGeometry`.
     * 5. Si el archivo no traía el área precalculada en hectáreas, la calcula automáticamente mediante PostGIS con `recalculateGeometryStats`.
     *
     * @param array $featureData Estructura del Feature GeoJSON conteniendo 'geometry' y 'properties'
     * @param int   $srid        Identificador de proyección espacial (SRID)
     * @param array $extra       Datos contextuales suplementarios (productor asignado, parroquia por defecto, etc.)
     * @return static
     * @throws \Exception
     */
    public static function createFromGeoJsonFeature(array $featureData, int $srid, array $extra = []): static
    {
        $properties = $featureData['properties'] ?? [];
        $geometry = $featureData['geometry'];

        if (!isset($geometry['type']) || !in_array($geometry['type'], ['Polygon', 'MultiPolygon'])) {
            throw new \Exception('Geometría inválida. Solo se permiten Polygon o MultiPolygon.');
        }

        $geoJsonString = json_encode($geometry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Extraer atributos estándar o personalizados del archivo cartográfico
        $externalId = $properties['id'] ?? null;
        $areaHa = $properties['Area_Ha'] ?? null;
        $name = $properties['name'] ?? ($extra['producer_name'] ?? 'Parcela importado');
        $description = $properties['description'] ?? null;
        $producerId = $extra['producer_id'] ?? null;
        $parishId = $extra['parish_id'] ?? null;

        // Construir arreglo de inserción
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

        // Inserta en la base de datos transformando a PostGIS
        $polygon = static::createWithGeometry($data, $geoJsonString, $srid, true);

        // Si el archivo no traía el cálculo de área, calcularlo de forma esferoidal con PostGIS
        if (is_null($areaHa)) {
            $polygon->recalculateGeometryStats();
        } else {
            $polygon->updateQuietly(['area_ha' => $areaHa]);
        }

        return $polygon;
    }

    /**
     * Sincroniza la bandera booleana `deforested`:
     * Consulta directamente la tabla de análisis de deforestación para determinar si existe
     * algún registro con pérdida de bosque mayor a 0 hectáreas. Si el estado en BD no coincide
     * con la realidad, lo actualiza de manera silenciosa para mantener la integridad en listados rápidos.
     */
    public function refreshDeforestedFlag(): void
    {
        $hasDeforestation = $this->deforestations()
            ->where('deforested_area_ha', '>', 0)
            ->exists();

        if ((bool) $this->deforested !== $hasDeforestation) {
            $this->updateQuietly(['deforested' => $hasDeforestation]);
        }
    }

    /**
     * Realiza el análisis de deforestación histórico consultando la API satelital de Global Forest Watch (GFW):
     * 
     * Algoritmo de optimización por caché en base de datos:
     * 1. Extrae la geometría de la Parcela en formato GeoJSON.
     * 2. Revisa qué años dentro del rango solicitado [$startYear, $endYear] ya han sido analizados y guardados en la base de datos local.
     * 3. Calcula la diferencia (`array_diff`) para determinar exactamente qué años faltan.
     * 4. Consulta a la API de GFW ÚNICAMENTE por los años faltantes, ahorrando ancho de banda y cuota de peticiones satelitales.
     * 5. Si $persist = true, guarda los nuevos años obtenidos en la tabla `deforestation` y actualiza la bandera `deforested`.
     * 6. Retorna el mapa consolidado ordenado cronológicamente con la pérdida anual de todos los años del rango.
     *
     * @param int  $startYear Año inicial del rango (ej. 2001)
     * @param int  $endYear   Año final del rango (ej. 2024)
     * @param bool $persist   Indica si se deben guardar los nuevos resultados en la base de datos
     * @return array<int, array{area__ha: float, status: string, year: int, error?: string}>
     */
    public function analyzeDeforestationFromGFW(int $startYear, int $endYear, bool $persist = true): array
    {
        $geometry = json_decode($this->getGeometryGeoJson(), true);

        if (empty($geometry) || empty($geometry['type']) || empty($geometry['coordinates'])) {
            Log::warning("Parcela {$this->id} sin geometría; análisis omitido.");
            return [];
        }

        // 1. Obtener los años que ya están persistidos en la base de datos
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

        // 2. Determinar cuáles años faltan por consultar
        $requested    = range($startYear, $endYear);
        $yearsToFetch = array_values(array_diff($requested, array_keys($existing)));

        // 3. Consultar la API satelital de GFW únicamente para los años ausentes
        $fetched = [];
        if (!empty($yearsToFetch)) {
            $fetched = app(\App\Services\GFWService::class)
                ->getYearlyStatsForRange($geometry, $yearsToFetch);
        }

        // 4. Guardar en base de datos los años nuevos obtenidos
        if ($persist && !empty($fetched)) {
            $this->persistDeforestationYears($fetched);
            $this->refreshDeforestedFlag();
        }

        // 5. Mezclar datos preexistentes con los recién consultados y ordenar por año
        $yearly = $existing;
        foreach ($fetched as $year => $data) {
            $yearly[(int) $year] = $data;
        }
        ksort($yearly);

        return $yearly;
    }

    /**
     * Guarda masivamente en la tabla `deforestation` los resultados satelitales anuales devueltos por GFW:
     * 
     * Regla de negocio matemática:
     * - Calcula el porcentaje de pérdida del año con respecto al área total de la parcela:
     *   `porcentaje = min(100, (área_deforestada / área_total_Parcela) * 100)`
     * - Se ejecuta dentro de `withoutEvents` para evitar sobrecargar el sistema con múltiples disparos de eventos
     *   y listeners por cada año individual; al finalizar, se actualiza la bandera global una sola vez.
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
     * Guarda resultados anuales calculados previamente en el cliente o formulario interactivo:
     * Se utiliza en la creación de parcelas donde el usuario prueba la deforestación en el mapa interactivo
     * ANTES de guardar el Parcela; una vez que confirma el guardado, los datos calculados en pantalla
     * se insertan directamente sin repetir la llamada HTTP al satélite de GFW.
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
     * Calcula métricas globales acumuladas a partir del desglose anual de deforestación:
     * 
     * Cálculos realizados:
     * 1. Sumatoria del área total deforestada a lo largo de los años analizados.
     * 2. Conteo de años con datos válidos confirmados versus total de años solicitados.
     * 3. Porcentaje total acumulado de pérdida de cobertura arbórea:
     *    Para evitar distorsiones matemáticas si la suma acumulada de hectáreas sobrepasa el área inicial
     *    (por ejemplo, por regeneración y nueva tala sucesiva en el mismo punto), se toma como denominador
     *    `max(areaHa, totalDeforestedArea)` de modo que el porcentaje acumulado nunca sobrepase el 100%.
     *
     * @param  array<int, array{area__ha?: float, status?: string}>  $yearlyResults Resultados anuales
     * @param  int  $startYear Año base
     * @param  int  $endYear   Año tope
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

        // Si la suma anual sobrepasa el área de la Parcela, usamos el total acumulado como tope base
        // para garantizar que la proporción se mantenga matemáticamente coherente entre 0 y 100 %
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
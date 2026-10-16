<?php
// [file name]: app/Http/Controllers/PolygonController.php

namespace App\Http\Controllers;

use App\Models\Municipality;
use App\Models\Parish;
use App\Models\Polygon;
use App\Models\Producer;
use App\Models\State;
use App\Services\LocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use App\Traits\Filterable;
use Illuminate\Support\Facades\Cache;
use App\Services\PolygonImportService;
use App\Jobs\ImportPolygonsJob;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Bus;
use App\Jobs\ImportPolygonJob;

/**
 * Controlador de Gestión de Polígonos y Parcelas Geoespaciales
 * 
 * Centraliza la administración de los predios geográficos del sistema:
 * 1. Filtrado multidimensional (territorial, productivo, deforestación y rangos de superficie).
 * 2. Operaciones CRUD con almacenamiento espacial PostGIS en estándar EPSG:4326 (WGS84).
 * 3. Detección automática y geocodificación inversa de parroquias a partir del trazo en el mapa.
 * 4. Generación de capas GeoJSON FeatureCollection para visualización en Leaflet.
 * 5. Importación masiva distribuida por lotes (Queue Batching) de archivos Shapefile / GeoJSON.
 */
class PolygonController extends Controller
{
    use Filterable;

    /**
     * Inyecta los servicios de resolución territorial y de importación masiva.
     */
    public function __construct(
        private readonly LocationService $locationService,
        private readonly PolygonImportService $importService,
    ) {}

    // =========================================================================
    // Listados y Filtros Avanzados
    // =========================================================================

    /**
     * Muestra la tabla principal de polígonos aplicando filtros combinados:
     * - Búsqueda por texto (nombre de parcela).
     * - Rango de fechas de registro.
     * - Estado operativo (activo, inactivo, eliminado).
     * - Tenencia de la tierra (con productor asignado vs sin productor).
     * - Jerarquía territorial (Parroquia, Municipio, Estado).
     * - Superficie en hectáreas (rango mínimo y máximo).
     * - Productor específico.
     * - Criterios de deforestación (con/sin alertas, año de pérdida, porcentaje de afectación).
     */
    public function index(Request $request): View
    {
        // Parámetros de filtrado convencionales
        $search   = $request->get('search');
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');
        $status   = $request->get('status', 'all');
        $type     = $request->get('type', 'all');

        // Filtros espaciales y de cobertura forestal
        $parishId          = $request->get('parish_id');
        $municipalityId    = $request->get('municipality_id');
        $stateId           = $request->get('state_id');
        $areaMin           = $request->get('area_min');
        $areaMax           = $request->get('area_max');
        $producerId        = $request->get('producer_id');
        $hasDeforestation  = $request->get('has_deforestation'); // 'yes', 'no', 'all'
        $deforestationYear = $request->get('deforestation_year');
        $lossMin           = $request->get('loss_min');
        $lossMax           = $request->get('loss_max');

        // Validar coherencia del rango cronológico
        $validationError = $this->validateDateRange($dateFrom, $dateTo);
        if ($validationError) {
            return $validationError;
        }

        // Validar que el área máxima no sea inferior al área mínima
        if ($areaMin !== null && $areaMax !== null && $areaMax < $areaMin) {
            return redirect()->back()->withErrors(['area_max' => 'El área máxima no puede ser menor que el área mínima.'])->withInput();
        }

        // Validar que el porcentaje máximo de pérdida no sea menor al mínimo
        if ($lossMin !== null && $lossMax !== null && $lossMax < $lossMin) {
            return redirect()->back()->withErrors(['loss_max' => 'La pérdida máxima no puede ser menor que la pérdida mínima.'])->withInput();
        }

        // Eager loading para evitar N+1 en las relaciones territoriales y de productores
        $query = Polygon::with(['producer', 'parish.municipality.state']);

        // Aplicar filtro de fecha sobre created_at
        $query = $this->applyDateFilters($query, $dateFrom, $dateTo, 'created_at');

        // Aplicar búsqueda de texto libre sobre el nombre
        $query = $this->applySearchFilter(
            $query,
            $search,
            ['name'],
            []
        );

        // Filtros de estado operativo
        match ($status) {
            'active'   => $query->where('is_active', true),
            'inactive' => $query->where('is_active', false),
            'deleted'  => $query->onlyTrashed(),
            default    => null,
        };

        // Filtros por asignación de productor
        match ($type) {
            'with_producer'    => $query->withProducer(),
            'without_producer' => $query->withoutProducer(),
            default            => null,
        };

        // 1. Filtro territorial por Parroquia directa
        if ($parishId) {
            $query->where('parish_id', $parishId);
        }

        // 2. Filtro territorial por Municipio (subconsulta en la parroquia)
        if ($municipalityId) {
            $query->whereHas('parish', fn($q) => $q->where('municipality_id', $municipalityId));
        }

        // 3. Filtro territorial por Estado (subconsulta anidada parroquia -> municipio -> estado)
        if ($stateId) {
            $query->whereHas('parish.municipality', fn($q) => $q->where('state_id', $stateId));
        }

        // 4. Filtro por superficie en hectáreas (rango numérico)
        if ($areaMin !== null) {
            $query->where('area_ha', '>=', $areaMin);
        }
        if ($areaMax !== null) {
            $query->where('area_ha', '<=', $areaMax);
        }

        // 5. Filtro por productor titular
        if ($producerId) {
            $query->where('producer_id', $producerId);
        }

        // 6. Filtro rápido por bandera booleana de deforestación
        if ($hasDeforestation === 'yes') {
            $query->where('deforested', true);
        } elseif ($hasDeforestation === 'no') {
            $query->where('deforested', false);
        }

        // 7. Filtro por año específico con eventos de deforestación registrados
        if ($deforestationYear) {
            $query->whereHas('deforestations', fn($q) => $q->where('year', $deforestationYear));
        }

        // 8. Filtro por rango de porcentaje de pérdida arbórea histórica
        if ($lossMin !== null || $lossMax !== null) {
            $query->whereHas('deforestations', function ($q) use ($lossMin, $lossMax) {
                if ($lossMin !== null) {
                    $q->where('percentage_loss', '>=', $lossMin);
                }
                if ($lossMax !== null) {
                    $q->where('percentage_loss', '<=', $lossMax);
                }
            });
        }

        // Paginación con persistencia de variables en los links
        $polygons = $query->latest()->paginate(10);

        $polygons->appends([
            'search'             => $search,
            'date_from'          => $dateFrom,
            'date_to'            => $dateTo,
            'status'             => $status,
            'type'               => $type,
            'parish_id'          => $parishId,
            'municipality_id'    => $municipalityId,
            'state_id'           => $stateId,
            'area_min'           => $areaMin,
            'area_max'           => $areaMax,
            'producer_id'        => $producerId,
            'has_deforestation'  => $hasDeforestation,
            'deforestation_year' => $deforestationYear,
            'loss_min'           => $lossMin,
            'loss_max'           => $lossMax,
        ]);

        // Cargar listas maestras para alimentar los desplegables de filtrado
        $parishes       = Parish::orderBy('name')->get(['id', 'name']);
        $municipalities = Municipality::orderBy('name')->get(['id', 'name']);
        $states         = State::orderBy('name')->get(['id', 'name']);
        $producers      = Producer::orderBy('name')->get(['id', 'name', 'lastname']);
        $years          = range(2020, now()->year);
        
        return view('polygons.index', compact(
            'polygons',
            'search', 'dateFrom', 'dateTo', 'status', 'type',
            'parishId', 'municipalityId', 'stateId',
            'areaMin', 'areaMax',
            'producerId',
            'hasDeforestation', 'deforestationYear',
            'lossMin', 'lossMax',
            'parishes', 'municipalities', 'states', 'producers', 'years'
        ));
    }

    /**
     * Muestra la papelera de polígonos eliminados lógicamente (SoftDeletes).
     */
    public function deleted(Request $request): View
    {
        $search = $request->get('search');

        $query = Polygon::onlyTrashed()->with(['producer', 'parish.municipality.state']);

        if ($search) {
            $query->search($search);
        }

        $polygons = $query->latest('deleted_at')->paginate(10);

        return view('polygons.deleted', compact('polygons', 'search'));
    }

    /**
     * Vista general del mapa interactivo con todos los polígonos cargados en capas Leaflet.
     */
    public function map(): View
    {
        return view('polygons.map');
    }

    // =========================================================================
    // Operaciones CRUD
    // =========================================================================

    /**
     * Muestra el formulario interactivo para dibujar o ingresar un polígono nuevo.
     */
    public function create(): View
    {
        $producers = Producer::active()->get();
        $parishes  = Parish::with('municipality.state')->get();

        return view('polygons.create', compact('producers', 'parishes'));
    }

    /**
     * Almacena una nueva parcela en la base de datos geográfica:
     * 
     * Secuencia técnica:
     * 1. Valida reglas alfanuméricas y la presencia de la cadena GeoJSON.
     * 2. Resuelve la parroquia (`resolveParishId`): evalúa si el usuario seleccionó una manualmente
     *    o si debe autodetectarse a través de geocodificación inversa según las coordenadas del mapa.
     * 3. Normaliza el GeoJSON asegurando que sea un Polygon o MultiPolygon válido.
     * 4. Construye el paquete de auditoría y metadatos en `location_data`.
     * 5. Inserta en PostGIS mediante `Polygon::createWithGeometry` proyectando a SRID 4326.
     * 6. Recalcula el área en hectáreas y el centroide de forma esferoidal (`recalculateGeometryStats`).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->validationRules());

        try {
            $parishId = $this->resolveParishId($validated, null);
            $geoJson  = $this->normalizeGeoJson($validated['geometry']);
            $detected = $this->extractDetected($validated);

            $rawLocation  = ! empty($validated['location_data'])
                ? (json_decode($validated['location_data'], true) ?? [])
                : [];

            $temp         = new Polygon();
            $locationData = $temp->buildLocationDataForCreate($rawLocation, $detected, $parishId);

            $polygon = Polygon::createWithGeometry(
                [
                    'name'          => $validated['name'],
                    'description'   => $validated['description'] ?? null,
                    'producer_id'   => $validated['producer_id'] ?? null,
                    'parish_id'     => $parishId,
                    'area_ha'       => $validated['area_ha'] ?? null,
                    'is_active'     => true,
                    'centroid_lat'  => $validated['centroid_lat'] ?? null,
                    'centroid_lng'  => $validated['centroid_lng'] ?? null,
                    'location_data' => $locationData,
                ],
                $geoJson
            );

            // Calcular superficie geodésica real y centroide en PostGIS
            $polygon->recalculateGeometryStats();

            Log::info('Polígono creado', ['id' => $polygon->id, 'parish_id' => $polygon->parish_id]);

            return redirect()->route('polygons.index')
                ->with('success', "Polígono '{$polygon->name}' creado exitosamente.");

        } catch (\Throwable $e) {
            Log::error('Error al crear polígono', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Error al crear el polígono: ' . $e->getMessage());
        }
    }

    /**
     * Muestra la ficha técnica detallada del polígono, su productor y su ubicación.
     */
    public function show(Polygon $polygon): View
    {
        $polygon->load(['producer', 'parish.municipality.state']);

        return view('polygons.show', compact('polygon'));
    }

    /**
     * Muestra el formulario de edición con el visor cartográfico cargado con la geometría actual.
     */
    public function edit(Polygon $polygon): View
    {
        $polygon->load('parish.municipality.state');
        $producers = Producer::active()->get();
        $parishes  = Parish::with('municipality.state')->get();

        return view('polygons.edit', compact('polygon', 'producers', 'parishes'));
    }

    /**
     * Actualiza la información alfanumérica y/o la geometría espacial del polígono:
     * 
     * Detección de cambios espaciales:
     * - Compara la cadena GeoJSON previa normalizada con la nueva cadena enviada desde el mapa.
     * - Si la geometría fue alterada (vértices movidos, redibujado): ejecuta la actualización en PostGIS
     *   y llama a `recalculateGeometryStats` para recalcular el área en hectáreas y el nuevo centroide.
     * - Si solo cambiaron datos de texto (nombre, notas, productor): actualiza sin recalcular la geometría.
     */
    public function update(Request $request, Polygon $polygon): RedirectResponse
    {
        $validated = $request->validate($this->validationRules(isUpdate: true));

        DB::beginTransaction();
        try {
            Log::info('Iniciando update de polígono', ['id' => $polygon->id]);

            $parishId = $this->resolveParishId($validated, $polygon->parish_id);
            $geoJson  = $this->normalizeGeoJson($validated['geometry']);
            $detected = $this->extractDetected($validated);

            $oldGeoJson = $polygon->getGeometryGeoJson();

            $rawLocation = !empty($validated['location_data'])
                ? (json_decode($validated['location_data'], true) ?? [])
                : [];

            $locationData = $polygon->mergeLocationDataForUpdate(
                $rawLocation,
                $detected,
                auth()->id()
            );

            // Actualizar registro y geometría PostGIS
            $updated = $polygon->updateWithGeometry(
                [
                    'name'          => $validated['name'],
                    'description'   => $validated['description'] ?? null,
                    'producer_id'   => $validated['producer_id'] ?? null,
                    'parish_id'     => $parishId,
                    'is_active'     => $validated['is_active'] ?? true,
                    'location_data' => $locationData,
                ],
                $geoJson
            );

            // Comprobar si la forma geométrica cambió sustancialmente para recalcular métricas
            $normalizedOld = $polygon->normalizeGeoJsonString($oldGeoJson);
            $normalizedNew = $polygon->normalizeGeoJsonString($geoJson);

            if ($normalizedOld !== $normalizedNew) {
                Log::info('La geometría cambió, recalculando stats...');
                $polygon->recalculateGeometryStats();
            } else {
                Log::info('La geometría no cambió, no se recalcula');
            }

            DB::commit();

            return redirect()->route('polygons.index')
                ->with('success', "Polígono '{$polygon->name}' actualizado exitosamente.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al actualizar polígono', [
                'id'    => $polygon->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'Error al actualizar el polígono: ' . $e->getMessage());
        }
    }

    /**
     * Elimina lógicamente un polígono (SoftDelete). Responde tanto en JSON (vía AJAX) como en redirección web.
     */
    public function destroy(Request $request, Polygon $polygon): JsonResponse|RedirectResponse
    {
        try {
            $polygon->delete();

            if ($this->wantsJson($request)) {
                return response()->json([
                    'success'    => true,
                    'message'    => 'Polígono eliminado exitosamente.',
                    'polygon_id' => $polygon->id,
                    'redirect'   => route('polygons.deleted'),
                ]);
            }

            return redirect()->route('polygons.index')
                ->with('success', 'Polígono eliminado exitosamente.');

        } catch (\Throwable $e) {
            Log::error('Error al eliminar polígono', ['id' => $polygon->id, 'error' => $e->getMessage()]);
            return $this->errorResponse($request, 'Error al eliminar el polígono: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // Acciones de Cambio de Estado y Restauración
    // =========================================================================

    /**
     * Restaura un polígono que había sido enviado a la papelera.
     */
    public function restore(Request $request, int $id): JsonResponse|RedirectResponse
    {
        try {
            $polygon = Polygon::withTrashed()->findOrFail($id);
            $polygon->restore();

            if ($this->wantsJson($request)) {
                return response()->json([
                    'success'    => true,
                    'message'    => 'Polígono restaurado exitosamente.',
                    'polygon_id' => $polygon->id,
                    'is_active'  => $polygon->is_active,
                    'redirect'   => route('polygons.index'),
                ]);
            }

            return redirect()->route('polygons.index')
                ->with('success', 'Polígono restaurado exitosamente.');

        } catch (\Throwable $e) {
            Log::error('Error al restaurar polígono', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->errorResponse($request, 'Error al restaurar el polígono: ' . $e->getMessage());
        }
    }

    /**
     * Alterna el estado operativo del polígono entre Activo e Inactivo.
     */
    public function toggleStatus(Request $request, Polygon $polygon): JsonResponse|RedirectResponse
    {
        try {
            $polygon->update(['is_active' => ! $polygon->is_active]);
            $verb = $polygon->is_active ? 'activado' : 'desactivado';

            if ($this->wantsJson($request)) {
                return response()->json([
                    'success'     => true,
                    'message'     => "Polígono {$verb} exitosamente.",
                    'is_active'   => $polygon->is_active,
                    'status_text' => $polygon->is_active ? 'Activo' : 'Inactivo',
                    'polygon_id'  => $polygon->id,
                ]);
            }

            return redirect()->route('polygons.index')
                ->with('success', "Polígono {$verb} exitosamente.");

        } catch (\Throwable $e) {
            Log::error('Error al cambiar estado del polígono', ['id' => $polygon->id, 'error' => $e->getMessage()]);
            return $this->errorResponse($request, 'Error al cambiar el estado del polígono: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // Endpoints API y Servicios Geoespaciales
    // =========================================================================

    /**
     * Genera una colección estándar GeoJSON (FeatureCollection) con todos los polígonos activos:
     * - Extrae la columna geométrica PostGIS convirtiéndola a JSON nativo mediante `ST_AsGeoJSON(geometry)`.
     * - Adjunta propiedades alfanuméricas (nombre, productor, área, clasificación de deforestación).
     * - Clasifica el polígono en 'has_deforestation', 'no_deforestation' o 'no_data' para que Leaflet
     *   pueda pintar los bordes de color verde o rojo dinámicamente en el mapa.
     */
    public function geojson(): JsonResponse
    {
        $polygons = Polygon::with(['producer', 'parish.municipality.state', 'deforestations'])
            ->active()
            ->get();

        $features = $polygons->map(function (Polygon $polygon) {
            try {
                $geojsonStr = DB::selectOne(
                    'SELECT ST_AsGeoJSON(geometry) AS geojson FROM polygons WHERE id = ?',
                    [$polygon->id]
                )?->geojson ?? '{}';

                // Compilar el historial anual de deforestación
                $deforestations = $polygon->deforestations->map(function ($d) {
                    return [
                        'year'            => $d->year,
                        'percentage_loss' => $d->percentage_loss,
                    ];
                })->toArray();

                // Evaluar si existen pérdidas mayores a cero para colorear la capa en el visor
                $deforestationStatus = 'no_data';
                if ($deforestations) {
                    $hasLoss = collect($deforestations)->contains(function ($d) {
                        return ($d['percentage_loss'] ?? 0) > 0;
                    });
                    $deforestationStatus = $hasLoss ? 'has_deforestation' : 'no_deforestation';
                }

                return [
                    'type'       => 'Feature',
                    'properties' => [
                        'id'                   => $polygon->id,
                        'name'                 => $polygon->name,
                        'producer'             => $polygon->producer_name,
                        'area_ha'              => $polygon->area_ha,
                        'description'          => $polygon->description,
                        'type'                 => $polygon->type,
                        'deforestation_status' => $deforestationStatus,
                        'deforestations'       => $deforestations,
                    ],
                    'geometry' => json_decode($geojsonStr, true),
                ];
            } catch (\Throwable $e) {
                Log::error('Error al procesar polígono para GeoJSON', [
                    'polygon_id' => $polygon->id,
                    'error'      => $e->getMessage(),
                ]);
                return null;
            }
        })->filter()->values();

        return response()->json(['type' => 'FeatureCollection', 'features' => $features]);
    }

    /**
     * Retorna la información completa de una parcela en formato JSON para el modal emergente de Leaflet.
     */
    public function details(int $id): JsonResponse
    {
        try {
            $polygon = Polygon::withTrashed()
                ->with(['producer', 'parish.municipality.state'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'polygon' => $this->serializePolygon($polygon),
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'El polígono no existe o fue eliminado permanentemente.',
            ], 404);

        } catch (\Throwable $e) {
            Log::error('Error al cargar detalles del polígono', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los detalles del polígono.',
            ], 500);
        }
    }

    /**
     * Endpoint API para resolver o autocompletar una parroquia desde el mapa interactivo:
     * Si no se encuentra una coincidencia exacta en la base de datos, devuelve sugerencias por similitud.
     */
    public function findParishApi(Request $request): JsonResponse
    {
        $parishName       = $request->get('parish_name');
        $municipalityName = $request->get('municipality_name');
        $stateName        = $request->get('state_name');

        $parishId = $this->locationService->findOrCreateLocation(
            $parishName,
            $municipalityName,
            $stateName
        );

        if ($parishId) {
            $parish = Parish::with('municipality.state')->find($parishId);

            return response()->json([
                'success' => true,
                'parish'  => [
                    'id'           => $parish->id,
                    'name'         => $parish->name,
                    'municipality' => $parish->municipality->name,
                    'state'        => $parish->municipality->state->name,
                ],
                'message' => 'Parroquia encontrada/creada en la base de datos.',
            ]);
        }

        return response()->json([
            'success'     => false,
            'parish'      => null,
            'suggestions' => $this->getLocationSuggestions($parishName, $municipalityName, $stateName),
            'message'     => 'No se encontró parroquia coincidente en la base de datos.',
        ]);
    }

    // =========================================================================
    // Métodos Auxiliares de Validación y Normalización
    // =========================================================================

    /**
     * Reglas de validación para la creación y edición de polígonos.
     */
    private function validationRules(bool $isUpdate = false): array
    {
        return [
            'name'                  => 'required|string|max:40',
            'description'           => 'nullable|string',
            'geometry'              => 'required|string',
            'producer_id'           => 'nullable|exists:producers,id',
            'parish_id'             => 'nullable|exists:parishes,id',
            'area_ha'               => 'nullable|numeric|min:0',
            'is_active'             => $isUpdate ? 'boolean' : 'sometimes|boolean',
            'centroid_lat'          => 'nullable|numeric|between:-90,90',
            'centroid_lng'          => 'nullable|numeric|between:-180,180',
            'location_data'         => 'nullable|string',
            'detected_parish'       => 'nullable|string|max:255',
            'detected_municipality' => 'nullable|string|max:255',
            'detected_state'        => 'nullable|string|max:255',
        ];
    }

    /**
     * Extrae los nombres de demarcación territorial detectados en el cliente.
     */
    private function extractDetected(array $validated): array
    {
        return [
            'parish'       => $validated['detected_parish'] ?? null,
            'municipality' => $validated['detected_municipality'] ?? null,
            'state'        => $validated['detected_state'] ?? null,
        ];
    }

    /**
     * Resuelve el ID final de la Parroquia aplicando una jerarquía de prioridades:
     * 1. Selección manual directa del usuario en el desplegable.
     * 2. Datos detectados por geocodificación inversa desde el mapa (OSM).
     * 3. Metadatos incluidos dentro del JSON de ubicación.
     * 4. En actualizaciones, conserva el valor previo si no hubo cambios.
     */
    private function resolveParishId(array $validated, ?int $currentParishId): ?int
    {
        // 1. Selección manual
        if (! empty($validated['parish_id'])) {
            return (int) $validated['parish_id'];
        }

        // 2. Detección automática por mapa
        if (
            ! empty($validated['detected_parish']) &&
            ! empty($validated['detected_municipality']) &&
            ! empty($validated['detected_state'])
        ) {
            $id = LocationService::createOrUpdateLocation(
                $validated['detected_parish'],
                $validated['detected_municipality'],
                $validated['detected_state']
            );

            if ($id) {
                return $id;
            }
        }

        // 3. Extracción desde dirección OSM en location_data
        if (! empty($validated['location_data'])) {
            $locationData = json_decode($validated['location_data'], true);

            if ($locationData && isset($locationData['address'])) {
                $result = $this->locationService->processOSMData($locationData);

                if (! empty($result['parish_id'])) {
                    return (int) $result['parish_id'];
                }

                if (
                    ! empty($result['detected_parish']) &&
                    ! empty($result['detected_municipality']) &&
                    ! empty($result['detected_state'])
                ) {
                    $id = LocationService::createOrUpdateLocation(
                        $result['detected_parish'],
                        $result['detected_municipality'],
                        $result['detected_state']
                    );

                    if ($id) {
                        return $id;
                    }
                }
            }
        }

        // 4. Mantener valor preexistente
        return $currentParishId;
    }

    /**
     * Valida y normaliza la cadena GeoJSON asegurando que contenga una geometría Polygon o MultiPolygon válida.
     * Extrae la propiedad 'geometry' si se recibió un objeto completo tipo 'Feature'.
     *
     * @throws \RuntimeException
     */
    private function normalizeGeoJson(string $raw): string
    {
        $decoded = json_decode($raw, true);

        if ($decoded === null) {
            throw new \RuntimeException('GeoJSON inválido: no se pudo parsear el JSON.');
        }

        $geometry = ($decoded['type'] ?? null) === 'Feature'
            ? ($decoded['geometry'] ?? null)
            : $decoded;

        if (empty($geometry['type']) || empty($geometry['coordinates'])) {
            throw new \RuntimeException('GeoJSON geometry inválido o incompleto.');
        }

        if (! in_array($geometry['type'], ['Polygon', 'MultiPolygon'], true)) {
            throw new \RuntimeException('Solo se permiten geometrías de tipo Polygon o MultiPolygon.');
        }

        return json_encode($geometry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Prepara el arreglo estructurado del polígono para devolverlo a ventanas modales o visores AJAX.
     */
    private function serializePolygon(Polygon $polygon): array
    {
        return [
            'id'                    => $polygon->id,
            'name'                  => $polygon->name,
            'description'           => $polygon->description,
            'area_ha'               => $polygon->area_ha,
            'area_formatted'        => $polygon->area_formatted,
            'is_active'             => $polygon->is_active,
            'centroid_lat'          => $polygon->centroid_lat,
            'centroid_lng'          => $polygon->centroid_lng,
            'detected_parish'       => $polygon->detected_parish,
            'detected_municipality' => $polygon->detected_municipality,
            'detected_state'        => $polygon->detected_state,
            'deleted_at'            => $polygon->deleted_at,
            'created_at'            => $polygon->created_at,
            'updated_at'            => $polygon->updated_at,
            'producer'              => $polygon->producer ? [
                'id'   => $polygon->producer->id,
                'name' => "{$polygon->producer->name} {$polygon->producer->lastname}",
            ] : null,
            'parish' => $polygon->parish ? [
                'id'           => $polygon->parish->id,
                'name'         => $polygon->parish->name,
                'municipality' => $polygon->parish->municipality ? [
                    'id'    => $polygon->parish->municipality->id,
                    'name'  => $polygon->parish->municipality->name,
                    'state' => $polygon->parish->municipality->state ? [
                        'id'   => $polygon->parish->municipality->state->id,
                        'name' => $polygon->parish->municipality->state->name,
                    ] : null,
                ] : null,
            ] : null,
        ];
    }

    /**
     * Genera sugerencias de ubicación jerárquicas buscando coincidencias parciales de nombres
     * en estados, municipios y parroquias para ayudar al usuario cuando falla la geocodificación automática.
     */
    private function getLocationSuggestions(
        ?string $parishName,
        ?string $municipalityName,
        ?string $stateName
    ): array {
        $suggestions = [];

        $states = State::where('name', 'like', "%{$stateName}%")
            ->orWhereRaw('LOWER(name) = LOWER(?)', [$stateName])
            ->limit(3)
            ->get();

        foreach ($states as $state) {
            $municipalities = Municipality::where('state_id', $state->id)
                ->where(fn ($q) =>
                    $q->where('name', 'like', "%{$municipalityName}%")
                      ->orWhereRaw('LOWER(name) = LOWER(?)', [$municipalityName])
                )
                ->limit(3)
                ->get();

            foreach ($municipalities as $municipality) {
                $parishes = Parish::where('municipality_id', $municipality->id)
                    ->where(fn ($q) =>
                        $q->where('name', 'like', "%{$parishName}%")
                          ->orWhereRaw('LOWER(name) = LOWER(?)', [$parishName])
                    )
                    ->limit(3)
                    ->get();

                foreach ($parishes as $parish) {
                    $suggestions[] = [
                        'id'           => $parish->id,
                        'name'         => $parish->name,
                        'municipality' => $municipality->name,
                        'state'        => $state->name,
                        'full_name'    => "{$parish->name}, {$municipality->name}, {$state->name}",
                    ];
                }
            }
        }

        return $suggestions;
    }

    /**
     * Evalúa si la petición entrante espera respuesta en formato JSON.
     */
    private function wantsJson(Request $request): bool
    {
        return $request->ajax() || $request->wantsJson();
    }

    /**
     * Construye una respuesta de error homogénea según el tipo de cliente (JSON o redirección con flash message).
     */
    private function errorResponse(
        Request $request,
        string $message,
        int $status = 500
    ): JsonResponse|RedirectResponse {
        if ($this->wantsJson($request)) {
            return response()->json(['success' => false, 'message' => $message], $status);
        }

        return back()->with('error', $message);
    }

    /**
     * Muestra la interfaz para la carga de capas de polígonos mediante archivos cartográficos (Shapefile/GeoJSON).
     */
    public function showImportForm(): View
    {
        $producers = Producer::active()->orderBy('name')->get(['id', 'name', 'lastname']);
        $parishes  = Parish::orderBy('name')->get(['id', 'name']);

        return view('polygons.import', compact('producers', 'parishes'));
    }

    // =========================================================================
    // Procesamiento Masivo de Importación por Lotes (Queue Batches)
    // =========================================================================

    /**
     * Inicia la importación de polígonos desde una capa vectorial procesada en el cliente web:
     * 
     * Arquitectura de colas en lote (Bus Batching):
     * 1. Valida los atributos de la lista de entidades y el sistema de referencia espacial (SRID).
     * 2. Inicializa la sesión de seguimiento en la memoria caché con el total de elementos.
     * 3. Crea una instancia de `ImportPolygonJob` por cada polígono del archivo.
     * 4. Despacha el lote (`Bus::batch`) permitiendo fallos individuales (`allowFailures`) de modo
     *    que si un polígono tiene una geometría rota, no interrumpa la importación de las demás parcelas válidas.
     * 5. Retorna inmediatamente una respuesta HTTP 202 (Accepted) con el UUID del lote
     *    para que la interfaz inicie el sondeo (polling) de la barra de progreso en tiempo real.
     */
    public function processImport(Request $request): JsonResponse
    {
        $request->validate([
            'features'                 => 'required|array',
            'features.*.id'            => 'nullable',
            'features.*.name'          => 'nullable|string|max:255',
            'features.*.area_ha'       => 'nullable|numeric',
            'features.*.producer_id'   => 'nullable|exists:producers,id',
            'features.*.parish_id'     => 'nullable|exists:parishes,id',
            'features.*.geometry'      => 'required|string',
            'srid'                     => 'required|integer',
            'skip_existing'            => 'boolean',
            'create_missing_producers' => 'boolean',
            'analyze_deforestation'    => 'boolean',
            'import_id'                => 'nullable|string|max:64',
        ]);

        $importId = $request->input('import_id') ?: (string) Str::uuid();
        $options  = $this->importService->buildImportOptions($request);
        $srid     = (int) $request->input('srid');
        $causerId = auth()->id();

        // Normalizar los elementos de la capa
        $normalized = array_map(
            fn($f) => $this->importService->normalizeFrontendFeature($f),
            $request->input('features')
        );

        $total = count($normalized);

        // Inicializar el tracking en caché antes del despacho para que el sondeo del frontend no devuelva 404
        $this->importService->initImportProgress($importId, $total);

        // Generar un trabajo por cada entidad cartográfica
        $jobs = [];
        foreach ($normalized as $index => $feature) {
            $jobs[] = new ImportPolygonJob(
                feature:       $feature,
                index:         $index,
                srid:          $srid,
                options:       $options,
                importId:      $importId,
                totalFeatures: $total,
                causerId:      $causerId,
            );
        }

        // Ejecutar el procesamiento asíncrono en segundo plano
        Bus::batch($jobs)
            ->name("import:{$importId}")
            ->allowFailures()
            ->then(function () use ($importId) {
                app(\App\Services\PolygonImportService::class)->finalizeImport($importId, 'done');
            })
            ->catch(function () use ($importId) {
                app(\App\Services\PolygonImportService::class)->finalizeImport($importId, 'done');
            })
            ->finally(function () use ($importId) {
                app(\App\Services\PolygonImportService::class)->finalizeImport($importId, 'done');
            })
            ->dispatch();

        return response()->json([
            'success'   => true,
            'import_id' => $importId,
            'total'     => $total,
        ], 202);
    }

    /**
     * Consulta el estado de avance de una importación por lotes en segundo plano.
     * Es consultado periódicamente por el componente JavaScript de la barra de progreso.
     */
    public function importProgress(string $importId): JsonResponse
    {
        $state = Cache::get("import_progress:{$importId}");

        if (!$state) {
            return response()->json([
                'success' => false,
                'message' => 'Importación no encontrada o expirada.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'state'   => $state,
        ]);
    }
}
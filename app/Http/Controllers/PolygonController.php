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
use Illuminate\Support\Str;

class PolygonController extends Controller
{
    use Filterable;

    public function __construct(
        private readonly LocationService $locationService,
    ) {}

    // =========================================================================
    // Listados
    // =========================================================================

    public function index(Request $request): View
    {
        // Obtener todos los filtros
        $search = $request->get('search');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $status = $request->get('status', 'all');
        $type = $request->get('type', 'all');

        // Nuevos filtros
        $parishId = $request->get('parish_id');
        $municipalityId = $request->get('municipality_id');
        $stateId = $request->get('state_id');
        $areaMin = $request->get('area_min');
        $areaMax = $request->get('area_max');
        $producerId = $request->get('producer_id');
        $hasDeforestation = $request->get('has_deforestation'); // 'yes', 'no', 'all'
        $deforestationYear = $request->get('deforestation_year');
        $lossMin = $request->get('loss_min');
        $lossMax = $request->get('loss_max');

        // Validaciones
        $validationError = $this->validateDateRange($dateFrom, $dateTo);
        if ($validationError) {
            return $validationError;
        }

        // Validar rango de área
        if ($areaMin !== null && $areaMax !== null && $areaMax < $areaMin) {
            return redirect()->back()->withErrors(['area_max' => 'El área máxima no puede ser menor que el área mínima.'])->withInput();
        }

        // Validar rango de pérdida
        if ($lossMin !== null && $lossMax !== null && $lossMax < $lossMin) {
            return redirect()->back()->withErrors(['loss_max' => 'La pérdida máxima no puede ser menor que la pérdida mínima.'])->withInput();
        }

        $query = Polygon::with(['producer', 'parish.municipality.state']);

        // Filtros de fecha
        $query = $this->applyDateFilters($query, $dateFrom, $dateTo, 'created_at');

        // Búsqueda flexible
        $query = $this->applySearchFilter(
            $query,
            $search,
            ['name'],
            []
        );

        // Filtros específicos de estado y tipo
        match ($status) {
            'active'   => $query->where('is_active', true),
            'inactive' => $query->where('is_active', false),
            'deleted'  => $query->onlyTrashed(),
            default    => null,
        };

        match ($type) {
            'with_producer'    => $query->withProducer(),
            'without_producer' => $query->withoutProducer(),
            default            => null,
        };

        // ----- NUEVOS FILTROS -----

        // 1. Parroquia
        if ($parishId) {
            $query->where('parish_id', $parishId);
        }

        // 2. Municipio (a través de parish)
        if ($municipalityId) {
            $query->whereHas('parish', fn($q) => $q->where('municipality_id', $municipalityId));
        }

        // 3. Estado (a través de parish.municipality)
        if ($stateId) {
            $query->whereHas('parish.municipality', fn($q) => $q->where('state_id', $stateId));
        }

        // 4. Área mín/máx
        if ($areaMin !== null) {
            $query->where('area_ha', '>=', $areaMin);
        }
        if ($areaMax !== null) {
            $query->where('area_ha', '<=', $areaMax);
        }

        // 5. Productor específico
        if ($producerId) {
            $query->where('producer_id', $producerId);
        }

        // 6. Deforestación (con o sin)
        if ($hasDeforestation === 'yes') {
            $query->where('deforested', true);
        } elseif ($hasDeforestation === 'no') {
            $query->where('deforested', false);
        }

        // 7. Rango de años (polígonos que tienen deforestación en ese año)
        if ($deforestationYear) {
            $query->whereHas('deforestations', fn($q) => $q->where('year', $deforestationYear));
        }

        // 8. Pérdida mín/máx (usando subconsulta para obtener el último registro o el máximo)
        // Opción: pérdida máxima en cualquier registro
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

        // Paginar
        $polygons = $query->latest()->paginate(10);

        // Mantener filtros en la paginación
        $polygons->appends([
            'search' => $search,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'status' => $status,
            'type' => $type,
            'parish_id' => $parishId,
            'municipality_id' => $municipalityId,
            'state_id' => $stateId,
            'area_min' => $areaMin,
            'area_max' => $areaMax,
            'producer_id' => $producerId,
            'has_deforestation' => $hasDeforestation,
            'deforestation_year' => $deforestationYear,
            'loss_min' => $lossMin,
            'loss_max' => $lossMax,
        ]);

        // Obtener listas para los selects (parroquias, municipios, estados, productores, años)
        $parishes = Parish::orderBy('name')->get(['id', 'name']);
        $municipalities = Municipality::orderBy('name')->get(['id', 'name']);
        $states = State::orderBy('name')->get(['id', 'name']);
        $producers = Producer::orderBy('name')->get(['id', 'name', 'lastname']);
        $years = range(2020, now()->year); // rango de años para deforestación
        
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

    public function map(): View
    {
        return view('polygons.map');
    }

    // =========================================================================
    // CRUD
    // =========================================================================

    public function create(): View
    {
        $producers = Producer::active()->get();
        $parishes  = Parish::with('municipality.state')->get();

        return view('polygons.create', compact('producers', 'parishes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->validationRules());

        try {
            $parishId = $this->resolveParishId($validated, null);
            $geoJson  = $this->normalizeGeoJson($validated['geometry']);
            $detected = $this->extractDetected($validated);

            // Construir location_data (lógica en el modelo)
            $rawLocation  = ! empty($validated['location_data'])
                ? (json_decode($validated['location_data'], true) ?? [])
                : [];

            // Crear un polígono temporal para acceder al método del modelo
            $temp         = new Polygon();
            $locationData = $temp->buildLocationDataForCreate($rawLocation, $detected, $parishId);

            // Delegar creación con geometría al modelo
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

            // Recalcular área y centroide desde PostGIS (lógica en el modelo)
            $polygon->recalculateGeometryStats();

            

            Log::info('Polígono creado', ['id' => $polygon->id, 'parish_id' => $polygon->parish_id]);

            return redirect()->route('polygons.index')
                ->with('success', "Polígono '{$polygon->name}' creado exitosamente.");

        } catch (\Throwable $e) {
            
            Log::error('Error al crear polígono', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Error al crear el polígono: ' . $e->getMessage());
        }
    }

    public function show(Polygon $polygon): View
    {
        $polygon->load(['producer', 'parish.municipality.state']);

        return view('polygons.show', compact('polygon'));
    }

    public function edit(Polygon $polygon): View
    {
        $polygon->load('parish.municipality.state');
        $producers = Producer::active()->get();
        $parishes  = Parish::with('municipality.state')->get();

        return view('polygons.edit', compact('polygon', 'producers', 'parishes'));
    }

    public function update(Request $request, Polygon $polygon): RedirectResponse
    {
        $validated = $request->validate($this->validationRules(isUpdate: true));

        DB::beginTransaction();
        try {
            Log::info('Iniciando update de polígono', ['id' => $polygon->id]);

            $parishId = $this->resolveParishId($validated, $polygon->parish_id);
            $geoJson = $this->normalizeGeoJson($validated['geometry']);
            $detected = $this->extractDetected($validated);

            Log::info('GeoJson recibido', ['geoJson' => substr($geoJson, 0, 200)]);

            $oldGeoJson = $polygon->getGeometryGeoJson();
            Log::info('GeoJson actual', ['oldGeoJson' => substr($oldGeoJson, 0, 200)]);

            $rawLocation = !empty($validated['location_data'])
                ? (json_decode($validated['location_data'], true) ?? [])
                : [];

            $locationData = $polygon->mergeLocationDataForUpdate(
                $rawLocation,
                $detected,
                auth()->id()
            );

            Log::info('Preparando updateWithGeometry...');
            $updated = $polygon->updateWithGeometry(
                [
                    'name'          => $validated['name'],
                    'description'   => $validated['description'] ?? null,
                    'producer_id'   => $validated['producer_id'] ?? null,
                    'parish_id'     => $parishId,
                    //'area_ha'       => $validated['area_ha'] ?? null,
                    'is_active'     => $validated['is_active'] ?? true,
                    //'centroid_lat'  => $validated['centroid_lat'] ?? null,
                    //'centroid_lng'  => $validated['centroid_lng'] ?? null,
                    'location_data' => $locationData,
                ],
                $geoJson
            );

            Log::info('updateWithGeometry resultó', ['result' => $updated]);

            // Comparación normalizada
            $normalizedOld = $polygon->normalizeGeoJsonString($oldGeoJson);
            $normalizedNew = $polygon->normalizeGeoJsonString($geoJson);

            if ($normalizedOld !== $normalizedNew) {
                Log::info('La geometría cambió, recalculando stats...');
                $polygon->recalculateGeometryStats();
                Log::info('recalculateGeometryStats ejecutado');
            } else {
                Log::info('La geometría no cambió, no se recalcula');
            }

            DB::commit();
            Log::info('Transacción commit exitosa');

            return redirect()->route('polygons.index')
                ->with('success', "Polígono '{$polygon->name}' actualizado exitosamente.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al actualizar polígono', [
                'id' => $polygon->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'Error al actualizar el polígono: ' . $e->getMessage());
        }
    }

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
    // Acciones de estado
    // =========================================================================

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
    // Endpoints JSON
    // =========================================================================

    /**
     * Devuelve todos los polígonos activos como GeoJSON FeatureCollection.
     */
    /**
     * Devuelve todos los polígonos activos como GeoJSON FeatureCollection.
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

                // Obtener datos de deforestación
                $deforestations = $polygon->deforestations->map(function ($d) {
                    return [
                        'year' => $d->year,
                        'percentage_loss' => $d->percentage_loss,
                    ];
                })->toArray();

                // Determinar el estado de deforestación
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
                        'id'          => $polygon->id,
                        'name'        => $polygon->name,
                        'producer'    => $polygon->producer_name,
                        'area_ha'     => $polygon->area_ha,
                        'description' => $polygon->description,
                        'type'        => $polygon->type,
                        'deforestation_status' => $deforestationStatus,
                        'deforestations' => $deforestations,
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
     * Detalles de un polígono para modal (incluye soft-deleted).
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
     * Busca o crea una parroquia a partir de datos OSM enviados por el frontend.
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
    // Helpers privados — solo coordinación, sin lógica de negocio
    // =========================================================================

    /**
     * Reglas de validación compartidas entre store y update.
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
     * Extrae los campos detectados del request en un array uniforme.
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
     * Resuelve el parish_id final siguiendo esta cadena de prioridad:
     *   1. Selección manual en el formulario
     *   2. Datos detectados por el frontend (OSM)
     *   3. location_data completo con address
     *   4. Valor previo del modelo (solo en update)
     */
    private function resolveParishId(array $validated, ?int $currentParishId): ?int
    {
        // 1. Selección manual
        if (! empty($validated['parish_id'])) {
            return (int) $validated['parish_id'];
        }

        // 2. Detectado por el frontend
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

        // 3. Desde location_data OSM
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

        // 4. Conservar valor previo (update)
        return $currentParishId;
    }

    /**
     * Parsea y normaliza el GeoJSON del frontend.
     * Acepta Feature o Geometry directamente.
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
     * Serializa un Polygon para respuestas JSON (modal de detalles).
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
     * Busca sugerencias de ubicación por similitud de nombre.
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
     * Detecta si la request espera una respuesta JSON.
     */
    private function wantsJson(Request $request): bool
    {
        return $request->ajax() || $request->wantsJson();
    }

    /**
     * Respuesta de error uniforme: JSON o redirect según el tipo de request.
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
     * Muestra el formulario de importación de GeoJSON.
     */
    public function showImportForm(): View
    {
        $producers = Producer::active()->orderBy('name')->get(['id', 'name', 'lastname']);
        $parishes  = Parish::orderBy('name')->get(['id', 'name']);

        return view('polygons.import', compact('producers', 'parishes'));
    }

    // =========================================================================
    // Importación (archivo GeoJSON y JSON del frontend)
    // =========================================================================

    /**
     * Importa polígonos desde un archivo GeoJSON subido.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file'                     => 'required|file|mimes:json,geojson|max:10240',
            'parish_id'                => 'nullable|exists:parishes,id',
            'default_producer_id'      => 'nullable|exists:producers,id',
            'create_missing_producers' => 'boolean',
            'skip_existing'            => 'boolean',
            'srid'                     => 'nullable|integer|min:0',
            'producer_field'           => 'nullable|string|max:50',
            'analyze_deforestation'    => 'boolean',
            'import_id'                => 'nullable|string|max:64',
        ]);


        $importId = $request->input('import_id');
        $geojson = json_decode(file_get_contents($request->file('file')->getPathname()), true);

        if (($geojson['type'] ?? null) !== 'FeatureCollection') {
            return back()->withErrors(['file' => 'El archivo no es un FeatureCollection GeoJSON válido.']);
        }

        $features = $geojson['features'] ?? [];
        if (empty($features)) {
            return back()->withErrors(['file' => 'El archivo no contiene features.']);
        }

        $options = $this->buildImportOptions($request);
        $srid    = $this->detectSrid($request, $geojson);

        // Normalizar cada feature raw al shape común
        $normalized = array_map(
            fn($f) => $this->normalizeRawFeature($f, $options['producer_field']),
            $features
        );

        $result = $this->processImportFeatures($normalized, $srid, $options, $importId);

        return redirect()->route('polygons.index')
            ->with('success', $this->buildImportSummary($result));
    }

    /**
     * Importa polígonos desde un JSON enviado por el frontend.
     */
    public function processImport(Request $request): RedirectResponse
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

        $importId = $request->input('import_id');
        $options = $this->buildImportOptions($request);
        $srid    = (int) $request->input('srid');

        // Normalizar cada feature del frontend al shape común
        $normalized = array_map(
            fn($f) => $this->normalizeFrontendFeature($f),
            $request->input('features')
        );

        $result = $this->processImportFeatures($normalized, $srid, $options, $importId);

        return redirect()->route('polygons.index')
            ->with('success', $this->buildImportSummary($result));
    }


    // =========================================================================
    // Helpers de importación (compartidos por import() y processImport())
    // =========================================================================

    /**
     * Construye el array de opciones comunes a ambas importaciones.
     */
    private function buildImportOptions(Request $request): array
    {
        return [
            'parish_id'                => $request->input('parish_id'),
            'default_producer_id'      => $request->input('default_producer_id'),
            'create_missing_producers' => $request->boolean('create_missing_producers'),
            'skip_existing'            => $request->boolean('skip_existing'),
            'producer_field'           => $request->input('producer_field', 'Productor'),
            'analyze_deforestation'    => $request->boolean('analyze_deforestation'),
        ];
    }

    /**
     * Detecta el SRID desde el request o desde el CRS del GeoJSON.
     */
    private function detectSrid(Request $request, array $geojson): int
    {
        if ($request->filled('srid')) {
            return (int) $request->input('srid');
        }

        if (isset($geojson['crs']['properties']['name'])
            && preg_match('/EPSG::(\d+)/', $geojson['crs']['properties']['name'], $m)) {
            return (int) $m[1];
        }

        return 4326;
    }

    /**
     * Normaliza un Feature GeoJSON crudo (de archivo) al shape común.
     */
    private function normalizeRawFeature(array $feature, string $producerField): array
    {
        $props = $feature['properties'] ?? [];

        return [
            'geometry'       => $feature['geometry'] ?? null,
            'external_id'    => $props['id'] ?? null,
            'name'           => $props['name'] ?? null,
            'description'    => $props['description'] ?? null,
            'area_ha'        => $props['Area_Ha'] ?? $props['area_ha'] ?? $props['area'] ?? null,
            'producer_name'  => trim($props[$producerField] ?? ''),
            'producer_id'    => null,
            'parish_id'      => null,
            'raw_properties' => $props,
        ];
    }

    /**
     * Normaliza un Feature del frontend (geometry como string JSON) al shape común.
     */
    private function normalizeFrontendFeature(array $featureData): array
    {
        $geometry = null;
        if (!empty($featureData['geometry'])) {
            $geometry = is_string($featureData['geometry'])
                ? json_decode($featureData['geometry'], true)
                : $featureData['geometry'];
        }

        return [
            'geometry'       => $geometry,
            'external_id'    => $featureData['id'] ?? null,
            'name'           => $featureData['name'] ?? null,
            'description'    => $featureData['description'] ?? null,
            'area_ha'        => $featureData['area_ha'] ?? null,
            'producer_name'  => trim($featureData['producer_name'] ?? ''),
            'producer_id'    => $featureData['producer_id'] ?? null,
            'parish_id'      => $featureData['parish_id'] ?? null,
            'raw_properties' => $featureData,
        ];
    }

    /**
     * Procesa una lista de features ya normalizados.
     *
     * @return array{imported:int, skipped:int, duplicated:array, errors:array}
     */
    private function processImportFeatures(
        array $features,
        int $srid,
        array $options,
        ?string $importId = null
    ): array {
        $imported   = 0;
        $skipped    = 0;
        $duplicated = [];
        $errors     = [];
        $analyzed   = 0;

        // Inicializar progreso
        $this->initImportProgress($importId, count($features));

        set_time_limit(0);

        foreach ($features as $index => $feature) {
            try {
                $wasAnalyzed = false;
                $this->importSingleFeature(
                    $feature, $index, $srid, $options,
                    $imported, $skipped, $wasAnalyzed, $importId
                );
                if ($wasAnalyzed) {
                    $analyzed++;
                }
            } catch (\Illuminate\Database\QueryException $e) {
                if ($this->isUniqueViolation($e)) {
                    $duplicated[] = $feature['external_id'] ?? "#{$index}";
                } else {
                    Log::error('Error de BD importando feature', [
                        'index' => $index, 'error' => $e->getMessage(),
                    ]);
                    $errors[] = "Feature #{$index}: error de base de datos";
                }
            } catch (\Throwable $e) {
                Log::error('Error inesperado importando feature', [
                    'index' => $index, 'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $errors[] = "Feature #{$index}: " . $e->getMessage();
            }
        }

        $this->finishImportProgress($importId, 'done');

        return compact('imported', 'skipped', 'duplicated', 'errors', 'analyzed');
    }

    /**
     * Importa un único feature. Modifica $imported y $skipped por referencia.
     */
    private function importSingleFeature(
        array $feature,
        int $index,
        int $srid,
        array $options,
        int &$imported,
        int &$skipped,
        bool &$wasAnalyzed,
        ?string $importId = null
    ): void {
        $wasAnalyzed = false;
        $featureName = $feature['name'] ?? "Feature #{$index}";

        try {
            // 1. Validar geometría
            $geometry = $feature['geometry'];
            if (empty($geometry) || empty($geometry['type'])) {
                $this->updateImportProgress($importId, $index, $featureName, 'error');
                throw new \RuntimeException("Geometría inválida en feature #{$index}");
            }
            if (!in_array($geometry['type'], ['Polygon', 'MultiPolygon'], true)) {
                $this->updateImportProgress($importId, $index, $featureName, 'error');
                throw new \RuntimeException("Tipo no soportado en feature #{$index}: {$geometry['type']}");
            }

            // 2. Omitir duplicados
            $externalId = $feature['external_id'];
            if ($externalId && $options['skip_existing']
                && Polygon::withTrashed()->where('external_id', $externalId)->exists()) {
                $skipped++;
                $this->updateImportProgress($importId, $index, $featureName, 'skipped');
                return;
            }

            // 3. Resolver productor
            $producerId = $this->resolveProducerId(
                $feature['producer_id'],
                $feature['producer_name'],
                $options['default_producer_id'],
                $options['create_missing_producers']
            );

            // 4. Crear polígono
            $data = [
                'external_id'   => $externalId,
                'name'          => $featureName,
                'description'   => $feature['description'] ?? null,
                'producer_id'   => $producerId,
                'parish_id'     => $feature['parish_id'] ?? $options['parish_id'],
                'area_ha'       => $feature['area_ha'] ?? null,
                'is_active'     => true,
                'location_data' => [
                    'imported_from'       => 'geojson',
                    'original_properties' => $feature['raw_properties'],
                    'external_id'         => $externalId,
                ],
            ];

            $polygon = Polygon::createWithGeometry($data, json_encode($geometry), $srid, true);

            if (is_null($data['area_ha'])) {
                $polygon->recalculateGeometryStats();
            } else {
                $polygon->updateQuietly(['area_ha' => $data['area_ha']]);
            }

            // 5. Análisis opcional
            $deforestedHa = null;
            if (!empty($options['analyze_deforestation'])) {
                try {
                    $startYear = (int) config('deforestation.import_default_start_year');
                    $endYear   = (int) config('deforestation.import_default_end_year');

                    $yearly       = $polygon->analyzeDeforestationFromGFW($startYear, $endYear);
                    $wasAnalyzed  = true;
                    $deforestedHa = array_sum(array_column($yearly, 'area__ha'));
                } catch (\Throwable $e) {
                    Log::warning("Análisis post-importación falló para polígono {$polygon->id}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $imported++;
            $this->updateImportProgress(
                $importId,
                $index,
                $featureName,
                $wasAnalyzed ? 'analyzed' : 'success',
                $deforestedHa
            );

        } catch (\Illuminate\Database\QueryException $e) {
            if ($this->isUniqueViolation($e)) {
                $this->updateImportProgress($importId, $index, $featureName, 'duplicated');
                throw $e;
            }
            $this->updateImportProgress($importId, $index, $featureName, 'error');
            throw $e;
        } catch (\Throwable $e) {
            // Si no se actualizó antes (por ej. antes de resolver el nombre), lo marcamos aquí
            $this->updateImportProgress($importId, $index, $featureName, 'error');
            throw $e;
        }
    }

    /**
     * Resuelve el id de productor: busca por nombre, crea si se pidió,
     * o cae al default.
     */
    private function resolveProducerId(
        ?int $producerId,
        string $producerName,
        ?int $defaultProducerId,
        bool $createMissing
    ): ?int {
        // Ya viene por id
        if ($producerId) {
            return $producerId;
        }

        if ($producerName !== '') {
            $producer = Producer::whereRaw(
                'LOWER(name || \' \' || lastname) = ?',
                [strtolower($producerName)]
            )->first();

            if ($producer) {
                return $producer->id;
            }

            if ($createMissing) {
                [$firstName, $lastName] = array_pad(explode(' ', $producerName, 2), 2, '');

                return Producer::create([
                    'name'        => $firstName,
                    'lastname'    => $lastName,
                    'cedula'      => null,
                    'cedula_type' => 'V',
                    'code'        => null,
                    'is_active'   => true,
                ])->id;
            }
        }

        return $defaultProducerId;
    }

    /**
     * Determina si una QueryException es violación de unicidad de Postgres.
     */
    private function isUniqueViolation(\Illuminate\Database\QueryException $e): bool
    {
        return $e->getCode() === '23505' || str_contains($e->getMessage(), '23505');
    }

    /**
     * Construye el mensaje resumen de la importación.
     */
    private function buildImportSummary(array $result): string
    {
        $parts = [];

        if ($result['imported'] > 0) {
            $parts[] = "<strong>{$result['imported']}</strong> importado" . ($result['imported'] === 1 ? '' : 's');
        }
        if (!empty($result['analyzed'])) {
            $parts[] = "<strong>{$result['analyzed']}</strong> analizado" . ($result['analyzed'] === 1 ? '' : 's');
        }
        if ($result['skipped'] > 0) {
            $parts[] = "<strong>{$result['skipped']}</strong> omitido" . ($result['skipped'] === 1 ? '' : 's');
        }
        if (!empty($result['duplicated'])) {
            $n = count($result['duplicated']);
            $parts[] = "<strong>{$n}</strong> duplicado" . ($n === 1 ? '' : 's');
        }
        if (!empty($result['errors'])) {
            $n = count($result['errors']);
            $parts[] = "<strong>{$n}</strong> error" . ($n === 1 ? '' : 'es');
        }

        return empty($parts) ? 'Sin cambios.' : implode(' · ', $parts);
    }

    /**
     * Inicializa el estado de progreso de una importación en cache.
     */
    private function initImportProgress(?string $importId, int $total): void
    {
        if (!$importId) {
            return;
        }

        Cache::put("import_progress:{$importId}", [
            'total'            => $total,
            'current'          => 0,
            'status'           => 'processing', // processing | done | error
            'feature_statuses' => [],
            'summary'          => [
                'imported'   => 0,
                'skipped'    => 0,
                'duplicated' => [],
                'errors'     => [],
                'analyzed'   => 0,
            ],
            'started_at'       => now()->toISOString(),
        ], now()->addHour());
    }

    /**
     * Actualiza el progreso tras procesar un feature.
     */
    private function updateImportProgress(
        ?string $importId,
        int $featureIndex,
        string $featureName,
        string $status, // success | skipped | duplicated | error
        ?float $deforestedHa = null
    ): void {
        if (!$importId) {
            return;
        }

        $key   = "import_progress:{$importId}";
        $state = Cache::get($key);

        if (!$state) {
            return;
        }

        $state['current'] = $featureIndex + 1;

        $state['feature_statuses'][$featureIndex] = [
            'name'           => $featureName,
            'status'         => $status,
            'deforested_ha'  => $deforestedHa,
        ];

        // Actualizar contadores del summary
        match ($status) {
            'success'     => $state['summary']['imported']++,
            'analyzed'    => $state['summary']['analyzed']++,
            'skipped'     => $state['summary']['skipped']++,
            'duplicated'  => $state['summary']['duplicated'][] = $featureName,
            'error'       => $state['summary']['errors'][]     = $featureName,
            default       => null,
        };

        Cache::put($key, $state, now()->addHour());
    }

    /**
     * Marca la importación como terminada.
     */
    private function finishImportProgress(?string $importId, string $status = 'done'): void
    {
        if (!$importId) {
            return;
        }

        $key   = "import_progress:{$importId}";
        $state = Cache::get($key);

        if ($state) {
            $state['status']     = $status;
            $state['finished_at'] = now()->toISOString();
            Cache::put($key, $state, now()->addHour());
        }
    }

    /**
     * Devuelve el estado de progreso de una importación en curso.
     * Se consulta vía polling desde el widget.
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
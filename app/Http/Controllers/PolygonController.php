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

class PolygonController extends Controller
{
    public function __construct(private readonly LocationService $locationService) {}

    // =========================================================================
    // Listados
    // =========================================================================

    public function index(Request $request): View
    {
        $search = $request->get('search');
        $status = $request->get('status', 'all');
        $type   = $request->get('type', 'all');

        $query = Polygon::with(['producer', 'parish.municipality.state']);

        if ($search) {
            $query->search($search);
        }

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

        $polygons = $query->latest()->paginate(10);

        return view('polygons.index', compact('polygons', 'search', 'status', 'type'));
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

        DB::beginTransaction();
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

            DB::commit();

            Log::info('Polígono creado', ['id' => $polygon->id, 'parish_id' => $polygon->parish_id]);

            return redirect()->route('polygons.index')
                ->with('success', "Polígono '{$polygon->name}' creado exitosamente.");

        } catch (\Throwable $e) {
            DB::rollBack();
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
            $parishId = $this->resolveParishId($validated, $polygon->parish_id);
            $geoJson  = $this->normalizeGeoJson($validated['geometry']);
            $detected = $this->extractDetected($validated);

            $rawLocation  = ! empty($validated['location_data'])
                ? (json_decode($validated['location_data'], true) ?? [])
                : [];

            // Fusionar location_data con detección (lógica en el modelo)
            $locationData = $polygon->mergeLocationDataForUpdate(
                $rawLocation,
                $detected,
                auth()->id()
            );

            // Delegar actualización con geometría al modelo
            // fill() + save() dispara el evento 'updated' → Spatie lo captura
            $polygon->updateWithGeometry(
                [
                    'name'          => $validated['name'],
                    'description'   => $validated['description'] ?? null,
                    'producer_id'   => $validated['producer_id'] ?? null,
                    'parish_id'     => $parishId,
                    'area_ha'       => $validated['area_ha'] ?? null,
                    'is_active'     => $validated['is_active'] ?? true,
                    'centroid_lat'  => $validated['centroid_lat'] ?? null,
                    'centroid_lng'  => $validated['centroid_lng'] ?? null,
                    'location_data' => $locationData,
                ],
                $geoJson
            );

            // Recalcular área y centroide desde PostGIS (lógica en el modelo)
            $polygon->recalculateGeometryStats();

            DB::commit();

            Log::info('Polígono actualizado', ['id' => $polygon->id, 'parish_id' => $polygon->parish_id]);

            return redirect()->route('polygons.index')
                ->with('success', "Polígono '{$polygon->name}' actualizado exitosamente.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al actualizar polígono', ['id' => $polygon->id, 'error' => $e->getMessage()]);
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
    public function geojson(): JsonResponse
    {
        $polygons = Polygon::with(['producer', 'parish.municipality.state'])
            ->active()
            ->get();

        $features = $polygons->map(function (Polygon $polygon) {
            try {
                $geojsonStr = DB::selectOne(
                    'SELECT ST_AsGeoJSON(geometry) AS geojson FROM polygons WHERE id = ?',
                    [$polygon->id]
                )?->geojson ?? '{}';

                return [
                    'type'       => 'Feature',
                    'properties' => [
                        'id'          => $polygon->id,
                        'name'        => $polygon->name,
                        'producer'    => $polygon->producer_name,
                        'area_ha'     => $polygon->area_ha,
                        'description' => $polygon->description,
                        'type'        => $polygon->type,
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
            'name'                  => 'required|string|max:255',
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
}
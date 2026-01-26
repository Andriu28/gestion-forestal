<?php
// [file name]: app/Http/Controllers/PolygonController.php

namespace App\Http\Controllers;

use App\Models\Polygon;
use App\Models\Producer;
use App\Models\Parish;
use App\Models\Municipality;
use App\Models\State;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PolygonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // En PolygonController.php - método index()
    public function index(Request $request): View
    {
        $search = $request->get('search');
        $status = $request->get('status', 'all');
        $type = $request->get('type', 'all');

        // Iniciar query incluyendo soft deleted
        $query = Polygon::with(['producer', 'parish.municipality.state'])
            ->withTrashed(); // ← AÑADE ESTA LÍNEA

        // Aplicar búsqueda
        if ($search) {
            $query->search($search);
        }

        // Aplicar filtro de estado
        if ($status === 'active') {
            $query->where('is_active', true)
                ->whereNull('deleted_at'); // Solo activos no eliminados
        } elseif ($status === 'inactive') {
            $query->where('is_active', false)
                ->whereNull('deleted_at'); // Solo inactivos no eliminados
        } elseif ($status === 'deleted') {
            $query->onlyTrashed(); // Solo eliminados
        } else {
            // 'all' - ya tenemos withTrashed() arriba, muestra todos incluyendo eliminados
        }

        // Aplicar filtro de tipo
        if ($type === 'with_producer') {
            $query->whereNotNull('producer_id');
        } elseif ($type === 'without_producer') {
            $query->whereNull('producer_id');
        }

        $polygons = $query->latest()->paginate(10);

        return view('polygons.index', compact('polygons', 'search', 'status', 'type'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $producers = Producer::active()->get();
        $parishes = Parish::with(['municipality.state'])->get();

        return view('polygons.create', compact('producers', 'parishes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    // En PolygonController.php - Actualizar el método store
    

    private function convertToPostGISGeometry(string $coordinatesJson): string
    {
        $coordinates = json_decode($coordinatesJson, true);
        
        if (!isset($coordinates[0]) || count($coordinates[0]) < 4) {
            throw new \Exception('Se requieren al menos 3 puntos para un polígono válido');
        }
        
        // Crear estructura GeoJSON para PostGIS
        $geojson = [
            'type' => 'Polygon',
            'coordinates' => $coordinates,
            'crs' => [
                'type' => 'name',
                'properties' => [
                    'name' => 'EPSG:4326'
                ]
            ]
        ];
        
        // PostGIS puede usar ST_GeomFromGeoJSON
        return json_encode($geojson);
    }

    // En el método store():
    public function store(Request $request): RedirectResponse
    {
        // AGREGAR los campos detected_* para poder crear las ubicaciones
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'geometry' => 'required|string',
            'producer_id' => 'nullable|exists:producers,id',
            'parish_id' => 'nullable|exists:parishes,id',
            'area_ha' => 'nullable|numeric|min:0',
            'centroid_lat' => 'nullable|numeric|between:-90,90',
            'centroid_lng' => 'nullable|numeric|between:-180,180',
            'location_data' => 'nullable|string',
            'detected_parish' => 'nullable|string',      // Para crear nueva ubicación
            'detected_municipality' => 'nullable|string', // Para crear nueva ubicación
            'detected_state' => 'nullable|string'         // Para crear nueva ubicación
        ]);

        DB::beginTransaction();
        try {
            $parishId = $validated['parish_id'] ?? null;
            
            // Si NO hay parish_id pero SÍ hay datos detectados, CREAR la ubicación
            if (!$parishId && !empty($validated['detected_parish']) && 
                !empty($validated['detected_municipality']) && !empty($validated['detected_state'])) {
                
                $locationService = new LocationService();
                $parishId = $locationService::createOrUpdateLocation(
                    $validated['detected_parish'],
                    $validated['detected_municipality'],
                    $validated['detected_state']
                );
                
                if ($parishId) {
                    \Log::info('Creada nueva ubicación desde datos detectados', [
                        'parish_id' => $parishId,
                        'parish' => $validated['detected_parish'],
                        'municipality' => $validated['detected_municipality'],
                        'state' => $validated['detected_state']
                    ]);
                }
            }
            
            // Si todavía no hay parish_id pero tenemos location_data (OSM), procesarlo
            if (!$parishId && !empty($validated['location_data'])) {
                $locationData = json_decode($validated['location_data'], true);
                if ($locationData && isset($locationData['address'])) {
                    $locationService = new LocationService();
                    $result = $locationService->processOSMData($locationData);
                    
                    // Intentar con datos procesados de OSM
                    if (!empty($result['parish_id'])) {
                        $parishId = $result['parish_id'];
                        \Log::info('Parroquia encontrada desde OSM: ' . $parishId);
                    } 
                    // Si OSM no encontró match PERO tiene datos detectados, CREAR la ubicación
                    else if (!empty($result['detected_parish']) && 
                            !empty($result['detected_municipality']) && 
                            !empty($result['detected_state'])) {
                        
                        $parishId = $locationService::createOrUpdateLocation(
                            $result['detected_parish'],
                            $result['detected_municipality'],
                            $result['detected_state']
                        );
                        
                        if ($parishId) {
                            \Log::info('Creada nueva ubicación desde OSM', [
                                'parish_id' => $parishId,
                                'parish' => $result['detected_parish'],
                                'municipality' => $result['detected_municipality'],
                                'state' => $result['detected_state']
                            ]);
                        }
                    }
                }
            }
            
            // Construir location_data para registro
            $locationDataJson = null;
            if (!empty($validated['location_data'])) {
                $locationData = json_decode($validated['location_data'], true);
                if ($locationData) {
                    // Guardar datos de creación
                    $locationData['created_info'] = [
                        'has_detected_data' => !empty($validated['detected_parish']),
                        'detected_parish' => $validated['detected_parish'] ?? null,
                        'detected_municipality' => $validated['detected_municipality'] ?? null,
                        'detected_state' => $validated['detected_state'] ?? null,
                        'assigned_parish_id' => $parishId,
                        'created_at' => now()->toISOString()
                    ];
                    $locationDataJson = json_encode($locationData, JSON_UNESCAPED_UNICODE);
                }
            }

            // Normalizar GeoJSON (tu código existente)
            $geojsonRaw = $validated['geometry'] ?? null;
            if (empty($geojsonRaw)) throw new \Exception('La geometría no puede estar vacía.');
            $decoded = json_decode($geojsonRaw, true);
            if ($decoded === null) throw new \Exception('GeoJSON inválido (no se pudo parsear).');
            $geometryObj = (isset($decoded['type']) && $decoded['type'] === 'Feature') 
                ? ($decoded['geometry'] ?? null) 
                : $decoded;
            if (empty($geometryObj) || empty($geometryObj['type']) || empty($geometryObj['coordinates'])) {
                throw new \Exception('GeoJSON geometry inválido o incompleto.');
            }
            if (!in_array($geometryObj['type'], ['Polygon','MultiPolygon'])) {
                throw new \Exception('Solo se permiten Polygon o MultiPolygon.');
            }
            $geoForDb = json_encode($geometryObj, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            // Insertar con SQL - SOLO parish_id
            $now = now();

            $row = DB::selectOne(
                "INSERT INTO polygons
                    (name, description, producer_id, parish_id, area_ha, is_active, 
                    centroid_lat, centroid_lng, location_data, geometry, 
                    created_at, updated_at)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, 
                    ST_SetSRID(ST_GeomFromGeoJSON(?), 4326), ?, ?)
                RETURNING id",
                [
                    $validated['name'],
                    $validated['description'] ?? null,
                    $validated['producer_id'] ?? null,
                    $parishId,  // ¡AHORA SIEMPRE tendrá un valor (si se detectó ubicación)!
                    $validated['area_ha'] ?? null,
                    true,
                    $validated['centroid_lat'] ?? null,
                    $validated['centroid_lng'] ?? null,
                    $locationDataJson,
                    $geoForDb,
                    $now,
                    $now
                ]
            );

            if (!isset($row->id)) {
                throw new \Exception('No se pudo insertar polígono (no se obtuvo id).');
            }

            // Obtener el polígono creado con sus relaciones
            $polygon = Polygon::with(['parish.municipality.state'])->find($row->id);

            // Calcular área y centroide automáticamente
            $res = DB::selectOne("
                SELECT 
                ST_Area(geometry::geography) / 10000 AS area_ha,
                ST_AsGeoJSON(ST_Centroid(geometry)) AS centroid_geojson
                FROM polygons
                WHERE id = ?
            ", [$polygon->id]);

            if ($res) {
                DB::table('polygons')->where('id', $polygon->id)->update([
                    'area_ha' => isset($res->area_ha) ? round((float)$res->area_ha, 2) : null,
                    'centroid_lat' => !empty($res->centroid_geojson) ? 
                        json_decode($res->centroid_geojson, true)['coordinates'][1] ?? null : null,
                    'centroid_lng' => !empty($res->centroid_geojson) ? 
                        json_decode($res->centroid_geojson, true)['coordinates'][0] ?? null : null,
                    'updated_at' => now()
                ]);
                
                $polygon->refresh();
            }

            // Registrar actividad
            if (class_exists('Spatie\Activitylog\Models\Activity')) {
                activity()
                    ->performedOn($polygon)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'attributes' => [
                            'name' => $polygon->name,
                            'area_ha' => $polygon->area_ha,
                            'producer_id' => $polygon->producer_id,
                            'parish_id' => $polygon->parish_id,
                            'is_active' => $polygon->is_active,
                            'location' => $polygon->parish ? 
                                "{$polygon->parish->name}, {$polygon->parish->municipality->name}, {$polygon->parish->municipality->state->name}" : 
                                'Sin ubicación'
                        ]
                    ])
                    ->event('created')
                    ->log("Polígono '{$polygon->name}' fue creado");
            }

            DB::commit();

            \Log::info('Polígono creado exitosamente', [
                'id' => $polygon->id,
                'name' => $polygon->name,
                'parish_id' => $polygon->parish_id,
                'has_location' => !is_null($polygon->parish_id),
                'location_full' => $polygon->parish ? 
                    "{$polygon->parish->name}, {$polygon->parish->municipality->name}, {$polygon->parish->municipality->state->name}" : 
                    null
            ]);

            return redirect()->route('polygons.index')->with('success', 'Polígono creado exitosamente.')
                ->with('debug_info', [
                    'polygon_id' => $polygon->id,
                    'parish_id' => $polygon->parish_id,
                    'location' => $polygon->parish ? 
                        "{$polygon->parish->name}, {$polygon->parish->municipality->name}" : 
                        'Sin ubicación'
                ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear polígono: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al crear polígono: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Polygon $polygon): View
    {
        $polygon->load(['producer', 'parish.municipality.state']);
        return view('polygons.show', compact('polygon'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Polygon $polygon): View
    {
        $producers = Producer::active()->get();
        $parishes = Parish::with(['municipality.state'])->get();
        
        $polygon->load(['parish.municipality.state']);
        
        return view('polygons.edit', compact('polygon', 'producers', 'parishes'));
    }

    /**
     * Update the specified resource in storage.
     */
    // En PolygonController.php - Método update() corregido
    /**
 * Update the specified resource in storage.
 */
    public function update(Request $request, Polygon $polygon): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'geometry' => 'required|string',
            'producer_id' => 'nullable|exists:producers,id',
            'parish_id' => 'nullable|exists:parishes,id',
            'area_ha' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'detected_parish' => 'nullable|string|max:255',
            'detected_municipality' => 'nullable|string|max:255',
            'detected_state' => 'nullable|string|max:255',
            'centroid_lat' => 'nullable|numeric|between:-90,90',
            'centroid_lng' => 'nullable|numeric|between:-180,180',
            'location_data' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            // ============================================
            // NUEVO: Procesar ubicación detectada en UPDATE
            // ============================================
            $parishId = $validated['parish_id'] ?? $polygon->parish_id;
            
            // Si hay nuevos datos detectados, procesarlos para obtener parish_id
            if (!empty($validated['detected_parish']) && 
                !empty($validated['detected_municipality']) && 
                !empty($validated['detected_state'])) {
                
                // CORREGIDO: Usar el método estático correcto
                $newParishId = LocationService::createOrUpdateLocation(
                    $validated['detected_parish'],
                    $validated['detected_municipality'],
                    $validated['detected_state']
                );
                
                if ($newParishId) {
                    $parishId = $newParishId;
                    \Log::info('Update: LocationService encontró parish_id: ' . $parishId);
                }
            }
            
            // Si tenemos location_data (JSON de OSM), también procesarlo
            if (!$parishId && !empty($validated['location_data'])) {
                $locationData = json_decode($validated['location_data'], true);
                if ($locationData && isset($locationData['address'])) {
                    // CORREGIDO: Usar el método estático correcto
                    $result = LocationService::processOSMData($locationData);
                    
                    if (!empty($result['parish_id'])) {
                        $parishId = $result['parish_id'];
                        \Log::info('Update: Procesado location_data OSM, parish_id: ' . $parishId);
                    }
                }
            }

            // Normalizar GeoJSON (código existente)
            $geojsonRaw = $validated['geometry'] ?? null;
            if (empty($geojsonRaw)) throw new \Exception('La geometría no puede estar vacía.');
            
            $decoded = json_decode($geojsonRaw, true);
            if ($decoded === null) throw new \Exception('GeoJSON inválido (no se pudo parsear).');
            
            $geometryObj = (isset($decoded['type']) && $decoded['type'] === 'Feature') 
                ? ($decoded['geometry'] ?? null) 
                : $decoded;
            
            if (empty($geometryObj) || empty($geometryObj['type']) || empty($geometryObj['coordinates'])) {
                throw new \Exception('GeoJSON geometry inválido o incompleto.');
            }
            
            if (!in_array($geometryObj['type'], ['Polygon','MultiPolygon'])) {
                throw new \Exception('Solo se permiten Polygon o MultiPolygon.');
            }
            
            $geoForDb = json_encode($geometryObj, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            // Preparar datos para actualización
            $updateData = [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'producer_id' => $validated['producer_id'] ?? null,
                'parish_id' => $parishId, // ← ¡USA EL parish_id PROCESADO!
                'is_active' => $validated['is_active'] ?? true,
                'centroid_lat' => $validated['centroid_lat'] ?? null,
                'centroid_lng' => $validated['centroid_lng'] ?? null,
                'location_data' => !empty($validated['location_data']) ? $validated['location_data'] : null,
                'updated_at' => now()
            ];

            // Si se proporcionó area_ha manualmente, usarla
            if (isset($validated['area_ha'])) {
                $updateData['area_ha'] = $validated['area_ha'];
            }

            // Actualizar la geometría usando consulta SQL directa
            DB::update(
                "UPDATE polygons SET 
                    name = ?, 
                    description = ?, 
                    producer_id = ?, 
                    parish_id = ?, 
                    area_ha = ?, 
                    is_active = ?, 
                    centroid_lat = ?, 
                    centroid_lng = ?, 
                    location_data = ?, 
                    geometry = ST_SetSRID(ST_GeomFromGeoJSON(?), 4326),
                    updated_at = ?
                WHERE id = ?",
                [
                    $updateData['name'],
                    $updateData['description'],
                    $updateData['producer_id'],
                    $updateData['parish_id'], // ← ¡PARISH_ID CORRECTO!
                    $updateData['area_ha'] ?? null,
                    $updateData['is_active'],
                    $updateData['centroid_lat'],
                    $updateData['centroid_lng'],
                    $updateData['location_data'],
                    $geoForDb,
                    $updateData['updated_at'],
                    $polygon->id
                ]
            );

            // Cálculo de área y centroide (código existente)
            if (!isset($validated['area_ha'])) {
                $res = DB::selectOne("
                    SELECT ST_Area(geometry::geography) / 10000 AS area_ha
                    FROM polygons
                    WHERE id = ?
                ", [$polygon->id]);

                if ($res && isset($res->area_ha)) {
                    DB::table('polygons')->where('id', $polygon->id)->update([
                        'area_ha' => round((float)$res->area_ha, 2),
                        'updated_at' => now()
                    ]);
                }
            }

            if (!$validated['centroid_lat'] || !$validated['centroid_lng']) {
                $centroidRes = DB::selectOne("
                    SELECT ST_AsGeoJSON(ST_Centroid(geometry)) AS centroid_geojson
                    FROM polygons
                    WHERE id = ?
                ", [$polygon->id]);

                if ($centroidRes && !empty($centroidRes->centroid_geojson)) {
                    $centroid = json_decode($centroidRes->centroid_geojson, true);
                    if (isset($centroid['coordinates'])) {
                        DB::table('polygons')->where('id', $polygon->id)->update([
                            'centroid_lng' => $centroid['coordinates'][0] ?? null,
                            'centroid_lat' => $centroid['coordinates'][1] ?? null,
                            'updated_at' => now()
                        ]);
                    }
                }
            }

            // Guardar datos detectados en el registro si existen
            if (!empty($validated['detected_parish']) || !empty($validated['detected_municipality']) || !empty($validated['detected_state'])) {
                $polygon->refresh();
                $currentLocationData = $polygon->location_data ?? [];
                
                if (is_string($currentLocationData)) {
                    $currentLocationData = json_decode($currentLocationData, true) ?? [];
                }
                
                $currentLocationData['detected_info'] = [
                    'detected_parish' => $validated['detected_parish'] ?? null,
                    'detected_municipality' => $validated['detected_municipality'] ?? null,
                    'detected_state' => $validated['detected_state'] ?? null,
                    'updated_at' => now()->toISOString(),
                    'updated_by' => auth()->id()
                ];
                
                DB::table('polygons')->where('id', $polygon->id)->update([
                    'location_data' => json_encode($currentLocationData, JSON_UNESCAPED_UNICODE),
                    'updated_at' => now()
                ]);
            }

            DB::commit();

            // Refrescar el modelo
            $polygon->refresh();

            \Log::info('Polígono actualizado exitosamente', [
                'id' => $polygon->id,
                'parish_id' => $polygon->parish_id,
                'has_location' => !is_null($polygon->parish_id),
                'location_full' => $polygon->parish ? 
                    "{$polygon->parish->name}, {$polygon->parish->municipality->name}, {$polygon->parish->municipality->state->name}" : 
                    null
            ]);

            return redirect()->route('polygons.index')
                ->with('success', 'Polígono actualizado exitosamente.')
                ->with('debug_info', [
                    'polygon_id' => $polygon->id,
                    'parish_id' => $polygon->parish_id,
                    'location' => $polygon->parish ? 
                        "{$polygon->parish->name}, {$polygon->parish->municipality->name}" : 
                        'Sin ubicación'
                ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar polígono: ' . $e->getMessage());
            
            return back()
                ->with('error', 'Error al actualizar el polígono: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Polygon $polygon): JsonResponse|RedirectResponse
    {
        try {
            $polygon->delete();
            
            // Respuesta para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Polígono eliminado exitosamente.',
                    'polygon_id' => $polygon->id
                ]);
            }
            
            // Respuesta tradicional
            return redirect()->route('polygons.index')
                ->with('success', 'Polígono eliminado exitosamente.');
                
        } catch (\Exception $e) {
            // Respuesta para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el polígono: ' . $e->getMessage()
                ], 500);
            }
            
            // Respuesta tradicional
            return back()->with('error', 'Error al eliminar el polígono: ' . $e->getMessage());
        }
    }

    /**
     * Restore the specified soft deleted resource.
     */
    public function restore(Request $request, $id): JsonResponse|RedirectResponse
    {
        try {
            $polygon = Polygon::withTrashed()->findOrFail($id);
            $polygon->restore();
            
            // Respuesta para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Polígono restaurado exitosamente.',
                    'polygon_id' => $polygon->id,
                    'is_active' => $polygon->is_active
                ]);
            }
            
            // Respuesta tradicional
            return redirect()->route('polygons.index')
                ->with('success', 'Polígono restaurado exitosamente.');
                
        } catch (\Exception $e) {
            // Respuesta para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al restaurar el polígono: ' . $e->getMessage()
                ], 500);
            }
            
            // Respuesta tradicional
            return back()->with('error', 'Error al restaurar el polígono: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the active status of the polygon.
     */
    public function toggleStatus(Request $request, Polygon $polygon): JsonResponse|RedirectResponse
    {
        try {
            $polygon->update(['is_active' => !$polygon->is_active]);
            
            $status = $polygon->is_active ? 'activado' : 'desactivado';
            
            // Respuesta para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Polígono {$status} exitosamente.",
                    'is_active' => $polygon->is_active,
                    'status_text' => $polygon->is_active ? 'Activo' : 'Inactivo',
                    'polygon_id' => $polygon->id
                ]);
            }
            
            // Respuesta tradicional
            return redirect()->route('polygons.index')
                ->with('success', "Polígono {$status} exitosamente.");
                
        } catch (\Exception $e) {
            // Respuesta para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cambiar el estado del polígono: ' . $e->getMessage()
                ], 500);
            }
            
            // Respuesta tradicional
            return back()->with('error', 'Error al cambiar el estado del polígono: ' . $e->getMessage());
        }
    }

    /**
     * Display the map view.
     */
    public function map(): View
    {
        return view('polygons.map');
    }

    /**
     * Get polygons as GeoJSON for the map.
     */
    public function geojson(): JsonResponse
    {
        $polygons = Polygon::with(['producer', 'parish.municipality.state'])
            ->active()
            ->get();

        $features = [];

        foreach ($polygons as $polygon) {
            try {
                $geojson = DB::selectOne("SELECT ST_AsGeoJSON(geometry) as geojson FROM polygons WHERE id = ?", [$polygon->id])->geojson ?? '{}';
                $geometry = json_decode($geojson, true);

                // NO invertir coordenadas: ST_AsGeoJSON ya devuelve [lng,lat] correcto para GeoJSON
                // Construir feature con la geometría tal cual
                $features[] = [
                    'type' => 'Feature',
                    'properties' => [
                        'id' => $polygon->id,
                        'name' => $polygon->name,
                        'producer' => $polygon->producer_name ?? null,
                        'area_ha' => $polygon->area_ha,
                        'description' => $polygon->description,
                        'type' => $polygon->type
                    ],
                    'geometry' => $geometry
                ];
            } catch (\Exception $e) {
                Log::error('Error al procesar polígono para GeoJSON:', [
                    'polygon_id' => $polygon->id,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }

        return response()->json(['type' => 'FeatureCollection', 'features' => $features]);
    }

    /**
     * API para buscar parroquias desde JavaScript
     */
    public function findParishApi(Request $request): JsonResponse
    {
        $parishName = $request->get('parish_name');
        $municipalityName = $request->get('municipality_name');
        $stateName = $request->get('state_name');
        
        $locationService = new LocationService();
        $parishId = $locationService->findOrCreateLocation($parishName, $municipalityName, $stateName);
        
        if ($parishId) {
            $parish = Parish::with(['municipality.state'])->find($parishId);
            
            return response()->json([
                'success' => true,
                'parish' => [
                    'id' => $parish->id,
                    'name' => $parish->name,
                    'municipality' => $parish->municipality->name,
                    'state' => $parish->municipality->state->name
                ],
                'message' => 'Parroquia encontrada/creada en la base de datos'
            ]);
        }
        
        $suggestions = $this->getLocationSuggestions($parishName, $municipalityName, $stateName);
        
        return response()->json([
            'success' => false,
            'parish' => null,
            'suggestions' => $suggestions,
            'message' => 'No se encontró parroquia coincidente en la base de datos'
        ]);
    }

    private function getLocationSuggestions($parishName, $municipalityName, $stateName)
    {
        $suggestions = [];
        
        // Buscar estados similares
        $states = State::where('name', 'like', "%{$stateName}%")
                    ->orWhereRaw('LOWER(name) = LOWER(?)', [$stateName])
                    ->limit(3)
                    ->get();
        
        foreach ($states as $state) {
            // Buscar municipios en ese estado
            $municipalities = Municipality::where('state_id', $state->id)
                ->where('name', 'like', "%{$municipalityName}%")
                ->orWhereRaw('LOWER(name) = LOWER(?)', [$municipalityName])
                ->limit(3)
                ->get();
                
            foreach ($municipalities as $municipality) {
                // Buscar parroquias en ese municipio
                $parishes = Parish::where('municipality_id', $municipality->id)
                    ->where('name', 'like', "%{$parishName}%")
                    ->orWhereRaw('LOWER(name) = LOWER(?)', [$parishName])
                    ->limit(3)
                    ->get();
                    
                foreach ($parishes as $parish) {
                    $suggestions[] = [
                        'id' => $parish->id,
                        'name' => $parish->name,
                        'municipality' => $municipality->name,
                        'state' => $state->name,
                        'full_name' => "{$parish->name}, {$municipality->name}, {$state->name}"
                    ];
                }
            }
        }
        
        return $suggestions;
    }

    /**
     * Convierte el JSON de coordenadas recibido desde la vista a WKT.
     * Espera un JSON como el producido por layer.toGeoJSON().geometry.coordinates
     * @param string $coordinatesJson
     * @return string WKT POLYGON
     * @throws \Exception
     */
    private function convertCoordinatesToWKT(string $coordinatesJson): string
    {
        $coords = json_decode($coordinatesJson, true);

        if (empty($coords) || !isset($coords[0])) {
            throw new \Exception('Coordenadas inválidas o vacías');
        }

        // Tomar primer anillo
        $ring = $coords[0];

        // Asegurar que cada punto tenga 2 elementos y cerrar el anillo
        $processed = [];
        foreach ($ring as $pt) {
            if (!is_array($pt) || count($pt) < 2) {
                throw new \Exception('Formato de punto inválido en coordenadas');
            }
            // Suponemos GeoJSON/Leaflet: [lng, lat]
            $processed[] = [ (float)$pt[0], (float)$pt[1] ];
        }

        if ($processed[0][0] !== end($processed)[0] || $processed[0][1] !== end($processed)[1]) {
            $processed[] = $processed[0];
        }

        $wktParts = array_map(fn($p) => "{$p[0]} {$p[1]}", $processed);
        return 'POLYGON((' . implode(', ', $wktParts) . '))';
    }

    private function getLocationConfirmationText(Polygon $polygon): string
    {
        if ($polygon->parish_id) {
            $polygon->load('parish.municipality.state');
            return "Ubicación asignada: {$polygon->parish->name}, {$polygon->parish->municipality->name}, {$polygon->parish->municipality->state->name}";
        } elseif ($polygon->detected_parish) {
            return "Ubicación detectada: {$polygon->detected_parish}, {$polygon->detected_municipality} (No se encontró coincidencia exacta en la base de datos)";
        }

        return "Ubicación no detectada";
    }

    /**
 * Get polygon details for modal view.
 */
    public function details(Polygon $polygon): JsonResponse
    {
        try {
            $polygon->load(['producer', 'parish.municipality.state']);
            
            return response()->json([
                'success' => true,
                'polygon' => [
                    'id' => $polygon->id,
                    'name' => $polygon->name,
                    'description' => $polygon->description,
                    'area_ha' => $polygon->area_ha,
                    'area_formatted' => $polygon->area_formatted,
                    'is_active' => $polygon->is_active,
                    'centroid_lat' => $polygon->centroid_lat,
                    'centroid_lng' => $polygon->centroid_lng,
                    'detected_parish' => $polygon->detected_parish,
                    'detected_municipality' => $polygon->detected_municipality,
                    'detected_state' => $polygon->detected_state,
                    'created_at' => $polygon->created_at,
                    'updated_at' => $polygon->updated_at,
                    'producer' => $polygon->producer ? [
                        'id' => $polygon->producer->id,
                        'name' => $polygon->producer->name . ' ' . $polygon->producer->lastname,
                        'email' => $polygon->producer->email,
                        'phone' => $polygon->producer->phone
                    ] : null,
                    'parish' => $polygon->parish ? [
                        'id' => $polygon->parish->id,
                        'name' => $polygon->parish->name,
                        'municipality' => $polygon->parish->municipality ? [
                            'name' => $polygon->parish->municipality->name,
                            'state' => $polygon->parish->municipality->state ? [
                                'name' => $polygon->parish->municipality->state->name
                            ] : null
                        ] : null
                    ] : null
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching polygon details:', [
                'polygon_id' => $polygon->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los detalles del polígono'
            ], 500);
        }
    }
}
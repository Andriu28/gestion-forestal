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
    public function index(Request $request): View
    {
        $search = $request->get('search');
        $status = $request->get('status', 'all');
        $type = $request->get('type', 'all');

        $query = Polygon::with(['producer', 'parish.municipality.state']);

        // Aplicar búsqueda
        if ($search) {
            $query->search($search);
        }

        // Aplicar filtro de estado
        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        } elseif ($status === 'deleted') {
            $query->onlyTrashed();
        }

        // Aplicar filtro de tipo
        if ($type === 'with_producer') {
            $query->withProducer();
        } elseif ($type === 'without_producer') {
            $query->withoutProducer();
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
            throw new \Exception('Se requieren al menos 4 puntos para un polígono válido');
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
    public function store(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'geometry' => 'required|string',
            'producer_id' => 'nullable|exists:producers,id',
            'parish_id' => 'nullable|exists:parishes,id',
            'area_ha' => 'nullable|numeric|min:0',
            'detected_parish' => 'nullable|string|max:255',
            'detected_municipality' => 'nullable|string|max:255',
            'detected_state' => 'nullable|string|max:255',
            'centroid_lat' => 'nullable|numeric|between:-90,90',
            'centroid_lng' => 'nullable|numeric|between:-180,180',
            'location_data' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            // Normalizar GeoJSON (acepta Feature o geometry)
            $geojsonRaw = $validated['geometry'] ?? null;
            if (empty($geojsonRaw)) throw new \Exception('La geometría no puede estar vacía.');
            $decoded = json_decode($geojsonRaw, true);
            if ($decoded === null) throw new \Exception('GeoJSON inválido (no se pudo parsear).');
            $geometryObj = (isset($decoded['type']) && $decoded['type'] === 'Feature') ? ($decoded['geometry'] ?? null) : $decoded;
            if (empty($geometryObj) || empty($geometryObj['type']) || empty($geometryObj['coordinates'])) {
                throw new \Exception('GeoJSON geometry inválido o incompleto.');
            }
            if (!in_array($geometryObj['type'], ['Polygon','MultiPolygon'])) {
                throw new \Exception('Solo se permiten Polygon o MultiPolygon.');
            }
            $geoForDb = json_encode($geometryObj, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            // DEBUG: registrar inicio
            \Log::debug('DEBUG Polygon.store - inserting with geometry', ['name' => $validated['name'], 'geo_len' => strlen($geoForDb)]);

            // Insertar todo en una sola sentencia (evita NOT NULL violation)
            $now = now();
            $locationData = !empty($validated['location_data']) ? $validated['location_data'] : null;

            $row = DB::selectOne(
                "INSERT INTO polygons
                    (name, description, producer_id, parish_id, area_ha, is_active, detected_parish, detected_municipality, detected_state, centroid_lat, centroid_lng, location_data, geometry, created_at, updated_at)
                 VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ST_SetSRID(ST_GeomFromGeoJSON(?), 4326), ?, ?)
                 RETURNING id",
                [
                    $validated['name'],
                    $validated['description'] ?? null,
                    $validated['producer_id'] ?? null,
                    $validated['parish_id'] ?? null,
                    $validated['area_ha'] ?? null,
                    true,
                    $validated['detected_parish'] ?? null,
                    $validated['detected_municipality'] ?? null,
                    $validated['detected_state'] ?? null,
                    $validated['centroid_lat'] ?? null,
                    $validated['centroid_lng'] ?? null,
                    $locationData,
                    $geoForDb,
                    $now,
                    $now
                ]
            );

            if (!isset($row->id)) {
                throw new \Exception('No se pudo insertar polígono (no se obtuvo id).');
            }

            $polygon = \App\Models\Polygon::find($row->id);

            // Calcular área y centroide con PostGIS y actualizar columnas calculadas
            $res = DB::selectOne("
                SELECT 
                  ST_Area(geometry::geography) / 10000 AS area_ha,
                  ST_AsGeoJSON(ST_Centroid(geometry)) AS centroid_geojson
                FROM polygons
                WHERE id = ?
            ", [$polygon->id]);

            if ($res) {
                $polygon->area_ha = isset($res->area_ha) ? round((float)$res->area_ha, 2) : $polygon->area_ha;
                if (!empty($res->centroid_geojson)) {
                    $cj = json_decode($res->centroid_geojson, true);
                    if (!empty($cj['coordinates'])) {
                        $polygon->centroid_lat = $cj['coordinates'][1];
                        $polygon->centroid_lng = $cj['coordinates'][0];
                    }
                }
                $polygon->save();
            }

            DB::commit();

            \Log::info('DEBUG Polygon.store - success insert id=' . $polygon->id);

            return redirect()->route('polygons.index')->with('success', 'Polígono creado exitosamente.')
                ->with('debug_info', ['polygon_id' => $polygon->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('DEBUG Polygon.store - exception', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', 'Error al crear polígono: ' . $e->getMessage())
                ->with('debug_error', substr($e->getMessage(), 0, 1000));
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
    public function update(Request $request, Polygon $polygon): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'producer_id' => 'nullable|exists:producers,id',
            'parish_id' => 'nullable|exists:parishes,id',
            'area_ha' => 'nullable|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        try {
            $polygon->update($validated);
            
            return redirect()->route('polygons.index')
                ->with('success', 'Polígono actualizado exitosamente.');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar el polígono: ' . $e->getMessage())
                        ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Polygon $polygon): RedirectResponse
    {
        try {
            $polygon->delete();
            
            return redirect()->route('polygons.index')
                ->with('success', 'Polígono eliminado exitosamente.');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el polígono: ' . $e->getMessage());
        }
    }

    /**
     * Restore the specified soft deleted resource.
     */
    public function restore($id): RedirectResponse
    {
        try {
            $polygon = Polygon::withTrashed()->findOrFail($id);
            $polygon->restore();
            
            return redirect()->route('polygons.index')
                ->with('success', 'Polígono restaurado exitosamente.');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Error al restaurar el polígono: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the active status of the polygon.
     */
    public function toggleStatus(Polygon $polygon): RedirectResponse
    {
        try {
            $polygon->update(['is_active' => !$polygon->is_active]);
            
            $status = $polygon->is_active ? 'activado' : 'desactivado';
            
            return redirect()->route('polygons.index')
                ->with('success', "Polígono {$status} exitosamente.");
                
        } catch (\Exception $e) {
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
                
                // PostGIS devuelve [lng, lat], pero Leaflet necesita [lat, lng]
                if (isset($geometry['coordinates'][0])) {
                    // Convertir cada punto
                    $convertedCoords = [];
                    foreach ($geometry['coordinates'][0] as $point) {
                        $convertedCoords[] = [$point[1], $point[0]]; // Invertir: [lat, lng]
                    }
                    $geometry['coordinates'] = [$convertedCoords];
                }
                
                $features[] = [
                    'type' => 'Feature',
                    'properties' => [
                        'id' => $polygon->id,
                        'name' => $polygon->name,
                        'producer' => $polygon->producer_name,
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
}
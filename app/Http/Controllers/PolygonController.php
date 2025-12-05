<?php
// [file name]: app/Http/Controllers/PolygonController.php

namespace App\Http\Controllers;

use App\Models\Polygon;
use App\Models\Producer;
use App\Models\Parish;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

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
    public function store(Request $request): RedirectResponse
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
            'centroid_lat' => 'nullable|numeric',
            'centroid_lng' => 'nullable|numeric',
            'location_data' => 'nullable|json'
        ]);

        try {
            DB::beginTransaction();

            // Convertir coordenadas a formato WKT
            $geometry = $this->coordinatesToWKT($validated['geometry']);
            
            // BUSCAR Y ASIGNAR PARROQUIA AUTOMÁTICAMENTE
            $parishId = $validated['parish_id'];
            
            // Si no se seleccionó parroquia manualmente, buscar automáticamente
            if (!$parishId && $validated['detected_parish'] && $validated['detected_municipality'] && $validated['detected_state']) {
                $parishId = $this->findAndAssignParish(
                    $validated['detected_parish'],
                    $validated['detected_municipality'], 
                    $validated['detected_state']
                );
            }

            $polygon = Polygon::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'geometry' => DB::raw("ST_GeomFromText('$geometry')"),
                'producer_id' => $validated['producer_id'],
                'parish_id' => $parishId,
                'area_ha' => $validated['area_ha'] ?? $this->calculateAreaFromCoordinates($validated['geometry']),
                'is_active' => true,
                'detected_parish' => $validated['detected_parish'],
                'detected_municipality' => $validated['detected_municipality'],
                'detected_state' => $validated['detected_state'],
                'centroid_lat' => $validated['centroid_lat'],
                'centroid_lng' => $validated['centroid_lng'],
                'location_data' => $validated['location_data']
            ]);

            DB::commit();

            $locationText = $this->getLocationConfirmationText($polygon);

            return redirect()->route('polygons.index')
                ->with('success', "Polígono creado exitosamente. {$locationText}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear el polígono: ' . $e->getMessage())
                        ->withInput();
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
            $wkt = DB::select("SELECT ST_AsText(geometry) as wkt FROM polygons WHERE id = ?", [$polygon->id])[0]->wkt;
            
            // Convertir WKT a GeoJSON coordinates
            $coordinates = $this->wktToCoordinates($wkt);
            
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
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => $coordinates
                ]
            ];
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features
        ]);
    }

    /**
     * API para buscar parroquias desde JavaScript
     */
    public function findParishApi(Request $request): JsonResponse
    {
        $parishName = $request->get('parish_name');
        $municipalityName = $request->get('municipality_name');
        $stateName = $request->get('state_name');
        
        $parish = Polygon::findParishByName($parishName, $municipalityName, $stateName);
        
        if ($parish) {
            return response()->json([
                'success' => true,
                'parish' => [
                    'id' => $parish->id,
                    'name' => $parish->name,
                    'municipality' => $parish->municipality->name,
                    'state' => $parish->municipality->state->name
                ],
                'message' => 'Parroquia encontrada en la base de datos'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'parish' => null,
            'message' => 'No se encontró parroquia coincidente en la base de datos'
        ]);
    }

    /**
     * Métodos auxiliares
     */

    /**
     * Buscar parroquia en la base de datos y devolver el ID
     */
    private function findAndAssignParish($parishName, $municipalityName, $stateName): ?int
    {
        $parish = Polygon::findParishByName($parishName, $municipalityName, $stateName);
        return $parish ? $parish->id : null;
    }

    /**
     * Genera el texto de confirmación de ubicación
     */
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
     * Convert coordinates to WKT format
     */
    private function coordinatesToWKT(string $coordinatesJson): string
    {
        $coordinates = json_decode($coordinatesJson, true);
        
        if (!isset($coordinates[0])) {
            throw new \Exception('Formato de coordenadas inválido');
        }

        $wktCoordinates = [];
        foreach ($coordinates[0] as $coord) {
            $wktCoordinates[] = "{$coord[0]} {$coord[1]}";
        }

        // Cerrar el polígono (primera y última coordenada iguales)
        if ($wktCoordinates[0] !== end($wktCoordinates)) {
            $wktCoordinates[] = $wktCoordinates[0];
        }

        return 'POLYGON((' . implode(', ', $wktCoordinates) . '))';
    }

    /**
     * Convert WKT to coordinates array
     */
    private function wktToCoordinates(string $wkt): array
    {
        // Extraer coordenadas del WKT
        preg_match('/POLYGON\(\((.*?)\)\)/', $wkt, $matches);
        
        if (!isset($matches[1])) {
            return [];
        }

        $coords = [];
        $points = explode(',', $matches[1]);
        
        foreach ($points as $point) {
            $point = trim($point);
            list($lng, $lat) = explode(' ', $point);
            $coords[] = [(float)$lng, (float)$lat];
        }

        return [$coords];
    }

    /**
     * Calculate area from coordinates (simplified)
     */
    private function calculateAreaFromCoordinates(string $coordinatesJson): float
    {
        $coordinates = json_decode($coordinatesJson, true);
        
        if (!isset($coordinates[0])) {
            return 0.0;
        }

        // Cálculo simplificado del área (en un sistema real usarías una fórmula más precisa)
        $area = 0.0;
        $points = $coordinates[0];
        $n = count($points);
        
        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $area += $points[$i][0] * $points[$j][1];
            $area -= $points[$j][0] * $points[$i][1];
        }
        
        $area = abs($area) / 2.0;
        
        // Convertir a hectáreas (aproximación)
        return $area * 100; // Ajusta este factor según tu sistema de coordenadas
    }
}
<?php
// [file name]: app/Http/Controllers/Api/LocationApiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Controlador de Endpoints API para Geocodificación y Ubicaciones
 * 
 * Expone servicios REST consumidos por los visores cartográficos (Leaflet / OpenLayers):
 * 1. `findOrCreate`: Homologa y registra en base de datos la parroquia, municipio y estado enviados desde el cliente.
 * 2. `processOSM`: Procesa objetos de respuesta crudos de OpenStreetMap Nominatim extrayendo la jerarquía territorial.
 * 3. `search`: Motor de autocompletado rápido (Autocomplete) que busca simultáneamente en parroquias, municipios y estados.
 */
class LocationApiController extends Controller
{
    /**
     * Busca o crea la jerarquía territorial completa y retorna los datos estructurados en formato JSON.
     * Invocado cuando el usuario hace clic en el mapa y se detecta una nueva coordenada.
     */
    public function findOrCreate(Request $request): JsonResponse
    {
        $request->validate([
            'parish_name'       => 'required|string|max:255',
            'municipality_name' => 'required|string|max:255',
            'state_name'        => 'required|string|max:255',
        ]);
        
        try {
            $locationService = new LocationService();
            $parishId = $locationService->findOrCreateLocation(
                $request->parish_name,
                $request->municipality_name,
                $request->state_name
            );
            
            if ($parishId) {
                // Cargar relaciones anidadas para devolver la jerarquía formal completa
                $parish = \App\Models\Parish::with(['municipality.state'])->find($parishId);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Ubicación encontrada/creada exitosamente',
                    'data'    => [
                        'parish_id'         => $parishId,
                        'parish_name'       => $parish->name,
                        'municipality_name' => $parish->municipality->name,
                        'state_name'        => $parish->municipality->state->name,
                        'is_new'            => false,
                    ]
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No se pudo procesar la ubicación'
            ], 400);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en el servidor: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Analiza el objeto JSON crudo proveniente de la API de OpenStreetMap Nominatim:
     * Extrae las etiquetas cartográficas, limpia nombres y devuelve la parroquia resuelta.
     */
    public function processOSM(Request $request): JsonResponse
    {
        $request->validate([
            'osm_data' => 'required|array'
        ]);
        
        try {
            $locationService = new LocationService();
            $result = $locationService->processOSMData($request->osm_data);
            
            return response()->json([
                'success' => true,
                'message' => 'Datos OSM procesados',
                'data'    => $result
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error procesando datos OSM: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Endpoint para cajas de texto con autocompletado en vivo (Select2 / Typeahead):
     * - Se activa con un mínimo de 2 caracteres (`strlen($search) >= 2`).
     * - Consulta en paralelo hasta 10 parroquias, 5 municipios y 5 estados.
     * - Devuelve una colección unificada con identificador y etiqueta compuesta ('Texto - Municipio - Estado').
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->get('q', '');
        
        $results = [];
        
        if (strlen($search) >= 2) {
            // 1. Coincidencias en Parroquias
            $parishes = \App\Models\Parish::with(['municipality.state'])
                ->where('name', 'like', "%{$search}%")
                ->limit(10)
                ->get()
                ->map(function($parish) {
                    return [
                        'id'   => $parish->id,
                        'text' => "{$parish->name} - {$parish->municipality->name} - {$parish->municipality->state->name}",
                        'type' => 'parish'
                    ];
                });
            
            // 2. Coincidencias en Municipios
            $municipalities = \App\Models\Municipality::with(['state'])
                ->where('name', 'like', "%{$search}%")
                ->limit(5)
                ->get()
                ->map(function($municipality) {
                    return [
                        'id'   => $municipality->id,
                        'text' => "{$municipality->name} - {$municipality->state->name}",
                        'type' => 'municipality'
                    ];
                });
            
            // 3. Coincidencias en Estados
            $states = \App\Models\State::where('name', 'like', "%{$search}%")
                ->limit(5)
                ->get()
                ->map(function($state) {
                    return [
                        'id'   => $state->id,
                        'text' => $state->name,
                        'type' => 'state'
                    ];
                });
            
            // Fusión jerárquica de resultados
            $results = $parishes->merge($municipalities)->merge($states);
        }
        
        return response()->json($results);
    }
}
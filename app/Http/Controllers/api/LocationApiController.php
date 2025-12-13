<?php
// [file name]: app/Http/Controllers/Api/LocationApiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LocationApiController extends Controller
{
    /**
     * API para buscar/crear ubicación desde frontend
     */
    public function findOrCreate(Request $request): JsonResponse
    {
        $request->validate([
            'parish_name' => 'required|string|max:255',
            'municipality_name' => 'required|string|max:255',
            'state_name' => 'required|string|max:255',
        ]);
        
        try {
            $locationService = new LocationService();
            $parishId = $locationService->findOrCreateLocation(
                $request->parish_name,
                $request->municipality_name,
                $request->state_name
            );
            
            if ($parishId) {
                // Cargar información completa para mostrar al usuario
                $parish = \App\Models\Parish::with(['municipality.state'])->find($parishId);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Ubicación encontrada/creada exitosamente',
                    'data' => [
                        'parish_id' => $parishId,
                        'parish_name' => $parish->name,
                        'municipality_name' => $parish->municipality->name,
                        'state_name' => $parish->municipality->state->name,
                        'is_new' => false, // Podrías agregar lógica para detectar si fue creado nuevo
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
     * API para procesar datos crudos de OpenStreetMap
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
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error procesando datos OSM: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API para buscar ubicaciones existentes (autocomplete)
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->get('q', '');
        
        $results = [];
        
        if (strlen($search) >= 2) {
            // Buscar parroquias
            $parishes = \App\Models\Parish::with(['municipality.state'])
                ->where('name', 'like', "%{$search}%")
                ->limit(10)
                ->get()
                ->map(function($parish) {
                    return [
                        'id' => $parish->id,
                        'text' => "{$parish->name} - {$parish->municipality->name} - {$parish->municipality->state->name}",
                        'type' => 'parish'
                    ];
                });
            
            // Buscar municipios
            $municipalities = \App\Models\Municipality::with(['state'])
                ->where('name', 'like', "%{$search}%")
                ->limit(5)
                ->get()
                ->map(function($municipality) {
                    return [
                        'id' => $municipality->id,
                        'text' => "{$municipality->name} - {$municipality->state->name}",
                        'type' => 'municipality'
                    ];
                });
            
            // Buscar estados
            $states = \App\Models\State::where('name', 'like', "%{$search}%")
                ->limit(5)
                ->get()
                ->map(function($state) {
                    return [
                        'id' => $state->id,
                        'text' => $state->name,
                        'type' => 'state'
                    ];
                });
            
            $results = $parishes->merge($municipalities)->merge($states);
        }
        
        return response()->json($results);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Polygon;
use App\Services\DeforestationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class DeforestationController extends Controller
{
    protected $deforestationService;
    protected $gfwService;
    
    public function __construct(DeforestationService $deforestationService)
    {
        $this->deforestationService = $deforestationService;
    }

    /**
     * Muestra el formulario para crear nuevo análisis
     */
    public function create(): View
    {
        return view('deforestation.create');
    }
    
   /**
 * Procesa el análisis de deforestación
 */
public function analyze(Request $request)
{
    // 1. Obtener los datos del Request y asignarlos a variables
    $year = (int) $request->input('end_year'); 
    $geometryString = $request->input('geometry');
    $areaHa = (float) $request->input('area_ha');

    // 2. Decodificación y Estructuración del GeoJSON
    try {
        $geometryGeoJson = json_decode($geometryString, true); 
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['geometry' => 'Formato GeoJSON inválido']);
        }

    } catch (\Exception $e) {
        return back()->withErrors(['error' => 'Error procesando la geometría: ' . $e->getMessage()]);
    }

    // Preparar datos para la vista
    $dataToPass = [
        'analysis_year' => $year,
        'original_geojson' => $geometryString,
        'type' => $geometryGeoJson['type'],
        'geometry' => $geometryGeoJson['coordinates'][0],
        'polygon_area_ha' => $areaHa,
        'status' => $mainStats['status'] ?? 'error',
        'polygon_name' => $request->input('name', 'Área de Estudio'),
        'description' => $request->input('description', '')
    ];

    // Log para debugging
    Log::info('Datos enviados a la vista:', [
        'main_stats_status' => $mainStats['status'] ?? 'unknown'
    ]);

    return view('deforestation.results', compact('dataToPass'));

} /*################## fin de la funcion analyze #################*/

/**
 * Realiza consultas paralelas para múltiples años usando Guzzle
 */
private function getParallelYearlyStats($geometryGeoJson, $years)
{
    $results = [];
    $client = new Client([
        'timeout' => 30,
        'connect_timeout' => 10,
    ]);
    
    $promises = [];
    
    foreach ($years as $year) {
        // Crear promise para cada año usando el mismo formato que GFWService
        $promises[$year] = $this->createGFWRequestPromise($client, $geometryGeoJson, (int)$year);
    }
    
    try {
        // Esperar a que todas las promesas se resuelvan
        $responses = Promise\Utils::settle($promises)->wait();
        
        foreach ($responses as $year => $response) {
            if ($response['state'] === 'fulfilled') {
                $data = json_decode($response['value']->getBody(), true);
                
                Log::info("Respuesta GFW para año $year:", [
                    'status' => $data['status'] ?? 'unknown',
                    'area_ha' => $data['data'][0]['area__ha'] ?? 0,
                    'data_structure' => array_keys($data)
                ]);
                
                $results[$year] = [
                    'area__ha' => $data['data'][0]['area__ha'] ?? 0,
                    'status' => $data['status'] ?? 'error',
                    'year' => (int)$year
                ];
            } else {
                // En caso de error, registrar 0
                $errorMessage = $response['reason']->getMessage();
                Log::error("Error en consulta GFW para año $year: " . $errorMessage);
                
                $results[$year] = [
                    'area__ha' => 0,
                    'status' => 'error',
                    'year' => (int)$year,
                    'error' => $errorMessage
                ];
            }
        }
    } catch (\Exception $e) {
        // En caso de error general, llenar con valores por defecto
        Log::error("Error general en consultas paralelas: " . $e->getMessage());
        foreach ($years as $year) {
            $results[$year] = [
                'area__ha' => 0,
                'status' => 'error',
                'year' => (int)$year,
                'error' => 'Error general en consulta paralela: ' . $e->getMessage()
            ];
        }
    }
    
    return $results;
}
    
/**
 * Crea una promise para consulta GFW con el mismo formato que GFWService
 */
private function createGFWRequestPromise(Client $client, $geometryGeoJson, $year)
{
    // Usar el mismo endpoint y formato que tu GFWService
    $url = env('GFW_API_BASE_URI') . '/dataset/umd_tree_cover_loss/latest/query';
    
    $sql = sprintf(
        "SELECT SUM(area__ha) FROM results WHERE umd_tree_cover_loss__year=%d",
        $year
    );

    $payload = [
        'geometry' => $geometryGeoJson,
        'sql' => $sql
    ];

    Log::info("Enviando consulta GFW para año $year:", [
        'url' => $url,
        'sql' => $sql,
        'geometry_type' => $geometryGeoJson['type'] ?? 'unknown',
        'coordinates_count' => count($geometryGeoJson['coordinates'][0] ?? [])
    ]);

    return $client->postAsync($url, [
        'json' => $payload,
        'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'x-api-key' => env('GFW_API_KEY'),
            'User-Agent' => 'DeforestationAnalysisApp/1.0'
        ],
        'timeout' => 30,
        'connect_timeout' => 10,
    ]);
}

/**
 * Método temporal para debugging de la estructura GeoJSON
 */
private function debugGeoJsonStructure($geometryGeoJson, $completeGeoJson)
{
    Log::debug('Estructura GeoJSON:', [
        'geometry_input' => [
            'type' => $geometryGeoJson['type'] ?? 'missing',
            'coordinates_count' => count($geometryGeoJson['coordinates'][0] ?? []),
            'is_closed' => $this->isPolygonClosed($geometryGeoJson['coordinates'][0] ?? [])
        ],
        'complete_geojson' => [
            'type' => $completeGeoJson['type'] ?? 'missing',
            'has_geometry' => isset($completeGeoJson['geometry']),
            'geometry_type' => $completeGeoJson['geometry']['type'] ?? 'missing'
        ]
    ]);
}

/**
 * Verifica si el polígono está cerrado (primera y última coordenada iguales)
 */
private function isPolygonClosed($coordinates)
{
    if (empty($coordinates) || count($coordinates) < 4) {
        return false;
    }
    
    $first = $coordinates[0];
    $last = $coordinates[count($coordinates) - 1];
    
    return $first[0] === $last[0] && $first[1] === $last[1];
}
        
    public function multipleResults(Request $request): View
    {
        $polygonIds = explode(',', $request->input('polygon_ids', ''));
        $polygons = Polygon::with('analyses')->whereIn('id', $polygonIds)->get();
        
        return view('deforestation.multiple-results', compact('polygons'));
    }
    
    /**
     * Muestra los resultados del análisis para un solo polígono
     */
    public function results($polygonId): View
    {
        $polygon = Polygon::with('analyses')->findOrFail($polygonId);
        $analyses = $polygon->analyses->sortBy('year');
        
        return view('deforestation.results', compact('polygon', 'analyses'));
    }
    
    /**
     * Obtiene el historial de análisis en formato JSON para gráficos
     */
    public function getAnalysisData($polygonId): JsonResponse
    {
        $polygon = Polygon::findOrFail($polygonId);
        $history = $this->deforestationService->getAnalysisHistory($polygon);
        
        return response()->json($history);
    }
    
    /**
     * Exporta los datos a GeoJSON (placeholder)
     */
    public function export($polygonId)
    {
        // Implementar lógica de exportación
        return response()->json(['message' => 'Export functionality to be implemented']);
    }
    
    /**
     * Genera reporte PDF (placeholder)
     */
    public function report($polygonId)
    {
        // Implementar lógica de reporte PDF
        return response()->json(['message' => 'PDF report functionality to be implemented']);
    }
}
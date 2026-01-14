<?php

namespace App\Http\Controllers;

use App\Models\Polygon;
use App\Services\DeforestationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\GFWService;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class DeforestationController extends Controller
{
    protected $deforestationService;
    protected $gfwService;
    
    public function __construct(DeforestationService $deforestationService, GFWService $gfwService)
    {
        $this->deforestationService = $deforestationService;
        $this->gfwService = $gfwService;
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
        $startYear = (int) $request->input('start_year');
        $endYear = (int) $request->input('end_year');
        $geometryString = $request->input('geometry');
        $areaHa = (float) $request->input('area_ha');
        $polygonName = $request->input('name', 'Área de Estudio'); // Obtener nombre aquí para consistencia

        // Validar que el rango de años sea válido
        if ($startYear > $endYear) {
            return back()->withErrors(['error' => 'El año de inicio no puede ser mayor al año de fin.']);
        }

        // 2. Decodificación y Estructuración del GeoJSON
        try {
            $geometryGeoJson = json_decode($geometryString, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['geometry' => 'Formato GeoJSON inválido']);
            }

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error procesando la geometría: ' . $e->getMessage()]);
        }

        // 3. CONSULTAS PARALELAS PARA EL RANGO DE AÑOS ESPECIFICADO
        $yearsToAnalyze = range($startYear, $endYear);

        // Realizar consultas paralelas para TODOS los años en el rango, incluyendo $endYear
        $yearlyResults = $this->getParallelYearlyStats($geometryGeoJson, $yearsToAnalyze);

        // Obtener las estadísticas del año final del array de resultados paralelos
        $mainStatsForEndYear = $yearlyResults[$endYear] ?? ['area__ha' => 0, 'status' => 'error', 'year' => $endYear];
        $mainStatsAreaHa = $mainStatsForEndYear['area__ha'] ?? 0;
        $mainStatsStatus = $mainStatsForEndYear['status'] ?? 'error';
        
        // Ordenar por año
        ksort($yearlyResults);

        // 4. EJECUTAR CÁLCULO DE PÉRDIDA TOTAL ACUMULADA
        $totalLossResults = $this->calculateTotalLossStats($yearlyResults, $areaHa, $startYear, $endYear);

        // 5. Preparar datos para la vista
        $dataToPass = [
            'analysis_year' => $endYear, // Sigue siendo el punto focal del análisis
            'start_year' => $startYear,
            'end_year' => $endYear,
            'original_geojson' => $geometryString,
            'type' => $geometryGeoJson['type'],
            'geometry' => $geometryGeoJson['coordinates'][0],
            'area__ha' => $mainStatsAreaHa, // Usamos el dato del array paralelo
            'polygon_area_ha' => $areaHa < $totalLossResults['totalDeforestedArea'] ? $totalLossResults['totalDeforestedArea'] : $areaHa,
            'status' => $mainStatsStatus, // Usamos el status del array paralelo
            'polygon_name' => $polygonName,
            'description' => $request->input('description', ''),
            'yearly_results' => $yearlyResults,
            'total_loss' => $totalLossResults, // Añadir el resultado del cálculo
        ];


        // Log para debugging
        Log::info('Datos enviados a la vista:', [
            'start_year' => $startYear,
            'end_year' => $endYear,
            'years_analyzed' => $yearsToAnalyze,
            'yearly_results_count' => count($yearlyResults),
            'total_deforested_area' => $totalLossResults['totalDeforestedArea'],
            'main_stats_status' => $mainStatsStatus
        ]);

        $result = DB::insert(
            "INSERT INTO polygons 
                (name, description, geometry, area_ha, created_at, updated_at)
             VALUES 
                (?, ?, ?, ?, ?, ?)",
            [
                $dataToPass['polygon_name'],
                $dataToPass['description'],
                $dataToPass['original_geojson'],
                $dataToPass['polygon_area_ha'],
                now(),
                now(),
            ]
        );
        
       return view('deforestation.results', compact('dataToPass'));
    }

    

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
     * Calcula la pérdida total acumulada y el porcentaje de deforestación.
     */
    private function calculateTotalLossStats(array $yearlyResults, float $areaHa, int $startYear, int $endYear): array
    {
        $totalDeforestedArea = 0;
        $validYears = 0;

        foreach ($yearlyResults as $yearData) {
            // Se utiliza 'fulfilled' para verificar que la promesa de Guzzle fue exitosa.
            if (isset($yearData['area__ha']) && $yearData['status'] === 'success') {
                $totalDeforestedArea += $yearData['area__ha'];
                $validYears++;
            }
        }

        $areaHa = $areaHa < $totalDeforestedArea ? $totalDeforestedArea : $areaHa;
        $totalPercentage = $areaHa > 0 ? ($totalDeforestedArea / $areaHa) * 100 : 0;
        $totalYearsInRange = $endYear - $startYear + 1;


        return [
            'totalDeforestedArea' => $totalDeforestedArea,
            'totalPercentage' => $totalPercentage,
            'validYears' => $validYears,
            'totalYearsInRange' => $totalYearsInRange,
        ];
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
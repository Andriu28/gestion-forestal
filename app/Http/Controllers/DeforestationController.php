<?php

namespace App\Http\Controllers;

use App\Models\Polygon;
use App\Services\DeforestationService;
use App\Models\Deforestation;
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
        // Obtener la preferencia guardada de la sesión (si existe)
        $oldSaveAnalysis = old('save_analysis');
    
        if ($oldSaveAnalysis !== null) {
            // Si hay un valor old (de un envío previo con error)
            $saveByDefault = ($oldSaveAnalysis === '1' || $oldSaveAnalysis === true || $oldSaveAnalysis === 1);
        } else {
            // Si no hay valor old, usar la sesión o el valor por defecto
            $saveByDefault = session('save_analysis_by_default', false);
        }
        
        return view('deforestation.create', [
            'saveByDefault' => $saveByDefault
        ]);
    }
    
   /**
     * Procesa el análisis de deforestación
     */
    public function analyze(Request $request)
    {
        // Obtener datos
        $saveAnalysis = $request->boolean('save_analysis');
        
        // Validar que si se guarda, el nombre sea obligatorio
        if ($saveAnalysis) {
            $request->validate([
                'name' => 'required|string|min:3|max:255',
                'start_year' => 'required|integer|min:2001|max:2024',
                'end_year' => 'required|integer|min:2001|max:2024|gte:start_year',
                'geometry' => 'required|string',
                'area_ha' => 'required|numeric|min:0.01',
                'description' => 'nullable|string|max:1000',
                'save_analysis' => 'boolean'
            ], [
                'name.required' => 'El nombre del área es obligatorio cuando se guarda el análisis.',
                'name.min' => 'El nombre debe tener al menos 3 caracteres.',
                'geometry.required' => 'Debe dibujar un polígono en el mapa.',
                'area_ha.min' => 'El área debe ser mayor a 0 hectáreas.',
                'end_year.gte' => 'El año de fin debe ser mayor o igual al año de inicio.'
            ]);
        } else {
            $request->validate([
                'name' => 'nullable|string|max:255',
                'start_year' => 'required|integer|min:2001|max:2024',
                'end_year' => 'required|integer|min:2001|max:2024|gte:start_year',
                'geometry' => 'required|string',
                'area_ha' => 'required|numeric|min:0.01',
                'description' => 'nullable|string|max:1000',
                'save_analysis' => 'boolean'
            ]);
        }

        // 1. Obtener los datos del Request y asignarlos a variables
        $startYear = (int) $request->input('start_year');
        $endYear = (int) $request->input('end_year');
        $geometryString = $request->input('geometry');
        $areaHa = (float) $request->input('area_ha');
        $polygonName = $request->input('name', 'Área de Estudio');
        $description = $request->input('description', '');

         session(['save_analysis_by_default' => $saveAnalysis]);

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
            'description' => $description,
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
            'main_stats_status' => $mainStatsStatus,
            'save_analysis' => $saveAnalysis
        ]);

        // 6. GUARDAR EN BASE DE DATOS SOLO SI EL USUARIO LO PERMITE
        // En el método analyze(), dentro de la condición if ($saveAnalysis):
if ($saveAnalysis) {
    try {
        // Declarar la variable fuera de la transacción
        $newPolygonId = null;
        
        DB::transaction(function () use ($dataToPass, &$newPolygonId) { // Usar & para pasar por referencia
            
            // 1. Insertar Polígono
            $polygonRow = DB::selectOne(
                "INSERT INTO polygons 
                    (name, description, geometry, area_ha, created_at, updated_at)
                VALUES 
                    (?, ?, ST_SetSRID(ST_GeomFromGeoJSON(?), 4326), ?, ?, ?)
                RETURNING id",
                [
                    $dataToPass['polygon_name'],
                    $dataToPass['description'],
                    $dataToPass['original_geojson'],
                    $dataToPass['polygon_area_ha'],
                    now(),
                    now(),
                ]
            );

            $newPolygonId = $polygonRow->id;

            // 2. Insertar análisis de deforestación
            foreach ($dataToPass['total_loss']['yearlyBreakdown'] as $yearData) {
                DB::insert(
                    "INSERT INTO deforestation 
                        (polygon_id, year, deforested_area_ha, percentage_loss, created_at, updated_at)
                    VALUES 
                        (?, ?, ?, ?, ?, ?)",
                    [
                        $newPolygonId,
                        $yearData['year'],
                        $yearData['area_ha'],
                        $yearData['percentage'],
                        now(),
                        now(),
                    ]
                );
            }

            Log::info("Análisis guardado. Polígono ID: {$newPolygonId}");
        });

        // IMPORTANTE: Verificar que $newPolygonId fue asignado
        if ($newPolygonId) {
            // Guardar el ID en $dataToPass
            $dataToPass['polygon_id'] = $newPolygonId;
            
            // Guardar también en sesión para uso posterior
            session(['last_polygon_id' => $newPolygonId]);
            
            session()->flash('save_success', 'Análisis guardado exitosamente.');
            
            Log::info("Polígono ID asignado a dataToPass: {$newPolygonId}");
        } else {
            throw new \Exception("No se pudo obtener el ID del polígono insertado");
        }

    } catch (\Exception $e) {
        Log::error('Error al guardar en base de datos: ' . $e->getMessage());
        Log::error('Trace completo: ' . $e->getTraceAsString());
        
        $dataToPass['save_error'] = 'No se pudo guardar el análisis: ' . $e->getMessage();
    }
} else {
    Log::info('Análisis NO guardado por solicitud del usuario');
    $dataToPass['save_message'] = 'Este análisis no fue guardado.';
}
        
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
        $yearlyBreakdown = []; // Arreglo para guardar la deforestación por cada año

        foreach ($yearlyResults as $year => $yearData) {
            // Verificamos que la consulta fue exitosa para ese año
            if (isset($yearData['area__ha']) && $yearData['status'] === 'success') {
                $currentArea = $yearData['area__ha'];
                
                $totalDeforestedArea += $currentArea;
                $validYears++;

                // Guardamos el detalle de este año específico
                $yearlyBreakdown[$year] = [
                    'year' => $year,
                    'area_ha' => $currentArea,
                    // Calculamos el porcentaje relativo al área total solo para este año
                    'percentage' => $areaHa < $currentArea ? 100 : ($currentArea / $areaHa) * 100
                ];
            } else {
                // Opcional: Registrar años que fallaron o no tienen datos
                $yearlyBreakdown[$year] = [
                    'year' => $year,
                    'area_ha' => 0,
                    'percentage' => 0,
                    'status' => 'no_data'
                ];
            }
        }

        // Ajuste de seguridad: el área total no puede ser menor a lo deforestado
        $areaHa = $areaHa < $totalDeforestedArea ? $totalDeforestedArea : $areaHa;
        
        $totalPercentage = $areaHa > 0 ? ($totalDeforestedArea / $areaHa) * 100 : 0;
        $totalYearsInRange = $endYear - $startYear + 1;

        return [
            'totalDeforestedArea' => $totalDeforestedArea,
            'totalPercentage' => $totalPercentage,
            'validYears' => $validYears,
            'totalYearsInRange' => $totalYearsInRange,
            'yearlyBreakdown' => $yearlyBreakdown, 
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
     * Genera reporte PDF de los resultados
     */
    public function report($polygonId)
    {
        try {
            $polygon = Polygon::with('deforestationAnalyses')->findOrFail($polygonId);
            
            // Verificar que haya análisis guardados
            if ($polygon->deforestationAnalyses->isEmpty()) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'error' => 'No hay datos de análisis disponibles para este polígono. 
                                Primero debe ejecutar y guardar un análisis.'
                    ], 404);
                }
                
                return back()->withErrors(['error' => 'No hay datos de análisis disponibles para este polígono.']);
            }
            
            // Obtener análisis ordenados por año
            $analyses = $polygon->deforestationAnalyses->sortBy('year');
            
            // Calcular estadísticas
            $startYear = $analyses->min('year');
            $endYear = $analyses->max('year');
            $totalDeforestedArea = $analyses->sum('deforested_area_ha');
            $polygonArea = $polygon->area_ha ?? 0;
            $totalPercentage = $polygonArea > 0 ? ($totalDeforestedArea / $polygonArea) * 100 : 0;
            $conservedArea = max(0, $polygonArea - $totalDeforestedArea);
            
            // Preparar datos para la vista
            $data = [
                'polygon' => $polygon,
                'analyses' => $analyses->values(),
                'start_year' => $startYear,
                'end_year' => $endYear,
                'totalDeforestedArea' => $totalDeforestedArea,
                'totalPercentage' => $totalPercentage,
                'conservedArea' => $conservedArea,
                'report_date' => now()->format('d/m/Y H:i:s'),
                'isFromSaved' => true,
            ];

            Log::info('Generando PDF para polígono:', [
                'polygon_id' => $polygonId,
                'analyses_count' => $analyses->count(),
                'total_area' => $totalDeforestedArea,
                'polygon_area' => $polygonArea
            ]);

            // Generar PDF
            $pdf = \PDF::loadView('deforestation.report-pdf', $data)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'sans-serif',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'chroot' => public_path(),
                ]);
            
            // Limpiar nombre para el archivo
            $cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $polygon->name);
            $filename = "reporte-deforestacion-{$cleanName}-" . now()->format('Y-m-d') . '.pdf';
            
            // Descargar el PDF
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            Log::error('Error al generar PDF: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'error' => 'Error al generar el reporte: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Error al generar el reporte PDF: ' . $e->getMessage()]);
        }
    }
}
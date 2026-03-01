<?php

namespace App\Http\Controllers;

use App\Models\Polygon;
use App\Models\Producer;
use App\Services\DeforestationService;
use App\Services\PdfService;
use App\Models\Deforestation;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\GFWService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PDF;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class DeforestationController extends Controller
{
    protected $deforestationService;
    protected $gfwService;
    protected $pdfService;
    
    public function __construct(DeforestationService $deforestationService, GFWService $gfwService, PdfService $pdfService)
    {
        $this->deforestationService = $deforestationService;
        $this->gfwService = $gfwService;
        $this->pdfService = $pdfService;
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

        $producers = Producer::active()->get();
        /* dd($producers); */
        return view('deforestation.create', compact('saveByDefault', 'producers'));
    }
    
   /**
     * Procesa el análisis de deforestación
     */
     public function analyze(Request $request)
    {
        $geometryString = $request->input('geometry');

        // 1. Transformación de HEX a GeoJSON si es necesario
        if (preg_match('/^[0-9A-Fa-f]+$/', $geometryString)) {
            $geoJsonRes = DB::selectOne("SELECT ST_AsGeoJSON(ST_GeomFromWKB(decode(?, 'hex'))) as geojson", [$geometryString]);
            $geometryString = $geoJsonRes->geojson;
        }

        $saveAnalysis = $request->boolean('save_analysis');

        // Validaciones (se incluye producer_id como requerido si se guarda)
        $this->validateAnalyzeRequest($request, $saveAnalysis);

        $startYear = (int) $request->input('start_year');
        $endYear = (int) $request->input('end_year');
        $areaHa = (float) $request->input('area_ha');
        $polygonName = $request->input('name', 'Área de Estudio');
        $description = $request->input('description', '');
        $producerId = $request->input('producer_id'); // MODIFICADO: capturar productor
        $requestedYears = range($startYear, $endYear);

        session(['save_analysis_by_default' => $saveAnalysis]);

    try {
        $geometryGeoJson = json_decode($geometryString, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['geometry' => 'Formato GeoJSON inválido']);
        }
    } catch (\Exception $e) {
        return back()->withErrors(['error' => 'Error procesando la geometría: ' . $e->getMessage()]);
    }

    // --- NUEVA LÓGICA DE BÚSQUEDA POR GEOMETRÍA ---
    
    // Buscamos si ya existe un polígono con ESTA MISMA geometría
    $existingPolygon = DB::table('polygons')
        ->select('id', 'area_ha', 'name', 'description')
        ->whereRaw("ST_Equals(geometry, ST_SetSRID(ST_GeomFromGeoJSON(?), 4326))", [$geometryString])
        ->first();

    $existingRecords = [];
    $polygonId = null;

    if ($existingPolygon) {
        $polygonId = $existingPolygon->id;
        // Si no se envió nombre nuevo, usamos el que ya existe
        $polygonName = $request->filled('name') ? $polygonName : $existingPolygon->name;
        $areaHa = (float) $existingPolygon->area_ha;

        // Consultar qué años ya tenemos guardados para ese polígono
        $existingRecords = DB::table('deforestation')
            ->select('year', 'deforested_area_ha as area__ha', DB::raw("'success' as status"))
            ->where('polygon_id', $polygonId)
            ->whereBetween('year', [$startYear, $endYear])
            ->get()
            ->keyBy('year')
            ->toArray();
    }

    // Filtrar años: Solo pedir a la API los que NO están en la base de datos
    $existingYears = array_keys($existingRecords);
    $yearsToAnalyze = array_diff($requestedYears, $existingYears);

    // Consultar a la API de GFW solo los años faltantes
    $newResults = [];
    if (!empty($yearsToAnalyze)) {
        $newResults = $this->getParallelYearlyStats($geometryGeoJson, $yearsToAnalyze);
    }

    // Unificar resultados (Convertimos objetos de BD a arrays para mezclarlos con los de la API)
    $yearlyResults = array_replace(
        array_map(fn($item) => (array)$item, $existingRecords),
        $newResults
    );
    ksort($yearlyResults);

    // --- FIN DE LÓGICA DE BÚSQUEDA ---

    // 4. EJECUTAR CÁLCULO DE PÉRDIDA TOTAL ACUMULADA
    $totalLossResults = $this->calculateTotalLossStats($yearlyResults, $areaHa, $startYear, $endYear);

    // 5. Preparar datos para la vista
    $dataToPass = [
            'polygon_id' => $polygonId,
            'producer_id' => $producerId, // MODIFICADO: se incluye el productor
            'analysis_year' => $endYear,
            'start_year' => $startYear,
            'end_year' => $endYear,
            'original_geojson' => $geometryString,
            'type' => $geometryGeoJson['type'],
            'geometry' => $geometryGeoJson['coordinates'][0],
            'area__ha' => $yearlyResults[$endYear]['area__ha'] ?? 0,
            'polygon_area_ha' => max($areaHa, $totalLossResults['totalDeforestedArea']),
            'status' => $yearlyResults[$endYear]['status'] ?? 'success',
            'polygon_name' => $polygonName,
            'description' => $description,
            'yearly_results' => $yearlyResults,
            'total_loss' => $totalLossResults,
        ];

    // 6. GUARDAR SI ES NECESARIO
    if ($saveAnalysis) {
        $this->saveAnalysisData($dataToPass, $polygonId);
    }
    
    return view('deforestation.results', compact('dataToPass'));
}

/**
 * Función auxiliar para guardar (Lógica separada para limpieza)
 */
private function saveAnalysisData(&$dataToPass, $existingId)
    {
        try {
            DB::transaction(function () use (&$dataToPass, $existingId) {
                $polygonId = $existingId;

                // 1. Si el polígono no existe, se crea.
                if (!$polygonId) {
                    // MODIFICADO: orden correcto de parámetros y se agrega producer_id
                    $polygonRow = DB::selectOne(
                        "INSERT INTO polygons (name, description, geometry, producer_id, area_ha, created_at, updated_at)
                         VALUES (?, ?, ST_SetSRID(ST_GeomFromGeoJSON(?), 4326), ?, ?, ?, ?) RETURNING id",
                        [
                            $dataToPass['polygon_name'],
                            $dataToPass['description'],
                            $dataToPass['original_geojson'], // geometry
                            $dataToPass['producer_id'],      // producer_id
                            $dataToPass['polygon_area_ha'],
                            now(),  // created_at
                            now()   // updated_at
                        ]
                    );
                    $polygonId = $polygonRow->id;
                } else {
                    // MODIFICADO: si el polígono ya existe, actualizamos sus datos (incluyendo producer_id)
                    DB::update(
                        "UPDATE polygons 
                         SET name = ?, description = ?, producer_id = ?, updated_at = ? 
                         WHERE id = ?",
                        [
                            $dataToPass['polygon_name'],
                            $dataToPass['description'],
                            $dataToPass['producer_id'],
                            now(),
                            $polygonId
                        ]
                    );
                }

                // 2. Insertar o actualizar los años del desglose (sin cambios)
                foreach ($dataToPass['total_loss']['yearlyBreakdown'] as $yearData) {
                    $exists = DB::table('deforestation')
                        ->where('polygon_id', $polygonId)
                        ->where('year', $yearData['year'])
                        ->exists();

                    if ($exists) {
                        DB::table('deforestation')
                            ->where('polygon_id', $polygonId)
                            ->where('year', $yearData['year'])
                            ->update([
                                'deforested_area_ha' => $yearData['area_ha'],
                                'percentage_loss'    => $yearData['percentage'],
                                'updated_at'         => now(),
                            ]);
                    } else {
                        DB::table('deforestation')->insert([
                            'polygon_id'         => $polygonId,
                            'year'               => $yearData['year'],
                            'deforested_area_ha' => $yearData['area_ha'],
                            'percentage_loss'    => $yearData['percentage'],
                            'created_at'         => now(),
                            'updated_at'         => now(),
                        ]);
                    }
                }

                $dataToPass['polygon_id'] = $polygonId;
            });
            session()->flash('save_success', 'Análisis actualizado y guardado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al guardar: ' . $e->getMessage());
            $dataToPass['save_error'] = 'Error al guardar: ' . $e->getMessage();
        }
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
    
   public function report(Request $request)
{
    \Log::info('Datos recibidos para PDF:', ['data' => $request->all()]);
    
    try {
        $rawData = $request->input('report_data');
        $decoded = is_string($rawData) ? json_decode($rawData, true) : $rawData;

        // CORRECCIÓN: Acceder al nivel 'dataToPass' que viene dentro del JSON
        $dataToPass = $decoded['dataToPass'] ?? $decoded; 

        if (!$dataToPass || !isset($dataToPass['yearly_results'])) {
            \Log::warning('Estructura de datos inválida:', ['decoded' => $decoded]);
            return redirect()->back()
                ->withErrors(['error' => 'La estructura de los datos no es válida para generar el reporte.']);
        }

        // 1. Extraer variables clave
        $polygonArea = (float) ($dataToPass['polygon_area_ha'] ?? 0);
        $totalLossData = $dataToPass['total_loss'] ?? [];

        // 2. Mapear y transformar los análisis anuales
        $analyses = collect($dataToPass['yearly_results'] ?? [])->map(function($item) use ($polygonArea) {
            $area = (float) ($item['area__ha'] ?? 0);
            return (object)[
                'year' => $item['year'],
                'deforested_area_ha' => $area,
                'percentage_loss' => $polygonArea > 0 ? ($area / $polygonArea) * 100 : 0
            ];
        })->sortBy('year')->values();

        // 3. Preparar el paquete de datos final
        $data = [
            'polygon' => (object)[
                'name' => $dataToPass['polygon_name'] ?? 'Área sin nombre',
                'area_ha' => $polygonArea,
                'description' => $dataToPass['description'] ?? '',
            ],
            'analyses' => $analyses,
            'start_year' => $dataToPass['start_year'] ?? null,
            'end_year' => $dataToPass['end_year'] ?? null,
            'totalDeforestedArea' => (float) ($totalLossData['totalDeforestedArea'] ?? 0),
            'totalPercentage' => (float) ($totalLossData['totalPercentage'] ?? 0),
            'conservedArea' => $polygonArea - (float) ($totalLossData['totalDeforestedArea'] ?? 0),
            'report_date' => now()->format('d/m/Y H:i:s'),
            'isFromSaved' => false,
        ];

        // 4. Generación del PDF - CORREGIDO
        $pdf = \PDF::loadView('deforestation.report-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'chroot' => public_path(),
            ]);

        // Limpiar búfer
        if (ob_get_length()) ob_end_clean();

        // CORRECCIÓN: Usar Str::slug y acceder correctamente al array
        $filename = "reporte-" . \Illuminate\Support\Str::slug($data['polygon']->name) . "-" . now()->format('Ymd') . ".pdf";

        return $pdf->download($filename);

    } catch (\Exception $e) {
        \Log::error('Error en PDF: ' . $e->getMessage());
        \Log::error('Trace completo: ' . $e->getTraceAsString());
        
        return redirect()->route('deforestation.create')
            ->withErrors(['error' => 'Ocurrió un error técnico al generar el PDF: ' . $e->getMessage()]);
    }
}

public function polygon(Request $request)
{
    /* dd($request); */
    $geometryString = $request->input('geometry');
    $startYear = (int) $request->input('start_year');
    $end_year = (int) $request->input('end_year');
    $polygonId = $request->input('id');
    $polygonName = $request->input('name', 'Área de Estudio');
    $saveAnalysis = $request->boolean('save_analysis'); // Detectar si el usuario quiere guardar

    // 1. Transformación de Geometría (HEX a GeoJSON)
    if (preg_match('/^[0-9A-Fa-f]+$/', $geometryString)) {
        $geoJsonRes = DB::selectOne("SELECT ST_AsGeoJSON(ST_GeomFromWKB(decode(?, 'hex'))) as geojson", [$geometryString]);
        $geometryString = $geoJsonRes->geojson;
    }

    try {
        $geometryGeoJson = json_decode($geometryString, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['geometry' => 'Formato GeoJSON inválido']);
        }
    } catch (\Exception $e) {
        return back()->withErrors(['error' => 'Error procesando la geometría: ' . $e->getMessage()]);
    }

    // 2. Obtener años existentes y años faltantes
    $requestedYears = range($startYear, $end_year);

    // 1. Obtener el área total directamente de la tabla polygons
$polygonData = DB::table('polygons')
    ->where('id', $polygonId)
    ->select('area_ha')
    ->first();

// Asignar el valor a la variable $areaHa (si no existe el polígono, podrías usar el del request)
$areaHa = $polygonData ? (float)$polygonData->area_ha : (float)$request->input('area_ha');

// 2. Consultar registros existentes en la tabla deforestation
$existingRecords = DB::table('deforestation')
    ->select('year', 'deforested_area_ha as area__ha', DB::raw("'success' as status"))
    ->where('polygon_id', $polygonId)
    ->whereBetween('year', [$startYear, $end_year])
    ->get()
    ->keyBy('year')
    ->toArray();

/* dd($existingRecords); */
    $existingYears = array_keys($existingRecords);
    $yearsToAnalyze = array_diff($requestedYears, $existingYears);

    // 3. Consultar solo lo que falta a la API
    $newResults = [];
    if (!empty($yearsToAnalyze)) {
        $newResults = $this->getParallelYearlyStats($geometryGeoJson, $yearsToAnalyze);

        // --- LÓGICA DE GUARDADO ---
        if ($saveAnalysis) {
            try {
                DB::transaction(function () use ($newResults, $polygonId, $areaHa) {
                    foreach ($newResults as $year => $data) {
                        if ($data['status'] === 'success') {
                            // Calcular el porcentaje para este año específico
                            $currentArea = (float) $data['area__ha'];
                            $percentage = $areaHa > 0 ? ($currentArea / $areaHa) * 100 : 0;

                            DB::table('deforestation')->insert([
                                'polygon_id' => $polygonId,
                                'year' => (int) $year,
                                'deforested_area_ha' => $currentArea,
                                'percentage_loss' => $percentage > 100 ? 100 : $percentage,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                });
                session()->flash('save_success', 'Los nuevos datos del análisis han sido guardados.');
            } catch (\Exception $e) {
                Log::error("Error al guardar nuevos años para polígono {$polygonId}: " . $e->getMessage());
            }
        }
    }

    // 4. UNIFICAR: Mezclar registros viejos con resultados nuevos
    $combinedResults = array_replace(
        array_map(fn($item) => (array)$item, $existingRecords), 
        $newResults
    );
    ksort($combinedResults);

    // 5. Calcular estadísticas totales con la data unificada
    $totalLossResults = $this->calculateTotalLossStats($combinedResults, $areaHa, $startYear, $end_year);

    // 6. Preparar datos para la vista
    $dataToPass = [
        'polygon_id' => $polygonId,
        'analysis_year' => $end_year,
        'start_year' => $startYear,
        'end_year' => $end_year,
        'original_geojson' => $geometryString,
        'type' => $geometryGeoJson['type'] ?? 'Polygon',
        'geometry' => $geometryGeoJson['coordinates'][0] ?? [],
        'area__ha' => $areaHa < $totalLossResults['totalDeforestedArea'] ? $totalLossResults['totalDeforestedArea'] : $areaHa,
        'polygon_area_ha' => $areaHa,
        'status' => 'success',
        'polygon_name' => $polygonName,
        'description' => $request->input('description', ''),
        'yearly_results' => $combinedResults,
        'total_loss' => $totalLossResults,
    ];

    return view('deforestation.results', compact('dataToPass'));
}

/**
 * Valida la petición del análisis de deforestación
 */
 private function validateAnalyzeRequest(Request $request, bool $saveAnalysis)
    {
        $rules = [
            'start_year'    => 'required|integer|min:2001|max:2024',
            'end_year'      => 'required|integer|min:2001|max:2025|gte:start_year',
            'geometry'      => 'required|string',
            'area_ha'       => 'required|numeric|min:0.01',
            'description'   => 'nullable|string|max:1000',
            'save_analysis' => 'boolean'
        ];

        if ($saveAnalysis) {
            // MODIFICADO: se agrega producer_id como requerido
            $rules['name'] = 'required|string|min:3|max:255';
            $rules['producer_id'] = 'required|integer|exists:producers,id'; // productor obligatorio y debe existir
        } else {
            $rules['name'] = 'nullable|string|max:255';
            // producer_id no es obligatorio si no se guarda
        }

        $messages = [
            'name.required'      => 'El nombre del área es obligatorio cuando se guarda el análisis.',
            'name.min'           => 'El nombre debe tener al menos 3 caracteres.',
            'producer_id.required' => 'Debe seleccionar un productor para guardar el análisis.', // MODIFICADO
            'producer_id.exists' => 'El productor seleccionado no es válido.',
            'geometry.required'  => 'Debe dibujar un polígono en el mapa.',
            'area_ha.min'        => 'El área debe ser mayor a 0 hectáreas.',
            'end_year.gte'       => 'El año de fin debe ser mayor o igual al año de inicio.'
        ];

        $request->validate($rules, $messages);
    }
}
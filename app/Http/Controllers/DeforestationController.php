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
        $oldSaveAnalysis = old('save_analysis');
    
        if ($oldSaveAnalysis !== null) {
            $saveByDefault = ($oldSaveAnalysis === '1' || $oldSaveAnalysis === true || $oldSaveAnalysis === 1);
        } else {
            $saveByDefault = session('save_analysis_by_default', false);
        }

        $producers = Producer::active()->get();
        return view('deforestation.create', compact('saveByDefault', 'producers'));
    }
    
    /**
     * Procesa el análisis de deforestación (soporta FeatureCollection)
     */
    public function analyze(Request $request)
    {
        $geometryString = $request->input('geometry');

        // Transformación de HEX a GeoJSON si es necesario
        if (preg_match('/^[0-9A-Fa-f]+$/', $geometryString)) {
            $geoJsonRes = DB::selectOne("SELECT ST_AsGeoJSON(ST_GeomFromWKB(decode(?, 'hex'))) as geojson", [$geometryString]);
            $geometryString = $geoJsonRes->geojson;
        }

        $saveAnalysis = $request->boolean('save_analysis');
        $this->validateAnalyzeRequest($request, $saveAnalysis);

        // Parámetros comunes
        $globalParams = [
            'start_year'    => (int) $request->input('start_year'),
            'end_year'      => (int) $request->input('end_year'),
            'save_analysis' => $saveAnalysis,
            'polygon_name'  => $request->input('name', 'Área de Estudio'),
            'description'   => $request->input('description', ''),
            'producer_id'   => $request->input('producer_id'),
        ];

        session(['save_analysis_by_default' => $saveAnalysis]);

        // Decodificar GeoJSON
        $geojson = json_decode($geometryString, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['geometry' => 'Formato GeoJSON inválido']);
        }

        if ($geojson['type'] === 'FeatureCollection') {
            $features = $geojson['features'];
            if (count($features) === 1) {
                // Un solo polígono: registrar análisis individual
                $singleFeature = $features[0];
                $dataToPass = $this->processSinglePolygon($singleFeature, $globalParams, false);
                return view('deforestation.results', compact('dataToPass'));
            } else {
                // Múltiples polígonos: registrar un evento agrupado
                $multiResults = [];
                $polygonIds = [];

Log::info('Análisis múltiple iniciado', [
    'save_analysis' => $globalParams['save_analysis'],
    'count' => count($features),
    'polygon_name' => $globalParams['polygon_name'],
]);
                foreach ($features as $feature) {
                    $result = $this->processSinglePolygon($feature, $globalParams, true); // ← skipActivity = true
                    $multiResults[] = $result;
                    if (isset($result['polygon_id'])) {
                        $polygonIds[] = $result['polygon_id'];
                    }
                }
                // Registrar el evento agrupado si se guardó el análisis
                if ($globalParams['save_analysis'] && !empty($polygonIds)) {
                    $this->registerMultiAnalysisEvent($polygonIds, $globalParams, $multiResults);
                }
                return view('deforestation.multi-results', compact('multiResults'));
            }
        }
    }

    /**
     * Procesa un único polígono (Feature GeoJSON) y devuelve sus datos.
     */
    private function processSinglePolygon(array $feature, array $globalParams, bool $skipActivity = false): array
    {
        $geometryGeoJson = $feature['geometry'];
        $properties = $feature['properties'] ?? [];

        // Datos del polígono desde propiedades
        $externalId = $properties['id'] ?? null;
        $productorName = $properties['Productor'] ?? $properties['productor'] ?? 'Sin productor';
        $areaFromProps = $properties['Area_Ha'] ?? $properties['area'] ?? 0;

        // Buscar productor en BD por nombre
        $producer = Producer::where('name', $productorName)->first();
        
        $producerId = $producer ? $producer->id : $globalParams['producer_id']; // Usar el global si no se encontró

        $geometryString = json_encode($geometryGeoJson);

        // Buscar si ya existe un polígono con esta geometría
        $existingPolygon = DB::table('polygons')
            ->select('id', 'area_ha', 'name', 'description')
            ->whereRaw("ST_Equals(geometry, ST_SetSRID(ST_GeomFromGeoJSON(?), 4326))", [$geometryString])
            ->first();

        $polygonId = $existingPolygon ? $existingPolygon->id : null;
        $areaHa = $areaFromProps > 0 ? $areaFromProps : ($existingPolygon ? $existingPolygon->area_ha : 0);

        // Calcular área si no tenemos
        if ($areaHa <= 0 && $geometryString) {
            $areaCalculated = DB::selectOne("
                SELECT ST_Area(ST_SetSRID(ST_GeomFromGeoJSON(?), 4326)::geography) / 10000 as area
            ", [$geometryString]);
            $areaHa = $areaCalculated->area ?? 0;
        }

        $startYear = $globalParams['start_year'];
        $endYear = $globalParams['end_year'];
        $requestedYears = range($startYear, $endYear);

        // Obtener registros existentes de deforestación para este polígono
        $existingRecords = [];
        if ($polygonId) {
            $existingRecords = DB::table('deforestation')
                ->select('year', 'deforested_area_ha as area__ha', DB::raw("'success' as status"))
                ->where('polygon_id', $polygonId)
                ->whereBetween('year', [$startYear, $endYear])
                ->get()
                ->keyBy('year')
                ->toArray();
        }

        $existingYears = array_keys($existingRecords);
        $yearsToAnalyze = array_diff($requestedYears, $existingYears);

        // Consultar GFW solo para años faltantes
        $newResults = [];
        if (!empty($yearsToAnalyze)) {
            $newResults = $this->getParallelYearlyStats($geometryGeoJson, $yearsToAnalyze);
        }

        $yearlyResults = array_replace(
            array_map(fn($item) => (array)$item, $existingRecords),
            $newResults
        );
        ksort($yearlyResults);

        // Calcular pérdida total
        $totalLossResults = $this->calculateTotalLossStats($yearlyResults, $areaHa, $startYear, $endYear);

        // Preparar datos para la vista / guardado
        $dataToPass = [
            'polygon_id'       => $polygonId,
            'producer_id'      => $producerId,
            'analysis_year'    => $endYear,
            'start_year'       => $startYear,
            'end_year'         => $endYear,
            'original_geojson' => $geometryString,
            'type'             => $geometryGeoJson['type'],
            'geometry'         => $geometryGeoJson['coordinates'][0] ?? [],
            'area__ha'         => $yearlyResults[$endYear]['area__ha'] ?? 0,
            'polygon_area_ha'  => max($areaHa, $totalLossResults['totalDeforestedArea']),
            'status'           => $yearlyResults[$endYear]['status'] ?? 'success',
            'polygon_name'     => $globalParams['polygon_name'] ?? ('Polígono ' . ($externalId ?: uniqid())),
            'description'      => $globalParams['description'] ?? '',
            'yearly_results'   => $yearlyResults,
            'total_loss'       => $totalLossResults,
            'external_id'      => $externalId,
            'productor_name'   => $productorName,
        ];

        // Guardar si se solicitó
        if ($globalParams['save_analysis']) {
            $this->saveSinglePolygonData($dataToPass, $polygonId, $skipActivity);
        }

        return $dataToPass;
    }

    private function saveSinglePolygonData(array &$dataToPass, $existingId, bool $skipActivity = false)
    {
        try {
            DB::transaction(function () use (&$dataToPass, $existingId, $skipActivity) {
                $polygon = null;

                if ($existingId) {
                    // Actualizar polígono existente (SIN LOG)
                    $polygon = Polygon::withTrashed()->findOrFail($existingId);
                    $polygon->updateQuietly([
                        'name'        => $dataToPass['polygon_name'],
                        'description' => $dataToPass['description'],
                        'producer_id' => $dataToPass['producer_id'],
                        'area_ha'     => $dataToPass['polygon_area_ha'],
                    ]);
                } else {
                    // Crear nuevo polígono (SIN LOG)
                    $geoJson = $dataToPass['original_geojson'];
                    /* dd($dataToPass['polygon_name']); */
                    $polygon = Polygon::createWithGeometry(
                        [
                            'name'          => $dataToPass['polygon_name'],
                            'description'   => $dataToPass['description'],
                            'producer_id'   => $dataToPass['producer_id'],
                            'area_ha'       => $dataToPass['polygon_area_ha'],
                            'is_active'     => true,
                            'location_data' => [
                                'detected_parish'       => null,
                                'detected_municipality' => null,
                                'detected_state'        => null,
                                'created_info' => [
                                    'source' => 'deforestation_analysis',
                                    'created_at' => now()->toISOString(),
                                ],
                            ],
                        ],
                        $geoJson,
                        false // ← DESACTIVAR LOG
                    );
                }

                // Guardar/actualizar registros de deforestación (SIN LOG)
                Deforestation::withoutEvents(function () use ($polygon, $dataToPass) {
                    foreach ($dataToPass['total_loss']['yearlyBreakdown'] as $yearData) {
                        Deforestation::updateOrCreate(
                            [
                                'polygon_id' => $polygon->id,
                                'year'       => $yearData['year'],
                            ],
                            [
                                'deforested_area_ha' => $yearData['area_ha'],
                                'percentage_loss'    => $yearData['percentage'],
                            ]
                        );
                    }
                });

                $dataToPass['polygon_id'] = $polygon->id;

                    if (!$skipActivity) {
                    activity()
                        ->causedBy(auth()->user())
                        ->performedOn($polygon)
                        ->withProperties([
                            'start_year'      => $dataToPass['start_year'],
                            'end_year'        => $dataToPass['end_year'],
                            'total_deforested' => $dataToPass['total_loss']['totalDeforestedArea'],
                            'total_percentage' => $dataToPass['total_loss']['totalPercentage'],
                            'polygon_updated'  => $existingId !== null,
                            'years_analyzed'   => $dataToPass['yearly_results'],
                        ])
                        ->event('analyzed')
                        ->log("Poligono '{$polygon->name}' analizado");
                }
                
            });
            session()->flash('save_success', 'Análisis guardado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error guardando polígono: ' . $e->getMessage());
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
            $promises[$year] = $this->createGFWRequestPromise($client, $geometryGeoJson, (int)$year);
        }
        
        try {
            $responses = Promise\Utils::settle($promises)->wait();
            foreach ($responses as $year => $response) {
                if ($response['state'] === 'fulfilled') {
                    $data = json_decode($response['value']->getBody(), true);
                    Log::info("Respuesta GFW para año $year:", [
                        'status' => $data['status'] ?? 'unknown',
                        'area_ha' => $data['data'][0]['area__ha'] ?? 0,
                    ]);
                    $results[$year] = [
                        'area__ha' => $data['data'][0]['area__ha'] ?? 0,
                        'status' => $data['status'] ?? 'error',
                        'year' => (int)$year
                    ];
                } else {
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
     * Crea una promise para consulta GFW
     */
    private function createGFWRequestPromise(Client $client, $geometryGeoJson, $year)
    {
        $url = env('GFW_API_BASE_URI') . '/dataset/umd_tree_cover_loss/latest/query';
        $sql = sprintf("SELECT SUM(area__ha) FROM results WHERE umd_tree_cover_loss__year=%d", $year);
        $payload = [
            'geometry' => $geometryGeoJson,
            'sql' => $sql
        ];
        Log::info("Enviando consulta GFW para año $year:", [
            'url' => $url,
            'sql' => $sql,
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
     * Calcula la pérdida total acumulada y el porcentaje de deforestación.
     */
    private function calculateTotalLossStats(array $yearlyResults, float $areaHa, int $startYear, int $endYear): array
    {   
        $totalDeforestedArea = 0;
        $validYears = 0;
        $yearlyBreakdown = [];

        foreach ($yearlyResults as $year => $yearData) {
            if (isset($yearData['area__ha']) && $yearData['status'] === 'success') {
                $currentArea = $yearData['area__ha'];
                $totalDeforestedArea += $currentArea;
                $validYears++;
                $yearlyBreakdown[$year] = [
                    'year' => $year,
                    'area_ha' => $currentArea,
                    'percentage' => $areaHa < $currentArea ? 100 : ($currentArea / $areaHa) * 100
                ];
            } else {
                $yearlyBreakdown[$year] = [
                    'year' => $year,
                    'area_ha' => 0,
                    'percentage' => 0,
                    'status' => 'no_data'
                ];
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
            'yearlyBreakdown' => $yearlyBreakdown, 
        ];
    }

    /**
     * Valida la petición del análisis de deforestación (con soporte para múltiples polígonos)
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
            // Verificar si es una FeatureCollection (múltiples polígonos)
            $geojson = json_decode($request->input('geometry'), true);
            $isMulti = ($geojson['type'] ?? '') === 'FeatureCollection';
            if (!$isMulti) {
                $rules['name'] = 'required|string|min:3|max:255';
                $rules['producer_id'] = 'required|integer|exists:producers,id';
            }
        } else {
            $rules['name'] = 'nullable|string|max:255';
        }

        $messages = [
            'name.required'      => 'El nombre del área es obligatorio cuando se guarda el análisis.',
            'name.min'           => 'El nombre debe tener al menos 3 caracteres.',
            'producer_id.required' => 'Debe seleccionar un productor para guardar el análisis.',
            'producer_id.exists' => 'El productor seleccionado no es válido.',
            'geometry.required'  => 'Debe dibujar un polígono en el mapa.',
            'area_ha.min'        => 'El área debe ser mayor a 0 hectáreas.',
            'end_year.gte'       => 'El año de fin debe ser mayor o igual al año de inicio.'
        ];

        $request->validate($rules, $messages);
    }

    // ==================== OTROS MÉTODOS EXISTENTES (sin cambios) ====================

    public function multipleResults(Request $request): View
    {
        $polygonIds = explode(',', $request->input('polygon_ids', ''));
        
        if (empty($polygonIds)) {
            return redirect()->route('deforestation.create')
                ->withErrors(['error' => 'No se especificaron polígonos para mostrar.']);
        }

        $polygons = Polygon::with(['analyses', 'producer'])
            ->whereIn('id', $polygonIds)
            ->get();

        if ($polygons->isEmpty()) {
            return redirect()->route('deforestation.create')
                ->withErrors(['error' => 'No se encontraron polígonos con los IDs proporcionados.']);
        }

        $multiResults = [];
        foreach ($polygons as $polygon) {
            try {
                $multiResults[] = $this->buildDataFromPolygon($polygon);
            } catch (\Exception $e) {
                Log::warning($e->getMessage());
            }
        }

        if (empty($multiResults)) {
            return redirect()->route('deforestation.create')
                ->withErrors(['error' => 'Los polígonos seleccionados no tienen análisis guardados.']);
        }

        return view('deforestation.multi-results', compact('multiResults'));
    }
    
    public function results($polygonId): View
    {
        $polygon = Polygon::with(['analyses', 'producer'])->findOrFail($polygonId);

        try {
            $dataToPass = $this->buildDataFromPolygon($polygon);
        } catch (\Exception $e) {
            return redirect()->route('deforestation.create')
                ->withErrors(['error' => $e->getMessage()]);
        }

        return view('deforestation.results', compact('dataToPass'));
    }

    public function getAnalysisData($polygonId): JsonResponse
    {
        $polygon = Polygon::findOrFail($polygonId);
        $history = $this->deforestationService->getAnalysisHistory($polygon);
        return response()->json($history);
    }
    
    public function export($polygonId)
    {
        return response()->json(['message' => 'Export functionality to be implemented']);
    }
    
    public function report(Request $request)
    {
        \Log::info('Datos recibidos para PDF:', ['data' => $request->all()]);
        
        try {
            $rawData = $request->input('report_data');
            $decoded = is_string($rawData) ? json_decode($rawData, true) : $rawData;
            $dataToPass = $decoded['dataToPass'] ?? $decoded; 

            if (!$dataToPass || !isset($dataToPass['yearly_results'])) {
                \Log::warning('Estructura de datos inválida:', ['decoded' => $decoded]);
                return redirect()->back()
                    ->withErrors(['error' => 'La estructura de los datos no es válida para generar el reporte.']);
            }

            $polygonArea = (float) ($dataToPass['polygon_area_ha'] ?? 0);
            $totalLossData = $dataToPass['total_loss'] ?? [];

            $analyses = collect($dataToPass['yearly_results'] ?? [])->map(function($item) use ($polygonArea) {
                $area = (float) ($item['area__ha'] ?? 0);
                return (object)[
                    'year' => $item['year'],
                    'deforested_area_ha' => $area,
                    'percentage_loss' => $polygonArea > 0 ? ($area / $polygonArea) * 100 : 0
                ];
            })->sortBy('year')->values();

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

            $pdf = \PDF::loadView('deforestation.report-pdf', $data)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'sans-serif',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'chroot' => public_path(),
                ]);

            if (ob_get_length()) ob_end_clean();
            $filename = "reporte-" . \Illuminate\Support\Str::slug($data['polygon']->name) . "-" . now()->format('Ymd') . ".pdf";
            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Log::error('Error en PDF: ' . $e->getMessage());
            return redirect()->route('deforestation.create')
                ->withErrors(['error' => 'Ocurrió un error técnico al generar el PDF: ' . $e->getMessage()]);
        }
    }

    public function polygon(Request $request)
    {
        $geometryString = $request->input('geometry');
        $startYear = (int) $request->input('start_year');
        $end_year = (int) $request->input('end_year');
        $polygonId = $request->input('id');
        $polygonName = $request->input('name', 'Área de Estudio');
        $saveAnalysis = $request->boolean('save_analysis');

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

        $requestedYears = range($startYear, $end_year);

        $polygonData = DB::table('polygons')
            ->where('id', $polygonId)
            ->select('area_ha')
            ->first();

        $areaHa = $polygonData ? (float)$polygonData->area_ha : (float)$request->input('area_ha');

        $existingRecords = DB::table('deforestation')
            ->select('year', 'deforested_area_ha as area__ha', DB::raw("'success' as status"))
            ->where('polygon_id', $polygonId)
            ->whereBetween('year', [$startYear, $end_year])
            ->get()
            ->keyBy('year')
            ->toArray();

        $existingYears = array_keys($existingRecords);
        $yearsToAnalyze = array_diff($requestedYears, $existingYears);

        // 1. Primero consultar GFW para los años faltantes
        $newResults = [];
        if (!empty($yearsToAnalyze)) {
            $newResults = $this->getParallelYearlyStats($geometryGeoJson, $yearsToAnalyze);
        }

        // 2. Guardar si se solicita y hay nuevos resultados
        if (!empty($newResults) && $saveAnalysis) {
            try {
                DB::transaction(function () use ($newResults, $polygonId, $areaHa, $startYear, $end_year, $polygonName) {
                    $polygon = Polygon::withTrashed()->findOrFail($polygonId);

                    // Guardar sin eventos
                    Deforestation::withoutEvents(function () use ($newResults, $polygonId, $areaHa) {
                        foreach ($newResults as $year => $data) {
                            if ($data['status'] === 'success') {
                                $currentArea = (float) $data['area__ha'];
                                $percentage = $areaHa > 0 ? ($currentArea / $areaHa) * 100 : 0;

                                Deforestation::updateOrCreate(
                                    [
                                        'polygon_id' => $polygonId,
                                        'year'       => (int) $year,
                                    ],
                                    [
                                        'deforested_area_ha' => $currentArea,
                                        'percentage_loss'    => $percentage > 100 ? 100 : $percentage,
                                    ]
                                );
                            }
                        }
                    });

                    // Registrar evento analyzed
                    $totalLossResults = $this->calculateTotalLossStats(
                        array_replace($existingRecords, $newResults),
                        $areaHa,
                        $startYear,
                        $end_year
                    );

                    activity()
                        ->causedBy(auth()->user())
                        ->performedOn($polygon)
                        ->withProperties([
                            'start_year'      => $startYear,
                            'end_year'        => $end_year,
                            'total_deforested' => $totalLossResults['totalDeforestedArea'],
                            'total_percentage' => $totalLossResults['totalPercentage'],
                            'polygon_updated'  => true,
                            'years_analyzed'   => $newResults,
                        ])
                        ->event('analyzed')
                        ->log("Nuevo análisis de deforestación completado para el polígono '{$polygon->name}'");

                });
                session()->flash('save_success', 'Los nuevos datos del análisis han sido guardados.');
            } catch (\Exception $e) {
                Log::error("Error al guardar nuevos años para polígono {$polygonId}: " . $e->getMessage());
            }
        }

        // 3. Combinar resultados y mostrar vista
        $combinedResults = array_replace(
            array_map(fn($item) => (array)$item, $existingRecords),
            $newResults
        );
        ksort($combinedResults);

        $totalLossResults = $this->calculateTotalLossStats($combinedResults, $areaHa, $startYear, $end_year);

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
     * Construye el array dataToPass para un polígono con sus análisis.
     */
    private function buildDataFromPolygon(Polygon $polygon): array
    {
        $analyses = $polygon->analyses->sortBy('year');

        if ($analyses->isEmpty()) {
            throw new \Exception("El polígono '{$polygon->name}' no tiene análisis guardados.");
        }

        $yearlyResults = [];
        $totalDeforestedArea = 0;
        $startYear = $analyses->first()->year;
        $endYear = $analyses->last()->year;

        foreach ($analyses as $analysis) {
            $yearlyResults[$analysis->year] = [
                'area__ha' => $analysis->deforested_area_ha,
                'status' => 'success',
                'year' => $analysis->year,
            ];
            $totalDeforestedArea += $analysis->deforested_area_ha;
        }

        $totalPercentage = $polygon->area_ha > 0 ? ($totalDeforestedArea / $polygon->area_ha) * 100 : 0;

        $yearlyBreakdown = [];
        foreach ($analyses as $analysis) {
            $yearlyBreakdown[$analysis->year] = [
                'year' => $analysis->year,
                'area_ha' => $analysis->deforested_area_ha,
                'percentage' => $polygon->area_ha > 0 ? ($analysis->deforested_area_ha / $polygon->area_ha) * 100 : 0,
            ];
        }

        return [
            'polygon_id' => $polygon->id,
            'producer_id' => $polygon->producer_id,
            'analysis_year' => $endYear,
            'start_year' => $startYear,
            'end_year' => $endYear,
            'original_geojson' => $polygon->getGeometryGeoJson(),
            'type' => 'Polygon',
            'geometry' => [],
            'area__ha' => $totalDeforestedArea,
            'polygon_area_ha' => $polygon->area_ha,
            'status' => 'success',
            'polygon_name' => $polygon->name,
            'description' => $polygon->description ?? '',
            'yearly_results' => $yearlyResults,
            'total_loss' => [
                'totalDeforestedArea' => $totalDeforestedArea,
                'totalPercentage' => $totalPercentage,
                'validYears' => $analyses->count(),
                'totalYearsInRange' => $endYear - $startYear + 1,
                'yearlyBreakdown' => $yearlyBreakdown,
            ],
            'external_id' => null,
            'productor_name' => $polygon->producer ? $polygon->producer->name : 'Sin productor',
        ];
    }

    /**
     * Registra un evento agrupado para múltiples polígonos analizados.
     */
    private function registerMultiAnalysisEvent(array $polygonIds, array $globalParams, array $multiResults): void
    {
        $totalArea = array_sum(array_column($multiResults, 'polygon_area_ha'));
        $totalDeforested = array_sum(array_map(function($r) {
            return $r['total_loss']['totalDeforestedArea'] ?? 0;
        }, $multiResults));
        $totalPercentage = $totalArea > 0 ? ($totalDeforested / $totalArea) * 100 : 0;

        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'polygon_ids' => $polygonIds,
                'count' => count($polygonIds),
                'start_year' => $globalParams['start_year'],
                'end_year' => $globalParams['end_year'],
                'total_area' => $totalArea,
                'total_deforested' => $totalDeforested,
                'total_percentage' => $totalPercentage,
            ])
            ->event('analyzed_multiple')
            ->log("Análisis de deforestación completado para " . count($polygonIds) . " polígonos");
    }
}
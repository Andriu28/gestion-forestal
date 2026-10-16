<?php

namespace App\Http\Controllers;

use App\Models\Polygon;
use App\Models\Producer;
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
use Spatie\Activitylog\Models\Activity;
use App\Services\DeforestationAnalysisService;
use App\Jobs\AnalyzeDeforestationJob;

/**
 * Controlador de Análisis y Reportes de Deforestación Satelital
 * 
 * Gestiona el flujo analítico de pérdida de cobertura boscosa mediante satélites (Global Forest Watch):
 * 1. Recepción y conversión de formatos geométricos espaciales (GeoJSON y WKB Hexadecimal).
 * 2. Ejecución asíncrona de análisis por lotes en segundo plano con colas (Jobs).
 * 3. Consultas en tiempo real contra los sensores satelitales de UMD Hansen / GFW.
 * 4. Presentación de resultados individuales y comparativas multi-predio.
 * 5. Generación de informes técnicos de deforestación exportables en formato PDF.
 */
class DeforestationController extends Controller
{
    /** Servicio de conexión con la API satelital de Global Forest Watch. */
    protected $gfwService;

    /** Servicio de renderizado y configuración de documentos PDF. */
    protected $pdfService;
    
    public function __construct(GFWService $gfwService, PdfService $pdfService)
    {
        $this->gfwService = $gfwService;
        $this->pdfService = $pdfService;
    }

    /**
     * Muestra la interfaz para trazar o subir parcelas y configurar los parámetros del análisis
     * (rango de años satelitales, productor, y si los resultados se guardarán permanentemente en inventario).
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
     * Reconstruye y visualiza un análisis efímero o simulación previa a partir de los metadatos
     * guardados en el registro de auditoría de actividad (Spatie Activitylog).
     */
    public function viewUnsaved($activityId)
    {
        $activity = Activity::findOrFail($activityId);
        $data     = $activity->properties;

        $dataToPass = [
            'polygon_name'     => $data['polygon_name'] ?? 'Área sin nombre',
            'start_year'       => $data['start_year'],
            'end_year'         => $data['end_year'],
            'polygon_area_ha'  => $data['polygon_area_ha'],
            'total_deforested' => $data['total_deforested'],
            'total_percentage' => $data['total_percentage'],
        ];

        return view('deforestation.results-unsaved', compact('dataToPass'));
    }
    
    /**
     * Punto de entrada principal para el análisis satelital asíncrono:
     * 
     * Pipeline de procesamiento:
     * 1. Detección de formato geométrico: si la geometría viene en formato binario WKB (hexadecimal de PostGIS),
     *    la convierte al vuelo a GeoJSON mediante la función espacial `ST_AsGeoJSON(ST_GeomFromWKB(decode(?, 'hex')))`.
     * 2. Valida los rangos de años permitidos en la configuración y las reglas de obligatoriedad.
     * 3. Descompone el GeoJSON en entidades individuales (Features) si se envió una FeatureCollection.
     * 4. Inicializa el seguimiento en la memoria caché mediante `DeforestationAnalysisService::initProgress`.
     * 5. Encola un `AnalyzeDeforestationJob` por cada polígono dentro de un lote (`Bus::batch`).
     * 6. Devuelve un código HTTP 202 con el UUID del análisis para que el cliente inicie el polling.
     */
    public function analyze(Request $request)
    {
        Log::info('=== INICIO DE ANÁLISIS (async) ===');

        $geometryString = $request->input('geometry');

        // Conversión transparente de binario Well-Known Binary (WKB) a estándar GeoJSON
        if (preg_match('/^[0-9A-Fa-f]+$/', $geometryString)) {
            Log::info('Geometría recibida como WKB hex, convirtiendo a GeoJSON');
            $geoJsonRes = DB::selectOne(
                "SELECT ST_AsGeoJSON(ST_GeomFromWKB(decode(?, 'hex'))) as geojson",
                [$geometryString]
            );
            $geometryString = $geoJsonRes->geojson;
        }

        $saveAnalysis = $request->boolean('save_analysis');

        $this->validateAnalyzeRequest($request, $saveAnalysis);

        $globalParams = [
            'start_year'    => (int) $request->input('start_year'),
            'end_year'      => (int) $request->input('end_year'),
            'save_analysis' => $saveAnalysis,
            'polygon_name'  => $request->input('name', 'Área de Estudio'),
            'description'   => $request->input('description', ''),
            'producer_id'   => $request->input('producer_id'),
        ];

        session(['save_analysis_by_default' => $saveAnalysis]);

        $geojson = json_decode($geometryString, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['geometry' => 'Formato GeoJSON inválido']);
        }

        // Descomponer en lista plana de geometrías
        $features = $this->buildFeaturesFromGeojson($geojson);
        if (empty($features)) {
            return back()->withErrors([
                'geometry' => 'Tipo de geometría no soportado: ' . ($geojson['type'] ?? 'null'),
            ]);
        }

        /** @var DeforestationAnalysisService $service */
        $service    = app(DeforestationAnalysisService::class);
        $analysisId = (string) \Illuminate\Support\Str::uuid();
        $total      = count($features);

        // Inicializar el monitor de progreso en caché
        $service->initProgress($analysisId, $total, [
            'has_save'      => $saveAnalysis,
            'feature_count' => $total,
        ]);

        $causerId = auth()->id();
        $jobs     = [];

        // Generar un trabajo por cada parcela a evaluar contra los satélites
        foreach ($features as $index => $feature) {
            $jobs[] = new \App\Jobs\AnalyzeDeforestationJob(
                feature:       $feature,
                index:         $index,
                globalParams:  $globalParams,
                analysisId:    $analysisId,
                totalFeatures: $total,
                causerId:      $causerId,
            );
        }

        // Despachar el lote a la cola con tolerancia a fallos parciales
        \Illuminate\Support\Facades\Bus::batch($jobs)
            ->name("analysis:{$analysisId}")
            ->allowFailures()
            ->finally(function () use ($analysisId) {
                app(DeforestationAnalysisService::class)->finalizeProgress($analysisId);
            })
            ->dispatch();

        return response()->json([
            'success'     => true,
            'analysis_id' => $analysisId,
            'total'       => $total,
            'has_save'    => $saveAnalysis,
        ], 202);
    }

    /**
     * Convierte cualquier estructura GeoJSON admitida (FeatureCollection, Feature simple o Geometría directa)
     * en un arreglo estructurado uniforme de Features.
     */
    private function buildFeaturesFromGeojson(array $geojson): array
    {
        $type = $geojson['type'] ?? null;

        if ($type === 'FeatureCollection') {
            return $geojson['features'] ?? [];
        }
        if ($type === 'Feature') {
            return [$geojson];
        }
        if ($type === 'Polygon' || $type === 'MultiPolygon') {
            return [[
                'geometry'   => $geojson,
                'properties' => [],
            ]];
        }

        return [];
    }

    /**
     * Endpoint consultado por AJAX para conocer el estado y porcentaje de la barra de progreso en vivo.
     */
    public function analysisProgress(string $analysisId): JsonResponse
    {
        $state = app(DeforestationAnalysisService::class)->getProgress($analysisId);

        if (!$state) {
            return response()->json([
                'success' => false,
                'message' => 'Análisis no encontrado o expirado.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'state'   => $state,
        ]);
    }

    /**
     * Muestra la pantalla de resultados para análisis efímeros o no persistidos,
     * recuperando la información directamente desde la caché según el UUID de sesión.
     */
    public function viewAsync(string $analysisId)
    {
        $service = app(DeforestationAnalysisService::class);
        $state   = $service->getProgress($analysisId);

        if (!$state || ($state['status'] ?? null) !== 'done') {
            return redirect()->route('deforestation.create')
                ->withErrors(['error' => 'El análisis no está listo o expiró.']);
        }

        $results = $service->getAllResults($analysisId);

        if (empty($results)) {
            return redirect()->route('deforestation.create')
                ->withErrors(['error' => 'No se encontraron resultados para este análisis.']);
        }

        // Si fue una sola parcela, ir a la vista individual; si fueron varias, a la comparativa
        if (count($results) === 1) {
            $dataToPass = $results[0];
            return view('deforestation.results', compact('dataToPass'));
        }

        $multiResults = $results;
        return view('deforestation.multi-results', compact('multiResults'));
    }

    /**
     * Procesa un único polígono de forma sincrónica:
     * Comprueba si la forma ya existe con `ST_Equals`, calcula su área geodésica si falta,
     * y obtiene las alertas anuales contra GFW.
     */
    private function processSinglePolygon(array $feature, array $globalParams, bool $skipActivity = false): array
    {
        $geometryGeoJson = $feature['geometry'];
        $properties      = $feature['properties'] ?? [];

        $externalId    = $properties['id'] ?? null;
        $productorName = $properties['Productor'] ?? $properties['productor'] ?? 'Sin productor';
        $areaFromProps = $properties['Area_Ha'] ?? $properties['area'] ?? 0;

        $producer   = Producer::where('name', $productorName)->first();
        $producerId = $producer ? $producer->id : $globalParams['producer_id'];

        $geometryString = json_encode($geometryGeoJson);

        // Búsqueda espacial de polígono preexistente idéntico
        $existingPolygon = DB::table('polygons')
            ->select('id', 'area_ha', 'name', 'description')
            ->whereRaw("ST_Equals(geometry, ST_SetSRID(ST_GeomFromGeoJSON(?), 4326))", [$geometryString])
            ->first();

        $polygonId = $existingPolygon ? $existingPolygon->id : null;
        $areaHa    = $areaFromProps > 0 ? $areaFromProps : ($existingPolygon ? $existingPolygon->area_ha : 0);

        // Calcular área física en PostGIS sobre el esferoide si no vino en propiedades
        if ($areaHa <= 0 && $geometryString) {
            $areaCalculated = DB::selectOne("
                SELECT ST_Area(ST_SetSRID(ST_GeomFromGeoJSON(?), 4326)::geography) / 10000 as area
            ", [$geometryString]);
            $areaHa = $areaCalculated->area ?? 0;
        }

        $startYear = $globalParams['start_year'];
        $endYear   = $globalParams['end_year'];

        if ($polygonId) {
            $polygon = Polygon::findOrFail($polygonId);

            if ((float) $polygon->area_ha !== (float) $areaHa) {
                $polygon->updateQuietly(['area_ha' => $areaHa]);
                $polygon->refresh();
            }

            $yearlyResults = $polygon->analyzeDeforestationFromGFW(
                $startYear,
                $endYear,
                $globalParams['save_analysis']
            );
        } else {
            $polygon = new Polygon(['area_ha' => $areaHa]);

            $yearlyResults = $this->gfwService->getYearlyStatsForRange(
                $geometryGeoJson,
                range($startYear, $endYear)
            );
            ksort($yearlyResults);
        }

        $totalLossResults = $polygon->buildTotalLossStats($yearlyResults, $startYear, $endYear);

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

        if ($globalParams['save_analysis']) {
            $this->saveSinglePolygonData($dataToPass, $polygonId, $skipActivity);
        }

        return $dataToPass;
    }

    /**
     * Guarda dentro de una transacción el polígono y sus registros de deforestación anuales.
     */
    private function saveSinglePolygonData(array &$dataToPass, $existingId, bool $skipActivity = false)
    {
        try {
            DB::transaction(function () use (&$dataToPass, $existingId, $skipActivity) {
                $polygon = null;

                if ($existingId) {
                    $polygon = Polygon::withTrashed()->findOrFail($existingId);
                    $polygon->updateQuietly([
                        'name'        => $dataToPass['polygon_name'],
                        'description' => $dataToPass['description'],
                        'producer_id' => $dataToPass['producer_id'],
                        'area_ha'     => $dataToPass['polygon_area_ha'],
                    ]);
                } else {
                    $geoJson = $dataToPass['original_geojson'];
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
                                    'source'     => 'deforestation_analysis',
                                    'created_at' => now()->toISOString(),
                                ],
                            ],
                        ],
                        $geoJson,
                        4326,
                        false
                    );
                }

                if (!$existingId) {
                    $polygon->persistYearlyResultsFromBreakdown(
                        $dataToPass['total_loss']['yearlyBreakdown']
                    );
                }

                $dataToPass['polygon_id'] = $polygon->id;

                if (!$skipActivity) {
                    activity()
                        ->causedBy(auth()->user())
                        ->performedOn($polygon)
                        ->withProperties([
                            'start_year'       => $dataToPass['start_year'],
                            'end_year'         => $dataToPass['end_year'],
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
     * Valida los parámetros del formulario de análisis asegurando coherencia temporal y espacial:
     * - Rango de años dentro de los límites de datos satelitales disponibles.
     * - Geometría obligatoria.
     * - Superficie mínima mayor a 0 ha.
     */
    private function validateAnalyzeRequest(Request $request, bool $saveAnalysis)
    {
        $minYear = (int) config('deforestation.min_year');
        $maxYear = (int) config('deforestation.max_year');

        $rules = [
            'start_year'    => "required|integer|min:{$minYear}|max:{$maxYear}",
            'end_year'      => "required|integer|min:{$minYear}|max:{$maxYear}|gte:start_year",
            'geometry'      => 'required|string',
            'area_ha'       => 'required|numeric|min:0.01',
            'description'   => 'nullable|string|max:1000',
            'save_analysis' => 'boolean'
        ];

        if ($saveAnalysis) {
            $geojson = json_decode($request->input('geometry'), true);
            $isMulti = ($geojson['type'] ?? '') === 'FeatureCollection';
            if (!$isMulti) {
                $rules['name']        = 'required|string|min:3|max:255';
                $rules['producer_id'] = 'required|integer|exists:producers,id';
            }
        } else {
            $rules['name'] = 'nullable|string|max:255';
        }

        $messages = [
            'name.required'        => 'El nombre del área es obligatorio cuando se guarda el análisis.',
            'name.min'             => 'El nombre debe tener al menos 3 caracteres.',
            'producer_id.required' => 'Debe seleccionar un productor para guardar el análisis.',
            'producer_id.exists'   => 'El productor seleccionado no es válido.',
            'geometry.required'    => 'Debe dibujar un polígono en el mapa.',
            'area_ha.min'          => 'El área debe ser mayor a 0 hectáreas.',
            'end_year.gte'         => 'El año de fin debe ser mayor o igual al año de inicio.'
        ];

        $request->validate($rules, $messages);
    }

    /**
     * Muestra la vista comparativa multi-predio a partir de una lista de IDs separados por coma.
     */
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
    
    /**
     * Muestra la vista del reporte final de deforestación para un polígono persistido individual.
     */
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

    /**
     * Retorna en formato JSON los análisis anuales de un polígono indexados por año (usado por gráficas Chart.js).
     */
    public function getAnalysisData($polygonId): JsonResponse
    {
        $polygon = Polygon::findOrFail($polygonId);

        return response()->json(
            $polygon->analyses()
                ->orderBy('year')
                ->get()
                ->keyBy('year')
                ->toArray()
        );
    }
    
    public function export($polygonId)
    {
        return response()->json(['message' => 'Export functionality to be implemented']);
    }
    
    /**
     * Genera y descarga el informe técnico en PDF:
     * 
     * Métricas calculadas para la emisión del informe:
     * - Superficie total de la parcela ($polygonArea).
     * - Superficie total deforestada acumulada ($totalDeforestedArea).
     * - Superficie conservada o bosque remanente:
     *   `conservedArea = polygonArea - totalDeforestedArea`
     * - Desglose cronológico anual con porcentaje de pérdida por año.
     * - Formato de hoja A4 vertical renderizado mediante DomPDF.
     */
    public function report(Request $request)
    {
        \Log::info('Datos recibidos para PDF:', ['data' => $request->all()]);
        
        try {
            $rawData    = $request->input('report_data');
            $decoded    = is_string($rawData) ? json_decode($rawData, true) : $rawData;
            $dataToPass = $decoded['dataToPass'] ?? $decoded; 

            if (!$dataToPass || !isset($dataToPass['yearly_results'])) {
                \Log::warning('Estructura de datos inválida:', ['decoded' => $decoded]);
                return redirect()->back()
                    ->withErrors(['error' => 'La estructura de los datos no es válida para generar el reporte.']);
            }

            $polygonArea   = (float) ($dataToPass['polygon_area_ha'] ?? 0);
            $totalLossData = $dataToPass['total_loss'] ?? [];

            // Compilar desglose cronológico para la tabla del informe
            $analyses = collect($dataToPass['yearly_results'] ?? [])->map(function($item) use ($polygonArea) {
                $area = (float) ($item['area__ha'] ?? 0);
                return (object)[
                    'year'               => $item['year'],
                    'deforested_area_ha' => $area,
                    'percentage_loss'    => $polygonArea > 0 ? ($area / $polygonArea) * 100 : 0
                ];
            })->sortBy('year')->values();

            // Balance forestal (Área conservada vs Área deforestada)
            $data = [
                'polygon' => (object)[
                    'name'        => $dataToPass['polygon_name'] ?? 'Área sin nombre',
                    'area_ha'     => $polygonArea,
                    'description' => $dataToPass['description'] ?? '',
                ],
                'analyses'            => $analyses,
                'start_year'          => $dataToPass['start_year'] ?? null,
                'end_year'            => $dataToPass['end_year'] ?? null,
                'totalDeforestedArea' => (float) ($totalLossData['totalDeforestedArea'] ?? 0),
                'totalPercentage'     => (float) ($totalLossData['totalPercentage'] ?? 0),
                'conservedArea'       => $polygonArea - (float) ($totalLossData['totalDeforestedArea'] ?? 0),
                'report_date'         => now()->format('d/m/Y H:i:s'),
                'isFromSaved'         => false,
            ];

            $pdf = \PDF::loadView('deforestation.report-pdf', $data)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'defaultFont'          => 'sans-serif',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled'      => true,
                    'chroot'               => public_path(),
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

    /**
     * Ejecuta una reevaluación sincrónica de deforestación para un polígono específico ya existente.
     */
    public function polygon(Request $request)
    {
        $geometryString = $request->input('geometry');
        $startYear      = (int) $request->input('start_year');
        $endYear        = (int) $request->input('end_year');
        $polygonId      = $request->input('id');
        $polygonName    = $request->input('name', 'Área de Estudio');
        $saveAnalysis   = $request->boolean('save_analysis');

        if (preg_match('/^[0-9A-Fa-f]+$/', (string) $geometryString)) {
            $geoJsonRes     = DB::selectOne(
                "SELECT ST_AsGeoJSON(ST_GeomFromWKB(decode(?, 'hex'))) as geojson",
                [$geometryString]
            );
            $geometryString = $geoJsonRes->geojson;
        }

        $geometryGeoJson = json_decode($geometryString, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['geometry' => 'Formato GeoJSON inválido']);
        }

        /** @var Polygon $polygon */
        $polygon = Polygon::withTrashed()->findOrFail($polygonId);
        $areaHa  = (float) $polygon->area_ha;

        $beforeYears = $polygon->deforestations()->pluck('year')->all();

        // Consulta contra GFW reutilizando años locales
        $yearlyResults = $polygon->analyzeDeforestationFromGFW($startYear, $endYear, $saveAnalysis);

        $totalLossResults = $polygon->buildTotalLossStats(
            $yearlyResults, $startYear, $endYear
        );

        if ($saveAnalysis) {
            $afterYears = $polygon->deforestations()->pluck('year')->all();
            $newYears   = array_values(array_diff($afterYears, $beforeYears));

            if (!empty($newYears)) {
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($polygon)
                    ->withProperties([
                        'start_year'       => $startYear,
                        'end_year'         => $endYear,
                        'total_deforested' => $totalLossResults['totalDeforestedArea'],
                        'total_percentage' => $totalLossResults['totalPercentage'],
                        'polygon_updated'  => true,
                        'years_analyzed'   => array_intersect_key(
                            $yearlyResults,
                            array_flip($newYears)
                        ),
                    ])
                    ->event('analyzed')
                    ->log("Nuevo análisis de deforestación completado para el polígono '{$polygon->name}'");
            }

            session()->flash('save_success', 'Los nuevos datos del análisis han sido guardados.');
        }

        $polygonForStats = $polygonId
            ? Polygon::findOrFail($polygonId)
            : (new Polygon(['area_ha' => $areaHa]));

        $totalLossResults = $polygonForStats->buildTotalLossStats(
            $yearlyResults, $startYear, $endYear
        );

        $dataToPass = [
            'polygon_id'       => $polygon->id,
            'analysis_year'    => $endYear,
            'start_year'       => $startYear,
            'end_year'         => $endYear,
            'original_geojson' => $geometryString,
            'type'             => $geometryGeoJson['type'] ?? 'Polygon',
            'geometry'         => $geometryGeoJson['coordinates'][0] ?? [],
            'area__ha'         => max($areaHa, $totalLossResults['totalDeforestedArea']),
            'polygon_area_ha'  => $areaHa,
            'status'           => 'success',
            'polygon_name'     => $polygonName,
            'description'      => $request->input('description', ''),
            'yearly_results'   => $yearlyResults,
            'total_loss'       => $totalLossResults,
        ];

        return view('deforestation.results', compact('dataToPass'));
    }

    /**
     * Compila y estructura los registros históricos guardados en base de datos
     * en el formato requerido por la interfaz de resultados y los gráficos.
     */
    private function buildDataFromPolygon(Polygon $polygon): array
    {
        $analyses = $polygon->analyses->sortBy('year');

        if ($analyses->isEmpty()) {
            throw new \Exception("El polígono '{$polygon->name}' no tiene análisis guardados.");
        }

        $yearlyResults       = [];
        $totalDeforestedArea = 0;
        $startYear           = $analyses->first()->year;
        $endYear             = $analyses->last()->year;

        foreach ($analyses as $analysis) {
            $yearlyResults[$analysis->year] = [
                'area__ha' => $analysis->deforested_area_ha,
                'status'   => 'success',
                'year'     => $analysis->year,
            ];
            $totalDeforestedArea += $analysis->deforested_area_ha;
        }

        $totalPercentage = $polygon->area_ha > 0 ? ($totalDeforestedArea / $polygon->area_ha) * 100 : 0;

        $yearlyBreakdown = [];
        foreach ($analyses as $analysis) {
            $yearlyBreakdown[$analysis->year] = [
                'year'       => $analysis->year,
                'area_ha'    => $analysis->deforested_area_ha,
                'percentage' => $polygon->area_ha > 0 ? ($analysis->deforested_area_ha / $polygon->area_ha) * 100 : 0,
            ];
        }

        return [
            'polygon_id'       => $polygon->id,
            'producer_id'      => $polygon->producer_id,
            'analysis_year'    => $endYear,
            'start_year'       => $startYear,
            'end_year'         => $endYear,
            'original_geojson' => $polygon->getGeometryGeoJson(),
            'type'             => 'Polygon',
            'geometry'         => [],
            'area__ha'         => $totalDeforestedArea,
            'polygon_area_ha'  => $polygon->area_ha,
            'status'           => 'success',
            'polygon_name'     => $polygon->name,
            'description'      => $polygon->description ?? '',
            'yearly_results'   => $yearlyResults,
            'total_loss'       => [
                'totalDeforestedArea' => $totalDeforestedArea,
                'totalPercentage'     => $totalPercentage,
                'validYears'          => $analyses->count(),
                'totalYearsInRange'   => $endYear - $startYear + 1,
                'yearlyBreakdown'     => $yearlyBreakdown,
            ],
            'external_id'      => null,
            'productor_name'   => $polygon->producer ? $polygon->producer->name : 'Sin productor',
        ];
    }

    /**
     * Registra un evento de auditoría agrupado cuando se analizan múltiples predios en simultáneo.
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
                'polygon_ids'      => $polygonIds,
                'count'            => count($polygonIds),
                'start_year'       => $globalParams['start_year'],
                'end_year'         => $globalParams['end_year'],
                'total_area'       => $totalArea,
                'total_deforested' => $totalDeforested,
                'total_percentage' => $totalPercentage,
            ])
            ->event('analyzed_multiple')
            ->log("Análisis de deforestación completado para " . count($polygonIds) . " polígonos");
    }

    /**
     * Registra en la bitácora un análisis individual no guardado en base de datos (simulación).
     */
    private function registerUnsavedAnalysisEvent(array $dataToPass, array $globalParams): void
    {
        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'polygon_name'     => $dataToPass['polygon_name'],
                'polygon_area_ha'  => $dataToPass['polygon_area_ha'],
                'total_deforested' => $dataToPass['total_loss']['totalDeforestedArea'],
                'total_percentage' => $dataToPass['total_loss']['totalPercentage'],
                'start_year'       => $dataToPass['start_year'],
                'end_year'         => $dataToPass['end_year'],
                'original_geojson' => $dataToPass['original_geojson'],
                'saved'            => false,
            ])
            ->event('analyzed')
            ->log("Análisis de deforestación");
    }

    /**
     * Registra en la bitácora un análisis múltiple no guardado.
     */
    private function registerUnsavedMultiAnalysisEvent(array $multiResults, array $globalParams): void
    {
        $totalArea = array_sum(array_column($multiResults, 'polygon_area_ha'));
        $totalDeforested = array_sum(array_map(function($r) {
            return $r['total_loss']['totalDeforestedArea'] ?? 0;
        }, $multiResults));
        $totalPercentage = $totalArea > 0 ? ($totalDeforested / $totalArea) * 100 : 0;

        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'count'            => count($multiResults),
                'start_year'       => $globalParams['start_year'],
                'end_year'         => $globalParams['end_year'],
                'total_area'       => $totalArea,
                'total_deforested' => $totalDeforested,
                'total_percentage' => $totalPercentage,
                'saved'            => false,
            ])
            ->event('analyzed_multiple')
            ->log("Análisis múltiple de " . count($multiResults) . " polígonos (no guardado)");
    }

    /**
     * Sincroniza la bandera booleana `deforested` del polígono.
     */
    private function syncDeforestationDerivedFields(Polygon $polygon): void
    {
        $polygon->refreshDeforestedFlag();
    }
}
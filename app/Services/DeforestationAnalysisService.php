<?php

namespace App\Services;

use App\Models\Polygon;
use App\Models\Producer;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeforestationAnalysisService
{
    public function __construct(
        private readonly GFWService $gfwService,
    ) {}

    // =========================================================================
    // Progreso en cache (mismo patrón que PolygonImportService)
    // =========================================================================

    public function initProgress(string $analysisId, int $total, array $meta = []): void
    {
        Cache::put("analysis_progress:{$analysisId}", [
            'total'            => $total,
            'current'          => 0,
            'status'           => 'processing',
            'feature_statuses' => [],
            'summary'          => [
                'analyzed'    => 0,
                'errors'      => [],
                'polygon_ids' => [],
            ],
            'redirect_url'     => null,
            'meta'             => $meta,
            'started_at'       => now()->toISOString(),
        ], now()->addHours(6));
    }

    public function recordFeatureResult(
        string  $analysisId,
        int     $index,
        string  $name,
        string  $status,
        ?array  $payload = null,
        ?string $errorMessage = null,
    ): void {
        $key  = "analysis_progress:{$analysisId}";
        $lock = Cache::lock("analysis_progress_lock:{$analysisId}", 10);

        $lock->block(5, function () use ($key, $index, $name, $status, $payload, $errorMessage) {
            $state = Cache::get($key);
            if (!$state) return;

            $state['feature_statuses'][$index] = [
                'name'          => $name,
                'status'        => $status,
                'deforested_ha' => $payload['total_deforested'] ?? null,
                'polygon_id'    => $payload['polygon_id'] ?? null,
                'error'         => $errorMessage,
            ];

            $state['current'] = count($state['feature_statuses']);

            if ($status === 'analyzed') {
                $state['summary']['analyzed']++;
                if (!empty($payload['polygon_id'])) {
                    $state['summary']['polygon_ids'][] = $payload['polygon_id'];
                }
            } else {
                $state['summary']['errors'][] = $name;
            }

            Cache::put($key, $state, now()->addHours(6));
        });
    }

    public function getProgress(string $analysisId): ?array
    {
        return Cache::get("analysis_progress:{$analysisId}");
    }

    // =========================================================================
    // Lógica de análisis (extraída del controlador)
    // =========================================================================

    /**
     * Procesa un único feature GeoJSON.
     * Idéntico al antiguo DeforestationController::processSinglePolygon().
     */
    public function analyzeFeature(
        array $feature,
        array $globalParams,
        bool  $skipActivity = false,
    ): array {
        $geometryGeoJson = $feature['geometry'];
        $properties      = $feature['properties'] ?? [];

        $externalId     = $properties['id'] ?? null;
        $productorName  = $properties['Productor'] ?? $properties['productor'] ?? 'Sin productor';
        $areaFromProps  = $properties['Area_Ha'] ?? $properties['area'] ?? 0;

        $producer   = Producer::where('name', $productorName)->first();
        $producerId = $producer ? $producer->id : $globalParams['producer_id'];

        $geometryString = json_encode($geometryGeoJson);

        $existingPolygon = DB::table('polygons')
            ->select('id', 'area_ha', 'name', 'description')
            ->whereRaw("ST_Equals(geometry, ST_SetSRID(ST_GeomFromGeoJSON(?), 4326))", [$geometryString])
            ->first();

        $polygonId = $existingPolygon ? $existingPolygon->id : null;
        $areaHa    = $areaFromProps > 0 ? $areaFromProps : ($existingPolygon ? $existingPolygon->area_ha : 0);

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
     * Guarda el polígono y sus análisis.
     * Idéntico al antiguo DeforestationController::saveSinglePolygonData(),
     * pero usando `auth()->setUser()` en lugar de `auth()->user()` directo
     * (funciona tanto en request HTTP como en Job).
     */
    private function saveSinglePolygonData(array &$dataToPass, $existingId, bool $skipActivity = false): void
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
                        $dataToPass['original_geojson'],
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
        } catch (\Exception $e) {
            Log::error('Error guardando polígono: ' . $e->getMessage());
            $dataToPass['save_error'] = 'Error al guardar: ' . $e->getMessage();
        }
    }

    /**
     * Registra un evento de análisis no guardado (individual).
     * Extraído del controlador.
     */
    public function registerUnsavedAnalysisEvent(array $dataToPass, array $globalParams, ?User $causer = null): void
    {
        activity()
            ->causedBy($causer ?? auth()->user())
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

        // =========================================================================
    // Persistencia de resultados para vistas async (save_analysis = false)
    // =========================================================================

    public function saveResult(string $analysisId, int $index, array $dataToPass): void
    {
        Cache::put(
            "analysis_result:{$analysisId}:{$index}",
            $dataToPass,
            now()->addHours(6)
        );
    }

    public function getAllResults(string $analysisId): array
    {
        $state = $this->getProgress($analysisId);
        if (!$state) return [];

        $results = [];
        for ($i = 0; $i < $state['total']; $i++) {
            $r = Cache::get("analysis_result:{$analysisId}:{$i}");
            if ($r !== null) {
                $results[$i] = $r;
            }
        }
        ksort($results);
        return array_values($results);
    }

    // =========================================================================
    // Finalización: calcula redirect_url y marca status='done'
    // =========================================================================

    public function finalizeProgress(string $analysisId): void
    {
        $key   = "analysis_progress:{$analysisId}";
        $state = Cache::get($key);
        if (!$state) return;

        $state['status']       = 'done';
        $state['redirect_url'] = $this->buildRedirectUrl($state, $analysisId);
        $state['finished_at']  = now()->toISOString();

        Cache::put($key, $state, now()->addHours(6));
    }

    private function buildRedirectUrl(array $state, string $analysisId): string
    {
        $polygonIds = $state['summary']['polygon_ids'] ?? [];
        $hasSave    = $state['meta']['has_save'] ?? false;
        $count      = count($polygonIds);

        if ($hasSave && $count === 1) {
            return route('deforestation.results', $polygonIds[0]);
        }
        if ($hasSave && $count > 1) {
            return route('deforestation.multiple-results', [
                'polygon_ids' => implode(',', $polygonIds),
            ]);
        }
        // Sin save: vista async efímera
        return route('deforestation.view-async', $analysisId, false);
    }
}
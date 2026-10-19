<?php

namespace App\Services;

use App\Models\Polygon;
use App\Models\Producer;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de Orquestación para el Análisis de Deforestación
 * 
 * Gestiona el ciclo completo de evaluación de pérdida de cobertura arbórea:
 * 1. Control del estado y avance en tiempo real para análisis masivos o en segundo plano (vía Jobs/Queue).
 * 2. Reconocimiento e identificación de geometrías espaciales duplicadas o preexistentes mediante PostGIS.
 * 3. Ejecución de consultas satelitales a Global Forest Watch (GFW).
 * 4. Persistencia transaccional de Parcelas, productores asociados y registros anuales de deforestación.
 * 5. Generación de enlaces de redirección dinámica según el resultado (monitoreo individual, comparativo o efímero).
 */
class DeforestationAnalysisService
{
    /**
     * Inyecta el cliente del servicio satelital GFW.
     */
    public function __construct(
        private readonly GFWService $gfwService,
    ) {}

    // =========================================================================
    // Seguimiento de Progreso en Caché (Para peticiones asíncronas y barra de avance)
    // =========================================================================

    /**
     * Inicializa la estructura de seguimiento en la memoria caché para una tarea de análisis.
     * 
     * Guarda el número total de parcelas a procesar, el estado inicial 'processing'
     * y los arreglos para compilar los IDs generados, errores y métricas acumuladas.
     * La clave expira a las 6 horas para liberar memoria del servidor.
     *
     * @param string $analysisId Identificador único universal (UUID) de la sesión de análisis
     * @param int    $total      Cantidad total de parcelas/features a procesar
     * @param array  $meta       Metadatos contextuales (ej. si el usuario eligió guardar o solo simular)
     */
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

    /**
     * Registra de forma concurrente el resultado individual del análisis de una parcela.
     * 
     * Implementa un bloqueo atómico (`Cache::lock`) para evitar colisiones o condiciones de carrera
     * cuando múltiples hilos de cola o subprocesos actualizan el progreso al mismo tiempo:
     * - Actualiza el índice procesado y suma al contador de completados.
     * - Si fue exitoso: registra las hectáreas deforestadas y anexa el ID de la Parcela.
     * - Si falló: agrega el nombre de la parcela a la lista de errores para notificación al usuario.
     *
     * @param string      $analysisId   UUID de la sesión de análisis
     * @param int         $index        Posición o índice de la parcela en el archivo origen
     * @param string      $name         Nombre o identificador visible de la parcela
     * @param string      $status       Estado resultante ('analyzed' o 'error')
     * @param array|null  $payload      Datos calculados (total de hectáreas deforestadas, ID en base de datos)
     * @param string|null $errorMessage Mensaje de error si la evaluación satelital o geométrica falló
     */
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

    /**
     * Consulta el estado actual de la barra de progreso desde la caché.
     * Utilizado por el endpoint AJAX de sondeo continuo (polling) desde el navegador web.
     */
    public function getProgress(string $analysisId): ?array
    {
        return Cache::get("analysis_progress:{$analysisId}");
    }

    // =========================================================================
    // Lógica Central de Análisis Geoespacial
    // =========================================================================

    /**
     * Procesa y analiza un Feature individual (geometría + propiedades) de un GeoJSON o Shapefile:
     * 
     * Etapas del flujo:
     * 1. Extracción de propiedades GIS: busca campos como 'Productor', 'Area_Ha', 'id' externo.
     * 2. Vinculación con productor: busca si el productor ya existe en la base de datos por nombre;
     *    si no, utiliza el productor global seleccionado en el formulario.
     * 3. Comprobación de coincidencia espacial exacta:
     *    Usa la función de PostGIS `ST_Equals(geometry, ST_SetSRID(ST_GeomFromGeoJSON(?), 4326))`
     *    para detectar si este mismo Parcela ya fue registrado anteriormente en el sistema.
     * 4. Validación y cálculo de superficie: si el archivo no trae el área especificada,
     *    se calcula en el momento sobre el elipsoide terrestre mediante `ST_Area(...::geography) / 10000`.
     * 5. Consulta de deforestación a GFW:
     *    - Si el Parcela ya existía en BD, llama a su método interno `analyzeDeforestationFromGFW`
     *      (reutilizando años ya guardados y pidiendo a GFW solo los años faltantes).
     *    - Si es un Parcela nuevo, consulta a GFW por todo el rango solicitado.
     * 6. Consolidación de estadísticas: ejecuta `buildTotalLossStats` para obtener áreas acumuladas y porcentajes.
     * 7. Persistencia opcional: si el usuario marcó la opción de guardar, almacena el Parcela y los registros anuales.
     *
     * @param array $feature      Objeto Feature GeoJSON {'type': 'Feature', 'geometry': ..., 'properties': ...}
     * @param array $globalParams Parámetros globales (rango de años, guardar o no, productor por defecto, etc.)
     * @param bool  $skipActivity Si debe omitir la escritura en el log de auditoría
     * @return array              Paquete completo de resultados listos para presentación visual o almacenamiento
     */
    public function analyzeFeature(
        array $feature,
        array $globalParams,
        bool  $skipActivity = false,
    ): array {
        $geometryGeoJson = $feature['geometry'];
        $properties      = $feature['properties'] ?? [];

        // Extraer atributos alfanuméricos provistos por el archivo cartográfico
        $externalId     = $properties['id'] ?? null;
        $productorName  = $properties['Productor'] ?? $properties['productor'] ?? 'Sin productor';
        $areaFromProps  = $properties['Area_Ha'] ?? $properties['area'] ?? 0;

        // Intentar localizar el productor por nombre o recurrir al asignado en el formulario
        $producer   = Producer::where('name', $productorName)->first();
        $producerId = $producer ? $producer->id : $globalParams['producer_id'];

        $geometryString = json_encode($geometryGeoJson);

        // Comprobar con PostGIS si la forma geométrica exacta ya existe en la tabla polygons
        $existingPolygon = DB::table('polygons')
            ->select('id', 'area_ha', 'name', 'description')
            ->whereRaw("ST_Equals(geometry, ST_SetSRID(ST_GeomFromGeoJSON(?), 4326))", [$geometryString])
            ->first();

        $polygonId = $existingPolygon ? $existingPolygon->id : null;
        $areaHa    = $areaFromProps > 0 ? $areaFromProps : ($existingPolygon ? $existingPolygon->area_ha : 0);

        // Si el área no vino en los atributos, calcularla con precisión geodésica PostGIS
        if ($areaHa <= 0 && $geometryString) {
            $areaCalculated = DB::selectOne("
                SELECT ST_Area(ST_SetSRID(ST_GeomFromGeoJSON(?), 4326)::geography) / 10000 as area
            ", [$geometryString]);
            $areaHa = $areaCalculated->area ?? 0;
        }

        $startYear = $globalParams['start_year'];
        $endYear   = $globalParams['end_year'];

        // Analizar deforestación según si es una parcela ya registrada o una nueva simulación
        if ($polygonId) {
            $polygon = Polygon::findOrFail($polygonId);

            // Si el área geométrica cambió, actualizarla
            if ((float) $polygon->area_ha !== (float) $areaHa) {
                $polygon->updateQuietly(['area_ha' => $areaHa]);
                $polygon->refresh();
            }

            // Consulta inteligente contra GFW (reutiliza años en BD y pide faltantes)
            $yearlyResults = $polygon->analyzeDeforestationFromGFW(
                $startYear,
                $endYear,
                $globalParams['save_analysis']
            );
        } else {
            // Parcela no registrado: se crea una instancia temporal para cálculos
            $polygon = new Polygon(['area_ha' => $areaHa]);

            // Consulta directa a GFW para el rango completo
            $yearlyResults = $this->gfwService->getYearlyStatsForRange(
                $geometryGeoJson,
                range($startYear, $endYear)
            );
            ksort($yearlyResults);
        }

        // Construir métricas agregadas (totales, porcentaje global y desglose)
        $totalLossResults = $polygon->buildTotalLossStats($yearlyResults, $startYear, $endYear);

        // Estructura de entrega unificada
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
            'polygon_name'     => $globalParams['polygon_name'] ?? ('Parcela ' . ($externalId ?: uniqid())),
            'description'      => $globalParams['description'] ?? '',
            'yearly_results'   => $yearlyResults,
            'total_loss'       => $totalLossResults,
            'external_id'      => $externalId,
            'productor_name'   => $productorName,
        ];

        // Guardado permanente si fue solicitado por el usuario
        if ($globalParams['save_analysis']) {
            $this->saveSinglePolygonData($dataToPass, $polygonId, $skipActivity);
        }

        return $dataToPass;
    }

    /**
     * Persiste en una transacción segura de base de datos la parcela y sus registros anuales de deforestación:
     * 
     * Procedimiento:
     * 1. Si la parcela ya existía: actualiza su información alfanumérica y superficie.
     * 2. Si es una parcela nueva: la inserta con PostGIS mediante `createWithGeometry`.
     * 3. Registra el desglose anual en la tabla `deforestation` mediante `persistYearlyResultsFromBreakdown`.
     * 4. Genera la entrada en la bitácora de auditoría detallando los años analizados y hectáreas perdidas.
     *
     * @param array     $dataToPass   Paquete de datos del análisis (se pasa por referencia para añadir 'polygon_id' o 'save_error')
     * @param int|null  $existingId   ID de la Parcela preexistente en caso de actualización
     * @param bool      $skipActivity Bandera para evitar llenar la auditoría si es una carga masiva
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

                // Guardar los años satelitales analizados
                if (!$existingId) {
                    $polygon->persistYearlyResultsFromBreakdown(
                        $dataToPass['total_loss']['yearlyBreakdown']
                    );
                }

                $dataToPass['polygon_id'] = $polygon->id;

                // Auditoría detallada de la operación
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
            Log::error('Error guardando Parcela: ' . $e->getMessage());
            $dataToPass['save_error'] = 'Error al guardar: ' . $e->getMessage();
        }
    }

    /**
     * Registra en la bitácora de auditoría un análisis efímero o de simulación
     * (cuando el usuario evalúa una parcela en el mapa pero elige NO guardarla en el inventario).
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
    // Almacenamiento Efímero en Caché (Para vistas asíncronas no guardadas en BD)
    // =========================================================================

    /**
     * Almacena temporalmente el resultado de un análisis en caché
     * para que la vista web pueda representarlo visualmente sin persistirlo en BD.
     */
    public function saveResult(string $analysisId, int $index, array $dataToPass): void
    {
        Cache::put(
            "analysis_result:{$analysisId}:{$index}",
            $dataToPass,
            now()->addHours(6)
        );
    }

    /**
     * Recupera todos los resultados parciales ordenados por índice desde la memoria caché.
     */
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
    // Finalización y Enrutamiento Dinámico
    // =========================================================================

    /**
     * Cierra el seguimiento de la tarea, marca el estado en 'done',
     * estampa la fecha de finalización y calcula a qué URL debe redirigirse el navegador.
     */
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

    /**
     * Determina la URL de destino final según la cantidad de Parcelas y si se guardaron o no:
     * - Si se guardó 1 solo Parcela: redirige a la vista de reporte individual (`deforestation.results`).
     * - Si se guardaron múltiples Parcelas: redirige a la vista comparativa multi-predio (`deforestation.multiple-results`).
     * - Si fue una simulación sin guardar: redirige a la vista temporal de resultados en memoria (`deforestation.view-async`).
     */
    private function buildRedirectUrl(array $state, string $analysisId): string
    {
        $polygonIds = array_values(array_unique($state['summary']['polygon_ids'] ?? []));
        $hasSave    = $state['meta']['has_save'] ?? false;
        $count      = count($polygonIds);

        if ($hasSave && $count === 1) {
            return route('deforestation.results', $polygonIds[0], false);
        }
        if ($hasSave && $count > 1) {
            return route('deforestation.multiple-results', [
                'polygon_ids' => implode(',', $polygonIds),
            ], false);
        }

        return route('deforestation.view-async', $analysisId, false);
    }
}
<?php

namespace App\Services;

use App\Models\Polygon;
use App\Models\Producer;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de Importación Masiva de Parcelas y Archivos GIS
 * 
 * Se encarga de procesar capas cartográficas provenientes de archivos Shapefile (.zip), GeoJSON o KML:
 * 1. Normaliza las propiedades alfanuméricas heterogéneas que vienen dentro de los archivos GIS.
 * 2. Asocia o crea automáticamente los productores agrícolas/forestales vinculados a cada predio.
 * 3. Valida la topología de las geometrías (solo admite Parcelas y MultiParcelas).
 * 4. Inserta las parcelas en PostGIS gestionando transformaciones de proyección espacial (SRID).
 * 5. Dispara opcionalmente el análisis satelital automático de deforestación post-importación.
 * 6. Reporta el avance en tiempo real de manera concurrente (thread-safe) mediante bloqueos de caché.
 */
class PolygonImportService
{
    // =====================================================================
    // Normalización de Atributos Cartográficos (Features)
    // =====================================================================

    /**
     * Extrae y compila las opciones de importación configuradas por el usuario en el formulario web:
     * - parish_id: Parroquia asignada por defecto si el archivo cartográfico no la especifica.
     * - default_producer_id: Productor asignado por defecto para parcelas que vengan sin dueño.
     * - create_missing_producers: Si es verdadero, crea registros nuevos en la tabla `producers` cuando el nombre no exista.
     * - skip_existing: Si es verdadero, omite Parcelas cuyo `external_id` ya exista en la base de datos para evitar duplicados.
     * - producer_field: Nombre de la columna en la tabla de atributos del Shapefile que contiene el nombre del productor (ej. 'Productor', 'OWNER').
     * - analyze_deforestation: Si es verdadero, ejecuta de inmediato la consulta satelital a GFW para cada parcela importada.
     */
    public function buildImportOptions(\Illuminate\Http\Request $request): array
    {
        return [
            'parish_id'                => $request->input('parish_id'),
            'default_producer_id'      => $request->input('default_producer_id'),
            'create_missing_producers' => $request->boolean('create_missing_producers'),
            'skip_existing'            => $request->boolean('skip_existing'),
            'producer_field'           => $request->input('producer_field', 'Productor'),
            'analyze_deforestation'    => $request->boolean('analyze_deforestation'),
        ];
    }

    /**
     * Normaliza un Feature leído directamente de un archivo GeoJSON o Shapefile crudo:
     * Inspecciona la tabla de atributos (`properties`) buscando variaciones comunes de nomenclatura
     * para el área ('Area_Ha', 'area_ha', 'area') y extrae el identificador externo y el productor.
     *
     * @param array  $feature       Objeto Feature crudo
     * @param string $producerField Nombre de la columna donde reside el nombre del productor
     * @return array
     */
    public function normalizeRawFeature(array $feature, string $producerField): array
    {
        $props = $feature['properties'] ?? [];

        return [
            'geometry'       => $feature['geometry'] ?? null,
            'external_id'    => $props['id'] ?? null,
            'name'           => $props['name'] ?? null,
            'description'    => $props['description'] ?? null,
            'area_ha'        => $props['Area_Ha'] ?? $props['area_ha'] ?? $props['area'] ?? null,
            'producer_name'  => trim($props[$producerField] ?? ''),
            'producer_id'    => null,
            'parish_id'      => null,
            'raw_properties' => $props,
        ];
    }

    /**
     * Normaliza la estructura enviada desde la interfaz web (tabla de previsualización interactiva):
     * Permite que el usuario edite o corrija nombres de parcelas y productores en pantalla antes de confirmar la importación final.
     */
    public function normalizeFrontendFeature(array $featureData): array
    {
        $geometry = null;
        if (!empty($featureData['geometry'])) {
            $geometry = is_string($featureData['geometry'])
                ? json_decode($featureData['geometry'], true)
                : $featureData['geometry'];
        }

        return [
            'geometry'       => $geometry,
            'external_id'    => $featureData['id'] ?? null,
            'name'           => $featureData['name'] ?? null,
            'description'    => $featureData['description'] ?? null,
            'area_ha'        => $featureData['area_ha'] ?? null,
            'producer_name'  => trim($featureData['producer_name'] ?? ''),
            'producer_id'    => $featureData['producer_id'] ?? null,
            'parish_id'      => $featureData['parish_id'] ?? null,
            'raw_properties' => $featureData,
        ];
    }

    // =====================================================================
    // Procesamiento Individual de Entidades Cartográficas
    // =====================================================================

    /**
     * Importa una sola parcela/feature hacia la base de datos espacial.
     * Es invocado tanto en procesos sincrónicos como por el trabajo en cola `ImportPolygonJob`.
     * 
     * Secuencia operativa:
     * 1. Suplantación contextual de usuario: asume la identidad del usuario que inició la carga
     *    para que los registros de auditoría atribuyan correctamente la autoría de cada parcela.
     * 2. Validación geométrica: rechaza elementos sin coordenadas o tipos incompatibles (puntos, líneas).
     * 3. Prevención de duplicados: si la opción `skip_existing` está activa y ya existe el `external_id`, omite la inserción.
     * 4. Resolución del productor: busca por coincidencia fonética/texto o inserta un nuevo productor si no existía.
     * 5. Inserción PostGIS: ejecuta `Polygon::createWithGeometry` proyectando las coordenadas al estándar WGS84.
     * 6. Cálculo de área: si el archivo no la definía, calcula la superficie real sobre el elipsoide terrestre.
     * 7. Evaluación satelital opcional: si se activó el monitoreo, consulta inmediatamente las alertas de deforestación a GFW.
     * 8. Actualización de progreso: registra el estado de la parcela de manera segura en caché mediante bloqueos.
     *
     * @param array      $feature       Datos del feature cartográfico normalizado
     * @param int        $index         Índice secuencial del elemento dentro del lote
     * @param int        $srid          Código EPSG/SRID de proyección del archivo origen (ej. 4326, 32619, 2202)
     * @param array      $options       Opciones de importación (productores, saltar duplicados, etc.)
     * @param string     $importId      UUID de seguimiento de la importación
     * @param int        $totalFeatures Número total de elementos en el lote
     * @param User|null  $causer        Usuario que disparó la operación
     * @throws \Throwable
     */
    public function importSingleFeature(
        array  $feature,
        int    $index,
        int    $srid,
        array  $options,
        string $importId,
        int    $totalFeatures,
        ?User  $causer = null,
    ): void {
        $featureName = $feature['name'] ?? "Feature #{$index}";

        // Asignar el usuario causante para garantizar la atribución en Spatie ActivityLog
        $previousUser = auth()->user();
        if ($causer) {
            auth()->setUser($causer);
        }

        try {
            $geometry = $feature['geometry'];
            if (empty($geometry) || empty($geometry['type'])) {
                $this->recordFeatureResult($importId, $index, $featureName, 'error',
                    errorMessage: "Geometría inválida");
                return;
            }
            if (!in_array($geometry['type'], ['Polygon', 'MultiPolygon'], true)) {
                $this->recordFeatureResult($importId, $index, $featureName, 'error',
                    errorMessage: "Tipo no soportado: {$geometry['type']}");
                return;
            }

            // Omitir si ya existe el identificador catastral o externo
            $externalId = $feature['external_id'];
            if ($externalId && $options['skip_existing']
                && Polygon::withTrashed()->where('external_id', $externalId)->exists()) {
                $this->recordFeatureResult($importId, $index, $featureName, 'skipped');
                return;
            }

            // Resolver ID del productor (búsqueda inteligente o creación bajo demanda)
            $producerId = $this->resolveProducerId(
                $feature['producer_id'],
                $feature['producer_name'],
                $options['default_producer_id'],
                $options['create_missing_producers']
            );

            $data = [
                'external_id'   => $externalId,
                'name'          => $featureName,
                'description'   => $feature['description'] ?? null,
                'producer_id'   => $producerId,
                'parish_id'     => $feature['parish_id'] ?? $options['parish_id'],
                'area_ha'       => $feature['area_ha'] ?? null,
                'is_active'     => true,
                'location_data' => [
                    'imported_from'       => 'geojson',
                    'original_properties' => $feature['raw_properties'],
                    'external_id'         => $externalId,
                ],
            ];

            // Inserción PostGIS con transformación de coordenadas
            $polygon = Polygon::createWithGeometry($data, json_encode($geometry), $srid, true, $causer);

            // Si el archivo no traía el área, calcularla mediante PostGIS
            if (is_null($data['area_ha'])) {
                $polygon->recalculateGeometryStats();
            } else {
                $polygon->updateQuietly(['area_ha' => $data['area_ha']]);
            }

            $deforestedHa = null;
            $status       = 'success';

            // Análisis satelital inmediato post-importación si fue solicitado
            if (!empty($options['analyze_deforestation'])) {
                try {
                    $startYear = (int) config('deforestation.import_default_start_year');
                    $endYear   = (int) config('deforestation.import_default_end_year');

                    $yearly       = $polygon->analyzeDeforestationFromGFW($startYear, $endYear);
                    $deforestedHa = array_sum(array_column($yearly, 'area__ha'));
                    $status       = 'analyzed';
                } catch (\Throwable $e) {
                    Log::warning("Análisis post-importación falló para Parcela {$polygon->id}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->recordFeatureResult($importId, $index, $featureName, $status, $deforestedHa);

        } catch (\Illuminate\Database\QueryException $e) {
            // Manejar violaciones de unicidad en base de datos (PostgreSQL 23505)
            $status = $this->isUniqueViolation($e) ? 'duplicated' : 'error';
            $this->recordFeatureResult($importId, $index, $featureName, $status,
                errorMessage: $e->getMessage());
            throw $e;

        } catch (\Throwable $e) {
            $this->recordFeatureResult($importId, $index, $featureName, 'error',
                errorMessage: $e->getMessage());
            throw $e;

        } finally {
            // Restaurar el contexto del usuario autenticado original
            if ($causer) {
                auth()->forgetUser();
                if ($previousUser) {
                    auth()->setUser($previousUser);
                }
            }
        }
    }

    // =====================================================================
    // Progreso Seguro en Caché Con Bloqueos Atómicos (Concurrency-Safe)
    // =====================================================================

    /**
     * Crea la entrada de seguimiento inicial para el monitoreo de la barra de progreso en vivo.
     */
    public function initImportProgress(string $importId, int $total): void
    {
        Cache::put("import_progress:{$importId}", [
            'total'            => $total,
            'current'          => 0,
            'status'           => 'processing',
            'feature_statuses' => [],
            'summary'          => [
                'imported'   => 0,
                'skipped'    => 0,
                'duplicated' => [],
                'errors'     => [],
                'analyzed'   => 0,
            ],
            'started_at'       => now()->toISOString(),
        ], now()->addHours(6));
    }

    /**
     * Actualiza el progreso de forma atómica mediante `Cache::lock`.
     * 
     * Razón de diseño:
     * Si múltiples subprocesos o colas trabajan en paralelo importando Parcelas del mismo archivo,
     * un incremento convencional de lectura-modificación-escritura perdería datos por condiciones de carrera.
     * El bloqueo temporal garantiza la integridad de los contadores y listas de errores.
     *
     * @param string      $importId     UUID de la importación
     * @param int         $index        Índice de la entidad procesada
     * @param string      $name         Nombre visible de la entidad
     * @param string      $status       Estado final ('success', 'analyzed', 'skipped', 'duplicated', 'error')
     * @param float|null  $deforestedHa Hectáreas deforestadas encontradas si se analizó
     * @param string|null $errorMessage Detalle del fallo en caso de error
     */
    public function recordFeatureResult(
        string  $importId,
        int     $index,
        string  $name,
        string  $status,
        ?float  $deforestedHa = null,
        ?string $errorMessage = null,
    ): void {
        $key  = "import_progress:{$importId}";
        $lock = Cache::lock("import_progress_lock:{$importId}", 10);

        $lock->block(5, function () use ($key, $index, $name, $status, $deforestedHa, $errorMessage) {
            $state = Cache::get($key);
            if (!$state) return;

            $state['feature_statuses'][$index] = [
                'name'          => $name,
                'status'        => $status,
                'deforested_ha' => $deforestedHa,
                'error'         => $errorMessage,
            ];

            // Cantidad de elementos evaluados hasta el momento
            $state['current'] = count($state['feature_statuses']);

            // Incrementar contadores del resumen según el resultado
            match ($status) {
                'success'    => $state['summary']['imported']++,
                'analyzed'   => $state['summary']['analyzed']++,
                'skipped'    => $state['summary']['skipped']++,
                'duplicated' => $state['summary']['duplicated'][] = $name,
                'error'      => $state['summary']['errors'][]     = $name,
                default      => null,
            };

            Cache::put($key, $state, now()->addHours(6));
        });
    }

    /**
     * Marca la importación como finalizada ('done' o 'failed') registrando la marca de tiempo de cierre.
     */
    public function finalizeImport(string $importId, string $status = 'done'): void
    {
        $key   = "import_progress:{$importId}";
        $state = Cache::get($key);
        if ($state) {
            $state['status']      = $status;
            $state['finished_at'] = now()->toISOString();
            Cache::put($key, $state, now()->addHours(6));
        }
    }

    // =====================================================================
    // Métodos Auxiliares de Resolución y Validación
    // =====================================================================

    /**
     * Resuelve qué productor debe vincularse a la parcela:
     * 1. Si ya se proporcionó un ID numérico válido, lo utiliza directamente.
     * 2. Si viene un texto con el nombre del productor (ej. "Juan Pérez"), realiza una búsqueda
     *    insensible a mayúsculas/minúsculas concatenando `name` y `lastname`.
     * 3. Si no existe pero `$createMissing` está habilitado, separa el nombre en nombre y apellido
     *    e inserta un nuevo productor activo en la base de datos.
     * 4. En cualquier otro caso, recurre al productor predeterminado del formulario.
     */
    private function resolveProducerId(
        ?int $producerId,
        string $producerName,
        ?int $defaultProducerId,
        bool $createMissing
    ): ?int {
        if ($producerId) return $producerId;

        if ($producerName !== '') {
            $producer = Producer::whereRaw(
                'LOWER(name || \' \' || lastname) = ?',
                [strtolower($producerName)]
            )->first();

            if ($producer) return $producer->id;

            if ($createMissing) {
                [$firstName, $lastName] = array_pad(explode(' ', $producerName, 2), 2, '');

                return Producer::create([
                    'name'        => $firstName,
                    'lastname'    => $lastName,
                    'cedula'      => null,
                    'cedula_type' => 'V',
                    'code'        => null,
                    'is_active'   => true,
                ])->id;
            }
        }

        return $defaultProducerId;
    }

    /**
     * Comprueba si una excepción SQL corresponde a la violación de una restricción UNIQUE en PostgreSQL (código 23505).
     */
    private function isUniqueViolation(\Illuminate\Database\QueryException $e): bool
    {
        return $e->getCode() === '23505' || str_contains($e->getMessage(), '23505');
    }

    /**
     * Construye un resumen en texto enriquecido con formato HTML para notificar al usuario
     * cuántos Parcelas fueron importados, analizados, omitidos o si ocurrieron duplicados.
     */
    public function buildImportSummary(array $result): string
    {
        $parts = [];
        if ($result['imported'])         $parts[] = "<strong>{$result['imported']}</strong> importado(s)";
        if (!empty($result['analyzed'])) $parts[] = "<strong>{$result['analyzed']}</strong> analizado(s)";
        if ($result['skipped'])          $parts[] = "<strong>{$result['skipped']}</strong> omitido(s)";
        if (!empty($result['duplicated'])) $parts[] = "<strong>" . count($result['duplicated']) . "</strong> duplicado(s)";
        if (!empty($result['errors']))     $parts[] = "<strong>" . count($result['errors']) . "</strong> error(es)";
        return empty($parts) ? 'Sin cambios.' : implode(' · ', $parts);
    }
}
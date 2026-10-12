<?php

namespace App\Services;

use App\Models\Polygon;
use App\Models\Producer;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PolygonImportService
{
    // =====================================================================
    // Normalización de features (sin cambios respecto a la versión anterior)
    // =====================================================================

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
    // Import de UN feature (público, lo llama ImportPolygonJob)
    // =====================================================================

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

        // Fingimos el login para que el trait de activity atribuya al usuario.
        // Se limpia al final (incluso si hay excepción).
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

            $externalId = $feature['external_id'];
            if ($externalId && $options['skip_existing']
                && Polygon::withTrashed()->where('external_id', $externalId)->exists()) {
                $this->recordFeatureResult($importId, $index, $featureName, 'skipped');
                return;
            }

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

            $polygon = Polygon::createWithGeometry($data, json_encode($geometry), $srid, true, $causer);

            if (is_null($data['area_ha'])) {
                $polygon->recalculateGeometryStats();
            } else {
                $polygon->updateQuietly(['area_ha' => $data['area_ha']]);
            }

            $deforestedHa = null;
            $status       = 'success';

            if (!empty($options['analyze_deforestation'])) {
                try {
                    $startYear = (int) config('deforestation.import_default_start_year');
                    $endYear   = (int) config('deforestation.import_default_end_year');

                    $yearly       = $polygon->analyzeDeforestationFromGFW($startYear, $endYear);
                    $deforestedHa = array_sum(array_column($yearly, 'area__ha'));
                    $status       = 'analyzed';
                } catch (\Throwable $e) {
                    Log::warning("Análisis post-importación falló para polígono {$polygon->id}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->recordFeatureResult($importId, $index, $featureName, $status, $deforestedHa);

        } catch (\Illuminate\Database\QueryException $e) {
            $status = $this->isUniqueViolation($e) ? 'duplicated' : 'error';
            $this->recordFeatureResult($importId, $index, $featureName, $status,
                errorMessage: $e->getMessage());
            throw $e;

        } catch (\Throwable $e) {
            $this->recordFeatureResult($importId, $index, $featureName, 'error',
                errorMessage: $e->getMessage());
            throw $e;

        } finally {
            if ($causer) {
                auth()->forgetUser();
                if ($previousUser) {
                    auth()->setUser($previousUser);
                }
            }
        }
    }

    // =====================================================================
    // Progreso en cache — thread-safe con lock
    // =====================================================================

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
     * Actualiza el progreso de un feature de forma atómica con Cache::lock.
     * Sin esto, con múltiples jobs en paralelo, dos updates simultáneos
     * pueden perderse por read-modify-write.
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

            // 'current' = cantidad de features ya procesados
            $state['current'] = count($state['feature_statuses']);

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
    // Helpers privados
    // =====================================================================

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

    private function isUniqueViolation(\Illuminate\Database\QueryException $e): bool
    {
        return $e->getCode() === '23505' || str_contains($e->getMessage(), '23505');
    }

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
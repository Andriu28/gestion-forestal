<?php

namespace App\Services;

use App\Models\Polygon;
use App\Models\Producer;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PolygonImportService
{
    /**
     * Construye las opciones comunes desde el request.
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
     * Normaliza un Feature GeoJSON crudo (de archivo).
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
     * Normaliza un Feature del frontend (geometry como string JSON).
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

    /**
     * Procesa una lista de features ya normalizados.
     */
    public function processImportFeatures(
        array $features,
        int $srid,
        array $options,
        ?string $importId = null,
        ?User $causer = null,
    ): array {
        $imported   = 0;
        $skipped    = 0;
        $duplicated = [];
        $errors     = [];
        $analyzed   = 0;

        set_time_limit(0);

        foreach ($features as $index => $feature) {
            try {
                $wasAnalyzed = false;
                $this->importSingleFeature(
                    $feature, $index, $srid, $options,
                    $imported, $skipped, $wasAnalyzed, $importId,
                    $causer,
                );
                if ($wasAnalyzed) {
                    $analyzed++;
                }
            } catch (\Illuminate\Database\QueryException $e) {
                if ($this->isUniqueViolation($e)) {
                    $duplicated[] = $feature['external_id'] ?? "#{$index}";
                } else {
                    Log::error('Error de BD importando feature', [
                        'index' => $index, 'error' => $e->getMessage(),
                    ]);
                    $errors[] = "Feature #{$index}: error de base de datos";
                }
            } catch (\Throwable $e) {
                Log::error('Error inesperado importando feature', [
                    'index' => $index, 'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $errors[] = "Feature #{$index}: " . $e->getMessage();
            }
        }

        $this->finishImportProgress($importId, 'done');

        return compact('imported', 'skipped', 'duplicated', 'errors', 'analyzed');
    }

    private function importSingleFeature(
        array $feature,
        int $index,
        int $srid,
        array $options,
        int &$imported,
        int &$skipped,
        bool &$wasAnalyzed,
        ?string $importId = null,
        ?User $causer = null,
    ): void {
        $wasAnalyzed = false;
        $featureName = $feature['name'] ?? "Feature #{$index}";

        try {
            $geometry = $feature['geometry'];
            if (empty($geometry) || empty($geometry['type'])) {
                $this->updateImportProgress($importId, $index, $featureName, 'error');
                throw new \RuntimeException("Geometría inválida en feature #{$index}");
            }
            if (!in_array($geometry['type'], ['Polygon', 'MultiPolygon'], true)) {
                $this->updateImportProgress($importId, $index, $featureName, 'error');
                throw new \RuntimeException("Tipo no soportado en feature #{$index}: {$geometry['type']}");
            }

            $externalId = $feature['external_id'];
            if ($externalId && $options['skip_existing']
                && Polygon::withTrashed()->where('external_id', $externalId)->exists()) {
                $skipped++;
                $this->updateImportProgress($importId, $index, $featureName, 'skipped');
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
            if (!empty($options['analyze_deforestation'])) {
                try {
                    $startYear = (int) config('deforestation.import_default_start_year');
                    $endYear   = (int) config('deforestation.import_default_end_year');

                    $yearly       = $polygon->analyzeDeforestationFromGFW($startYear, $endYear);
                    $wasAnalyzed  = true;
                    $deforestedHa = array_sum(array_column($yearly, 'area__ha'));
                } catch (\Throwable $e) {
                    Log::warning("Análisis post-importación falló para polígono {$polygon->id}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $imported++;
            $this->updateImportProgress(
                $importId,
                $index,
                $featureName,
                $wasAnalyzed ? 'analyzed' : 'success',
                $deforestedHa
            );

        } catch (\Illuminate\Database\QueryException $e) {
            if ($this->isUniqueViolation($e)) {
                $this->updateImportProgress($importId, $index, $featureName, 'duplicated');
                throw $e;
            }
            $this->updateImportProgress($importId, $index, $featureName, 'error');
            throw $e;
        } catch (\Throwable $e) {
            $this->updateImportProgress($importId, $index, $featureName, 'error');
            throw $e;
        }
    }

    private function resolveProducerId(
        ?int $producerId,
        string $producerName,
        ?int $defaultProducerId,
        bool $createMissing
    ): ?int {
        if ($producerId) {
            return $producerId;
        }

        if ($producerName !== '') {
            $producer = Producer::whereRaw(
                'LOWER(name || \' \' || lastname) = ?',
                [strtolower($producerName)]
            )->first();

            if ($producer) {
                return $producer->id;
            }

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

        if ($result['imported'] > 0) {
            $parts[] = "<strong>{$result['imported']}</strong> importado" . ($result['imported'] === 1 ? '' : 's');
        }
        if (!empty($result['analyzed'])) {
            $parts[] = "<strong>{$result['analyzed']}</strong> analizado" . ($result['analyzed'] === 1 ? '' : 's');
        }
        if ($result['skipped'] > 0) {
            $parts[] = "<strong>{$result['skipped']}</strong> omitido" . ($result['skipped'] === 1 ? '' : 's');
        }
        if (!empty($result['duplicated'])) {
            $n = count($result['duplicated']);
            $parts[] = "<strong>{$n}</strong> duplicado" . ($n === 1 ? '' : 's');
        }
        if (!empty($result['errors'])) {
            $n = count($result['errors']);
            $parts[] = "<strong>{$n}</strong> error" . ($n === 1 ? '' : 'es');
        }

        return empty($parts) ? 'Sin cambios.' : implode(' · ', $parts);
    }

    // =====================================================================
    // Progreso en cache
    // =====================================================================

    public function initImportProgress(?string $importId, int $total): void
    {
        if (!$importId) return;

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
        ], now()->addHour());
    }

    public function updateImportProgress(
        ?string $importId,
        int $featureIndex,
        string $featureName,
        string $status,
        ?float $deforestedHa = null
    ): void {
        if (!$importId) return;

        $key   = "import_progress:{$importId}";
        $state = Cache::get($key);
        if (!$state) return;

        $state['current'] = $featureIndex + 1;
        $state['feature_statuses'][$featureIndex] = [
            'name'          => $featureName,
            'status'        => $status,
            'deforested_ha' => $deforestedHa,
        ];

        match ($status) {
            'success'    => $state['summary']['imported']++,
            'analyzed'   => $state['summary']['analyzed']++,
            'skipped'    => $state['summary']['skipped']++,
            'duplicated' => $state['summary']['duplicated'][] = $featureName,
            'error'      => $state['summary']['errors'][]     = $featureName,
            default      => null,
        };

        Cache::put($key, $state, now()->addHour());
    }

    public function finishImportProgress(?string $importId, string $status = 'done'): void
    {
        if (!$importId) return;

        $key   = "import_progress:{$importId}";
        $state = Cache::get($key);
        if ($state) {
            $state['status']      = $status;
            $state['finished_at'] = now()->toISOString();
            Cache::put($key, $state, now()->addHour());
        }
    }
}
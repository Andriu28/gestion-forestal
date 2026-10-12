<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\DeforestationAnalysisService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeDeforestationJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;      // 5 min por feature
    public int $tries   = 2;
    public array $backoff = [30, 120];

    public function __construct(
        public readonly array  $feature,
        public readonly int    $index,
        public readonly array  $globalParams,
        public readonly string $analysisId,
        public readonly int    $totalFeatures,
        public readonly ?int   $causerId = null,
    ) {}

    public function handle(DeforestationAnalysisService $service): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $causer = $this->causerId ? User::find($this->causerId) : null;

        // Mismo patrón que ImportPolygonJob: impersonamos al usuario para
        // que `activity()` atribuya correctamente durante el análisis.
        $previousUser = auth()->user();
        if ($causer) {
            auth()->setUser($causer);
        }

        try {
            $featureName = $this->feature['properties']['Productor']
                ?? $this->feature['properties']['productor']
                ?? $this->feature['properties']['name']
                ?? "Feature #{$this->index}";

            $dataToPass = $service->analyzeFeature(
                $this->feature,
                $this->globalParams,
                false // no skipear activity
            );

            // Guardar el resultado completo en cache para la vista async
            $service->saveResult($this->analysisId, $this->index, $dataToPass);

            // Si NO se guardó, registrar evento de "análisis no guardado"
            if (empty($this->globalParams['save_analysis'])) {
                $service->registerUnsavedAnalysisEvent(
                    $dataToPass,
                    $this->globalParams,
                    $causer
                );
            }

            $service->recordFeatureResult(
                analysisId: $this->analysisId,
                index:      $this->index,
                name:       $featureName,
                status:     'analyzed',
                payload:    [
                    'polygon_id'        => $dataToPass['polygon_id'] ?? null,
                    'total_deforested'  => $dataToPass['total_loss']['totalDeforestedArea'] ?? 0,
                ],
            );

        } catch (\Throwable $e) {
            $service->recordFeatureResult(
                analysisId:   $this->analysisId,
                index:        $this->index,
                name:         $this->feature['properties']['Productor'] ?? "Feature #{$this->index}",
                status:       'error',
                errorMessage: $e->getMessage(),
            );
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

    public function failed(\Throwable $exception): void
    {
        Log::error("AnalyzeDeforestationJob failed [{$this->analysisId}][#{$this->index}]: "
            . $exception->getMessage());

        app(DeforestationAnalysisService::class)->recordFeatureResult(
            analysisId:   $this->analysisId,
            index:        $this->index,
            name:         $this->feature['properties']['Productor'] ?? "Feature #{$this->index}",
            status:       'error',
            errorMessage: $exception->getMessage(),
        );
    }
}
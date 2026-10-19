<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\PolygonImportService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportPolygonJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 min máximo por Parcela (suficiente)
    public int $tries   = 2;
    public array $backoff = [30, 120];

    public function __construct(
        public readonly array  $feature,
        public readonly int    $index,
        public readonly int    $srid,
        public readonly array  $options,
        public readonly string $importId,
        public readonly int    $totalFeatures,
        public readonly ?int   $causerId = null,
    ) {}

    public function handle(PolygonImportService $service): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $causer = $this->causerId ? User::find($this->causerId) : null;

        $service->importSingleFeature(
            feature:       $this->feature,
            index:         $this->index,
            srid:          $this->srid,
            options:       $this->options,
            importId:      $this->importId,
            totalFeatures: $this->totalFeatures,
            causer:        $causer,
        );
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ImportPolygonJob failed [{$this->importId}][#{$this->index}]: "
            . $exception->getMessage());

        app(PolygonImportService::class)->recordFeatureResult(
            importId:  $this->importId,
            index:     $this->index,
            name:      $this->feature['name'] ?? "Feature #{$this->index}",
            status:    'error',
            errorMessage: $exception->getMessage(),
        );
    }
}
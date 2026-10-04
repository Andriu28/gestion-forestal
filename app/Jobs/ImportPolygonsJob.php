<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\PolygonImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ImportPolygonsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries   = 1;

    public function __construct(
        public readonly array  $features,
        public readonly int    $srid,
        public readonly array  $options,
        public readonly string $importId,
        public readonly ?int   $causerId = null,
    ) {}

    public function handle(PolygonImportService $service): void
    {
        // Resolvemos el causer AQUÍ, no en el constructor, por si el usuario
        // cambió entre encolar y ejecutar. Si no existe, causer = null.
        $causer = $this->causerId ? User::find($this->causerId) : null;

        $service->processImportFeatures(
            $this->features,
            $this->srid,
            $this->options,
            $this->importId,
            $causer,
        );
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ImportPolygonsJob failed [{$this->importId}]: " . $exception->getMessage());

        $key   = "import_progress:{$this->importId}";
        $state = Cache::get($key) ?? [
            'total'            => count($this->features),
            'current'          => 0,
            'feature_statuses' => [],
            'summary'          => [
                'imported'   => 0,
                'skipped'    => 0,
                'duplicated' => [],
                'errors'     => [],
                'analyzed'   => 0,
            ],
            'started_at'       => now()->toISOString(),
        ];

        $state['status']              = 'done';
        $state['summary']['errors'][] = 'Error general: ' . $exception->getMessage();
        $state['finished_at']         = now()->toISOString();

        Cache::put($key, $state, now()->addHour());
    }
}
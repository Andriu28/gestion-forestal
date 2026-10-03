<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;

class GFWService
{
    protected Client $client;

    protected int $timeout;
    protected int $connectTimeout;
    protected string $defaultDataset;
    protected string $defaultVersion;

    public function __construct()
    {
        $cfg = config('services.gfw');

        $this->timeout        = $cfg['timeout'] ?? 30;
        $this->connectTimeout = $cfg['connect_timeout'] ?? 10;
        $this->defaultDataset = $cfg['dataset'] ?? 'umd_tree_cover_loss';
        $this->defaultVersion = $cfg['version'] ?? 'latest';

        $this->client = new Client([
            'base_uri'        => rtrim($cfg['base_uri'] ?? '', '/') . '/',
            'timeout'         => $this->timeout,
            'connect_timeout' => $this->connectTimeout,
            'headers'         => [
                'x-api-key'  => $cfg['api_key'],
                'Accept'     => 'application/json',
                'User-Agent' => 'DeforestationAnalysisApp/1.0',
            ],
        ]);
    }

    /**
     * Ejecuta una consulta SQL sincrónica contra un dataset.
     * Devuelve siempre: ['status' => ..., 'data' => [...], 'message' => ...?]
     */
    public function executeQuery(
        string $dataset,
        string $version,
        array $geometry,
        string $sql
    ): array {
        try {
            $response = $this->client->post("dataset/{$dataset}/{$version}/query", [
                'json' => ['geometry' => $geometry, 'sql' => $sql],
            ]);

            $data = json_decode($response->getBody()->getContents(), true) ?? [];

            return [
                'status' => $data['status'] ?? 'success',
                'data'   => $data['data'] ?? [],
            ];
        } catch (ClientException $e) {
            $errorData = json_decode($e->getResponse()->getBody()->getContents(), true);
            $message   = $errorData['message'] ?? 'Error de validación de la API.';
            Log::error("GFW API Error (HTTP {$e->getCode()}): {$message}");

            return ['status' => 'error', 'message' => $message, 'data' => []];
        } catch (\Throwable $e) {
            Log::error('GFW API Error inesperado: ' . $e->getMessage());

            return [
                'status'  => 'error',
                'message' => 'Error inesperado al conectar con la API.',
                'data'    => [],
            ];
        }
    }

    /**
     * Suma de área de pérdida de cobertura arbórea para un polígono y un año.
     */
    public function getZonalStats(array $geometry, int $year): array
    {
        $sql = sprintf(
            "SELECT SUM(area__ha) FROM results WHERE %s__year=%d",
            $this->defaultDataset,
            $year
        );

        return $this->executeQuery(
            $this->defaultDataset,
            $this->defaultVersion,
            $geometry,
            $sql
        );
    }

        /**
     * Consulta las estadísticas anuales de pérdida de cobertura en UNA sola
     * llamada a GFW, usando GROUP BY por año.
     *
     * @param  array  $geometry  GeoJSON geometry
     * @param  int[]  $years     Lista de años (se normalizan y deduplican)
     * @return array<int, array{area__ha: float, status: string, year: int, error?: string}>
     *         Mapa [year => datos]. Los años solicitados que GFW no devuelva
     *         (porque no hubo pérdida) se rellenan con area__ha = 0.
     */
    public function getYearlyStatsForRange(array $geometry, array $years): array
    {
        $years = array_values(array_unique(array_map('intval', $years)));

        if (empty($years)) {
            return [];
        }

        sort($years);
        $minYear = $years[0];
        $maxYear = $years[count($years) - 1];

        $sql = sprintf(
            "SELECT %s__year, SUM(area__ha) AS area__ha FROM results " .
            "WHERE %s__year >= %d AND %s__year <= %d " .
            "GROUP BY %s__year ORDER BY %s__year",
            $this->defaultDataset,
            $this->defaultDataset, $minYear,
            $this->defaultDataset, $maxYear,
            $this->defaultDataset,
            $this->defaultDataset
        );

        // Pre-inicializamos TODOS los años solicitados con cero.
        // Así si GFW no devuelve un año (no hubo pérdida) queda en 0 y el
        // contrato de retorno es consistente para los consumidores.
        $results = [];
        foreach ($years as $year) {
            $results[$year] = [
                'area__ha' => 0.0,
                'status'   => 'success',
                'year'     => $year,
            ];
        }

        try {
            $response = $this->client->post(
                "dataset/{$this->defaultDataset}/{$this->defaultVersion}/query",
                ['json' => ['geometry' => $geometry, 'sql' => $sql]]
            );

            $payload = json_decode($response->getBody()->getContents(), true) ?? [];

            if (($payload['status'] ?? null) !== 'success') {
                $message = $payload['message'] ?? 'Respuesta inesperada de GFW.';
                Log::error("GFW error en consulta por rango: {$message}");

                return $this->fillYearsWithError($years, $message);
            }

            $yearColumn = "{$this->defaultDataset}__year";

            foreach ($payload['data'] ?? [] as $row) {
                $year = (int) ($row[$yearColumn] ?? 0);

                if (isset($results[$year])) {
                    $results[$year]['area__ha'] = (float) ($row['area__ha'] ?? 0);
                }
            }

            return $results;
        } catch (ClientException $e) {
            $errorData = json_decode($e->getResponse()->getBody()->getContents(), true);
            $message   = $errorData['message'] ?? 'Error de validación de la API.';
            Log::error("GFW API Error (HTTP {$e->getCode()}): {$message}");

            return $this->fillYearsWithError($years, $message);
        } catch (\Throwable $e) {
            Log::error('GFW API Error inesperado: ' . $e->getMessage());

            return $this->fillYearsWithError($years, 'Error inesperado al conectar con la API.');
        }
    }

    /**
     * Rellena el mapa de años con el mismo error, manteniendo el shape
     * consistente para los consumidores.
     */
    private function fillYearsWithError(array $years, string $message): array
    {
        $results = [];

        foreach ($years as $year) {
            $results[$year] = [
                'area__ha' => 0.0,
                'status'   => 'error',
                'year'     => $year,
                'error'    => $message,
            ];
        }

        return $results;
    }
}
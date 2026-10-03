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
     * Consulta varios años en paralelo.
     * Devuelve por año: ['area__ha' => float, 'status' => 'success'|'error', 'year' => int, 'error' => ?string]
     *
     * @param  array  $geometry  GeoJSON geometry (array)
     * @param  int[]  $years
     */
    public function getParallelYearlyStats(array $geometry, array $years): array
    {
        $years = array_values(array_map('intval', $years));

        if (empty($years)) {
            return [];
        }

        $promises = [];
        foreach ($years as $year) {
            $sql = sprintf(
                "SELECT SUM(area__ha) FROM results WHERE %s__year=%d",
                $this->defaultDataset,
                $year
            );

            $promises[$year] = $this->client->postAsync(
                "dataset/{$this->defaultDataset}/{$this->defaultVersion}/query",
                ['json' => ['geometry' => $geometry, 'sql' => $sql]]
            );
        }

        $results = [];

        try {
            $responses = \GuzzleHttp\Promise\Utils::settle($promises)->wait();

            foreach ($responses as $year => $response) {
                $results[$year] = $this->normalizeYearlyResponse((int) $year, $response);
            }
        } catch (\Throwable $e) {
            Log::error('Error general en consultas paralelas GFW: ' . $e->getMessage());

            foreach ($years as $year) {
                $results[$year] = [
                    'area__ha' => 0.0,
                    'status'   => 'error',
                    'year'     => $year,
                    'error'    => 'Error general en consulta paralela: ' . $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Normaliza la respuesta de una promesa ya resuelta.
     */
    private function normalizeYearlyResponse(int $year, array $response): array
    {
        if (($response['state'] ?? 'rejected') !== 'fulfilled') {
            $errorMessage = $response['reason']->getMessage() ?? 'Error desconocido';
            Log::error("Error en consulta GFW para año {$year}: {$errorMessage}");

            return [
                'area__ha' => 0.0,
                'status'   => 'error',
                'year'     => $year,
                'error'    => $errorMessage,
            ];
        }

        $data = json_decode($response['value']->getBody(), true) ?? [];

        return [
            'area__ha' => (float) ($data['data'][0]['area__ha'] ?? 0),
            'status'   => $data['status'] ?? 'error',
            'year'     => $year,
        ];
    }
}
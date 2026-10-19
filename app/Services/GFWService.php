<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de Integración con Global Forest Watch (GFW API)
 * 
 * Se encarga de la comunicación satelital externa con la plataforma de monitoreo forestal de
 * World Resources Institute (WRI) y la Universidad de Maryland (UMD).
 * 
 * Funcionalidad técnica:
 * - Envía la geometría vectorial (GeoJSON) de las parcelas hacia los servidores de GFW.
 * - Ejecuta consultas de análisis geoespacial tipo SQL contra el dataset de pérdida de cobertura arbórea (`umd_tree_cover_loss`).
 * - Agrupa y totaliza el área deforestada (en hectáreas) año por año mediante procesamiento en la nube satelital.
 */
class GFWService
{
    /** Cliente HTTP configurado para interactuar con los endpoints REST de GFW. */
    protected Client $client;

    /** Tiempos máximos de espera (en segundos) para evitar bloqueos del sistema. */
    protected int $timeout;
    protected int $connectTimeout;

    /** Dataset satelital por defecto (UMD Tree Cover Loss de Hansen et al.). */
    protected string $defaultDataset;

    /** Versión del dataset (generalmente 'latest' o una versión específica como 'v1.10'). */
    protected string $defaultVersion;

    /**
     * Inicializa el cliente HTTP con los parámetros de configuración y credenciales API.
     * Carga el api-key obligatorio, headers de identificación y timeouts de red.
     */
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
     * Ejecuta una consulta SQL sincrónica contra el motor analítico de GFW pasando una geometría arbitraria.
     * 
     * Flujo:
     * 1. Envía un POST al endpoint `dataset/{dataset}/{version}/query` con el cuerpo JSON:
     *    `{ geometry: {...}, sql: "SELECT ... FROM results WHERE ..." }`
     * 2. El servidor satelital rasteriza la geometría contra sus capas globales de píxeles (30m de resolución)
     *    y totaliza la pérdida de biomasa o área boscosa.
     * 3. Procesa respuestas exitosas y captura excepciones de cliente (ej. Parcelas inválidos, auto-intersecciones).
     * 
     * @param string $dataset   Identificador del dataset (ej. 'umd_tree_cover_loss')
     * @param string $version   Versión del dataset (ej. 'latest')
     * @param array  $geometry  Geometría GeoJSON de la Parcela
     * @param string $sql       Sentencia SQL espacial compatible con la API de GFW
     * @return array            Estructura estandarizada ['status' => 'success'|'error', 'data' => [...], 'message' => ...]
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
            // Errores HTTP 4xx (Parcela deformado, clave API inválida, sintaxis SQL errónea)
            $errorData = json_decode($e->getResponse()->getBody()->getContents(), true);
            $message   = $errorData['message'] ?? 'Error de validación de la API.';
            Log::error("GFW API Error (HTTP {$e->getCode()}): {$message}");

            return ['status' => 'error', 'message' => $message, 'data' => []];
        } catch (\Throwable $e) {
            // Errores imprevistos de conexión, caída de red o timeouts
            Log::error('GFW API Error inesperado: ' . $e->getMessage());

            return [
                'status'  => 'error',
                'message' => 'Error inesperado al conectar con la API.',
                'data'    => [],
            ];
        }
    }

    /**
     * Consulta el área deforestada exclusivamente para un solo año específico.
     * 
     * Genera la consulta SQL espacial:
     * `SELECT SUM(area__ha) FROM results WHERE umd_tree_cover_loss__year = YYYY`
     * 
     * @param array $geometry Geometría GeoJSON de la Parcela a evaluar
     * @param int   $year     Año que se desea consultar
     * @return array
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
     * Obtiene estadísticas anuales de deforestación para un rango o lista de años en UNA SOLA llamada HTTP:
     * 
     * Optimización de rendimiento y cuota de red:
     * - En lugar de hacer una petición HTTP por cada año (lo que saturaría la conexión si se analizan 20 años),
     *   se agrupan los años mediante una sentencia SQL con `GROUP BY umd_tree_cover_loss__year`.
     * 
     * Manejo de años sin pérdida de bosque (Zero-filling):
     * - La API de GFW solo retorna filas para aquellos años donde el satélite detectó pérdida arbórea en algún píxel.
     * - Si en un año no hubo tala (pérdida = 0 ha), GFW omite la fila por completo.
     * - Este método inicializa previamente todos los años solicitados en 0.0 ha para asegurar que el consumidor
     *   siempre reciba un reporte cronológico continuo sin huecos.
     *
     * @param  array  $geometry  Geometría GeoJSON {'type': 'Polygon', 'coordinates': [...]}
     * @param  int[]  $years     Lista de años a consultar (ej. [2018, 2019, 2020, 2021])
     * @return array<int, array{area__ha: float, status: string, year: int, error?: string}>
     *         Diccionario indexado por año con el resultado de cada periodo.
     */
    public function getYearlyStatsForRange(array $geometry, array $years): array
    {
        // Normalizar y ordenar la lista de años eliminando duplicados
        $years = array_values(array_unique(array_map('intval', $years)));

        if (empty($years)) {
            return [];
        }

        sort($years);
        $minYear = $years[0];
        $maxYear = $years[count($years) - 1];

        // Construcción de la sentencia SQL satelital agrupada por año
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

        // Pre-inicializar todos los años con 0.0 ha para evitar valores vacíos si no hubo deforestación
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

            // Asignar los valores reales devueltos por el satélite
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
     * Helper de recuperación ante fallos de red:
     * Cuando la petición satelital falla por completo, marca todos los años solicitados
     * con estado 'error' y el mensaje correspondiente, permitiendo que la interfaz de usuario
     * informe con precisión cuál fue el fallo sin romper la ejecución del sistema.
     * 
     * @param array  $years   Años que se intentaron consultar
     * @param string $message Motivo o detalle del error
     * @return array
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
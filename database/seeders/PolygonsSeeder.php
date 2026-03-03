<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\LocationService;

class PolygonsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Iniciando importación de polígonos...');

        $polygons = [
         
            // ============================================
            // POLÍGONOS DEL ESTADO BOLÍVAR
            // ============================================
            [
                'name' => 'Parcela El Criollo - Lote A',
                'description' => 'Plantación de cacao criollo porcino, árboles de 3 años en producción',
                'geometry' => 'POLYGON(( -64.7718 9.7623, -64.7715 9.7620, -64.7713 9.7618, -64.7708 9.7615, -64.7710 9.7612, -64.7713 9.7614, -64.7713 9.7611, -64.7714 9.7611, -64.7715 9.7613, -64.7716 9.7619, -64.7716 9.7620, -64.7718 9.7623 ))',
                'producer_name' => null,
                'detected_parish' => 'Santa Elena de Uairén',
                'detected_municipality' => 'Gran Sabana',
                'detected_state' => 'Bolívar',
                'area_ha' => 0.56,
            ],
            [
                'name' => 'Fundo Trinitario - Sector 1',
                'description' => 'Cacao trinitario injertado, variedad SC-6 con alta producción de mazorcas',
                'geometry' => 'POLYGON(( -64.7458 9.5775, -64.7445 9.5764, -64.7445 9.5764, -64.7445 9.5764, -64.7447 9.5767, -64.7455 9.5766, -64.7456 9.5767, -64.7445 9.5773, -64.7445 9.5775, -64.7435 9.5778, -64.7445 9.5783, -64.7458 9.5775 ))',
                'producer_name' => null,
                'detected_parish' => 'Santa Elena de Uairén',
                'detected_municipality' => 'Gran Sabana',
                'detected_state' => 'Bolívar',
                'area_ha' => 1.58,
            ],
            [
                'name' => 'Hacienda Amazonía - Lote B',
                'description' => 'Cacao forastero amazónico, cultivo tradicional con sombra de bucare',
                'geometry' => 'POLYGON(( -64.7523 9.7967, -64.7514 9.7961, -64.7512 9.7960, -64.7556 9.7957, -64.7568 9.7968, -64.7523 9.7967 ))',
                'producer_name' => null,
                'detected_parish' => 'Santa Elena de Uairén',
                'detected_municipality' => 'Gran Sabana',
                'detected_state' => 'Bolívar',
                'area_ha' => 1.01,
            ],
            [
                'name' => 'Parcela Los Ríos - Cacao Fino',
                'description' => 'Cacao fino de aroma, variedad porcelana, manejo agroecológico',
                'geometry' => 'POLYGON(( -64.7633 9.8024, -64.7558 9.8020, -64.7584 9.8014, -64.7618 9.8015, -64.7636 9.8017, -64.7674 9.8012, -64.7702 9.8015, -64.7666 9.8021, -64.7633 9.8024 ))',
                'producer_name' => null,
                'detected_parish' => 'Santa Elena de Uairén',
                'detected_municipality' => 'Gran Sabana',
                'detected_state' => 'Bolívar',
                'area_ha' => 0.91,
            ],
            [
                'name' => 'Fundo El Cacao - Leonel',
                'description' => 'Plantación de cacao trinitario con sistema agroforestal, asociado con plátano y maderables',
                'geometry' => 'POLYGON(( -69.7792 10.1972, -69.7782 10.1966, -69.7816 10.1942, -69.7818 10.1944, -69.7792 10.1972 ))',
                'producer_name' => 'Leonel',
                'detected_parish' => 'San Fernando',
                'detected_municipality' => 'San Fernando',
                'detected_state' => 'Apure',
                'area_ha' => 1.82,
            ],
            [
                'name' => 'Finca La Montaña - Alonso',
                'description' => 'Cacao criollo andino, cultivo en laderas con manejo de sombra regulada',
                'geometry' => 'POLYGON(( -70.2728 9.8340, -70.2751 9.8340, -70.2748 9.8354, -70.2737 9.8357, -70.2720 9.8355, -70.2728 9.8340 ))',
                'producer_name' => 'Alonso',
                'detected_parish' => 'San Cristóbal',
                'detected_municipality' => 'San Cristóbal',
                'detected_state' => 'Táchira',
                'area_ha' => 4.03,
            ],
            [
                'name' => 'Hacienda El Bosque - Josefa',
                'description' => 'Cacao orgánico certificado, variedades criollas seleccionadas, fermentación en cajas',
                'geometry' => 'POLYGON(( -69.9306 10.1774, -69.9278 10.1778, -69.9275 10.1767, -69.9303 10.1766, -69.9306 10.1774 ))',
                'producer_name' => 'Josefa',
                'detected_parish' => 'San Cristóbal',
                'detected_municipality' => 'San Cristóbal',
                'detected_state' => 'Táchira',
                'area_ha' => 2.63,
            ],
            [
                'name' => 'Parcela Las Mercedes - Deiby',
                'description' => 'Cacao trinitario con clones injertados, sistema de riego por goteo',
                'geometry' => 'POLYGON(( -70.2762 9.8333, -70.2766 9.8342, -70.2761 9.8353, -70.2753 9.8357, -70.2751 9.8340, -70.2762 9.8333 ))',
                'producer_name' => 'Deiby',
                'detected_parish' => 'San Cristóbal',
                'detected_municipality' => 'San Cristóbal',
                'detected_state' => 'Táchira',
                'area_ha' => 2.16,
            ],
            [
                'name' => 'Finca La Cascada - Alcide',
                'description' => 'Plantación joven de cacao forastero, 2 años de establecimiento, asociado con musáceas',
                'geometry' => 'POLYGON(( -69.9250 10.1780, -69.9250 10.1771, -69.9275 10.1767, -69.9278 10.1778, -69.9255 10.1780, -69.9250 10.1780 ))',
                'producer_name' => 'Alcide',
                'detected_parish' => 'San Cristóbal',
                'detected_municipality' => 'San Cristóbal',
                'detected_state' => 'Táchira',
                'area_ha' => 2.56,
            ],
            [
                'name' => 'Fundo La Sabana - Adolfo',
                'description' => 'Cacao criollo en sistema agroforestal con maderables y frutales, manejo orgánico',
                'geometry' => 'POLYGON(( -64.1789 9.6938, -64.1627 9.6936, -64.1606 9.6929, -64.1789 9.6932, -64.1789 9.6938 ))',
                'producer_name' => 'Adolfo',
                'detected_parish' => 'Santa Elena de Uairén',
                'detected_municipality' => 'Gran Sabana',
                'detected_state' => 'Bolívar',
                'area_ha' => 1.18,
            ],
            [
                'name' => 'Hacienda El Jardín - Angel',
                'description' => 'Cacao fino de aroma, variedades criollas selectas, beneficiado artesanal',
                'geometry' => 'POLYGON(( -64.7653 9.5765, -64.7545 9.5760, -64.7602 9.5752, -64.7630 9.5754, -64.7687 9.5759, -64.7672 9.5762, -64.7653 9.5765 ))',
                'producer_name' => 'Angel',
                'detected_parish' => 'Santa Elena de Uairén',
                'detected_municipality' => 'Gran Sabana',
                'detected_state' => 'Bolívar',
                'area_ha' => 1.14,
            ],
            [
                'name' => 'Parcela San Isidro - Cruz R',
                'description' => 'Cacao trinitario con alta densidad de siembra, manejo técnico intensivo',
                'geometry' => 'POLYGON(( -64.1559 9.6831, -64.1528 9.6827, -64.1536 9.6826, -64.1531 9.6826, -64.1556 9.6820, -64.1478 9.6814, -64.1508 9.6812, -64.1506 9.6804, -64.1585 9.6801, -64.1595 9.6794, -64.1691 9.6793, -64.1695 9.6797, -64.1715 9.6799, -64.1721 9.6818, -64.1737 9.6820, -64.1692 9.6829, -64.1666 9.6828, -64.1612 9.6825, -64.1559 9.6831 ))',
                'producer_name' => 'Cruz R',
                'detected_parish' => 'Santa Elena de Uairén',
                'detected_municipality' => 'Gran Sabana',
                'detected_state' => 'Bolívar',
                'area_ha' => 6.01,
            ],
            [
                'name' => 'Finca El Tesoro - Erick',
                'description' => 'Cacao forastero mejorado, producción convencional con control de monilia',
                'geometry' => 'POLYGON(( -63.9166 9.6739, -63.9271 9.6627, -63.9185 9.6517, -63.9206 9.6486, -63.9293 9.6662, -63.9164 9.6777, -63.9137 9.6795, -63.9166 9.6739 ))',
                'producer_name' => 'Erick',
                'detected_parish' => 'Caripito',
                'detected_municipality' => 'Bolívar',
                'detected_state' => 'Monagas',
                'area_ha' => 1.76,
            ],
            [
                'name' => 'Hacienda Los Clonales - Erive',
                'description' => 'Banco de clones de cacao trinitario, material genético elite para injertación',
                'geometry' => 'POLYGON(( -63.9106 9.6784, -63.9083 9.6569, -63.9226 9.6480, -63.9214 9.6609, -63.9108 9.6777, -63.9106 9.6784 ))',
                'producer_name' => 'Erive',
                'detected_parish' => 'Caripito',
                'detected_municipality' => 'Bolívar',
                'detected_state' => 'Monagas',
                'area_ha' => 3.45,
            ],
            [
                'name' => 'Parcela Gran Sabana - Cacao Nativo',
                'description' => 'Cacao silvestre nativo, recolección de mazorcas en bosque de galería',
                'geometry' => 'POLYGON(( -64.7718 9.7623, -64.7715 9.7620, -64.7713 9.7618, -64.7708 9.7615, -64.7710 9.7612, -64.7713 9.7614, -64.7713 9.7611, -64.7714 9.7611, -64.7715 9.7613, -64.7716 9.7619, -64.7716 9.7620, -64.7718 9.7623 ))',
                'producer_name' => null,
                'detected_parish' => 'Santa Elena de Uairén',
                'detected_municipality' => 'Gran Sabana',
                'detected_state' => 'Bolívar',
                'area_ha' => 0.56,
            ],
            [
                'name' => 'Finca La Esperanza - Cacao Premium',
                'description' => 'Cacao de alta calidad para chocolate fino, secado solar en eras',
                'geometry' => 'POLYGON(( -64.7458 9.5775, -64.7445 9.5764, -64.7445 9.5764, -64.7445 9.5764, -64.7447 9.5767, -64.7455 9.5766, -64.7456 9.5767, -64.7445 9.5773, -64.7445 9.5775, -64.7435 9.5778, -64.7445 9.5783, -64.7458 9.5775 ))',
                'producer_name' => null,
                'detected_parish' => 'Santa Elena de Uairén',
                'detected_municipality' => 'Gran Sabana',
                'detected_state' => 'Bolívar',
                'area_ha' => 1.58,
            ],
        ];

        $this->command->info('Procesando ' . count($polygons) . ' polígonos...');

        $count = 0;
        $errors = 0;

        foreach ($polygons as $polygonData) {
            try {
                DB::beginTransaction();

                // 1. Buscar o crear la ubicación
                $parishId = null;
                if (!empty($polygonData['detected_parish']) && 
                    !empty($polygonData['detected_municipality']) && 
                    !empty($polygonData['detected_state'])) {
                    
                    $parishId = LocationService::createOrUpdateLocation(
                        $polygonData['detected_parish'],
                        $polygonData['detected_municipality'],
                        $polygonData['detected_state']
                    );
                    
                    $this->command->info("  → Ubicación: {$polygonData['detected_parish']}, {$polygonData['detected_municipality']}, {$polygonData['detected_state']} - Parish ID: " . ($parishId ?? 'NULL'));
                }

                // 2. Buscar el productor
                $producerId = null;
                if (!empty($polygonData['producer_name'])) {
                    $producer = DB::table('producers')
                        ->where('name', $polygonData['producer_name'])
                        ->whereNull('deleted_at')
                        ->first();
                    
                    if ($producer) {
                        $producerId = $producer->id;
                    }
                }

                // 3. Calcular centroide
                $centroidRes = DB::selectOne("
                    SELECT 
                        ST_X(ST_Centroid(ST_GeomFromText(?, 4326))) as lng,
                        ST_Y(ST_Centroid(ST_GeomFromText(?, 4326))) as lat
                ", [$polygonData['geometry'], $polygonData['geometry']]);

                // 4. Preparar location_data
                $locationData = [
                    'detected_info' => [
                        'detected_parish' => $polygonData['detected_parish'],
                        'detected_municipality' => $polygonData['detected_municipality'],
                        'detected_state' => $polygonData['detected_state'],
                    ],
                    'import_info' => [
                        'source' => 'geojson_seeder',
                        'imported_at' => now()->toDateTimeString(),
                        'original_name' => $polygonData['name'],
                        'has_parish_match' => !is_null($parishId),
                        'has_producer_match' => !is_null($producerId),
                    ],
                    'cultivo_info' => [
                        'tipo' => 'cacao',
                        'variedades' => $this->detectCacaoVarieties($polygonData['description']),
                        'sistema' => $this->detectCultivoSystem($polygonData['description']),
                    ]
                ];

                // 5. Insertar el polígono
                $id = DB::table('polygons')->insertGetId([
                    'name' => $polygonData['name'],
                    'description' => $polygonData['description'],
                    'geometry' => DB::raw("ST_GeomFromText('{$polygonData['geometry']}', 4326)"),
                    'producer_id' => $producerId,
                    'parish_id' => $parishId,
                    'area_ha' => $polygonData['area_ha'],
                    'is_active' => true,
                    'centroid_lat' => $centroidRes->lat ?? null,
                    'centroid_lng' => $centroidRes->lng ?? null,
                    'location_data' => json_encode($locationData, JSON_UNESCAPED_UNICODE),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::commit();
                $count++;
                
                $this->command->info("  ✓ Polígono {$polygonData['name']} insertado con ID: {$id}");

            } catch (\Exception $e) {
                DB::rollBack();
                $errors++;
                $this->command->error("  ✗ Error en polígono {$polygonData['name']}: " . $e->getMessage());
                Log::error('Error en seeder', [
                    'polygon' => $polygonData['name'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->command->info('');
        $this->command->info('====================================');
        $this->command->info("RESUMEN DE IMPORTACIÓN:");
        $this->command->info("  ✓ Polígonos insertados: {$count}");
        $this->command->info("  ✗ Errores: {$errors}");
        $this->command->info('====================================');
    }

    /**
     * Detecta las variedades de cacao mencionadas en la descripción
     */
    private function detectCacaoVarieties($description): array
    {
        $varieties = [];
        $description = strtolower($description);
        
        if (strpos($description, 'criollo') !== false) {
            $varieties[] = 'Criollo';
        }
        if (strpos($description, 'trinitario') !== false) {
            $varieties[] = 'Trinitario';
        }
        if (strpos($description, 'forastero') !== false) {
            $varieties[] = 'Forastero';
        }
        if (strpos($description, 'porcelana') !== false) {
            $varieties[] = 'Porcelana';
        }
        if (strpos($description, 'clon') !== false || strpos($description, 'clones') !== false) {
            $varieties[] = 'Clones seleccionados';
        }
        
        return !empty($varieties) ? $varieties : ['No especificada'];
    }

    /**
     * Detecta el sistema de cultivo mencionado en la descripción
     */
    private function detectCultivoSystem($description): string
    {
        $description = strtolower($description);
        
        if (strpos($description, 'agroforestal') !== false || strpos($description, 'sombra') !== false) {
            return 'Agroforestal';
        }
        if (strpos($description, 'orgánico') !== false || strpos($description, 'organico') !== false) {
            return 'Orgánico';
        }
        if (strpos($description, 'intensivo') !== false) {
            return 'Intensivo';
        }
        if (strpos($description, 'tradicional') !== false) {
            return 'Tradicional';
        }
        
        return 'Convencional';
    }
}
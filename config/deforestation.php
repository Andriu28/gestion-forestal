<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rango de años permitido para análisis de deforestación
    |--------------------------------------------------------------------------
    |
    | GFW publica datos anuales del dataset UMD Tree Cover Loss. El año mínimo
    | es 2001 (inicio del dataset). El máximo se calcula respecto al año de
    | publicación disponible en la API; ajusta a medida que GFW publique más.
    |
    */

    'min_year' => (int) env('DEFORESTATION_MIN_YEAR', 2001),
    'max_year' => (int) env('DEFORESTATION_MAX_YEAR', 2024),

    /*
    |--------------------------------------------------------------------------
    | Rango por defecto para análisis post-importación
    |--------------------------------------------------------------------------
    |
    | Cuando se importa un GeoJSON con "analizar deforestación" activo,
    | se consultan estos años. Mantén un rango razonable para no gastar
    | cuota de la API innecesariamente.
    |
    */

    'import_default_start_year' => (int) env('DEFORESTATION_IMPORT_START_YEAR', 2020),
    'import_default_end_year'   => (int) env('DEFORESTATION_IMPORT_END_YEAR', 2024),
];
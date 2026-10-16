<?php
// [file name]: app/Services/LocationService.php

namespace App\Services;

use App\Models\State;
use App\Models\Municipality;
use App\Models\Parish;
use Illuminate\Support\Str;

/**
 * Servicio de Resolución y Normalización Político-Territorial
 * 
 * Se encarga de traducir las respuestas de geocodificación inversa (por ejemplo de OpenStreetMap / Nominatim)
 * en la jerarquía territorial formal de Venezuela:
 * Estado (1er nivel) → Municipio (2do nivel) → Parroquia (3er nivel).
 * 
 * Desafíos que resuelve:
 * 1. Heterogeneidad en los nombres devueltos por los satélites y mapas ("Edo. Barinas" vs "Barinas", "Municipio Sucre" vs "Sucre").
 * 2. Eliminación de prefijos administrativos ("Parroquia", "Municipio", "Sector", "Urb").
 * 3. Búsqueda por similitud fonética o textual antes de insertar duplicados.
 * 4. Creación jerárquica en cascada para no interrumpir el flujo cuando se registran nuevas demarcaciones.
 */
class LocationService
{
    /**
     * Resuelve y vincula la jerarquía territorial completa (Estado → Municipio → Parroquia).
     * 
     * Flujo en cascada:
     * 1. Localiza o crea el Estado por su nombre.
     * 2. Localiza o crea el Municipio subordinado a ese Estado (`state_id`).
     * 3. Localiza o crea la Parroquia subordinada a ese Municipio (`municipality_id`).
     * 4. Retorna el identificador numérico de la Parroquia (`parish_id`) para relacionarlo con la parcela.
     *
     * @param string $parishName       Nombre de la parroquia detectada
     * @param string $municipalityName Nombre del municipio detectado
     * @param string $stateName        Nombre del estado detectado
     * @return int|null Identificador de la parroquia encontrada o creada, o null si falló
     */
    public static function createOrUpdateLocation(
        string $parishName,
        string $municipalityName,
        string $stateName
    ): ?int {
        try {
            // 1. Buscar o crear el Estado (Nivel 1)
            $state = self::findOrCreateState($stateName);
            
            if (!$state) {
                return null;
            }
            
            // 2. Buscar o crear el Municipio acotado a ese estado (Nivel 2)
            $municipality = self::findOrCreateMunicipality($municipalityName, $state->id);
            
            if (!$municipality) {
                return null;
            }
            
            // 3. Buscar o crear la Parroquia acotada a ese municipio (Nivel 3)
            $parish = self::findOrCreateParish($parishName, $municipality->id);
            
            return $parish ? $parish->id : null;
            
        } catch (\Exception $e) {
            \Log::error('Error en createOrUpdateLocation: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Alias semántico de createOrUpdateLocation para mayor legibilidad en el código cliente.
     */
    public static function findOrCreateLocation(
        string $parishName,
        string $municipalityName,
        string $stateName
    ): ?int {
        return self::createOrUpdateLocation($parishName, $municipalityName, $stateName);
    }
    
    /**
     * Localiza un Estado en la base de datos o lo crea si no existe:
     * - Aplica normalización textual para evitar discrepancias por mayúsculas o acentos.
     * - Realiza una búsqueda por similitud usando LIKE para resolver variantes (ej. "Zulia" vs "Estado Zulia").
     */
    private static function findOrCreateState(string $stateName): ?State
    {
        if (empty(trim($stateName))) {
            return null;
        }
        
        $normalized = self::normalizeName($stateName);
        
        // 1. Búsqueda exacta insensible a mayúsculas
        $state = State::whereRaw('LOWER(name) = ?', [strtolower($normalized)])->first();
        
        if ($state) {
            return $state;
        }
        
        // 2. Búsqueda parcial por similitud
        $state = State::where('name', 'like', "%{$normalized}%")
                     ->orWhere('name', 'like', "%{$stateName}%")
                     ->first();
        
        if ($state) {
            return $state;
        }
        
        // 3. Creación si no existe previamente
        return State::create([
            'name' => self::cleanName($stateName)
        ]);
    }
    
    /**
     * Localiza un Municipio dentro de un Estado específico o lo crea si es nuevo:
     * Evita mezclar municipios homónimos que pertenecen a estados distintos
     * (por ejemplo, "Libertador" en Mérida vs "Libertador" en Distrito Capital)
     * condicionando la consulta a `state_id`.
     */
    private static function findOrCreateMunicipality(string $municipalityName, int $stateId): ?Municipality
    {
        if (empty(trim($municipalityName))) {
            return null;
        }
        
        $normalized = self::normalizeName($municipalityName);
        
        // Búsqueda condicionada estrictamente al ID del Estado padre
        $municipality = Municipality::where('state_id', $stateId)
            ->where(function($query) use ($normalized, $municipalityName) {
                $query->whereRaw('LOWER(name) = ?', [strtolower($normalized)])
                      ->orWhere('name', 'like', "%{$normalized}%")
                      ->orWhere('name', 'like', "%{$municipalityName}%");
            })
            ->first();
        
        if ($municipality) {
            return $municipality;
        }
        
        // Registrar el nuevo municipio asociado al estado correspondiente
        return Municipality::create([
            'name' => self::cleanName($municipalityName),
            'state_id' => $stateId
        ]);
    }
    
    /**
     * Localiza una Parroquia dentro de un Municipio específico o la crea si es nueva:
     * Condiciona la búsqueda a `municipality_id` para garantizar que la delimitación
     * pertenezca al término municipal exacto.
     */
    private static function findOrCreateParish(string $parishName, int $municipalityId): ?Parish
    {
        if (empty(trim($parishName))) {
            return null;
        }
        
        $normalized = self::normalizeName($parishName);
        
        $parish = Parish::where('municipality_id', $municipalityId)
            ->where(function($query) use ($normalized, $parishName) {
                $query->whereRaw('LOWER(name) = ?', [strtolower($normalized)])
                      ->orWhere('name', 'like', "%{$normalized}%")
                      ->orWhere('name', 'like', "%{$parishName}%");
            })
            ->first();
        
        if ($parish) {
            return $parish;
        }
        
        return Parish::create([
            'name' => self::cleanName($parishName),
            'municipality_id' => $municipalityId
        ]);
    }
    
    /**
     * Algoritmo de normalización léxica y geográfica:
     * 1. Convierte a minúsculas en codificación UTF-8.
     * 2. Elimina prefijos burocráticos y administrativos comunes ("Parroquia", "Municipio", "Estado", "Sector", "Urb.", etc.)
     *    tanto al principio de la cadena como en su interior mediante expresiones regulares.
     * 3. Remueve caracteres de puntuación o signos especiales preservando letras con acentos y números.
     * 4. Colapsa espacios en blanco repetidos a un solo espacio.
     */
    private static function normalizeName(string $name): string
    {
        if (empty($name)) {
            return '';
        }
        
        $name = mb_strtolower(trim($name), 'UTF-8');
        
        $commonPrefixes = [
            'Parroquia', 'Municipio', 'municipal', 'Estado', 
            'sector', 'zona', 'urb', 'urb.', 'urbano', 
            'rural', 'distrito', 'cantón', 'departamento',
            'provincia', 'región', 'comuna', 'aldea'
        ];
        
        // Eliminar prefijos al comienzo del texto
        $pattern = '/^(' . implode('|', array_map('preg_quote', $commonPrefixes)) . ')\s+/i';
        $name = preg_replace($pattern, '', $name);
        
        // Eliminar si están rodeados de espacios
        $pattern2 = '/\s+(' . implode('|', array_map('preg_quote', $commonPrefixes)) . ')\s+/i';
        $name = preg_replace($pattern2, ' ', $name);
        
        // Filtrar signos de puntuación extraños manteniendo caracteres alfanuméricos unicode
        $name = preg_replace('/[^\p{L}\p{N}\s]/u', '', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        
        return trim($name);
    }
    
    /**
     * Limpia y estandariza la capitalización de nombres propios para almacenamiento en base de datos:
     * Convierte a formato título (primera letra de cada palabra en mayúscula), pero respetando
     * acrónimos o nombres mixtos (como "McDonald" o siglas) sin corromperlos.
     */
    private static function cleanName(string $name): string
    {
        $name = trim($name);
        
        $words = explode(' ', $name);
        
        $cleaned = array_map(function($word) {
            if (mb_strlen($word) > 0 && ctype_upper(mb_substr($word, 0, 1))) {
                return $word;
            }
            
            if ($word !== '' && mb_strtolower($word) === $word) {
                return mb_convert_case($word, MB_CASE_TITLE, 'UTF-8');
            }
            
            return $word;
        }, $words);
        
        return implode(' ', $cleaned);
    }
    
    /**
     * Procesa la carga útil (payload) devuelta por el API de OpenStreetMap / Nominatim:
     * - Analiza los atributos del objeto 'address'.
     * - Extrae los nombres de parroquia, municipio y estado mediante orden de precedencia.
     * - Inserta o recupera la entidad en la base de datos devolviendo el ID de parroquia.
     * - Retorna un paquete completo con los nombres limpios y la dirección cruda original.
     *
     * @param array $osmData Respuesta JSON de OSM Nominatim
     * @return array
     */
    public static function processOSMData(array $osmData): array
    {
        $address = $osmData['address'] ?? [];
        
        // Extraer los 3 niveles jerárquicos
        $parishName       = self::extractOSMParish($address);
        $municipalityName = self::extractOSMMunicipality($address);
        $stateName        = self::extractOSMState($address);
        
        // Resolver o registrar en base de datos
        $parishId = self::findOrCreateLocation($parishName, $municipalityName, $stateName);
        
        return [
            'parish_id'             => $parishId,
            'detected_parish'       => $parishName,
            'detected_municipality' => $municipalityName,
            'detected_state'        => $stateName,
            'parish_name'           => $parishName,
            'municipality_name'     => $municipalityName,
            'state_name'            => $stateName,
            'full_address'          => $address
        ];
    }
    
    /**
     * Extrae el nombre más cercano a nivel de Parroquia a partir de las etiquetas de OpenStreetMap:
     * Revisa en orden de prioridad de lo más específico a lo más amplio
     * ('county', 'municipality', 'city_district', 'district', 'suburb', 'village', 'town', 'city').
     */
    private static function extractOSMParish(array $address): string
    {
        $fields = [
            'county', 'municipality', 'city_district', 'district',
            'suburb', 'village', 'town', 'city'
        ];
        
        foreach ($fields as $field) {
            if (!empty($address[$field])) {
                return $address[$field];
            }
        }
        
        return 'No detectado';
    }
    
    /**
     * Extrae el nombre del Municipio a partir de las etiquetas de OpenStreetMap:
     * Prioriza etiquetas como 'municipality', 'county', 'state_district', etc.
     */
    private static function extractOSMMunicipality(array $address): string
    {
        $fields = [
            'municipality', 'county', 'state_district',
            'city', 'town', 'region'
        ];
        
        foreach ($fields as $field) {
            if (!empty($address[$field])) {
                return $address[$field];
            }
        }
        
        return 'No detectado';
    }
    
    /**
     * Extrae el nombre del Estado o Entidad Federal a partir de las etiquetas de OpenStreetMap:
     * Lee directamente 'state' o 'region'.
     */
    private static function extractOSMState(array $address): string
    {
        if (!empty($address['state'])) {
            return $address['state'];
        }
        
        if (!empty($address['region'])) {
            return $address['region'];
        }
        
        return 'No detectado';
    }
}
<?php
// [file name]: app/Services/LocationService.php

namespace App\Services;

use App\Models\State;
use App\Models\Municipality;
use App\Models\Parish;
use Illuminate\Support\Str;

class LocationService
{
    /**
     * Busca o crea una ubicación completa (Estado → Municipio → Parroquia)
     */
    // En LocationService.php - agregar este método
    public static function createOrUpdateLocation(
        string $parishName,
        string $municipalityName,
        string $stateName
    ): ?int {
        try {
            // 1. Buscar o crear Estado
            $state = self::findOrCreateState($stateName);
            
            if (!$state) {
                return null;
            }
            
            // 2. Buscar o crear Municipio (dentro del estado)
            $municipality = self::findOrCreateMunicipality($municipalityName, $state->id);
            
            if (!$municipality) {
                return null;
            }
            
            // 3. Buscar o crear Parroquia (dentro del municipio)
            $parish = self::findOrCreateParish($parishName, $municipality->id);
            
            return $parish ? $parish->id : null;
            
        } catch (\Exception $e) {
            \Log::error('Error en createOrUpdateLocation: ' . $e->getMessage());
            return null;
        }
    }

    // En LocationService.php - agregar este método
    public static function findOrCreateLocation(
        string $parishName,
        string $municipalityName,
        string $stateName
    ): ?int {
        // Este método es un alias para createOrUpdateLocation
        return self::createOrUpdateLocation($parishName, $municipalityName, $stateName);
    }
    
    /**
     * Busca o crea un estado
     */
    private static function findOrCreateState(string $stateName): ?State
    {
        if (empty(trim($stateName))) {
            return null;
        }
        
        $normalized = self::normalizeName($stateName);
        
        // Buscar primero por coincidencia exacta (normalizada)
        $state = State::whereRaw('LOWER(name) = ?', [strtolower($normalized)])->first();
        
        if ($state) {
            return $state;
        }
        
        // Si no existe, buscar por similitud (para evitar "Zulia" vs "Estado Zulia")
        $state = State::where('name', 'like', "%{$normalized}%")
                     ->orWhere('name', 'like', "%{$stateName}%")
                     ->first();
        
        if ($state) {
            return $state;
        }
        
        // Crear nuevo estado
        return State::create([
            'name' => self::cleanName($stateName)
        ]);
    }
    
    /**
     * Busca o crea un municipio (dentro de un estado)
     */
    private static function findOrCreateMunicipality(string $municipalityName, int $stateId): ?Municipality
    {
        if (empty(trim($municipalityName))) {
            return null;
        }
        
        $normalized = self::normalizeName($municipalityName);
        
        // Buscar municipio dentro del estado específico
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
        
        // Crear nuevo municipio
        return Municipality::create([
            'name' => self::cleanName($municipalityName),
            'state_id' => $stateId
        ]);
    }
    
    /**
     * Busca o crea una parroquia (dentro de un municipio)
     */
    private static function findOrCreateParish(string $parishName, int $municipalityId): ?Parish
    {
        if (empty(trim($parishName))) {
            return null;
        }
        
        $normalized = self::normalizeName($parishName);
        
        // Buscar parroquia dentro del municipio específico
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
        
        // Crear nueva parroquia
        return Parish::create([
            'name' => self::cleanName($parishName),
            'municipality_id' => $municipalityId
        ]);
    }
    
    /**
     * Normaliza nombres para búsqueda (elimina acentos, palabras comunes)
     */
    private static function normalizeName(string $name): string
    {
        if (empty($name)) {
            return '';
        }
        
        // Convertir a minúsculas
        $name = mb_strtolower(trim($name), 'UTF-8');
        
        // Eliminar prefijos comunes (MEJORADO)
        $commonPrefixes = [
            'parroquia', 'municipio', 'municipal', 'estado', 
            'sector', 'zona', 'urb', 'urb.', 'urbano', 
            'rural', 'distrito', 'cantón', 'departamento',
            'provincia', 'región', 'comuna', 'aldea'
        ];
        
        // Patrón para eliminar prefijos al inicio
        $pattern = '/^(' . implode('|', array_map('preg_quote', $commonPrefixes)) . ')\s+/i';
        $name = preg_replace($pattern, '', $name);
        
        // También eliminar si están en cualquier parte (pero cuidado con nombres como "Parroquia" como parte del nombre)
        $pattern2 = '/\s+(' . implode('|', array_map('preg_quote', $commonPrefixes)) . ')\s+/i';
        $name = preg_replace($pattern2, ' ', $name);
        
        // Eliminar caracteres especiales y múltiples espacios
        $name = preg_replace('/[^\p{L}\p{N}\s]/u', '', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        
        return trim($name);
    }
    
    /**
     * Limpia el nombre para almacenamiento
     */
    private static function cleanName(string $name): string
    {
        $name = trim($name);
        
        // Capitalizar palabras (excepto preposiciones)
        $prepositions = ['de', 'del', 'la', 'las', 'los', 'y', 'e', 'el'];
        $words = explode(' ', $name);
        
        $cleaned = array_map(function($word) use ($prepositions) {
            if (in_array(mb_strtolower($word), $prepositions)) {
                return mb_strtolower($word);
            }
            return mb_convert_case($word, MB_CASE_TITLE, 'UTF-8');
        }, $words);
        
        return implode(' ', $cleaned);
    }
    
    /**
     * Procesa datos de OpenStreetMap y devuelve ID de parroquia
     */
    public static function processOSMData(array $osmData): array
    {
        $address = $osmData['address'] ?? [];
        
        // Extraer componentes jerárquicos de OSM
        $parishName = self::extractOSMParish($address);
        $municipalityName = self::extractOSMMunicipality($address);
        $stateName = self::extractOSMState($address);
        
        // Intentar encontrar/crear la ubicación
        $parishId = self::findOrCreateLocation($parishName, $municipalityName, $stateName);
        
        return [
            'parish_id' => $parishId,
            'detected_parish' => $parishName,
            'detected_municipality' => $municipalityName,
            'detected_state' => $stateName,
            'parish_name' => $parishName,
            'municipality_name' => $municipalityName,
            'state_name' => $stateName,
            'full_address' => $address
        ];
    }
    
    /**
     * Extrae nombre de parroquia de datos OSM
     */
    private static function extractOSMParish(array $address): string
    {
        // Prioridad de campos OSM para parroquia
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
     * Extrae nombre de municipio de datos OSM
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
     * Extrae nombre de estado de datos OSM
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
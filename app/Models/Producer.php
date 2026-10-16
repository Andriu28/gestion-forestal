<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

/**
 * Modelo Producer (Productor Agrícola / Forestal)
 * 
 * Representa a la persona natural o jurídica propietaria, arrendataria o responsable
 * de los predios y parcelas forestales administradas en el sistema.
 * 
 * Funcionalidades del dominio:
 * 1. Identificación ciudadana y catastral (tipo de documento venezolano: V, E, J, G, número de cédula y código interno de productor).
 * 2. Anclaje territorial formal: Vinculado a Estado, Municipio y Parroquia mediante claves foráneas.
 * 3. Georreferenciación de sede o domicilio legal (latitud, longitud y dirección textual).
 * 4. Trazabilidad de auditoría detallada (Spatie Activitylog) con mensajes en español ante cambios de datos o estado.
 * 5. Búsqueda inteligente por nombre, apellido, código o árbol territorial subordinado.
 */
class Producer extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * Atributos asignables de forma masiva:
     * - name: Nombres de pila o razón social.
     * - lastname: Apellidos del productor (opcional si es empresa).
     * - cedula_type: Tipo de documento de identidad en Venezuela ('V' venezolano, 'E' extranjero, 'J' jurídico, 'G' gubernamental).
     * - cedula: Número de identificación ciudadana o RIF (numérico).
     * - code: Código correlativo interno de registro agrario asignado por la organización (ej. 'PROD-0042').
     * - description: Notas técnicas sobre el productor o rubros cultivados (cacao, café, madera, etc.).
     * - is_active: Estado operativo (activo/inactivo) para suspender operaciones sin borrar historial.
     * - latitude / longitude: Coordenadas geográficas fijas del domicilio legal o vivienda del productor.
     * - address: Dirección física descriptiva de referencia.
     * - state_id / municipality_id / parish_id: Identificadores de demarcación territorial venezolana.
     */
    protected $fillable = [
        'name',
        'lastname',
        'cedula_type',
        'cedula',
        'code', 
        'description',
        'is_active',
        'latitude',
        'longitude',
        'address',
        'state_id',
        'municipality_id',
        'parish_id',
    ];

    /**
     * Conversión estricta de tipos de datos:
     * - is_active: Booleano para respuestas API y condicionales en vistas.
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    // =========================================================================
    // Relaciones de Demarcación Territorial
    // =========================================================================

    /**
     * Estado o Entidad Federal en la que reside el productor.
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Municipio político-administrativo de residencia.
     */
    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    /**
     * Parroquia civil en la que se ubica el domicilio.
     */
    public function parish()
    {
        return $this->belongsTo(Parish::class);
    }

    /**
     * Lotes o parcelas de terreno georreferenciadas que pertenecen o están asignadas a este productor.
     */
    public function polygons()
    {
        return $this->hasMany(Polygon::class);
    }

    // =========================================================================
    // Configuración de Bitácora de Auditoría (Spatie Activitylog)
    // =========================================================================

    /**
     * Define qué campos del productor son monitoreados ante modificaciones:
     * - `logOnlyDirty`: Solo crea una entrada si el valor nuevo difiere del anterior.
     * - `dontSubmitEmptyLogs`: Evita guardar registros vacíos si un update no modificó datos reales.
     * - Genera descripciones dinámicas y legibles en español indicando el campo específico alterado
     *   o si el productor fue activado/desactivado.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'lastname', 'cedula_type', 'cedula', 'code', 'description', 'state_id', 'municipality_id', 'parish_id'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(function(string $eventName) {
                $producerName = $this->name && $this->lastname 
                    ? "{$this->name} {$this->lastname}"
                    : ($this->name ?: 'Productor #' . $this->id);
                
                switch($eventName) {
                    case 'created':
                        return "Productor '{$producerName}' fue creado";
                        
                    case 'updated':
                        $changes = $this->getChanges();
                        unset($changes['updated_at']);
                        
                        // Si el único cambio fue el estado activo/inactivo
                        if (count($changes) === 1 && isset($changes['is_active'])) {
                            $newStatus = $changes['is_active'] ? 'activado' : 'desactivado';
                            return "Productor '{$producerName}' fue {$newStatus}";
                        }
                        
                        // Si se actualizó un solo campo específico
                        $changedFields = array_keys($changes);
                        if (count($changedFields) === 1) {
                            $field = $changedFields[0];
                            $fieldNames = [
                                'name'            => 'nombre',
                                'lastname'        => 'apellido',
                                'description'     => 'descripción',
                                'is_active'       => 'estado',
                                'state_id'        => 'estado',
                                'municipality_id' => 'municipio',
                                'parish_id'       => 'parroquia',
                            ];
                            
                            $fieldName = $fieldNames[$field] ?? $field;
                            return "Productor '{$producerName}' - {$fieldName} actualizado";
                        }
                        
                        return "Productor '{$producerName}' fue actualizado";
                        
                    case 'deleted':
                        return "Productor '{$producerName}' fue eliminado";
                        
                    case 'restored':
                        return "Productor '{$producerName}' fue restaurado";
                        
                    default:
                        return "Productor '{$producerName}' - {$eventName}";
                }
            })
            ->dontSubmitEmptyLogs();
    }

    // =========================================================================
    // Scopes (Filtros Reutilizables de Consulta)
    // =========================================================================

    /**
     * Filtra únicamente productores activos en el sistema.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Búsqueda integral por texto libre:
     * Busca coincidencias parciales con LIKE en:
     * - Nombre o apellido del productor.
     * - Código interno asignado.
     * - Descripción de la ficha técnica.
     * - Nombre de cualquiera de sus niveles territoriales asignados (Estado, Municipio o Parroquia).
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('lastname', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhereHas('state', function ($q2) use ($search) {
                  $q2->where('name', 'like', "%{$search}%");
              })
              ->orWhereHas('municipality', function ($q2) use ($search) {
                  $q2->where('name', 'like', "%{$search}%");
              })
              ->orWhereHas('parish', function ($q2) use ($search) {
                  $q2->where('name', 'like', "%{$search}%");
              });
        });
    }

    // =========================================================================
    // Accessors (Formateadores Dinámicos de Salida)
    // =========================================================================

    /**
     * Nombre del Estado asignado o 'Sin estado'.
     */
    public function getStateNameAttribute()
    {
        return $this->state ? $this->state->name : 'Sin estado';
    }

    /**
     * Nombre del Municipio asignado o 'Sin municipio'.
     */
    public function getMunicipalityNameAttribute()
    {
        return $this->municipality ? $this->municipality->name : 'Sin municipio';
    }

    /**
     * Nombre de la Parroquia asignada o 'Sin parroquia'.
     */
    public function getParishNameAttribute()
    {
        return $this->parish ? $this->parish->name : 'Sin parroquia';
    }

    /**
     * Construye la cadena territorial legible completa (ej. "San José, Andrés Mata, Sucre").
     */
    public function getFullLocationAttribute()
    {
        $parts = [];
        if ($this->parish)       $parts[] = $this->parish->name;
        if ($this->municipality) $parts[] = $this->municipality->name;
        if ($this->state)        $parts[] = $this->state->name;
        return implode(', ', $parts) ?: 'Sin ubicación';
    }

    /**
     * Devuelve la cédula con puntos separadores de miles para visualización formal (ej. "V-12.345.678").
     */
    public function getCedulaFullAttribute(): string
    {
        if (!$this->cedula) return '—';
        return "{$this->cedula_type}-" . number_format((int) $this->cedula, 0, '', '.');
    }

    /**
     * Devuelve la cédula en formato compacto estándar venezolano (ej. "V-12345678").
     * Si no se especificó tipo de documento, asume 'V' (Venezolano).
     */
    public function getCedulaFormattedAttribute(): ?string
    {
        if (empty($this->cedula)) {
            return null;
        }

        $type = $this->cedula_type ?: 'V';

        return strtoupper($type) . '-' . $this->cedula;
    }
}
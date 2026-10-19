<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\LogsActivityWithDescriptions;

/**
 * Modelo Deforestation
 * 
 * Representa el registro histórico de pérdida de cobertura boscosa para un Parcela específico
 * durante un año calendario determinado (obtenido a través de sensores satelitales como Global Forest Watch).
 * 
 * Permite monitorear la evolución temporal de la deforestación parcela por parcela,
 * almacenando tanto el área afectada en hectáreas como el porcentaje relativo que representa
 * sobre la superficie total del predio.
 */
class Deforestation extends Model
{
    use HasFactory, SoftDeletes, LogsActivityWithDescriptions;

    /**
     * Nombre explícito de la tabla en base de datos.
     */
    protected $table = 'deforestation';

    /**
     * Atributos asignables de forma masiva:
     * - polygon_id: Identificador de la Parcela/parcela monitoreada.
     * - year: Año calendario de la observación satelital (ej. 2020, 2021, etc.).
     * - deforested_area_ha: Superficie de bosque talado o perdido durante ese año (en hectáreas).
     * - percentage_loss: Porcentaje que representa esa pérdida respecto al área total de la Parcela.
     */
    protected $fillable = [
        'polygon_id',
        'year',
        'deforested_area_ha',
        'percentage_loss',
    ];

    /**
     * Casts de tipos de datos para mantener precisión numérica estricta:
     * - deforested_area_ha: Decimal con 4 cifras decimales para mediciones de área precisas.
     * - percentage_loss: Decimal con 2 cifras decimales para tasas porcentuales (ej. 14.25%).
     */
    protected $casts = [
        'deforested_area_ha' => 'decimal:4',
        'percentage_loss'    => 'decimal:2',
    ];

    // =========================================================================
    // Relaciones del Modelo
    // =========================================================================

    /**
     * Parcela o parcela territorial a la que pertenece esta alerta/registro de deforestación.
     */
    public function polygon()
    {
        return $this->belongsTo(Polygon::class);
    }

    // =========================================================================
    // Configuración de Activity Log (Historial de Auditoría)
    // =========================================================================

    /**
     * Campos que se auditan ante inserciones, actualizaciones o eliminaciones.
     */
    protected function getActivitylogAttributes(): array
    {
        return ['polygon_id', 'year', 'deforested_area_ha', 'percentage_loss'];
    }

    /**
     * Traducción amigable de los nombres de campos para la bitácora de auditoría.
     */
    protected function getActivityDescriptions(): array
    {
        return [
            'polygon_id'           => 'Parcela asociado',
            'year'                 => 'Año',
            'deforested_area_ha'   => 'Área deforestada (ha)',
            'percentage_loss'      => 'Porcentaje de pérdida',
        ];
    }

    /**
     * Orden de importancia al listar cambios en las pantallas de auditoría del administrador.
     */
    protected function getActivityPriority(): array
    {
        return ['year', 'deforested_area_ha', 'percentage_loss'];
    }

    /**
     * Etiqueta descriptiva que sintetiza el evento para los reportes de auditoría.
     */
    protected function getActivityLabel(): ?string
    {
        if ($this->polygon) {
            return "Análisis de '{$this->polygon->name}' - {$this->year}";
        }
        return "Análisis #{$this->id} - {$this->year}";
    }

    // =========================================================================
    // Ciclo de Vida y Sincronización Automática
    // =========================================================================

    /**
     * Eventos de ciclo de vida del modelo:
     * 
     * Mantiene sincronizada automáticamente la bandera `deforested` (booleana) de la Parcela padre:
     * - `saved`: Al registrar un nuevo año o editar la superficie deforestada.
     * - `deleted`: Al eliminar un registro de deforestación (por ejemplo, si fue un falso positivo).
     * - `restored`: Al restaurar un registro eliminado con SoftDeletes.
     * 
     * Esto asegura que las consultas rápidas sobre si un Parcela está deforestado o no
     * no tengan inconsistencias de datos.
     */
    protected static function booted(): void
    {
        static::saved(function (Deforestation $deforestation) {
            $deforestation->polygon?->refreshDeforestedFlag();
        });

        static::deleted(function (Deforestation $deforestation) {
            $deforestation->polygon?->refreshDeforestedFlag();
        });

        static::restored(function (Deforestation $deforestation) {
            $deforestation->polygon?->refreshDeforestedFlag();
        });
    }
}
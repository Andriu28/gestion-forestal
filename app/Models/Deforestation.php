<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\LogsActivityWithDescriptions;

class Deforestation extends Model
{
    use HasFactory, SoftDeletes, LogsActivityWithDescriptions;

    protected $table = 'deforestation';

    protected $fillable = [
        'polygon_id',
        'year',
        'deforested_area_ha',
        'percentage_loss',
    ];

    protected $casts = [
        'deforested_area_ha' => 'decimal:4',
        'percentage_loss'    => 'decimal:2',
    ];

    // =========================================================================
    // Relaciones
    // =========================================================================

    public function polygon()
    {
        return $this->belongsTo(Polygon::class);
    }

    // =========================================================================
    // Configuración de Activity Log
    // =========================================================================

    protected function getActivitylogAttributes(): array
    {
        return ['polygon_id', 'year', 'deforested_area_ha', 'percentage_loss'];
    }

    protected function getActivityDescriptions(): array
    {
        return [
            'polygon_id'           => 'Polígono asociado',
            'year'                 => 'Año',
            'deforested_area_ha'   => 'Área deforestada (ha)',
            'percentage_loss'      => 'Porcentaje de pérdida',
        ];
    }

    protected function getActivityPriority(): array
    {
        return ['year', 'deforested_area_ha', 'percentage_loss'];
    }

    protected function getActivityLabel(): ?string
    {
        if ($this->polygon) {
            return "Análisis de '{$this->polygon->name}' - {$this->year}";
        }
        return "Análisis #{$this->id} - {$this->year}";
    }
}
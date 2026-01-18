<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deforestation extends Model
{
    use HasFactory, SoftDeletes;

    // Nombre de la tabla (si no sigue convenciones)
    protected $table = 'deforestation';

    // Campos que se pueden llenar masivamente
    protected $fillable = [
        'polygon_id',
        'year',
        'deforested_area_ha',
        'percentage_loss'
    ];

    // Casts para los tipos de datos
    protected $casts = [
        'deforested_area_ha' => 'decimal:4',
        'percentage_loss' => 'decimal:2',
        'year' => 'integer',
    ];

    // Relación inversa con Polygon
    public function polygon(): BelongsTo
    {
        return $this->belongsTo(Polygon::class);
    }

    // Accesor para el área formateada
    public function getAreaFormattedAttribute(): string
    {
        return number_format($this->deforested_area_ha, 4) . ' ha';
    }

    // Accesor para el porcentaje formateado
    public function getPercentageFormattedAttribute(): string
    {
        return number_format($this->percentage_loss, 2) . ' %';
    }
}
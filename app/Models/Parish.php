<?php
// [file name]: app/Models/Parish.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Parish extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'municipality_id'
    ];

    // Relaciones
    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function polygons()
    {
        return $this->hasMany(Polygon::class);
    }

    // Scope para búsqueda
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
                    ->orWhereHas('municipality', function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhereHas('state', function($q2) use ($search) {
                              $q2->where('name', 'like', "%{$search}%");
                          });
                    });
    }
}
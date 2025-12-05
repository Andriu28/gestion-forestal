<?php
// [file name]: app/Models/Municipality.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Municipality extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'state_id'
    ];

    // Relaciones
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function parishes()
    {
        return $this->hasMany(Parish::class);
    }
}
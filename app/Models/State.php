<?php
// [file name]: app/Models/State.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class State extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    // Relaciones
    public function municipalities()
    {
        return $this->hasMany(Municipality::class);
    }

    public function parishes()
    {
        return $this->hasManyThrough(Parish::class, Municipality::class);
    }
}
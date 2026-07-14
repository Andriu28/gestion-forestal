<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivityWithDescriptions; // ← Importar el wrapper

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes, LogsActivityWithDescriptions;

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Atributos a registrar en el log (sin incluir password por seguridad).
     */
    protected function getActivitylogAttributes(): array
    {
        return ['name', 'email', 'role'];
    }

    /**
     * Mapa de descripciones amigables para cada atributo.
     */
    protected function getActivityDescriptions(): array
    {
        return [
            'name'  => 'Nombre',
            'email' => 'Correo',
            'role'  => 'Rol',
        ];
    }

    /**
     * Orden de prioridad para revisar cambios.
     * Los primeros atributos tienen prioridad al generar la descripción.
     */
    protected function getActivityPriority(): array
    {
        return ['role', 'name', 'email'];
    }

    /**
     * Etiqueta para mostrar en las descripciones (ej. "Usuario 'nombre'").
     */
    protected function getActivityLabel(): ?string
    {
        return $this->name ?: 'Usuario #' . $this->id;
    }

    
}
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes; 


class User extends Authenticatable implements MustVerifyEmail{

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'role']) // Añadir 'role'
            ->logOnlyDirty()
            ->setDescriptionForEvent(function(string $eventName) {
                $userName = $this->name ?: 'Usuario #' . $this->id;
                
                switch($eventName) {
                    case 'created':
                        return "Usuario '{$userName}' fue creado";
                    case 'updated':
                        return "Usuario '{$userName}' fue actualizado su rol";
                    case 'deleted':
                        return "Usuario '{$userName}' fue deshabilitado";
                    case 'restored':
                        return "Usuario '{$userName}' fue habilitado";
                    default:
                        return "Usuario '{$userName}' fue {$eventName}";
                }
            })
            ->dontSubmitEmptyLogs();
    }
}

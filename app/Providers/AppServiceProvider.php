<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL; // <-- MANTENER esto
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // ❌❌❌ COMENTA o ELIMINA ESTA LÍNEA COMPLETAMENTE ❌❌❌
        // URL::forceScheme('https');
        
        // Mantener solo los eventos:
        Event::listen(function (Login $event) {
            activity()
                ->causedBy($event->user)
                ->log('ha iniciado sesión');
        });

        Event::listen(function (Logout $event) {
            activity()
                ->causedBy($event->user)
                ->log('ha cerrado sesión');
        });
    }
}
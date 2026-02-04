<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL; 
use Illuminate\Support\Facades\Request;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Events\MessageFailed;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // ✅ Forzar HTTPS solo en producción
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            
            // Configurar trusted hosts para Railway
            $this->configureTrustedHosts();
            
            // Configurar manejo de errores de email
            $this->configureEmailErrorHandling();
        }
        
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
        
        // Configuración global para URLs de verificación
        $this->configureVerificationUrls();
    }
    
    /**
     * Configurar trusted hosts para Railway
     */
    protected function configureTrustedHosts(): void
    {
        Request::setTrustedHosts([
            'gestion-forestal-production.up.railway.app',
            '^.+\.up\.railway\.app$', // Todos los subdominios Railway
        ]);
        
        // Configurar dominio para cookies
        config(['session.domain' => '.up.railway.app']);
    }
    
    /**
     * Configurar manejo de errores de email
     */
    protected function configureEmailErrorHandling(): void
    {
        Event::listen(MessageFailed::class, function ($event) {
            Log::channel('mail')->error('Fallo en envío de email', [
                'to' => $this->extractEmailAddresses($event->message->getTo()),
                'subject' => $event->message->getSubject(),
                'error' => $event->exception->getMessage(),
            ]);
        });
        
        // Macro para envío seguro
        Mail::macro('sendSafely', function ($mailable, array $options = []) {
            try {
                return Mail::send($mailable);
            } catch (\Exception $e) {
                Log::channel('mail')->warning('Email enviado en modo seguro (log)', [
                    'error' => $e->getMessage(),
                ]);
                
                // En producción, loggear pero no fallar
                if (app()->environment('production')) {
                    Mail::mailer('log')->send($mailable);
                    return true;
                }
                
                throw $e;
            }
        });
    }
    
    /**
     * Configurar URLs de verificación para producción
     */
    protected function configureVerificationUrls(): void
    {
        // Asegurar que las URLs de verificación usen APP_URL correcto
        if (app()->environment('production')) {
            $appUrl = config('app.url');
            if ($appUrl && !str_contains($appUrl, 'localhost')) {
                // Sobrescribir cualquier URL localhost
                URL::forceRootUrl($appUrl);
            }
        }
    }
    
    /**
     * Extraer direcciones de email para logging
     */
    private function extractEmailAddresses(?array $addresses): array
    {
        if (!$addresses) return [];
        
        return array_map(function ($address) {
            return $address->getAddress();
        }, $addresses);
    }
}
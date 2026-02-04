<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Mail\Events\MessageFailed;

class MailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Log de emails en desarrollo
        if ($this->app->environment('local', 'development')) {
            $this->configureMailLogging();
        }
        
        // Manejo de fallos en producción
        if ($this->app->environment('production')) {
            $this->configureMailFailureHandling();
        }
    }
    
    /**
     * Configurar logging de emails
     */
    protected function configureMailLogging(): void
    {
        Event::listen(MessageSending::class, function ($event) {
            Log::channel('mail')->debug('📤 Enviando email', [
                'to' => $this->formatAddresses($event->message->getTo()),
                'subject' => $event->message->getSubject(),
                'from' => $this->formatAddresses($event->message->getFrom()),
            ]);
        });
        
        Event::listen(MessageSent::class, function ($event) {
            Log::channel('mail')->info('✅ Email enviado', [
                'to' => $this->formatAddresses($event->message->getTo()),
                'subject' => $event->message->getSubject(),
            ]);
        });
    }
    
    /**
     * Configurar manejo de fallos de email
     */
    protected function configureMailFailureHandling(): void
    {
        Event::listen(MessageFailed::class, function ($event) {
            Log::channel('mail')->error('❌ Fallo en email', [
                'to' => $this->formatAddresses($event->message->getTo()),
                'subject' => $event->message->getSubject(),
                'exception' => $event->exception->getMessage(),
                'trace' => substr($event->exception->getTraceAsString(), 0, 500),
            ]);
            
            // Notificación adicional si hay muchos fallos
            $this->notifyIfCriticalFailure($event);
        });
    }
    
    /**
     * Formatear direcciones para logging
     */
    private function formatAddresses(?array $addresses): array
    {
        if (!$addresses) {
            return ['none'];
        }
        
        return collect($addresses)->map(function ($address, $key) {
            return is_numeric($key) ? $address->getAddress() : "{$key} <{$address->getAddress()}>";
        })->toArray();
    }
    
    /**
     * Notificar si hay fallos críticos
     */
    private function notifyIfCriticalFailure($event): void
    {
        static $failureCount = 0;
        static $lastNotification = null;
        
        $failureCount++;
        
        // Notificar cada 5 fallos o después de 5 minutos
        if ($failureCount >= 5 || 
            ($lastNotification && now()->diffInMinutes($lastNotification) >= 5)) {
            
            Log::channel('slack')->error('🚨 Múltiples fallos en emails', [
                'failures' => $failureCount,
                'last_error' => $event->exception->getMessage(),
            ]);
            
            $failureCount = 0;
            $lastNotification = now();
        }
    }
}
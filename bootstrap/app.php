<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ⚠️ IMPORTANTE: trustProxies debe ir PRIMERO
        $middleware->trustProxies(at: '*');
        
        // Luego tus otros middlewares
        $middleware->alias([
            'is.admin' => \App\Http\Middleware\IsAdmin::class,
        ]);
        
        $middleware->web(append: [
            // Si tienes otros middlewares web
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
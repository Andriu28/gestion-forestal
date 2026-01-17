<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\ProducerController; 
use App\Http\Controllers\PolygonController; 
use App\Http\Controllers\AreaController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\DeforestationController;
use App\Http\Controllers\ForestController;


//  RUTA TEMPORAL PARA EJECUTAR SEEDERS - ELIMINAR DESPUÉS
//CODIGO PARA EJECUTAR LOS SEEDER EN LA BASE DE DATOS
Route::get('/run-seeders', function() {
    try {
        // Eliminar todos los usuarios existentes antes de sembrar
        \App\Models\User::truncate();
        
        \Artisan::call('db:seed', ['--force' => true]);
        $userCount = \App\Models\User::count();
        return " Seeders ejecutados exitosamente. Usuarios en la base de datos: $userCount";
    } catch (\Exception $e) {
        return " Error: " . $e->getMessage();
    }
});


// RUTA PARA VERIFICAR CONFIGURACIÓN DE CORREO
Route::get('/check-mail-config', function() {
    return [
        'mail_mailer' => config('mail.default'),
        'mail_host' => config('mail.mailers.smtp.host'),
        'mail_timeout' => config('mail.mailers.smtp.timeout'),
        'env_mailer' => env('MAIL_MAILER'),
    ];
});

// En routes/web.php
Route::get('/test-email', function() {
    try {
        \Mail::raw('Email de prueba', function($message) {
            $message->to('diperishilla2468@gmail.com')
                    ->subject('Prueba de correo');
        });
        return 'Email enviado correctamente';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

// Rutas públicas
Route::get('/', function () {
    return view('auth.login');
});


// Rutas accesibles para cualquier usuario autenticado (básico o administrador)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/forest-stats', [ForestController::class, 'showStats'])->name('forest.stats');
    Route::get('/radd-alerts', [ForestController::class, 'showRADDAlerts']);

   // RUTAS DE productorES 
    Route::resource('providers', ProviderController::class)->names([
        'index' => 'providers.index',
        'create' => 'providers.create', 
        'edit' => 'providers.edit',
        'update' => 'providers.update',
    ]);

    // Rutas adicionales para funcionalidades extra de productores
    Route::prefix('providers')->group(function () {
        Route::post('{provider}/toggle-status', [ProviderController::class, 'toggleStatus'])
            ->name('providers.toggle-status');
        Route::post('{provider}/restore', [ProviderController::class, 'restore'])
            ->name('providers.restore');
        Route::delete('{provider}/force-delete', [ProviderController::class, 'forceDelete'])
            ->name('providers.force-delete');
    });


    // RUTAS DE PRODUCTORES 
    Route::resource('producers', ProducerController::class)->names([
        'index' => 'producers.index',
        'create' => 'producers.create', 
        'edit' => 'producers.edit',
        'update' => 'producers.update',
    ]);

    // Rutas adicionales para funcionalidades extra de productores
    Route::prefix('producers')->group(function () {
        Route::post('{producer}/toggle-status', [ProducerController::class, 'toggleStatus'])
            ->name('producers.toggle-status');
        Route::post('{id}/restore', [ProducerController::class, 'restore'])
            ->name('producers.restore');
        Route::delete('{id}/force-delete', [ProducerController::class, 'forceDelete'])
            ->name('producers.force-delete');
    });

    


    // Rutas para gestión de áreas
    Route::resource('areas', AreaController::class)->except(['show']);

    // Rutas adicionales
    Route::prefix('areas')->group(function () {
        Route::get('/{area}/toggle-status', [AreaController::class, 'toggleStatus'])
            ->name('areas.toggle-status');
        
        Route::get('/search/{search}', [AreaController::class, 'search'])
            ->name('areas.search');
        
        Route::get('/{area}', [AreaController::class, 'show'])
            ->name('areas.show');
    });


      // ========== RUTAS PARA POLÍGONOS - ORDEN CORREGIDO ==========
    
    // PRIMERO las rutas personalizadas (específicas)
    Route::get('/polygons/map', [PolygonController::class, 'map'])->name('polygons.map');
    Route::get('/polygons/geojson', [PolygonController::class, 'geojson'])->name('polygons.geojson');
    Route::post('/polygons/find-parish', [PolygonController::class, 'findParishApi'])->name('polygons.find-parish-api');
    Route::post('/polygons/{polygon}/toggle-status', [PolygonController::class, 'toggleStatus'])->name('polygons.toggle-status');
    Route::post('/polygons/{id}/restore', [PolygonController::class, 'restore'])->name('polygons.restore');

    // LUEGO la ruta resource (genérica)
    Route::resource('polygons', PolygonController::class);

    Route::get('/polygons/{polygon}/details', [PolygonController::class, 'details'])
    ->name('polygons.details')
    ->middleware('auth');

       // Grupo de rutas para deforestación - CORREGIDO
    Route::prefix('deforestation')->name('deforestation.')->group(function () {
        
        // Mostrar formulario de análisis - RUTA CORREGIDA
        Route::get('/create', [DeforestationController::class, 'create'])
            ->name('create');
        
        // Procesar análisis 
        Route::post('/analyze', [DeforestationController::class, 'analyze'])
            ->name('analyze');
        
        // Mostrar resultados para múltiples polígonos
        Route::get('/multiple-results', [DeforestationController::class, 'multipleResults'])
            ->name('multiple-results');
        
        // Mostrar resultados para un solo polígono
        Route::get('/results/{polygon}', [DeforestationController::class, 'results'])
            ->name('results');
        
        // Exportar datos
        Route::get('/export/{polygon}', [DeforestationController::class, 'export'])
            ->name('export');
        
        // Generar reporte PDF
        Route::get('/report/{polygon}', [DeforestationController::class, 'report'])
            ->name('report');
        
        // API para obtener datos del análisis (para gráficos)
        Route::get('/api/analysis-data/{polygon}', [DeforestationController::class, 'getAnalysisData'])
            ->name('api.analysis-data');
    });


});
// Rutas exclusivas para ADMINISTRADORES
// Rutas del panel de administración (solo para administradores)
Route::middleware(['auth', 'verified', 'is.admin'])->prefix('admin')->name('admin.')->group(function () {
    // Usuarios: Rutas de recurso y adicionales
    Route::resource('users', UserController::class)->except(['show'])->names([
        'index' => 'users.index',
        'create' => 'users.create',
        'store' => 'users.store',
        'edit' => 'users.edit',
        'update' => 'users.update',
        'destroy' => 'users.destroy',
    ]);

    //MOVER AQUÍ las rutas personalizadas de usuarios
    Route::get('users/disabled', [UserController::class, 'listDisabledUsers'])->name('users.disabled');
    Route::patch('users/{user}/update-role', [UserController::class, 'updateUserRole'])->name('users.update-role');
    Route::post('users/{user}/enable', [UserController::class, 'enableUser'])->name('users.enable');
    
    //MOVER también la ruta de auditoría aquí
    Route::get('/audit', [AuditLogController::class, 'showAuditLog'])->name('audit');
});



// Esta línea es la que importa las rutas de autenticación
require __DIR__.'/auth.php';
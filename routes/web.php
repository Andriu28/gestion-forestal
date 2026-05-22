<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\ProducerController;
use App\Http\Controllers\PolygonController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\DeforestationController;
use App\Http\Controllers\ForestController;
use App\Http\Controllers\SupportController;

/*
|--------------------------------------------------------------------------
| Rutas de Diagnóstico (SOLO DESARROLLO)
|--------------------------------------------------------------------------
| IMPORTANTE: Estas rutas deben estar deshabilitadas en producción.
| Se recomienda eliminarlas o envolverlas en un condicional de entorno.
*/



/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/

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

// RUTA TEMPORAL PARA EJECUTAR SEEDERS DE PRODUCTORES - ELIMINAR DESPUÉS
Route::get('/run-producers', function() {
    try {
        // Opcional: Limpiar la tabla antes de sembrar
        \App\Models\Producer::truncate();
        
        \Artisan::call('db:seed', [
            '--class' => 'ProducersSeeder',
            '--force' => true
        ]);
        
        $producerCount = \App\Models\Producer::count();
        return "Seeder ProducersSeeder ejecutado exitosamente. Productores en la base de datos: $producerCount";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

// ===== NUEVA RUTA PARA EJECUTAR EL POLYGONS SEEDER =====
Route::get('/run-polygons', function() {
    try {
        // Opcional: Limpiar la tabla antes de sembrar (descomenta si quieres borrar todo)
        \App\Models\Polygon::truncate();
        
        \Artisan::call('db:seed', [
            '--class' => 'PolygonsSeeder',
            '--force' => true
        ]);
        
        $polygonCount = \App\Models\Polygon::count();
        $output = \Artisan::output();
        
        return "Seeder PolygonsSeeder ejecutado exitosamente. Polígonos en la base de datos: $polygonCount";
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});


Route::get('/', function () {
    return view('auth.login');
});

/*
|--------------------------------------------------------------------------
| Rutas para Usuarios Autenticados y Verificados
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    
    // ========== DASHBOARD Y PERFIL ==========
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // ========== SOPORTE Y AYUDA ==========
    Route::get('/support', [SupportController::class, 'index'])->name('support');
    Route::get('/support/download', [SupportController::class, 'generatePdf'])->name('support.pdf');
    Route::view('/developers', 'developers.developers')->name('developers');

    // ========== MÓDULO DE RESPALDOS ==========
    Route::prefix('backups')->name('backups.')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index');
        Route::post('/create', [BackupController::class, 'create'])->name('create');
        Route::get('/download/{filename}', [BackupController::class, 'download'])->name('download');
        Route::delete('/{filename}', [BackupController::class, 'destroy'])->name('destroy');
        Route::post('/restore', [BackupController::class, 'restore'])->name('restore');
        Route::post('/import', [BackupController::class, 'import'])->name('import');
    });
    
    // ========== ESTADÍSTICAS FORESTALES ==========
    Route::get('/forest-stats', [ForestController::class, 'showStats'])->name('forest.stats');
    Route::get('/radd-alerts', [ForestController::class, 'showRADDAlerts']);
    
    /*
    |--------------------------------------------------------------------------
    | MÓDULO: Productores (Producers)
    |--------------------------------------------------------------------------
    */
    
    // Rutas personalizadas (ANTES del resource)
    Route::prefix('producers')->name('producers.')->group(function () {
        Route::get('/deleted', [ProducerController::class, 'deleted'])->name('deleted');
        Route::get('/generate-pdf', [ProducerController::class, 'generatePdf'])->name('generate.pdf');
        
        // Rutas con parámetro {producer}
        Route::get('/{producer}/details', [ProducerController::class, 'details'])->name('details');
        Route::post('/{producer}/toggle-status', [ProducerController::class, 'toggleStatus'])
            ->name('toggle-status');
        Route::post('/{producer}/restore', [ProducerController::class, 'restore'])->name('restore');
        Route::delete('/{producer}/force-delete', [ProducerController::class, 'forceDelete'])
            ->name('force-delete');
    });
    
    Route::resource('producers', ProducerController::class);
    
    /*
    |--------------------------------------------------------------------------
    | MÓDULO: Polígonos
    |--------------------------------------------------------------------------
    */
    
    // Rutas personalizadas (ANTES del resource)
    Route::prefix('polygons')->name('polygons.')->group(function () {
        // Vistas especiales
        Route::get('/map', [PolygonController::class, 'map'])->name('map');
        Route::get('/geojson', [PolygonController::class, 'geojson'])->name('geojson');
        Route::get('/deleted', [PolygonController::class, 'deleted'])->name('deleted');
        
        // API endpoints
        Route::post('/find-parish', [PolygonController::class, 'findParishApi'])
            ->name('find-parish-api');
        
        // Acciones sobre polígonos específicos
        Route::get('/{polygon}/details', [PolygonController::class, 'details'])->name('details');
        Route::post('/{polygon}/toggle-status', [PolygonController::class, 'toggleStatus'])
            ->name('toggle-status');
        Route::post('/{polygon}/restore', [PolygonController::class, 'restore'])->name('restore');
        Route::delete('/{polygon}/force-delete', [PolygonController::class, 'forceDelete'])
            ->name('force-delete');
    });
    
    Route::resource('polygons', PolygonController::class);
    
    /*
    |--------------------------------------------------------------------------
    | MÓDULO: Deforestación
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('deforestation')->name('deforestation.')->group(function () {
        // Formulario y procesamiento
        Route::get('/create', [DeforestationController::class, 'create'])->name('create');
        Route::post('/analyze', [DeforestationController::class, 'analyze'])->name('analyze');
        Route::post('/polygon', [DeforestationController::class, 'polygon'])->name('polygon');
        
        // Resultados
        Route::get('/multiple-results', [DeforestationController::class, 'multipleResults'])
            ->name('multiple-results');
        Route::get('/results/{polygon}', [DeforestationController::class, 'results'])->name('results');
        
        // Exportación y reportes
        Route::get('/export/{polygon}', [DeforestationController::class, 'export'])->name('export');
        Route::post('/generar-report', [DeforestationController::class, 'report'])->name('report');
        
        // API para gráficos
        Route::get('/api/analysis-data/{polygon}', [DeforestationController::class, 'getAnalysisData'])
            ->name('api.analysis-data');
    });
});

/*
|--------------------------------------------------------------------------
| Rutas de Administración (Solo Administradores)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'is.admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        
        // Rutas personalizadas de usuarios (ANTES del resource)
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/disabled', [UserController::class, 'listDisabledUsers'])->name('disabled');
            Route::patch('/{user}/update-role', [UserController::class, 'updateUserRole'])
                ->name('update-role');
            Route::post('/{user}/enable', [UserController::class, 'enableUser'])->name('enable');
        });
        
        Route::resource('users', UserController::class)->except(['show']);
        
        // Auditoría
        Route::get('/audit', [AuditLogController::class, 'showAuditLog'])->name('audit');
    });

/*
|--------------------------------------------------------------------------
| Rutas de Autenticación (Laravel Breeze/Jetstream)
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
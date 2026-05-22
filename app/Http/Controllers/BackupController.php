<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class BackupController extends Controller implements HasMiddleware
{
    /**
     * Define los middlewares para este controlador
     */
    public static function middleware(): array
    {
        return [
            new Middleware(function ($request, $next) {
                if (!in_array(auth()->user()->role, ['administrador', 'tecnico'])) {
                    abort(403, 'No tienes permiso para acceder a esta sección.');
                }
                return $next($request);
            })
        ];
    }

    /**
     * Muestra la página principal de respaldos
     */
    public function index()
    {
        $backups = $this->getBackupList();
        
        return view('backups.index', compact('backups'));
    }

    /**
     * Crea un nuevo backup de la base de datos
     */
    public function create(Request $request)
    {
        try {
            $request->validate([
                'backup_name' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9_-]+$/',
            ]);

            $database = config('database.connections.pgsql.database');
            $username = config('database.connections.pgsql.username');
            $password = config('database.connections.pgsql.password');
            $host = config('database.connections.pgsql.host');
            $port = config('database.connections.pgsql.port');
            
            // Nombre del archivo de backup
            $backupName = $request->input('backup_name');
            $filename = $backupName 
                ? $backupName . '_' . now()->format('Y-m-d_H-i-s') . '.sql'
                : 'backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
            
            $path = storage_path('app/backups/' . $filename);
            
            // Crear directorio si no existe
            if (!File::exists(storage_path('app/backups'))) {
                File::makeDirectory(storage_path('app/backups'), 0755, true);
            }
            
            // Comando para pg_dump
            $command = sprintf(
                'PGPASSWORD=%s pg_dump -h %s -p %s -U %s -d %s -F c -b -v -f %s',
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($database),
                escapeshellarg($path)
            );
            
            // Ejecutar el comando
            $process = Process::fromShellCommandline($command);
            $process->setTimeout(300); // 5 minutos máximo
            $process->run();
            
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            
            // Registrar actividad
            activity()
                ->causedBy(auth()->user())
                ->log("Respaldó la base de datos: {$filename}");
            
            return response()->json([
                'success' => true,
                'message' => 'Backup creado exitosamente',
                'filename' => $filename,
                'size' => $this->formatBytes(File::size($path))
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descarga un archivo de backup
     */
    public function download($filename)
    {
        $path = storage_path('app/backups/' . $filename);
        
        if (!File::exists($path)) {
            return back()->with('swal', [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'El archivo de backup no existe.'
            ]);
        }
        
        // Registrar actividad
        activity()
            ->causedBy(auth()->user())
            ->log("Descargó el backup: {$filename}");
        
        return response()->download($path);
    }

    /**
     * Elimina un archivo de backup
     */
    public function destroy($filename)
    {
        try {
            $path = storage_path('app/backups/' . $filename);
            
            if (!File::exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo de backup no existe.'
                ], 404);
            }
            
            File::delete($path);
            
            // Registrar actividad
            activity()
                ->causedBy(auth()->user())
                ->log("Eliminó el backup: {$filename}");
            
            return response()->json([
                'success' => true,
                'message' => 'Backup eliminado exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaura la base de datos desde un backup
     */
    public function restore(Request $request)
    {
        try {
            $request->validate([
                'backup_file' => 'required|string',
                'confirm_restore' => 'required|in:YES,SI'
            ]);
            
            // Verificar confirmación
            if (!in_array($request->input('confirm_restore'), ['YES', 'SI'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes escribir YES para confirmar la restauración.'
                ], 400);
            }
            
            $filename = $request->input('backup_file');
            $path = storage_path('app/backups/' . $filename);
            
            if (!File::exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo de backup no existe.'
                ], 404);
            }
            
            // Crear backup de seguridad antes de restaurar
            $safetyBackup = 'pre_restore_' . now()->format('Y-m-d_H-i-s') . '.sql';
            $this->createSafetyBackup($safetyBackup);
            
            $database = config('database.connections.pgsql.database');
            $username = config('database.connections.pgsql.username');
            $password = config('database.connections.pgsql.password');
            $host = config('database.connections.pgsql.host');
            $port = config('database.connections.pgsql.port');
            
            // Comando para pg_restore
            $command = sprintf(
                'PGPASSWORD=%s pg_restore -h %s -p %s -U %s -d %s -c -v %s',
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($database),
                escapeshellarg($path)
            );
            
            $process = Process::fromShellCommandline($command);
            $process->setTimeout(600); // 10 minutos máximo
            $process->run();
            
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            
            // Registrar actividad
            activity()
                ->causedBy(auth()->user())
                ->log("Restauró la base de datos desde: {$filename}");
            
            return response()->json([
                'success' => true,
                'message' => 'Base de datos restaurada exitosamente. Se recomienda cerrar sesión y volver a iniciar.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar la base de datos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Importa un archivo SQL externo
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'sql_file' => 'required|file|mimes:sql,txt|max:102400', // 100MB máximo
            ]);
            
            $file = $request->file('sql_file');
            $filename = 'import_' . now()->format('Y-m-d_H-i-s') . '.sql';
            
            // Guardar el archivo temporalmente
            $file->storeAs('temp', $filename);
            $path = storage_path('app/temp/' . $filename);
            
            // Crear backup de seguridad antes de importar
            $safetyBackup = 'pre_import_' . now()->format('Y-m-d_H-i-s') . '.sql';
            $this->createSafetyBackup($safetyBackup);
            
            $database = config('database.connections.pgsql.database');
            $username = config('database.connections.pgsql.username');
            $password = config('database.connections.pgsql.password');
            $host = config('database.connections.pgsql.host');
            $port = config('database.connections.pgsql.port');
            
            // Comando para psql (importar SQL plano)
            $command = sprintf(
                'PGPASSWORD=%s psql -h %s -p %s -U %s -d %s -f %s',
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($database),
                escapeshellarg($path)
            );
            
            $process = Process::fromShellCommandline($command);
            $process->setTimeout(600);
            $process->run();
            
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            
            // Limpiar archivo temporal
            File::delete($path);
            
            // Registrar actividad
            activity()
                ->causedBy(auth()->user())
                ->log("Importó un archivo SQL externo: {$file->getClientOriginalName()}");
            
            return response()->json([
                'success' => true,
                'message' => 'Archivo SQL importado exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al importar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene la lista de backups existentes
     */
    private function getBackupList()
    {
        $backupPath = storage_path('app/backups');
        
        if (!File::exists($backupPath)) {
            return collect([]);
        }
        
        $files = File::files($backupPath);
        
        return collect($files)->map(function ($file) {
            return [
                'filename' => $file->getFilename(),
                'size' => $this->formatBytes($file->getSize()),
                'size_bytes' => $file->getSize(),
                'date' => date('d/m/Y H:i:s', $file->getMTime()),
                'timestamp' => $file->getMTime(),
            ];
        })->sortByDesc('timestamp')->values();
    }

    /**
     * Crea un backup de seguridad antes de restaurar/importar
     */
    private function createSafetyBackup($filename)
    {
        $database = config('database.connections.pgsql.database');
        $username = config('database.connections.pgsql.username');
        $password = config('database.connections.pgsql.password');
        $host = config('database.connections.pgsql.host');
        $port = config('database.connections.pgsql.port');
        
        $path = storage_path('app/backups/' . $filename);
        
        $command = sprintf(
            'PGPASSWORD=%s pg_dump -h %s -p %s -U %s -d %s -F c -b -v -f %s',
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($path)
        );
        
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(300);
        $process->run();
    }

    /**
     * Formatea bytes a formato legible
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
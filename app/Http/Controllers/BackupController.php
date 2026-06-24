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
            
            // Encontrar la ruta de pg_dump
            try {
                $pgDumpPath = $this->findPostgresqlExecutable('pg_dump');
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() . '. También puedes agregar PG_PATH=C:\\xampp\\pgsql\\bin en tu archivo .env'
                ], 500);
            }
            
            // Detectar sistema operativo
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // En Windows, asegurarse de que la ruta no tenga espacios sin comillas
                $escapedPath = '"' . $pgDumpPath . '"';
                $command = sprintf(
                    'set PGPASSWORD=%s && %s -h %s -p %s -U %s -d %s -F c -b -v -f "%s"',
                    escapeshellarg($password),
                    $escapedPath,
                    escapeshellarg($host),
                    escapeshellarg($port),
                    escapeshellarg($username),
                    escapeshellarg($database),
                    $path
                );
            } else {
                // Linux/Mac
                $command = sprintf(
                    'PGPASSWORD=%s %s -h %s -p %s -U %s -d %s -F c -b -v -f %s',
                    escapeshellarg($password),
                    $pgDumpPath,
                    escapeshellarg($host),
                    escapeshellarg($port),
                    escapeshellarg($username),
                    escapeshellarg($database),
                    escapeshellarg($path)
                );
            }
            
            // Ejecutar el comando
            $process = Process::fromShellCommandline($command);
            $process->setTimeout(300);
            $process->run();
            
            if (!$process->isSuccessful()) {
                $error = $process->getErrorOutput();
                $output = $process->getOutput();
                
                throw new \Exception("Error en pg_dump: " . ($error ?: $output));
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
     * Encuentra la ruta del ejecutable de PostgreSQL
     */
    /**
     * Encuentra la ruta del ejecutable de PostgreSQL
     */
    private function findPostgresqlExecutable($executable)
    {
        // Primero, verificar si hay una ruta personalizada en .env
        $customPath = env('PG_PATH');
        if ($customPath && File::exists($customPath . '\\' . $executable . '.exe')) {
            return $customPath . '\\' . $executable . '.exe';
        }
        
        // Ampliar la lista de posibles rutas
        $possiblePaths = [
            // XAMPP con PostgreSQL
            'C:\\xampp\\pgsql\\bin\\' . $executable . '.exe',
            'C:\\xampp\\postgresql\\bin\\' . $executable . '.exe',
            
            // PostgreSQL estándar - Incluyendo versión 18
            'C:\\Program Files\\PostgreSQL\\18\\bin\\' . $executable . '.exe',  // <-- AGREGADA VERSIÓN 18
            'C:\\Program Files\\PostgreSQL\\17\\bin\\' . $executable . '.exe',
            'C:\\Program Files\\PostgreSQL\\16\\bin\\' . $executable . '.exe',
            'C:\\Program Files\\PostgreSQL\\15\\bin\\' . $executable . '.exe',
            'C:\\Program Files\\PostgreSQL\\14\\bin\\' . $executable . '.exe',
            'C:\\Program Files\\PostgreSQL\\13\\bin\\' . $executable . '.exe',
            'C:\\Program Files\\PostgreSQL\\12\\bin\\' . $executable . '.exe',
            'C:\\Program Files\\PostgreSQL\\11\\bin\\' . $executable . '.exe',
            'C:\\Program Files\\PostgreSQL\\10\\bin\\' . $executable . '.exe',
            
            // Program Files (x86)
            'C:\\Program Files (x86)\\PostgreSQL\\18\\bin\\' . $executable . '.exe',  // <-- AGREGADA VERSIÓN 18
            'C:\\Program Files (x86)\\PostgreSQL\\17\\bin\\' . $executable . '.exe',
            'C:\\Program Files (x86)\\PostgreSQL\\16\\bin\\' . $executable . '.exe',
            'C:\\Program Files (x86)\\PostgreSQL\\15\\bin\\' . $executable . '.exe',
            'C:\\Program Files (x86)\\PostgreSQL\\14\\bin\\' . $executable . '.exe',
            'C:\\Program Files (x86)\\PostgreSQL\\13\\bin\\' . $executable . '.exe',
            'C:\\Program Files (x86)\\PostgreSQL\\12\\bin\\' . $executable . '.exe',
            
            // Instalaciones personalizadas comunes
            'C:\\pgsql\\bin\\' . $executable . '.exe',
            'C:\\PostgreSQL\\bin\\' . $executable . '.exe',
            'D:\\PostgreSQL\\bin\\' . $executable . '.exe',
            
            // Usar la variable de entorno PGPATH si existe
            getenv('PGPATH') ? getenv('PGPATH') . '\\' . $executable . '.exe' : null,
        ];
        
        // Filtrar rutas nulas
        $possiblePaths = array_filter($possiblePaths);
        
        // Verificar si el ejecutable existe en alguna de las rutas
        foreach ($possiblePaths as $path) {
            if (File::exists($path)) {
                return $path;
            }
        }
        
        // Intentar encontrar usando comandos del sistema
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Intentar con 'where' en Windows
            $commands = [
                'where ' . $executable,
                'where /R "C:\\Program Files\\PostgreSQL" ' . $executable . '.exe',
                'where /R "C:\\Program Files (x86)\\PostgreSQL" ' . $executable . '.exe',
                'where /R "C:\\xampp" ' . $executable . '.exe',
            ];
            
            foreach ($commands as $cmd) {
                $process = Process::fromShellCommandline($cmd);
                $process->run();
                if ($process->isSuccessful()) {
                    $paths = explode("\n", trim($process->getOutput()));
                    if (!empty($paths[0])) {
                        return trim($paths[0]);
                    }
                }
            }
        } else {
            // En Linux/Mac, usar 'which'
            $process = Process::fromShellCommandline('which ' . $executable);
            $process->run();
            if ($process->isSuccessful()) {
                $path = trim($process->getOutput());
                if (!empty($path)) {
                    return $path;
                }
            }
        }
        
        // Si no se encuentra, lanzar una excepción con información útil
        throw new \Exception(
            "No se pudo encontrar {$executable}.exe. " .
            "Por favor, asegúrate de que PostgreSQL esté instalado y configura la ruta en el archivo .env " .
            "agregando: PG_PATH=C:\\Program Files\\PostgreSQL\\18\\bin"
        );
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
            
            // Encontrar la ruta de pg_restore
            $pgRestorePath = $this->findPostgresqlExecutable('pg_restore');
            
            // Detectar sistema operativo
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows
                $command = sprintf(
                    'set PGPASSWORD=%s && "%s" -h %s -p %s -U %s -d %s -c -v "%s"',
                    escapeshellarg($password),
                    $pgRestorePath,
                    escapeshellarg($host),
                    escapeshellarg($port),
                    escapeshellarg($username),
                    escapeshellarg($database),
                    $path
                );
            } else {
                // Linux/Mac
                $command = sprintf(
                    'PGPASSWORD=%s %s -h %s -p %s -U %s -d %s -c -v %s',
                    escapeshellarg($password),
                    $pgRestorePath,
                    escapeshellarg($host),
                    escapeshellarg($port),
                    escapeshellarg($username),
                    escapeshellarg($database),
                    escapeshellarg($path)
                );
            }
            
            $process = Process::fromShellCommandline($command);
            $process->setTimeout(600);
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
                'sql_file' => 'required|file|max:102400', // Quitamos mimes:sql,txt
            ]);
            
            $file = $request->file('sql_file');
            
            // Validar extensión manualmente
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['sql', 'txt', 'psql', 'dump', 'backup'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo debe tener extensión .sql, .txt, .psql, .dump o .backup'
                ], 400);
            }
            
            // Validar que el archivo no esté vacío
            if ($file->getSize() == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo está vacío'
                ], 400);
            }
            
            $filename = 'import_' . now()->format('Y-m-d_H-i-s') . '.sql';
            
            // Crear directorio temp si no existe
            if (!File::exists(storage_path('app/temp'))) {
                File::makeDirectory(storage_path('app/temp'), 0755, true);
            }
            
            // Guardar el archivo temporalmente
            $path = storage_path('app/temp/' . $filename);
            File::put($path, file_get_contents($file->getRealPath()));
            
            // Verificar que el archivo tenga contenido
            $content = File::get($path);
            if (empty(trim($content))) {
                File::delete($path);
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo está vacío'
                ], 400);
            }
            
            // Detectar si es un dump custom (formato binario) o SQL plano
            $isCustomFormat = $this->isCustomFormatDump($path);
            
            // Crear backup de seguridad antes de importar
            $safetyBackup = 'pre_import_' . now()->format('Y-m-d_H-i-s') . '.sql';
            $this->createSafetyBackup($safetyBackup);
            
            $database = config('database.connections.pgsql.database');
            $username = config('database.connections.pgsql.username');
            $password = config('database.connections.pgsql.password');
            $host = config('database.connections.pgsql.host');
            $port = config('database.connections.pgsql.port');
            
            // Usar pg_restore para formato custom, psql para SQL plano
            if ($isCustomFormat) {
                // Usar pg_restore para formato custom
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $command = sprintf(
                        'set PGPASSWORD=%s && pg_restore -h %s -p %s -U %s -d %s -c -v %s',
                        escapeshellarg($password),
                        escapeshellarg($host),
                        escapeshellarg($port),
                        escapeshellarg($username),
                        escapeshellarg($database),
                        escapeshellarg($path)
                    );
                } else {
                    $command = sprintf(
                        'PGPASSWORD=%s pg_restore -h %s -p %s -U %s -d %s -c -v %s',
                        escapeshellarg($password),
                        escapeshellarg($host),
                        escapeshellarg($port),
                        escapeshellarg($username),
                        escapeshellarg($database),
                        escapeshellarg($path)
                    );
                }
            } else {
                // Usar psql para SQL plano
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $command = sprintf(
                        'set PGPASSWORD=%s && psql -h %s -p %s -U %s -d %s -f %s',
                        escapeshellarg($password),
                        escapeshellarg($host),
                        escapeshellarg($port),
                        escapeshellarg($username),
                        escapeshellarg($database),
                        escapeshellarg($path)
                    );
                } else {
                    $command = sprintf(
                        'PGPASSWORD=%s psql -h %s -p %s -U %s -d %s -f %s',
                        escapeshellarg($password),
                        escapeshellarg($host),
                        escapeshellarg($port),
                        escapeshellarg($username),
                        escapeshellarg($database),
                        escapeshellarg($path)
                    );
                }
            }
            
            $process = Process::fromShellCommandline($command);
            $process->setTimeout(600);
            $process->run();
            
            if (!$process->isSuccessful()) {
                // Limpiar archivo temporal
                File::delete($path);
                
                throw new ProcessFailedException($process);
            }
            
            // Limpiar archivo temporal
            File::delete($path);
            
            // Registrar actividad
            activity()
                ->causedBy(auth()->user())
                ->log("Importó un archivo externo: {$file->getClientOriginalName()}");
            
            return response()->json([
                'success' => true,
                'message' => 'Archivo importado exitosamente'
            ]);
            
        } catch (ProcessFailedException $e) {
            // Limpiar archivo temporal si existe
            if (isset($path) && File::exists($path)) {
                File::delete($path);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Error al ejecutar el comando: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            // Limpiar archivo temporal si existe
            if (isset($path) && File::exists($path)) {
                File::delete($path);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Error al importar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detecta si un archivo es un dump en formato custom de PostgreSQL
     */
    private function isCustomFormatDump($filePath)
    {
        try {
            // Abrir el archivo y leer los primeros bytes
            $handle = fopen($filePath, 'rb');
            $header = fread($handle, 5);
            fclose($handle);
            
            // Los archivos custom de pg_dump comienzan con "PGDMP"
            if (substr($header, 0, 5) === 'PGDMP') {
                return true;
            }
            
            // También verificar si el archivo contiene texto SQL común
            $content = file_get_contents($filePath, false, null, 0, 4096);
            if (strpos($content, 'CREATE TABLE') !== false || 
                strpos($content, 'INSERT INTO') !== false ||
                strpos($content, 'COPY') !== false) {
                return false; // Es SQL plano
            }
            
            // Por defecto, si tiene extensión .dump o .backup, asumir custom
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if (in_array($extension, ['dump', 'backup'])) {
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            // En caso de error, asumir SQL plano
            return false;
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
        
        // Encontrar la ruta de pg_dump
        $pgDumpPath = $this->findPostgresqlExecutable('pg_dump');
        
        // Detectar sistema operativo
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = sprintf(
                'set PGPASSWORD=%s && "%s" -h %s -p %s -U %s -d %s -F c -b -v -f "%s"',
                escapeshellarg($password),
                $pgDumpPath,
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($database),
                $path
            );
        } else {
            $command = sprintf(
                'PGPASSWORD=%s %s -h %s -p %s -U %s -d %s -F c -b -v -f %s',
                escapeshellarg($password),
                $pgDumpPath,
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($database),
                escapeshellarg($path)
            );
        }
        
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
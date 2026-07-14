<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ActivityRecordsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Iniciando siembra de actividades...');

        // Obtener usuarios existentes
        $users = DB::table('users')->whereNull('deleted_at')->pluck('id')->toArray();
        
        if (empty($users)) {
            $this->command->warn('No hay usuarios disponibles. Creando usuario por defecto...');
            
            $userId = DB::table('users')->insertGetId([
                'name' => 'Usuario Sistema',
                'email' => 'sistema@test.com',
                'password' => bcrypt('password'),
                'role' => 'administrador',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $users = [$userId];
        }

        // Obtener productores y polígonos existentes
        $producers = DB::table('producers')->whereNull('deleted_at')->pluck('id', 'name')->toArray();
        $polygons = DB::table('polygons')->whereNull('deleted_at')->pluck('id', 'name')->toArray();

        // Si no hay datos, crear algunos de prueba
        if (empty($producers)) {
            $this->command->warn('No hay productores disponibles. Creando productores de prueba...');
            for ($i = 1; $i <= 5; $i++) {
                $id = DB::table('producers')->insertGetId([
                    'name' => "Productor $i",
                    'lastname' => "Apellido $i",
                    'description' => "Productor de prueba $i",
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $producers["Productor $i"] = $id;
            }
        }

        if (empty($polygons)) {
            $this->command->warn('No hay polígonos disponibles. Creando polígonos de prueba...');
            for ($i = 1; $i <= 5; $i++) {
                $producerId = !empty($producers) ? array_values($producers)[array_rand($producers)] : null;
                $id = DB::table('polygons')->insertGetId([
                    'name' => "Polígono $i",
                    'description' => "Polígono de prueba $i",
                    'geometry' => DB::raw("ST_GeomFromText('POLYGON((-64.7718 9.7623, -64.7715 9.7620, -64.7713 9.7618, -64.7708 9.7615, -64.7710 9.7612, -64.7713 9.7614, -64.7713 9.7611, -64.7714 9.7611, -64.7715 9.7613, -64.7716 9.7619, -64.7716 9.7620, -64.7718 9.7623))', 4326)"),
                    'producer_id' => $producerId,
                    'parish_id' => null,
                    'area_ha' => rand(10, 100) / 10,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $polygons["Polígono $i"] = $id;
            }
        }

        $activities = [];
        $startDate = Carbon::create(2026, 1, 1);
        $endDate = Carbon::create(2026, 7, 14);
        $currentDate = clone $startDate;

        $this->command->info("Generando actividades del {$startDate->format('d/m/Y')} al {$endDate->format('d/m/Y')}...");

        while ($currentDate <= $endDate) {
            $dailyActivities = rand(5, 20);
            
            for ($i = 0; $i < $dailyActivities; $i++) {
                $activity = $this->generateRandomActivity(
                    $users,
                    $producers,
                    $polygons,
                    $currentDate
                );
                
                if ($activity) {
                    $activities[] = $activity;
                }
            }

            $currentDate->addDay();
        }

        // Insertar actividades en la tabla activity_log
        if (!empty($activities)) {
            $chunks = array_chunk($activities, 100);
            $totalInserted = 0;

            foreach ($chunks as $chunk) {
                DB::table('activity_log')->insert($chunk);
                $totalInserted += count($chunk);
            }

            $this->command->newLine();
            $this->command->info('====================================');
            $this->command->info("RESUMEN DE IMPORTACIÓN:");
            $this->command->info("  ✓ Actividades insertadas: {$totalInserted}");
            $this->command->info('====================================');
        } else {
            $this->command->error('No se generaron actividades.');
        }
    }

    /**
     * Generar una actividad aleatoria
     */
    private function generateRandomActivity($users, $producers, $polygons, $date)
    {
        $user = $users[array_rand($users)];
        
        // Tipos de acción y su distribución
        $actionTypes = [
            'login' => 20,
            'logout' => 10,
            'created' => 10,
            'updated' => 25,
            'deleted' => 10,
            'restored' => 5,
            'activated' => 10,
            'deactivated' => 10,
        ];
        
        $actionType = $this->weightedRandom($actionTypes);
        
        // Determinar el sujeto de la acción
        $subject = $this->getRandomSubject($actionType, $producers, $polygons);
        
        if (!$subject) {
            return null;
        }

        // Generar descripción legible
        $description = $this->generateDescription($actionType, $subject);

        // Generar timestamp con hora aleatoria
        $timestamp = $date->copy()->setTime(
            rand(6, 22),
            rand(0, 59),
            rand(0, 59)
        );

        // Preparar propiedades - SOLO CON IDS, NO NOMBRES
        $properties = [];
        if ($subject['id']) {
            $properties['subject_id'] = $subject['id'];
            $properties['subject_type'] = $subject['type'];
            $properties['subject_table'] = $subject['table'];
        }

        // Para acciones de modificación, agregar algunos datos extra
        if (in_array($actionType, ['created', 'updated', 'deleted', 'restored', 'activated', 'deactivated'])) {
            $properties['action'] = $actionType;
            $properties['timestamp'] = now()->toDateTimeString();
        }

        return [
            'log_name' => $actionType,
            'description' => $description,
            'subject_type' => $subject['type'],
            'subject_id' => $subject['id'],
            'causer_type' => 'App\Models\User',
            'causer_id' => $user,
            'properties' => json_encode($properties),
            'event' => $actionType,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }

    /**
     * Selección aleatoria ponderada
     */
    private function weightedRandom($weights)
    {
        $total = array_sum($weights);
        $random = rand(1, $total);
        
        foreach ($weights as $key => $weight) {
            $random -= $weight;
            if ($random <= 0) {
                return $key;
            }
        }
        
        return array_key_first($weights);
    }

    /**
     * Obtener un sujeto aleatorio para la acción
     */
    private function getRandomSubject($actionType, $producers, $polygons)
    {
        $allSubjects = [];

        if (!empty($producers)) {
            $allSubjects[] = [
                'type' => 'App\Models\Producer',
                'table' => 'producers',
                'ids' => array_values($producers), // SOLO IDs
                'names' => array_keys($producers)
            ];
        }

        if (!empty($polygons)) {
            $allSubjects[] = [
                'type' => 'App\Models\Polygon',
                'table' => 'polygons',
                'ids' => array_values($polygons), // SOLO IDs
                'names' => array_keys($polygons)
            ];
        }

        // Para login/logout no hay sujeto específico
        if (in_array($actionType, ['login', 'logout'])) {
            return [
                'type' => null,
                'id' => null,
                'table' => 'users',
                'name' => 'Usuario',
            ];
        }

        if (empty($allSubjects)) {
            return null;
        }

        $subject = $allSubjects[array_rand($allSubjects)];
        $index = array_rand($subject['ids']);
        
        return [
            'type' => $subject['type'],
            'id' => $subject['ids'][$index], // ESTO ES UN ID (bigint)
            'table' => $subject['table'],
            'name' => $subject['names'][$index] ?? 'Sin nombre',
        ];
    }

    /**
     * Generar descripción legible
     */
    private function generateDescription($actionType, $subject)
    {
        $subjectName = $subject['name'] ?? 'elemento';
        
        $descriptions = [
            'login' => "Inicio de sesión en el sistema",
            'logout' => "Cierre de sesión del sistema",
            'created' => "Creación de {$subject['table']}: {$subjectName}",
            'updated' => "Modificación de {$subject['table']}: {$subjectName}",
            'deleted' => "Eliminación (soft delete) de {$subject['table']}: {$subjectName}",
            'restored' => "Restauración de {$subject['table']}: {$subjectName}",
            'activated' => "Activación de {$subject['table']}: {$subjectName}",
            'deactivated' => "Desactivación de {$subject['table']}: {$subjectName}",
        ];

        return $descriptions[$actionType] ?? "Actividad no especificada";
    }
}
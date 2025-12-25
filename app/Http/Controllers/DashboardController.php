<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;
use App\Models\Producer;

class DashboardController extends Controller
{
    public function index()
    {
        // ===== MÉTRICAS PRINCIPALES =====
        
        // 1. Usuarios
        $totalUsers = User::count();
        
        // 1.1 NUEVO: Usuarios activos (con sesión abierta)
        $activeUsersCount = $this->getActiveUsersCount();
        $activeUsersPercentage = $totalUsers > 0 
            ? round(($activeUsersCount / $totalUsers) * 100, 1)
            : 0;
        
        // 1.2 Métricas existentes de usuarios
        $newUsersToday = User::whereDate('created_at', today())->count();
        $newUsersThisWeek = User::whereBetween('created_at', 
            [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $usersThisMonth = User::whereMonth('created_at', now()->month)
                               ->whereYear('created_at', now()->year)->count();
        
        // 1.3 Usuarios por estado (habilitados vs deshabilitados)
        $trashedUsers = User::onlyTrashed()->count();
        $enabledUsers = $totalUsers - $trashedUsers; // Usuarios habilitados
        $activeUsers = $enabledUsers; // Para compatibilidad con la vista
        
        // 2. Actividades
        $totalActivities = Activity::count();
        $activitiesToday = Activity::whereDate('created_at', today())->count();
        $activitiesThisWeek = Activity::whereBetween('created_at',
            [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $activitiesThisMonth = Activity::whereMonth('created_at', now()->month)
                                       ->whereYear('created_at', now()->year)->count();
        
        // 3. Productores (NUEVA MÉTRICA)
        $totalProducers = Producer::count();
        $activeProducers = Producer::where('is_active', true)->count();
        $newProducersToday = Producer::whereDate('created_at', today())->count();
        $newProducersThisWeek = Producer::whereBetween('created_at',
            [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        
        // 4. Distribución de Roles
        $roleDistribution = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get()
            ->pluck('count', 'role');
        
        // 5. Actividades por Tipo
        $activityTypes = Activity::select('event', DB::raw('count(*) as count'))
            ->whereIn('event', ['created', 'updated', 'deleted', 'restored'])
            ->groupBy('event')
            ->get()
            ->pluck('count', 'event');
        
        // 6. Actividad por Hora (últimas 24h)
        $hourlyActivity = Activity::select(
                DB::raw("EXTRACT(HOUR FROM created_at) as hour"),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->groupBy(DB::raw("EXTRACT(HOUR FROM created_at)"))
            ->orderBy('hour')
            ->get();
        
        // 7. Top Usuarios más Activos (últimos 7 días)
        $topActiveUsers = Activity::select(
                'causer_id',
                DB::raw('COUNT(*) as activity_count'),
                DB::raw('MAX(users.name) as user_name')
            )
            ->where('activity_log.created_at', '>=', Carbon::now()->subDays(7))
            ->whereNotNull('causer_id')
            ->leftJoin('users', 'activity_log.causer_id', '=', 'users.id')
            ->groupBy('causer_id')
            ->orderByDesc('activity_count')
            ->limit(5)
            ->get();
        
        // 8. Actividad por Día de la Semana
        $activityByDay = Activity::select(
                DB::raw("EXTRACT(DOW FROM created_at) as day_of_week"),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy(DB::raw("EXTRACT(DOW FROM created_at)"))
            ->orderBy('day_of_week')
            ->get();
        
        // 9. Tendencias (comparación con período anterior)
        $lastWeekUsers = User::whereBetween('created_at', 
            [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])->count();
        $lastWeekActivities = Activity::whereBetween('created_at',
            [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])->count();
        $lastWeekProducers = Producer::whereBetween('created_at',
            [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])->count();
        
        // 10. Actividades por Mes
        $monthlyActivity = Activity::select(
                DB::raw("DATE_TRUNC('month', created_at) as month"),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy(DB::raw("DATE_TRUNC('month', created_at)"))
            ->orderBy('month')
            ->get();
        
        // 11. Actividades recientes (para tabla)
        $recentActivities = Activity::with('causer')
            ->latest()
            ->take(10)
            ->get();
        
        // ===== CÁLCULO DE PORCENTAJES =====
        
        // Porcentajes de crecimiento
        $userGrowthPercentage = $lastWeekUsers > 0 
            ? round((($newUsersThisWeek - $lastWeekUsers) / $lastWeekUsers) * 100, 1)
            : ($newUsersThisWeek > 0 ? 100 : 0);
        
        $activityGrowthPercentage = $lastWeekActivities > 0 
            ? round((($activitiesThisWeek - $lastWeekActivities) / $lastWeekActivities) * 100, 1)
            : ($activitiesThisWeek > 0 ? 100 : 0);
        
        // Porcentaje de crecimiento de productores
        $producerGrowthPercentage = $lastWeekProducers > 0 
            ? round((($newProducersThisWeek - $lastWeekProducers) / $lastWeekProducers) * 100, 1)
            : ($newProducersThisWeek > 0 ? 100 : 0);
        
        // Porcentajes de distribución
        $adminPercentage = isset($roleDistribution['administrador']) && $totalUsers > 0
            ? round(($roleDistribution['administrador'] / $totalUsers) * 100, 1)
            : 0;
        
        $basicPercentage = isset($roleDistribution['basico']) && $totalUsers > 0
            ? round(($roleDistribution['basico'] / $totalUsers) * 100, 1)
            : 0;
        
        // Porcentaje de productores activos
        $activeProducersPercentage = $totalProducers > 0
            ? round(($activeProducers / $totalProducers) * 100, 1)
            : 0;
        
        // Calcular tasa de actividad (actividades por usuario)
        $completionRate = $totalUsers > 0 
            ? min(round(($activitiesThisMonth / ($totalUsers * 5)) * 100, 1), 100)
            : 0;
        
        // Tasa de actividad diaria promedio
        $daysInMonth = now()->daysInMonth;
        $dailyActivityRate = $totalUsers > 0 && $daysInMonth > 0
            ? round(($activitiesThisMonth / ($totalUsers * $daysInMonth)) * 100, 1)
            : 0;
        
        // Preparar datos para gráfico de horas
        $hourlyChartData = [];
        for ($i = 0; $i < 24; $i++) {
            $hourData = $hourlyActivity->firstWhere('hour', $i);
            $hourlyChartData[] = [
                'hour' => $i,
                'count' => $hourData ? $hourData->count : 0,
                'label' => sprintf('%02d:00', $i)
            ];
        }
        
        // Preparar datos para gráfico por día de la semana
        $dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $activityByDayFormatted = [];
        foreach ($dayNames as $index => $dayName) {
            $dayData = $activityByDay->firstWhere('day_of_week', $index);
            $activityByDayFormatted[] = [
                'day' => $dayName,
                'count' => $dayData ? $dayData->count : 0,
                'short' => substr($dayName, 0, 3)
            ];
        }
        
        // Si no hay datos para los gráficos, crea datos de ejemplo para demostración
        $hasHourlyData = $hourlyActivity->sum('count') > 0;
        $hasTopUsersData = $topActiveUsers->count() > 0;
        
        return view('dashboard', compact(
            // Métricas principales
            'totalUsers',
            'totalActivities',
            'totalProducers',
            'activeProducers',
            
            // NUEVAS: Métricas de usuarios activos (sesión)
            'activeUsersCount',
            'activeUsersPercentage',
            
            // Métricas de estado de usuarios
            'enabledUsers',
            'trashedUsers',
            'activeUsers', // Para compatibilidad
            
            // Métricas diarias
            'newUsersToday',
            'activitiesToday',
            'newProducersToday',
            
            // Métricas semanales
            'newUsersThisWeek',
            'activitiesThisWeek',
            'newProducersThisWeek',
            
            // Métricas mensuales
            'usersThisMonth',
            'activitiesThisMonth',
            
            // Distribuciones
            'roleDistribution',
            'activityTypes',
            
            // Porcentajes
            'userGrowthPercentage',
            'activityGrowthPercentage',
            'producerGrowthPercentage',
            'activeProducersPercentage',
            'adminPercentage',
            'basicPercentage',
            'completionRate',
            'dailyActivityRate',
            
            // Datos para gráficos
            'hourlyChartData',
            'activityByDayFormatted',
            'monthlyActivity',
            'topActiveUsers',
            
            // Flags para mostrar/ocultar secciones
            'hasHourlyData',
            'hasTopUsersData',
            
            // Comparativas
            'lastWeekUsers',
            'lastWeekActivities',
            'lastWeekProducers',
            
            // Actividades recientes
            'recentActivities'
        ));
    }
    
    public function showAuditLog()
    {
        $activities = Activity::with('causer')
            ->latest()
            ->paginate(50);
        
        return view('admin.audit_log', compact('activities'));
    }
    
    /**
     * Método para obtener usuarios activos (con sesión abierta)
     */
    private function getActiveUsersCount()
    {
        // Usuarios con actividad en los últimos 15 minutos
        $activeThreshold = now()->subMinutes(15);
        
        // Opción 1: Si usas sesiones de base de datos (recomendado)
        if (config('session.driver') === 'database') {
            return DB::table('sessions')
                ->where('last_activity', '>=', $activeThreshold->timestamp)
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count('user_id');
        }
        
        // Opción 2: Si tienes campo 'last_seen_at' o 'last_login_at' en users
        if (Schema::hasColumn('users', 'last_seen_at')) {
            return User::where('last_seen_at', '>=', $activeThreshold)->count();
        }
        
        if (Schema::hasColumn('users', 'last_login_at')) {
            return User::where('last_login_at', '>=', $activeThreshold)->count();
        }
        
        // Opción 3: Usar actividad reciente (últimas 2 horas)
        // Para equipos pequeños de 4 personas
        return User::whereHas('activities', function($query) use ($activeThreshold) {
                $query->where('created_at', '>=', $activeThreshold);
            })
            ->orWhere('updated_at', '>=', $activeThreshold)
            ->distinct()
            ->count();
    }
    
    /**
     * Método alternativo más seguro para obtener usuarios activos
     */
    private function getTopActiveUsersAlternative()
    {
        $users = User::whereHas('activities', function($query) {
                $query->where('created_at', '>=', Carbon::now()->subDays(7));
            })
            ->withCount(['activities' => function($query) {
                $query->where('created_at', '>=', Carbon::now()->subDays(7));
            }])
            ->orderByDesc('activities_count')
            ->limit(5)
            ->get(['id', 'name', 'activities_count']);
        
        return $users;
    }
    
    /**
     * Método más simple sin JOIN
     */
    private function getTopActiveUsersSimple()
    {
        $activities = Activity::where('created_at', '>=', Carbon::now()->subDays(7))
            ->whereNotNull('causer_id')
            ->select('causer_id', DB::raw('COUNT(*) as count'))
            ->groupBy('causer_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get();
        
        $topUsers = collect();
        
        foreach ($activities as $activity) {
            $user = User::find($activity->causer_id);
            if ($user) {
                $topUsers->push((object)[
                    'causer_id' => $activity->causer_id,
                    'activity_count' => $activity->count,
                    'user_name' => $user->name,
                    'name' => $user->name
                ]);
            }
        }
        
        return $topUsers;
    }
}
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
        $enabledUsers = $totalUsers - $trashedUsers;
        $activeUsers = $enabledUsers;
        
        // 2. Actividades
        $totalActivities = Activity::count();
        $activitiesToday = Activity::whereDate('created_at', today())->count();
        $activitiesThisWeek = Activity::whereBetween('created_at',
            [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $activitiesThisMonth = Activity::whereMonth('created_at', now()->month)
                                       ->whereYear('created_at', now()->year)->count();
        
        // 3. Productores
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
        $activityTypesRaw = Activity::select(
                DB::raw("CASE 
                    WHEN event IS NOT NULL AND event IN ('created', 'updated', 'deleted', 'restored') THEN event
                    WHEN description LIKE '%ha iniciado sesión%' THEN 'login'
                    WHEN description LIKE '%ha cerrado sesión%' THEN 'logout'
                    ELSE 'other'
                END as activity_type"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('activity_type')
            ->get()
            ->pluck('count', 'activity_type');

        $activityTypes = collect([
            'created' => $activityTypesRaw['created'] ?? 0,
            'updated' => $activityTypesRaw['updated'] ?? 0,
            'deleted' => $activityTypesRaw['deleted'] ?? 0,
            'restored' => $activityTypesRaw['restored'] ?? 0,
            'login' => $activityTypesRaw['login'] ?? 0,
            'logout' => $activityTypesRaw['logout'] ?? 0
        ]);
        
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
        $recentActivities = Activity::with(['causer', 'subject'])
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
        
        $tecnicoPercentage = isset($roleDistribution['tecnico']) && $totalUsers > 0
            ? round(($roleDistribution['tecnico'] / $totalUsers) * 100, 1)
            : 0;
        
        // Porcentaje de productores activos
        $activeProducersPercentage = $totalProducers > 0
            ? round(($activeProducers / $totalProducers) * 100, 1)
            : 0;
        
        // Calcular tasa de actividad
        $completionRate = $totalUsers > 0 
            ? min(round(($activitiesThisMonth / ($totalUsers * 5)) * 100, 1), 100)
            : 0;
        
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
        
        $hasHourlyData = $hourlyActivity->sum('count') > 0;
        $hasTopUsersData = $topActiveUsers->count() > 0;
        
        // ===== DATOS PARA TÉCNICOS =====
        $myActivitiesCount = 0;
        $usersManagedCount = 0;
        $myWeeklyActivities = 0;
        $myRecentActivities = collect([]);
        $topActiveTecnicos = collect([]);
        $recentTecnicoActivities = collect([]);
        $techActivitiesCount = 0;
        
        if (auth()->check() && auth()->user()->role === 'tecnico') {
            $userId = auth()->id();
            
            $myActivitiesCount = Activity::where('causer_id', $userId)->count();
            
            $usersManagedCount = Activity::where('causer_id', $userId)
                ->where('subject_type', 'App\Models\User')
                ->distinct('subject_id')
                ->count('subject_id');
            
            $myWeeklyActivities = Activity::where('causer_id', $userId)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count();
            
            $myRecentActivities = Activity::where('causer_id', $userId)
                ->with(['subject'])
                ->latest()
                ->take(10)
                ->get();
        }
        
        if (auth()->check() && auth()->user()->role === 'administrador') {
            $techActivitiesCount = Activity::whereHas('causer', function($query) {
                $query->where('role', 'tecnico');
            })->where('created_at', '>=', now()->subDays(7))->count();
            
            $topActiveTecnicos = Activity::select(
                    'causer_id',
                    DB::raw('COUNT(*) as activity_count'),
                    DB::raw('MAX(users.name) as user_name')
                )
                ->where('activity_log.created_at', '>=', now()->subDays(7))
                ->whereNotNull('causer_id')
                ->join('users', 'activity_log.causer_id', '=', 'users.id')
                ->where('users.role', 'tecnico')
                ->groupBy('causer_id')
                ->orderByDesc('activity_count')
                ->limit(5)
                ->get();
            
            $recentTecnicoActivities = Activity::with(['causer', 'subject'])
                ->whereHas('causer', function($query) {
                    $query->where('role', 'tecnico');
                })
                ->latest()
                ->take(10)
                ->get();
        }
        
        return view('dashboard', compact(
            // Métricas principales
            'totalUsers',
            'totalActivities',
            'totalProducers',
            'activeProducers',
            
            // Métricas de usuarios activos
            'activeUsersCount',
            'activeUsersPercentage',
            
            // Métricas de estado de usuarios
            'enabledUsers',
            'trashedUsers',
            'activeUsers',
            
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
            'tecnicoPercentage',
            'completionRate',
            'dailyActivityRate',
            
            // Datos para gráficos
            'hourlyChartData',
            'activityByDayFormatted',
            'monthlyActivity',
            'topActiveUsers',
            
            // Flags
            'hasHourlyData',
            'hasTopUsersData',
            
            // Comparativas
            'lastWeekUsers',
            'lastWeekActivities',
            'lastWeekProducers',
            
            // Actividades recientes
            'recentActivities',
            
            // Datos para técnicos
            'myActivitiesCount',
            'usersManagedCount',
            'myWeeklyActivities',
            'myRecentActivities',
            
            // Datos de técnicos para admin
            'topActiveTecnicos',
            'techActivitiesCount',
            'recentTecnicoActivities'
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
        $activeThreshold = now()->subMinutes(15);
        
        if (config('session.driver') === 'database') {
            return DB::table('sessions')
                ->where('last_activity', '>=', $activeThreshold->timestamp)
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count('user_id');
        }
        
        if (Schema::hasColumn('users', 'last_seen_at')) {
            return User::where('last_seen_at', '>=', $activeThreshold)->count();
        }
        
        if (Schema::hasColumn('users', 'last_login_at')) {
            return User::where('last_login_at', '>=', $activeThreshold)->count();
        }
        
        return User::whereHas('activities', function($query) use ($activeThreshold) {
                $query->where('created_at', '>=', $activeThreshold);
            })
            ->orWhere('updated_at', '>=', $activeThreshold)
            ->distinct()
            ->count();
    }
    
    /**
     * Método alternativo para obtener usuarios activos
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
     * Método simple sin JOIN
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
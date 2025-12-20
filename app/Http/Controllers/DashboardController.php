<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index()
    {
        // ===== MÉTRICAS PRINCIPALES =====
        
        // 1. Usuarios
        $totalUsers = User::count();
        $newUsersToday = User::whereDate('created_at', today())->count();
        $newUsersThisWeek = User::whereBetween('created_at', 
            [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $usersThisMonth = User::whereMonth('created_at', now()->month)
                               ->whereYear('created_at', now()->year)->count();
        
        // 2. Actividades
        $totalActivities = Activity::count();
        $activitiesToday = Activity::whereDate('created_at', today())->count();
        $activitiesThisWeek = Activity::whereBetween('created_at',
            [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $activitiesThisMonth = Activity::whereMonth('created_at', now()->month)
                                       ->whereYear('created_at', now()->year)->count();
        
        // 3. Distribución de Roles
        $roleDistribution = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get()
            ->pluck('count', 'role');
        
        // 4. Actividades por Tipo
        $activityTypes = Activity::select('event', DB::raw('count(*) as count'))
            ->whereIn('event', ['created', 'updated', 'deleted', 'restored'])
            ->groupBy('event')
            ->get()
            ->pluck('count', 'event');
        
        // 5. Usuarios por Estado
        $activeUsers = User::count();
        $trashedUsers = User::onlyTrashed()->count();
        
        // 6. Actividad por Hora (últimas 24h)
        $hourlyActivity = Activity::select(
                DB::raw("EXTRACT(HOUR FROM created_at) as hour"),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->groupBy(DB::raw("EXTRACT(HOUR FROM created_at)"))
            ->orderBy('hour')
            ->get();
        
        // 7. Top Usuarios más Activos (últimos 7 días) - CORREGIDO
        $topActiveUsers = Activity::select(
                'causer_id',
                DB::raw('COUNT(*) as activity_count'),
                DB::raw('MAX(users.name) as user_name')
            )
            ->where('activity_log.created_at', '>=', Carbon::now()->subDays(7)) // Especificar tabla
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
        
        // Porcentajes de distribución
        $adminPercentage = isset($roleDistribution['administrador']) && $totalUsers > 0
            ? round(($roleDistribution['administrador'] / $totalUsers) * 100, 1)
            : 0;
        
        $basicPercentage = isset($roleDistribution['basico']) && $totalUsers > 0
            ? round(($roleDistribution['basico'] / $totalUsers) * 100, 1)
            : 0;
        
        // Calcular tasa de actividad (actividades por usuario)
        $completionRate = $totalUsers > 0 
            ? min(round(($activitiesThisMonth / ($totalUsers * 5)) * 100, 1), 100) // Meta: 5 actividades por usuario al mes
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
            'newUsersToday',
            'activitiesToday',
            'newUsersThisWeek',
            'activitiesThisWeek',
            'usersThisMonth',
            'activitiesThisMonth',
            
            // Distribuciones
            'roleDistribution',
            'activityTypes',
            'activeUsers',
            'trashedUsers',
            
            // Porcentajes
            'userGrowthPercentage',
            'activityGrowthPercentage',
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
     * Método alternativo más seguro para obtener usuarios activos
     * Usando Eloquent en lugar de Query Builder directo
     */
    private function getTopActiveUsersAlternative()
    {
        // Opción 1: Usando Eloquent con withCount
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
                    'name' => $user->name // alias para compatibilidad
                ]);
            }
        }
        
        return $topUsers;
    }
}
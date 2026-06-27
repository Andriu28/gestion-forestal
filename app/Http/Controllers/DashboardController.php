<?php
// [file name]: app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Producer;
use App\Models\Polygon;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index()
    {
        $role = auth()->user()->role ?? 'basico';

        return match ($role) {
            'administrador' => $this->adminDashboard(),
            'tecnico'       => $this->tecnicoDashboard(),
            default         => view('dashboard'),
        };
    }

    // =========================================================================
    // Vista Administrador
    // =========================================================================

    private function adminDashboard()
    {
        $now = Carbon::now();

        // ----- Usuarios -------------------------------------------------------
        $totalUsers         = User::count();
        $activeUsersCount   = $this->getActiveUsersCount();
        $enabledUsers       = User::count(); // no trashed
        $trashedUsers       = User::onlyTrashed()->count();

        $newUsersToday      = User::whereDate('created_at', today())->count();
        $newUsersThisWeek   = User::whereBetween('created_at', [$now->startOfWeek(), $now->copy()->endOfWeek()])->count();
        $usersThisMonth     = User::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $lastWeekUsers      = User::whereBetween('created_at', [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()])->count();

        $activeUsersPercentage  = $totalUsers > 0 ? round(($activeUsersCount / $totalUsers) * 100, 1) : 0;
        $userGrowthPercentage   = $this->growthRate($newUsersThisWeek, $lastWeekUsers);

        // ----- Productores ----------------------------------------------------
        $totalProducers         = Producer::count();
        $activeProducers        = Producer::where('is_active', true)->count();
        $inactiveProducers      = $totalProducers - $activeProducers;
        $newProducersThisWeek   = Producer::whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])->count();
        $newProducersToday      = Producer::whereDate('created_at', today())->count();
        $lastWeekProducers      = Producer::whereBetween('created_at', [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()])->count();

        $activeProducersPercentage  = $totalProducers > 0 ? round(($activeProducers / $totalProducers) * 100, 1) : 0;
        $producerGrowthPercentage   = $this->growthRate($newProducersThisWeek, $lastWeekProducers);

        // ----- Polígonos ------------------------------------------------------
        $totalPolygons      = Polygon::count();
        $activePolygons     = Polygon::where('is_active', true)->count();
        $polygonsThisMonth  = Polygon::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $polygonsWithProducer   = Polygon::whereNotNull('producer_id')->count();
        $polygonsWithoutProducer = Polygon::whereNull('producer_id')->count();
        $totalAreaHa        = Polygon::sum('area_ha');

        $activePolygonsPercentage   = $totalPolygons > 0 ? round(($activePolygons / $totalPolygons) * 100, 1) : 0;
        $polygonsWithProducerPct    = $totalPolygons > 0 ? round(($polygonsWithProducer / $totalPolygons) * 100, 1) : 0;

        // ----- Actividades (Bitácora) -----------------------------------------
        $totalActivities        = Activity::count();
        $activitiesToday        = Activity::whereDate('created_at', today())->count();
        $activitiesThisWeek     = Activity::whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])->count();
        $activitiesThisMonth    = Activity::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $lastWeekActivities     = Activity::whereBetween('created_at', [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()])->count();

        $activityGrowthPercentage = $this->growthRate($activitiesThisWeek, $lastWeekActivities);

        $dailyActivityRate = ($totalUsers > 0 && $now->daysInMonth > 0)
            ? round(($activitiesThisMonth / ($totalUsers * $now->daysInMonth)) * 100, 1)
            : 0;

        // ----- Distribución de Roles ------------------------------------------
        $roleDistribution = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get()
            ->pluck('count', 'role');

        $adminPercentage   = $totalUsers > 0 ? round((($roleDistribution['administrador'] ?? 0) / $totalUsers) * 100, 1) : 0;
        $tecnicoPercentage = $totalUsers > 0 ? round((($roleDistribution['tecnico'] ?? 0) / $totalUsers) * 100, 1) : 0;
        $basicPercentage   = $totalUsers > 0 ? round((($roleDistribution['basico'] ?? 0) / $totalUsers) * 100, 1) : 0;

        // ----- Tipos de Actividad ---------------------------------------------
        $activityTypesRaw = Activity::select(
                DB::raw("CASE
                    WHEN event IN ('created','updated','deleted','restored') THEN event
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
            'created'  => $activityTypesRaw['created']  ?? 0,
            'updated'  => $activityTypesRaw['updated']  ?? 0,
            'deleted'  => $activityTypesRaw['deleted']  ?? 0,
            'restored' => $activityTypesRaw['restored'] ?? 0,
            'login'    => $activityTypesRaw['login']    ?? 0,
            'logout'   => $activityTypesRaw['logout']   ?? 0,
        ]);

        // ----- Actividad por hora (últimas 24h) -------------------------------
        $hourlyActivity = Activity::select(
                DB::raw('EXTRACT(HOUR FROM created_at) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $now->copy()->subHours(24))
            ->groupBy(DB::raw('EXTRACT(HOUR FROM created_at)'))
            ->orderBy('hour')
            ->get();

        $hourlyChartData = [];
        for ($i = 0; $i < 24; $i++) {
            $hourData = $hourlyActivity->firstWhere('hour', $i);
            $hourlyChartData[] = ['hour' => $i, 'count' => $hourData?->count ?? 0, 'label' => sprintf('%02d:00', $i)];
        }

        // ----- Actividad por día de la semana (últimos 30 días) ---------------
        $activityByDay = Activity::select(
                DB::raw('EXTRACT(DOW FROM created_at) as day_of_week'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $now->copy()->subDays(30))
            ->groupBy(DB::raw('EXTRACT(DOW FROM created_at)'))
            ->orderBy('day_of_week')
            ->get();

        $dayNames = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $activityByDayFormatted = collect($dayNames)->map(function ($day, $i) use ($activityByDay) {
            $found = $activityByDay->firstWhere('day_of_week', $i);
            return ['day' => $day, 'count' => $found?->count ?? 0];
        })->toArray();

        // ----- Actividad mensual (últimos 6 meses) ----------------------------
        $monthlyActivity = Activity::select(
                DB::raw("DATE_TRUNC('month', created_at) as month"),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $now->copy()->subMonths(6))
            ->groupBy(DB::raw("DATE_TRUNC('month', created_at)"))
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => [
                'month' => Carbon::parse($row->month)->translatedFormat('M Y'),
                'count' => $row->count,
            ]);

        // ----- Polígonos por mes (últimos 6 meses) ----------------------------
        $polygonsByMonth = Polygon::select(
                DB::raw("DATE_TRUNC('month', created_at) as month"),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $now->copy()->subMonths(6))
            ->groupBy(DB::raw("DATE_TRUNC('month', created_at)"))
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => [
                'month' => Carbon::parse($row->month)->translatedFormat('M Y'),
                'count' => $row->count,
            ]);

        // ----- Top usuarios más activos (7 días) ------------------------------
        $topActiveUsers = Activity::select('causer_id', DB::raw('COUNT(*) as activity_count'), DB::raw('MAX(users.name) as user_name'))
            ->where('activity_log.created_at', '>=', $now->copy()->subDays(7))
            ->whereNotNull('causer_id')
            ->leftJoin('users', 'activity_log.causer_id', '=', 'users.id')
            ->groupBy('causer_id')
            ->orderByDesc('activity_count')
            ->limit(5)
            ->get();

        // ----- Top técnicos más activos (7 días) ------------------------------
        $techActivitiesCount = Activity::whereHas('causer', fn ($q) => $q->where('role', 'tecnico'))
            ->where('created_at', '>=', $now->copy()->subDays(7))
            ->count();

        $topActiveTecnicos = Activity::select('causer_id', DB::raw('COUNT(*) as activity_count'), DB::raw('MAX(users.name) as user_name'))
            ->where('activity_log.created_at', '>=', $now->copy()->subDays(7))
            ->whereNotNull('causer_id')
            ->join('users', 'activity_log.causer_id', '=', 'users.id')
            ->where('users.role', 'tecnico')
            ->groupBy('causer_id')
            ->orderByDesc('activity_count')
            ->limit(5)
            ->get();

        // ----- Actividades recientes ------------------------------------------
        $recentActivities = Activity::with(['causer', 'subject'])->latest()->take(10)->get();

        $recentTecnicoActivities = Activity::with(['causer', 'subject'])
            ->whereHas('causer', fn ($q) => $q->where('role', 'tecnico'))
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard', compact(
            // Usuarios
            'totalUsers', 'activeUsersCount', 'activeUsersPercentage',
            'enabledUsers', 'trashedUsers',
            'newUsersToday', 'newUsersThisWeek', 'usersThisMonth',
            'userGrowthPercentage', 'lastWeekUsers',
            // Productores
            'totalProducers', 'activeProducers', 'inactiveProducers',
            'activeProducersPercentage', 'producerGrowthPercentage',
            'newProducersToday', 'newProducersThisWeek',
            // Polígonos
            'totalPolygons', 'activePolygons', 'activePolygonsPercentage',
            'polygonsThisMonth', 'polygonsWithProducer', 'polygonsWithoutProducer',
            'polygonsWithProducerPct', 'totalAreaHa',
            // Actividades
            'totalActivities', 'activitiesToday', 'activitiesThisWeek',
            'activitiesThisMonth', 'activityGrowthPercentage', 'lastWeekActivities',
            'dailyActivityRate',
            // Distribuciones
            'roleDistribution', 'activityTypes',
            'adminPercentage', 'tecnicoPercentage', 'basicPercentage',
            // Gráficos
            'hourlyChartData', 'activityByDayFormatted',
            'monthlyActivity', 'polygonsByMonth',
            // Rankings
            'topActiveUsers', 'topActiveTecnicos', 'techActivitiesCount',
            // Tablas recientes
            'recentActivities', 'recentTecnicoActivities'
        ));
    }

    // =========================================================================
    // Vista Técnico
    // =========================================================================

    private function tecnicoDashboard()
    {
        $userId = auth()->id();
        $now    = Carbon::now();

        $myActivitiesCount   = Activity::where('causer_id', $userId)->count();
        $myActivitiesToday   = Activity::where('causer_id', $userId)->whereDate('created_at', today())->count();
        $myWeeklyActivities  = Activity::where('causer_id', $userId)
            ->whereBetween('created_at', [$now->startOfWeek(), $now->copy()->endOfWeek()])
            ->count();
        $myMonthlyActivities = Activity::where('causer_id', $userId)
            ->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)
            ->count();

        $usersManagedCount = Activity::where('causer_id', $userId)
            ->where('subject_type', 'App\Models\User')
            ->distinct('subject_id')
            ->count('subject_id');

        $myRecentActivities = Activity::where('causer_id', $userId)
            ->with(['subject'])
            ->latest()
            ->take(15)
            ->get();

        // Actividad por tipo del técnico
        $myActivityTypes = Activity::where('causer_id', $userId)
            ->select('event', DB::raw('COUNT(*) as count'))
            ->whereNotNull('event')
            ->groupBy('event')
            ->get()
            ->pluck('count', 'event');

        // Actividad diaria del técnico (últimos 7 días)
        $myDailyActivity = Activity::where('causer_id', $userId)
            ->where('created_at', '>=', $now->copy()->subDays(6)->startOfDay())
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $myDailyChartData = collect(range(6, 0))->map(function ($daysAgo) use ($now, $myDailyActivity) {
            $date = $now->copy()->subDays($daysAgo)->toDateString();
            return [
                'date'  => Carbon::parse($date)->translatedFormat('D d'),
                'count' => $myDailyActivity->get($date)?->count ?? 0,
            ];
        })->toArray();

        return view('dashboard', compact(
            'myActivitiesCount', 'myActivitiesToday',
            'myWeeklyActivities', 'myMonthlyActivities',
            'usersManagedCount', 'myRecentActivities',
            'myActivityTypes', 'myDailyChartData'
        ));
    }

    // =========================================================================
    // Helpers privados
    // =========================================================================

    private function growthRate(int $current, int $previous): float
    {
        if ($previous > 0) {
            return round((($current - $previous) / $previous) * 100, 1);
        }
        return $current > 0 ? 100.0 : 0.0;
    }

    private function getActiveUsersCount(): int
    {
        $threshold = now()->subMinutes(15);

        if (config('session.driver') === 'database') {
            return DB::table('sessions')
                ->where('last_activity', '>=', $threshold->timestamp)
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count('user_id');
        }

        if (Schema::hasColumn('users', 'last_seen_at')) {
            return User::where('last_seen_at', '>=', $threshold)->count();
        }

        return User::where('updated_at', '>=', $threshold)->count();
    }

    public function showAuditLog()
    {
        $activities = Activity::with('causer')->latest()->paginate(50);
        return view('admin.audit_log', compact('activities'));
    }
}
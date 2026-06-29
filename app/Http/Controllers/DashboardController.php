<?php
// [file name]: app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Polygon;
use App\Models\Producer;
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

        return $role === 'administrador'
            ? $this->adminDashboard()
            : view('dashboard');   // básico y técnico ven la pantalla de bienvenida
    }

    // =========================================================================
    // Vista Administrador
    // =========================================================================

    private function adminDashboard()
    {
        $now = Carbon::now();

        // ----- Usuarios -------------------------------------------------------
        $totalUsers       = User::count();
        $enabledUsers     = $totalUsers;
        $trashedUsers     = User::onlyTrashed()->count();
        $activeUsersCount = $this->getActiveUsersCount();
        $newUsersToday    = User::whereDate('created_at', today())->count();

        $thisWeekUsers = User::whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])->count();
        $lastWeekUsers = User::whereBetween('created_at', [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()])->count();
        $userGrowthPercentage = $this->growthRate($thisWeekUsers, $lastWeekUsers);

        // ----- Productores ----------------------------------------------------
        $totalProducers    = Producer::count();
        $activeProducers   = Producer::where('is_active', true)->count();
        $inactiveProducers = $totalProducers - $activeProducers;

        $thisWeekProducers    = Producer::whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])->count();
        $lastWeekProducers    = Producer::whereBetween('created_at', [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()])->count();
        $activeProducersPercentage  = $totalProducers > 0 ? round(($activeProducers / $totalProducers) * 100, 1) : 0;

        // ----- Polígonos ------------------------------------------------------
        $totalPolygons         = Polygon::count();
        $activePolygons        = Polygon::where('is_active', true)->count();
        $polygonsWithProducer  = Polygon::whereNotNull('producer_id')->count();
        $totalAreaHa           = Polygon::sum('area_ha');

        // ----- Actividades ----------------------------------------------------
        $activitiesToday    = Activity::whereDate('created_at', today())->count();
        $activitiesThisWeek = Activity::whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])->count();
        $activitiesThisMonth = Activity::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $lastWeekActivities = Activity::whereBetween('created_at', [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()])->count();
        $activityGrowthPercentage = $this->growthRate($activitiesThisWeek, $lastWeekActivities);

        // ----- Distribución de roles ------------------------------------------
        $roleDistribution = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get()
            ->pluck('count', 'role');

        $adminPercentage   = $totalUsers > 0 ? round((($roleDistribution['administrador'] ?? 0) / $totalUsers) * 100, 1) : 0;
        $tecnicoPercentage = $totalUsers > 0 ? round((($roleDistribution['tecnico'] ?? 0) / $totalUsers) * 100, 1) : 0;
        $basicPercentage   = $totalUsers > 0 ? round((($roleDistribution['basico'] ?? 0) / $totalUsers) * 100, 1) : 0;

        // ----- Actividad mensual (últimos 6 meses) — para gráfico ------------
        $monthlyActivity = Activity::select(
                DB::raw("DATE_TRUNC('month', created_at) as month"),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $now->copy()->subMonths(6))
            ->groupBy(DB::raw("DATE_TRUNC('month', created_at)"))
            ->orderBy('month')
            ->get()
            ->map(fn ($r) => ['month' => Carbon::parse($r->month)->translatedFormat('M Y'), 'count' => $r->count]);

        $polygonsByMonth = Polygon::select(
                DB::raw("DATE_TRUNC('month', created_at) as month"),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $now->copy()->subMonths(6))
            ->groupBy(DB::raw("DATE_TRUNC('month', created_at)"))
            ->orderBy('month')
            ->get()
            ->map(fn ($r) => ['month' => Carbon::parse($r->month)->translatedFormat('M Y'), 'count' => $r->count]);

        // ----- Top usuarios activos (últimos 7 días) -------------------------
        $topActiveUsers = Activity::select(
                'causer_id',
                DB::raw('COUNT(*) as activity_count'),
                DB::raw('MAX(users.name) as user_name')
            )
            ->where('activity_log.created_at', '>=', $now->copy()->subDays(7))
            ->whereNotNull('causer_id')
            ->leftJoin('users', 'activity_log.causer_id', '=', 'users.id')
            ->groupBy('causer_id')
            ->orderByDesc('activity_count')
            ->limit(5)
            ->get();

        // ----- Actividades recientes -----------------------------------------
        $recentActivities = Activity::with(['causer', 'subject'])->latest()->take(10)->get();

        return view('dashboard', compact(
            // Usuarios
            'totalUsers', 'enabledUsers', 'trashedUsers',
            'activeUsersCount', 'newUsersToday', 'userGrowthPercentage',
            // Productores
            'totalProducers', 'activeProducers', 'inactiveProducers', 'activeProducersPercentage',
            // Polígonos
            'totalPolygons', 'activePolygons', 'polygonsWithProducer', 'totalAreaHa',
            // Actividades
            'activitiesToday', 'activitiesThisWeek', 'activitiesThisMonth', 'activityGrowthPercentage',
            // Roles
            'roleDistribution', 'adminPercentage', 'tecnicoPercentage', 'basicPercentage',
            // Gráficos
            'monthlyActivity', 'polygonsByMonth',
            // Rankings y recientes
            'topActiveUsers', 'recentActivities'
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
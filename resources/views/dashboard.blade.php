<x-app-layout>

{{-- ============================================================
     ADMINISTRADOR
     ============================================================ --}}
@if(auth()->check() && auth()->user()->role === 'administrador')

{{-- Header --}}
<div class="mb-6 flex items-end justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Panel de Control</h1>
        <p class="text-sm text-gray-400 mt-0.5">{{ now()->translatedFormat('l, d \\d\\e F \\d\\e Y') }}</p>
    </div>
    <span class="text-xs px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full font-medium">
        ● En línea
    </span>
</div>

{{-- ---- FILA 1: 4 KPIs principales ---- --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">

    {{-- Usuarios --}}
    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow p-5 hover:-translate-y-0.5 hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                </svg>
            </div>
            @php $ugrow = $userGrowthPercentage; @endphp
            <span class="text-xs font-semibold {{ $ugrow >= 0 ? 'text-green-600 bg-green-50 dark:bg-green-900/20' : 'text-red-500 bg-red-50 dark:bg-red-900/20' }} px-2 py-0.5 rounded-full">
                {{ $ugrow >= 0 ? '+' : '' }}{{ $ugrow }}%
            </span>
        </div>
        <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $totalUsers }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Usuarios registrados</p>
        <p class="text-xs text-gray-400 mt-2">{{ $activeUsersCount }} conectados ahora · {{ $newUsersToday }} nuevos hoy</p>
    </div>

    {{-- Productores --}}
    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow p-5 hover:-translate-y-0.5 hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 bg-teal-100 dark:bg-teal-900/30 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20h10M10 20c5.5-2.5.8-6.4 3-10M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2z"/>
                </svg>
            </div>
            <span class="text-xs font-semibold text-teal-600 bg-teal-50 dark:bg-teal-900/20 px-2 py-0.5 rounded-full">
                {{ $activeProducersPercentage }}% activos
            </span>
        </div>
        <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $totalProducers }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Productores</p>
        <p class="text-xs text-gray-400 mt-2">{{ $activeProducers }} activos · {{ $inactiveProducers }} inactivos</p>
    </div>

    {{-- Polígonos --}}
    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow p-5 hover:-translate-y-0.5 hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
            </div>
            <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 px-2 py-0.5 rounded-full">
                {{ number_format($totalAreaHa, 0) }} Ha
            </span>
        </div>
        <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $totalPolygons }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Polígonos registrados</p>
        <p class="text-xs text-gray-400 mt-2">{{ $activePolygons }} activos · {{ $polygonsWithProducer }} con productor</p>
    </div>

    {{-- Actividades --}}
    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow p-5 hover:-translate-y-0.5 hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            @php $agrow = $activityGrowthPercentage; @endphp
            <span class="text-xs font-semibold {{ $agrow >= 0 ? 'text-green-600 bg-green-50 dark:bg-green-900/20' : 'text-red-500 bg-red-50 dark:bg-red-900/20' }} px-2 py-0.5 rounded-full">
                {{ $agrow >= 0 ? '+' : '' }}{{ $agrow }}% sem.
            </span>
        </div>
        <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $activitiesToday }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Actividades hoy</p>
        <p class="text-xs text-gray-400 mt-2">{{ $activitiesThisWeek }} esta semana · {{ $activitiesThisMonth }} este mes</p>
    </div>

</div>

{{-- ---- FILA 2: Gráfico + Distribuciones ---- --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

    {{-- Gráfico mensual --}}
    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Actividad últimos 6 meses</h3>
            <div class="flex items-center gap-4 text-xs text-gray-400">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-purple-400 inline-block"></span>Actividades</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-emerald-400 inline-block"></span>Polígonos</span>
            </div>
        </div>
        <div class="h-52">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    {{-- Distribución de roles --}}
    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow p-5">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-5">Usuarios por rol</h3>
        <div class="space-y-4">
            @foreach([
                ['label' => 'Administradores', 'color' => 'bg-blue-500',   'text' => 'text-blue-600',   'pct' => $adminPercentage,   'count' => $roleDistribution['administrador'] ?? 0],
                ['label' => 'Técnicos',         'color' => 'bg-indigo-500', 'text' => 'text-indigo-600', 'pct' => $tecnicoPercentage, 'count' => $roleDistribution['tecnico'] ?? 0],
                ['label' => 'Básicos',           'color' => 'bg-teal-400',  'text' => 'text-teal-600',   'pct' => $basicPercentage,   'count' => $roleDistribution['basico'] ?? 0],
            ] as $role)
            <div>
                <div class="flex justify-between text-xs mb-1.5">
                    <span class="text-gray-600 dark:text-gray-300">{{ $role['label'] }}</span>
                    <span class="font-bold {{ $role['text'] }}">{{ $role['count'] }}  <span class="text-gray-400 font-normal">({{ $role['pct'] }}%)</span></span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                    <div class="{{ $role['color'] }} h-1.5 rounded-full transition-all duration-700" style="width:{{ $role['pct'] }}%"></div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="grid grid-cols-2 gap-3 mt-5 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="text-center">
                <p class="text-xl font-black text-blue-600">{{ $enabledUsers }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Habilitados</p>
            </div>
            <div class="text-center">
                <p class="text-xl font-black text-red-500">{{ $trashedUsers }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Deshabilitados</p>
            </div>
        </div>
    </div>

</div>

{{-- ---- FILA 3: Rankings + Actividad reciente ---- --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

    {{-- Top usuarios activos --}}
    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Más activos (7 días)</h3>
            <span class="text-xs text-gray-400">por acciones</span>
        </div>
        <div class="space-y-3">
            @forelse($topActiveUsers as $i => $user)
            @php $maxU = $topActiveUsers->max('activity_count'); @endphp
            <div class="flex items-center gap-3">
                <span class="text-sm w-5 text-center {{ $i === 0 ? 'text-yellow-500' : ($i === 1 ? 'text-gray-400' : ($i === 2 ? 'text-orange-400' : 'text-gray-300')) }} font-bold flex-shrink-0">
                    {{ $i === 0 ? '①' : ($i === 1 ? '②' : ($i === 2 ? '③' : $i+1)) }}
                </span>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-xs font-medium text-gray-800 dark:text-gray-200 truncate">{{ $user->user_name ?? 'Usuario #'.$user->causer_id }}</span>
                        <span class="text-xs font-bold text-gray-500 ml-2 flex-shrink-0">{{ $user->activity_count }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1">
                        <div class="{{ $i === 0 ? 'bg-yellow-400' : 'bg-blue-400' }} h-1 rounded-full"
                             style="width:{{ $maxU > 0 ? round(($user->activity_count / $maxU) * 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-xs text-center text-gray-400 py-4">Sin datos esta semana</p>
            @endforelse
        </div>
    </div>

    {{-- Actividad reciente --}}
    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Actividad reciente</h3>
            <a href="{{ route('admin.audit') }}" class="text-xs text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors">
                Ver todo →
            </a>
        </div>
        <div class="space-y-1">
            @forelse($recentActivities as $activity)
            @php
                $ev = $activity->event ?? 'other';
                if (!$activity->event) {
                    if (str_contains($activity->description, 'iniciado sesión')) $ev = 'login';
                    elseif (str_contains($activity->description, 'cerrado sesión')) $ev = 'logout';
                }
                $badge = match($ev) {
                    'created'  => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                    'updated'  => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                    'deleted'  => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                    'restored' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                    'login'    => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                    'logout'   => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
                    default    => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                };
                $evLabel = ['created'=>'Creó','updated'=>'Actualizó','deleted'=>'Eliminó','restored'=>'Restauró','login'=>'Ingresó','logout'=>'Salió'][$ev] ?? ucfirst($ev);
            @endphp
            <div class="flex items-center gap-3 py-2 px-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $badge }} w-20 text-center flex-shrink-0">{{ $evLabel }}</span>
                <span class="text-sm font-medium text-gray-800 dark:text-gray-200 w-28 flex-shrink-0 truncate">{{ $activity->causer?->name ?? 'Sistema' }}</span>
                <span class="text-xs text-gray-400 flex-1 truncate">{{ Str::limit($activity->description, 50) }}</span>
                <span class="text-xs text-gray-300 dark:text-gray-500 flex-shrink-0">{{ $activity->created_at->diffForHumans() }}</span>
            </div>
            @empty
            <p class="text-xs text-center text-gray-400 py-6">Sin actividad reciente</p>
            @endforelse
        </div>
    </div>

</div>

{{-- ============================================================
     USUARIO BÁSICO
     ============================================================ --}}
@else
<div class="max-w-md mx-auto mt-16 text-center">
    <div class="w-16 h-16 mx-auto mb-5 bg-blue-100 dark:bg-blue-900/30 rounded-2xl flex items-center justify-center">
        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
    </div>
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Bienvenido, {{ Auth::user()->name }}</h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Tu cuenta tiene acceso básico. Contacta con un administrador para obtener más permisos.</p>
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-stone-100/90 dark:bg-custom-gray p-3 rounded-xl shadow">
            <p class="text-xs text-gray-400">Rol</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white capitalize mt-0.5">{{ Auth::user()->role }}</p>
        </div>
        <div class="bg-stone-100/90 dark:bg-custom-gray p-3 rounded-xl shadow">
            <p class="text-xs text-gray-400">Correo</p>
            <p class="text-xs font-semibold text-gray-900 dark:text-white truncate mt-0.5">{{ Auth::user()->email }}</p>
        </div>
        <div class="bg-stone-100/90 dark:bg-custom-gray p-3 rounded-xl shadow">
            <p class="text-xs text-gray-400">Miembro desde</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5">{{ Auth::user()->created_at->format('d/m/Y') }}</p>
        </div>
    </div>
</div>
@endif

</x-app-layout>

{{-- ============================================================
     SCRIPTS
     ============================================================ --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    @if(auth()->check() && auth()->user()->role === 'administrador')

    const isDark     = document.documentElement.classList.contains('dark');
    const gridColor  = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
    const labelColor = isDark ? '#6b7280' : '#9ca3af';
    const tooltipBg  = isDark ? '#1f2937' : '#ffffff';
    const tooltipTxt = isDark ? '#f3f4f6' : '#111827';

    // ---- Gráfico mensual ----
    const ctx = document.getElementById('monthlyChart');
    if (ctx) {
        const actData  = @json($monthlyActivity);
        const polyData = @json($polygonsByMonth);
        const labels   = [...new Set([...actData.map(d => d.month), ...polyData.map(d => d.month)])];

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Actividades',
                        data: labels.map(m => actData.find(d => d.month === m)?.count ?? 0),
                        backgroundColor: isDark ? 'rgba(192,132,252,0.6)' : 'rgba(168,85,247,0.55)',
                        borderRadius: 5,
                        borderSkipped: false,
                    },
                    {
                        label: 'Polígonos',
                        data: labels.map(m => polyData.find(d => d.month === m)?.count ?? 0),
                        backgroundColor: isDark ? 'rgba(52,211,153,0.6)' : 'rgba(16,185,129,0.55)',
                        borderRadius: 5,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: tooltipBg,
                        titleColor: tooltipTxt,
                        bodyColor: tooltipTxt,
                        borderColor: isDark ? '#374151' : '#e5e7eb',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 8,
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: labelColor, font: { size: 11 } } },
                    y: { grid: { color: gridColor }, ticks: { color: labelColor, font: { size: 11 } }, beginAtZero: true }
                },
                animation: { duration: 700, easing: 'easeOutQuart' }
            }
        });
    }

    @endif
});
</script>

<style>
    .dark canvas { filter: brightness(1.05); }
</style>
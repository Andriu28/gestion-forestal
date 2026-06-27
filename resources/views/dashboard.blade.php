<x-app-layout>

{{-- ============================================================
     VISTA ADMINISTRADOR
     ============================================================ --}}
@if(auth()->check() && auth()->user()->role === 'administrador')

{{-- Header --}}
<div class="mb-6 flex items-end justify-between">
    <div>
        <h1 class="text-3xl font-black text-gray-900 dark:text-white mb-1">Panel de Control</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ now()->translatedFormat('l, d \\d\\e F \\d\\e Y') }}
        </p>
    </div>
    <span class="text-xs px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full font-medium">
        ● Sistema activo
    </span>
</div>

{{-- ---- FILA 1: KPIs principales ---- --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">

    {{-- Usuarios conectados --}}
    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow-lg p-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-center justify-between mb-3">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Conectados ahora</p>
                <h3 class="text-3xl font-black text-gray-900 dark:text-white mt-1">
                    {{ $activeUsersCount }}<span class="text-base font-medium text-gray-400">/{{ $totalUsers }}</span>
                </h3>
            </div>
            <div class="w-11 h-11 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16 11 2 2 4-4M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle stroke-linecap="round" stroke-linejoin="round" stroke-width="2" cx="9" cy="7" r="4"/>
                </svg>
            </div>
        </div>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Sesiones activas (últimos 15 min)</p>
                @php $ugrow = $userGrowthPercentage; @endphp
                <p class="text-xs mt-1 {{ $ugrow >= 0 ? 'text-green-600' : 'text-red-500' }} font-semibold">
                    {{ $ugrow >= 0 ? '▲' : '▼' }} {{ abs($ugrow) }}% vs semana pasada
                </p>
            </div>
            <div class="relative w-16 h-16">
                <svg class="w-16 h-16 -rotate-90">
                    <circle cx="32" cy="32" r="26" class="fill-none stroke-gray-200 dark:stroke-gray-700" stroke-width="8"/>
                    <circle id="circle-users" cx="32" cy="32" r="26" class="fill-none stroke-blue-500 dark:stroke-blue-400" stroke-width="8" stroke-dasharray="163" stroke-dashoffset="163" stroke-linecap="round"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-xs font-bold text-blue-600 dark:text-blue-400">{{ $activeUsersPercentage }}%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Polígonos --}}
    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow-lg p-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-center justify-between mb-3">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Polígonos registrados</p>
                <h3 class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ number_format($totalPolygons) }}</h3>
            </div>
            <div class="w-11 h-11 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
            </div>
        </div>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($totalAreaHa, 0) }} Ha totales registradas</p>
                <p class="text-xs mt-1 text-emerald-600 font-semibold">{{ $activePolygons }} activos · {{ $polygonsWithProducer }} con productor</p>
            </div>
            <div class="relative w-16 h-16">
                <svg class="w-16 h-16 -rotate-90">
                    <circle cx="32" cy="32" r="26" class="fill-none stroke-gray-200 dark:stroke-gray-700" stroke-width="8"/>
                    <circle id="circle-polygons" cx="32" cy="32" r="26" class="fill-none stroke-emerald-500 dark:stroke-emerald-400" stroke-width="8" stroke-dasharray="163" stroke-dashoffset="163" stroke-linecap="round"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">{{ $activePolygonsPercentage }}%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Productores --}}
    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow-lg p-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-center justify-between mb-3">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Productores activos</p>
                <h3 class="text-3xl font-black text-gray-900 dark:text-white mt-1">
                    {{ $activeProducers }}<span class="text-base font-medium text-gray-400">/{{ $totalProducers }}</span>
                </h3>
            </div>
            <div class="w-11 h-11 bg-teal-100 dark:bg-teal-900/30 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20h10M10 20c5.5-2.5.8-6.4 3-10M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2z"/>
                </svg>
            </div>
        </div>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $inactiveProducers }} inactivos</p>
                @php $pgrow = $producerGrowthPercentage; @endphp
                <p class="text-xs mt-1 {{ $pgrow >= 0 ? 'text-teal-600' : 'text-red-500' }} font-semibold">
                    {{ $pgrow >= 0 ? '▲' : '▼' }} {{ abs($pgrow) }}% vs semana pasada
                </p>
            </div>
            <div class="relative w-16 h-16">
                <svg class="w-16 h-16 -rotate-90">
                    <circle cx="32" cy="32" r="26" class="fill-none stroke-gray-200 dark:stroke-gray-700" stroke-width="8"/>
                    <circle id="circle-producers" cx="32" cy="32" r="26" class="fill-none stroke-teal-500 dark:stroke-teal-400" stroke-width="8" stroke-dasharray="163" stroke-dashoffset="163" stroke-linecap="round"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-xs font-bold text-teal-600 dark:text-teal-400">{{ $activeProducersPercentage }}%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Actividades hoy --}}
    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow-lg p-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-center justify-between mb-3">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Actividades hoy</p>
                <h3 class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ $activitiesToday }}</h3>
            </div>
            <div class="w-11 h-11 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
        </div>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $activitiesThisWeek }} esta semana</p>
                @php $agrow = $activityGrowthPercentage; @endphp
                <p class="text-xs mt-1 {{ $agrow >= 0 ? 'text-purple-600' : 'text-red-500' }} font-semibold">
                    {{ $agrow >= 0 ? '▲' : '▼' }} {{ abs($agrow) }}% vs semana pasada
                </p>
            </div>
            <div class="relative w-16 h-16">
                <svg class="w-16 h-16 -rotate-90">
                    <circle cx="32" cy="32" r="26" class="fill-none stroke-gray-200 dark:stroke-gray-700" stroke-width="8"/>
                    <circle id="circle-activities" cx="32" cy="32" r="26" class="fill-none stroke-purple-500 dark:stroke-purple-400" stroke-width="8" stroke-dasharray="163" stroke-dashoffset="163" stroke-linecap="round"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    @php
                        $avgDay = $activitiesThisMonth > 0 ? $activitiesThisMonth / now()->daysInMonth : 1;
                        $todayPct = min(100, $avgDay > 0 ? round(($activitiesToday / $avgDay) * 100) : 0);
                    @endphp
                    <span class="text-xs font-bold text-purple-600 dark:text-purple-400">{{ $todayPct }}%</span>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ---- FILA 2: Indicadores secundarios (6 mini-KPIs) ---- --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-4">

    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-xl p-4 text-center shadow">
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Nuevos usuarios hoy</p>
        <p class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ $newUsersToday }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $newUsersThisWeek }} esta semana</p>
    </div>

    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-xl p-4 text-center shadow">
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Polígonos este mes</p>
        <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $polygonsThisMonth }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $polygonsWithProducerPct }}% con productor</p>
    </div>

    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-xl p-4 text-center shadow">
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Área total mapeada</p>
        <p class="text-2xl font-black text-teal-600 dark:text-teal-400">{{ number_format($totalAreaHa, 0) }}</p>
        <p class="text-xs text-gray-400 mt-1">hectáreas</p>
    </div>

    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-xl p-4 text-center shadow">
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Actividades este mes</p>
        <p class="text-2xl font-black text-purple-600 dark:text-purple-400">{{ $activitiesThisMonth }}</p>
        <p class="text-xs text-gray-400 mt-1">tasa diaria {{ $dailyActivityRate }}%</p>
    </div>

    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-xl p-4 text-center shadow">
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Usuarios deshabilitados</p>
        <p class="text-2xl font-black text-red-500">{{ $trashedUsers }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $enabledUsers }} habilitados</p>
    </div>

    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-xl p-4 text-center shadow">
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Técnicos activos 7d</p>
        <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400">{{ $techActivitiesCount }}</p>
        <p class="text-xs text-gray-400 mt-1">acciones registradas</p>
    </div>

</div>

{{-- ---- FILA 3: Gráficos principales ---- --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

    {{-- Actividad mensual --}}
    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-xl shadow-lg p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Actividad últimos 6 meses</h3>
            <div class="flex gap-3 text-xs text-gray-500">
                <span class="flex items-center gap-1"><span class="w-3 h-2 rounded bg-purple-500 inline-block"></span> Actividades</span>
                <span class="flex items-center gap-1"><span class="w-3 h-2 rounded bg-emerald-500 inline-block"></span> Polígonos</span>
            </div>
        </div>
        <div class="relative h-48" id="monthly-chart-container">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    {{-- Actividad por hora --}}
    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-xl shadow-lg p-5">
        <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">Actividad por hora (24h)</h3>
        <div class="relative h-48">
            <canvas id="hourlyChart"></canvas>
        </div>
    </div>

</div>

{{-- ---- FILA 4: Distribuciones y rankings ---- --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">

    {{-- Distribución de roles --}}
    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-xl shadow-lg p-5">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Distribución de roles</h3>
            <span class="text-xs text-gray-400">{{ $totalUsers }} usuarios</span>
        </div>
        <div class="space-y-4">
            @foreach([
                ['label' => 'Administradores', 'color' => 'bg-blue-500', 'text' => 'text-blue-600', 'pct' => $adminPercentage,   'count' => $roleDistribution['administrador'] ?? 0],
                ['label' => 'Técnicos',         'color' => 'bg-indigo-500','text'=> 'text-indigo-600','pct' => $tecnicoPercentage, 'count' => $roleDistribution['tecnico'] ?? 0],
                ['label' => 'Básicos',           'color' => 'bg-teal-500', 'text' => 'text-teal-600', 'pct' => $basicPercentage,   'count' => $roleDistribution['basico'] ?? 0],
            ] as $role)
            <div>
                <div class="flex justify-between mb-1">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $role['label'] }}</span>
                    <span class="text-sm font-bold {{ $role['text'] }}">{{ $role['count'] }} ({{ $role['pct'] }}%)</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="{{ $role['color'] }} h-2 rounded-full transition-all duration-700" style="width:{{ $role['pct'] }}%"></div>
                </div>
            </div>
            @endforeach

            <div class="grid grid-cols-2 gap-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <p class="text-xs text-gray-500">Habilitados</p>
                    <p class="text-xl font-black text-blue-600">{{ $enabledUsers }}</p>
                </div>
                <div class="text-center p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                    <p class="text-xs text-gray-500">Deshabilitados</p>
                    <p class="text-xl font-black text-red-500">{{ $trashedUsers }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tipos de actividad --}}
    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-xl shadow-lg p-5">
        <h3 class="text-base font-bold text-gray-900 dark:text-white mb-5">Tipos de actividad</h3>
        @php
            $activityColors = ['created'=>'bg-green-500','updated'=>'bg-blue-500','deleted'=>'bg-red-500','restored'=>'bg-yellow-500','login'=>'bg-purple-500','logout'=>'bg-orange-400'];
            $activityTextColors = ['created'=>'text-green-600','updated'=>'text-blue-600','deleted'=>'text-red-500','restored'=>'text-yellow-600','login'=>'text-purple-600','logout'=>'text-orange-500'];
            $activityLabels = ['created'=>'Creaciones','updated'=>'Actualizaciones','deleted'=>'Eliminaciones','restored'=>'Restauraciones','login'=>'Inicios de sesión','logout'=>'Cierres de sesión'];
            $totalAT = $activityTypes->sum();
        @endphp
        <div class="space-y-3">
            @foreach($activityTypes as $event => $count)
                @if($count > 0 && isset($activityColors[$event]))
                @php $pct = $totalAT > 0 ? round(($count / $totalAT) * 100, 1) : 0; @endphp
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ $activityColors[$event] }} inline-block"></span>
                            {{ $activityLabels[$event] }}
                        </span>
                        <span class="text-sm font-bold {{ $activityTextColors[$event] }}">{{ $count }} ({{ $pct }}%)</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                        <div class="{{ $activityColors[$event] }} h-1.5 rounded-full" style="width:{{ $pct }}%"></div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Total registros</span>
                <span class="font-black text-gray-900 dark:text-white">{{ number_format($totalActivities) }}</span>
            </div>
        </div>
    </div>

    {{-- Polígonos: cobertura --}}
    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-xl shadow-lg p-5">
        <h3 class="text-base font-bold text-gray-900 dark:text-white mb-5">Cobertura de polígonos</h3>
        <div class="space-y-4">
            @foreach([
                ['label' => 'Con productor asignado', 'color' => 'bg-emerald-500', 'text' => 'text-emerald-600', 'count' => $polygonsWithProducer,    'pct' => $polygonsWithProducerPct],
                ['label' => 'Sin productor',           'color' => 'bg-gray-400',    'text' => 'text-gray-500',    'count' => $polygonsWithoutProducer, 'pct' => 100 - $polygonsWithProducerPct],
                ['label' => 'Polígonos activos',       'color' => 'bg-blue-500',    'text' => 'text-blue-600',    'count' => $activePolygons,          'pct' => $activePolygonsPercentage],
            ] as $item)
            <div>
                <div class="flex justify-between mb-1">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $item['label'] }}</span>
                    <span class="text-sm font-bold {{ $item['text'] }}">{{ $item['count'] }} ({{ $item['pct'] }}%)</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="{{ $item['color'] }} h-2 rounded-full" style="width:{{ $item['pct'] }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-4 grid grid-cols-2 gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="text-center p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
                <p class="text-xs text-gray-500">Área promedio</p>
                <p class="text-xl font-black text-emerald-600">
                    {{ $totalPolygons > 0 ? number_format($totalAreaHa / $totalPolygons, 1) : 0 }}
                </p>
                <p class="text-xs text-gray-400">ha / polígono</p>
            </div>
            <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <p class="text-xs text-gray-500">Este mes</p>
                <p class="text-xl font-black text-blue-600">{{ $polygonsThisMonth }}</p>
                <p class="text-xs text-gray-400">nuevos</p>
            </div>
        </div>
    </div>

</div>

{{-- ---- FILA 5: Rankings ---- --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">

    {{-- Top usuarios --}}
    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-xl shadow-lg p-5">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Usuarios más activos (7 días)</h3>
            <span class="text-xs text-gray-400">por acciones</span>
        </div>
        <div class="space-y-3">
            @forelse($topActiveUsers as $i => $user)
            @php $maxCount = $topActiveUsers->max('activity_count'); @endphp
            <div class="flex items-center gap-3">
                <span class="w-6 text-center text-xs font-bold {{ $i === 0 ? 'text-yellow-500' : 'text-gray-400' }}">
                    {{ $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : $i+1)) }}
                </span>
                <div class="flex-1">
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->user_name ?? 'Usuario #'.$user->causer_id }}</span>
                        <span class="text-sm font-bold text-blue-600">{{ $user->activity_count }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                        <div class="{{ $i === 0 ? 'bg-yellow-400' : 'bg-blue-500' }} h-1.5 rounded-full"
                             style="width:{{ $maxCount > 0 ? round(($user->activity_count / $maxCount) * 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-center text-sm text-gray-500 py-6">Sin datos de actividad esta semana</p>
            @endforelse
        </div>
    </div>

    {{-- Top técnicos --}}
    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-xl shadow-lg p-5">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">
                <span class="inline-block w-2 h-2 bg-indigo-500 rounded-full mr-2"></span>
                Técnicos más activos (7 días)
            </h3>
            <span class="text-xs bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 px-2 py-1 rounded-full">{{ $techActivitiesCount }} acciones</span>
        </div>
        <div class="space-y-3">
            @forelse($topActiveTecnicos as $i => $tec)
            @php $maxT = $topActiveTecnicos->max('activity_count'); @endphp
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ $i + 1 }}</span>
                </div>
                <div class="flex-1">
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $tec->user_name ?? 'Técnico #'.$tec->causer_id }}</span>
                        <span class="text-sm font-bold text-indigo-600">{{ $tec->activity_count }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                        <div class="bg-indigo-500 h-1.5 rounded-full"
                             style="width:{{ $maxT > 0 ? round(($tec->activity_count / $maxT) * 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-6 text-gray-500">
                <p class="text-sm">No hay actividad de técnicos esta semana</p>
            </div>
            @endforelse
        </div>
    </div>

</div>

{{-- ---- FILA 6: Actividad reciente (tabla) ---- --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">

    {{-- Bitácora general --}}
    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-xl shadow-lg p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Actividad reciente</h3>
            <a href="{{ route('admin.audit') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Ver todo →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left pb-2 text-xs font-semibold text-gray-500 dark:text-gray-400">Usuario</th>
                        <th class="text-left pb-2 text-xs font-semibold text-gray-500 dark:text-gray-400">Acción</th>
                        <th class="text-left pb-2 text-xs font-semibold text-gray-500 dark:text-gray-400">Módulo</th>
                        <th class="text-right pb-2 text-xs font-semibold text-gray-500 dark:text-gray-400">Hace</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
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
                            default    => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                        };
                        $evLabel = ['created'=>'Creó','updated'=>'Actualizó','deleted'=>'Eliminó','restored'=>'Restauró','login'=>'Ingresó','logout'=>'Salió'][$ev] ?? ucfirst($ev);
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                        <td class="py-2.5 text-gray-900 dark:text-white font-medium">{{ Str::limit($activity->causer?->name ?? 'Sistema', 18) }}</td>
                        <td class="py-2.5">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $badge }}">{{ $evLabel }}</span>
                        </td>
                        <td class="py-2.5 text-gray-500 dark:text-gray-400">{{ class_basename($activity->subject_type ?? 'Sistema') }}</td>
                        <td class="py-2.5 text-gray-400 text-right">{{ $activity->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-6 text-center text-gray-500">Sin actividad reciente</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Bitácora técnicos --}}
    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-xl shadow-lg p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">
                <span class="w-2 h-2 bg-indigo-500 rounded-full inline-block mr-2"></span>
                Últimas acciones de técnicos
            </h3>
            <a href="{{ route('admin.audit', ['role' => 'tecnico']) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Ver todo →</a>
        </div>
        <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
            @forelse($recentTecnicoActivities as $activity)
            @php
                $ev2 = $activity->event ?? 'other';
                $badge2 = match($ev2) {
                    'created'  => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                    'updated'  => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                    'deleted'  => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                    'restored' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                    default    => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                };
            @endphp
            <div class="flex items-start gap-2 p-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                <div class="w-7 h-7 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ strtoupper(substr($activity->causer?->name ?? 'T', 0, 1)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $activity->causer?->name ?? 'Técnico' }}</span>
                        <span class="text-xs px-1.5 py-0.5 rounded-full {{ $badge2 }}">{{ ucfirst($ev2) }}</span>
                        <span class="text-xs text-gray-500 ml-auto">{{ $activity->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">{{ Str::limit($activity->description, 55) }}</p>
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-gray-500 text-sm">No hay acciones recientes de técnicos</div>
            @endforelse
        </div>
    </div>

</div>

{{-- ============================================================
     VISTA TÉCNICO
     ============================================================ --}}
@elseif(auth()->check() && auth()->user()->role === 'tecnico')

<div class="mb-6">
    <h1 class="text-3xl font-black text-gray-900 dark:text-white mb-1">Panel Técnico</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400">
        Bienvenido, {{ Auth::user()->name }} · {{ now()->translatedFormat('l, d \\d\\e F \\d\\e Y') }}
    </p>
</div>

{{-- KPIs del técnico --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">

    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow-lg p-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-center justify-between mb-3">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Mis actividades</p>
                <h3 class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ $myActivitiesCount ?? 0 }}</h3>
            </div>
            <div class="w-11 h-11 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400">Total histórico de acciones</p>
    </div>

    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow-lg p-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-center justify-between mb-3">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Actividad semanal</p>
                <h3 class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ $myWeeklyActivities ?? 0 }}</h3>
            </div>
            <div class="w-11 h-11 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400">Esta semana</p>
    </div>

    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow-lg p-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-center justify-between mb-3">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Actividad mensual</p>
                <h3 class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ $myMonthlyActivities ?? 0 }}</h3>
            </div>
            <div class="w-11 h-11 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400">Este mes</p>
    </div>

    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow-lg p-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-center justify-between mb-3">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Hoy</p>
                <h3 class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ $myActivitiesToday ?? 0 }}</h3>
            </div>
            <div class="w-11 h-11 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400">Acciones registradas hoy</p>
    </div>

</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

    {{-- Gráfico diario del técnico --}}
    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-xl shadow-lg p-5 lg:col-span-2">
        <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">Mi actividad últimos 7 días</h3>
        <div class="relative h-44">
            <canvas id="myDailyChart"></canvas>
        </div>
    </div>

    {{-- Acciones rápidas --}}
    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-xl shadow-lg p-5">
        <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">Acciones rápidas</h3>
        <div class="space-y-3">
            @foreach([
                ['route' => 'admin.users.index', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'label' => 'Gestionar usuarios', 'sub' => 'Ver y editar usuarios', 'color' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'],
                ['route' => 'admin.audit',       'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'label' => 'Ver bitácora',      'sub' => 'Registro de actividades', 'color' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400'],
                ['route' => 'profile.edit',      'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'Mi perfil', 'sub' => 'Configurar mi cuenta', 'color' => 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400'],
            ] as $action)
            <a href="{{ route($action['route']) }}" class="group flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/40 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <div class="w-9 h-9 {{ $action['color'] }} rounded-lg flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $action['icon'] }}"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $action['label'] }}</p>
                    <p class="text-xs text-gray-500">{{ $action['sub'] }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>

</div>

{{-- Tabla de mis actividades recientes --}}
<div class="bg-stone-100/90 dark:bg-custom-gray rounded-xl shadow-lg p-5">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Mis actividades recientes</h3>
        <span class="text-xs text-gray-400">Últimas {{ $myRecentActivities->count() }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <th class="text-left pb-2 text-xs font-semibold text-gray-500">Acción</th>
                    <th class="text-left pb-2 text-xs font-semibold text-gray-500">Descripción</th>
                    <th class="text-right pb-2 text-xs font-semibold text-gray-500">Fecha</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($myRecentActivities ?? [] as $activity)
                @php
                    $at = $activity->event ?? 'other';
                    $bc = match($at) { 'created'=>'bg-green-100 text-green-700','updated'=>'bg-blue-100 text-blue-700','deleted'=>'bg-red-100 text-red-700','restored'=>'bg-yellow-100 text-yellow-700', default=>'bg-gray-100 text-gray-700' };
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                    <td class="py-2.5"><span class="text-xs px-2 py-0.5 rounded-full {{ $bc }}">{{ ucfirst($at) }}</span></td>
                    <td class="py-2.5 text-gray-600 dark:text-gray-400">{{ Str::limit($activity->description ?? 'Sin descripción', 65) }}</td>
                    <td class="py-2.5 text-gray-400 text-right whitespace-nowrap">{{ $activity->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="py-6 text-center text-gray-500">No tienes actividades registradas aún</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ============================================================
     VISTA BÁSICO
     ============================================================ --}}
@else
<div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow-lg p-10 text-center max-w-lg mx-auto mt-12">
    <div class="w-20 h-20 mx-auto mb-6 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
        <svg class="w-10 h-10 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
    </div>
    <h2 class="text-2xl font-black text-gray-900 dark:text-white mb-2">¡Bienvenido, {{ Auth::user()->name }}!</h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Tu cuenta tiene acceso básico. Contacta con un administrador para obtener más permisos.</p>
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">
            <p class="text-xs text-gray-500">Rol</p>
            <p class="text-sm font-bold text-gray-900 dark:text-white capitalize">{{ Auth::user()->role }}</p>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">
            <p class="text-xs text-gray-500">Correo</p>
            <p class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ Auth::user()->email }}</p>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">
            <p class="text-xs text-gray-500">Miembro desde</p>
            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ Auth::user()->created_at->format('d/m/Y') }}</p>
        </div>
    </div>
</div>
@endif

</x-app-layout>

{{-- ============================================================
     SCRIPTS (Chart.js)
     ============================================================ --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const isDark = document.documentElement.classList.contains('dark');
    const gridColor   = isDark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.06)';
    const labelColor  = isDark ? '#9ca3af' : '#6b7280';
    const tooltipBg   = isDark ? '#1f2937' : '#ffffff';
    const tooltipText = isDark ? '#f9fafb' : '#111827';

    // ---- Animación de círculos de progreso ----
    @if(auth()->check() && auth()->user()->role === 'administrador')
    const circles = [
        { id: 'circle-users',      pct: {{ $activeUsersPercentage }} },
        { id: 'circle-polygons',   pct: {{ $activePolygonsPercentage }} },
        { id: 'circle-producers',  pct: {{ $activeProducersPercentage }} },
        { id: 'circle-activities', pct: {{ $todayPct ?? 0 }} },
    ];
    const CIRC = 163;
    circles.forEach(({ id, pct }, i) => {
        const el = document.getElementById(id);
        if (!el) return;
        setTimeout(() => {
            const target = CIRC - (Math.min(pct, 100) / 100) * CIRC;
            let start = null;
            const animate = ts => {
                if (!start) start = ts;
                const p = Math.min((ts - start) / 900, 1);
                const ease = 1 - Math.pow(1 - p, 3);
                el.style.strokeDashoffset = CIRC + (target - CIRC) * ease;
                if (p < 1) requestAnimationFrame(animate);
            };
            requestAnimationFrame(animate);
        }, i * 180);
    });

    // ---- Gráfico mensual (actividades + polígonos) ----
    const monthlyCtx = document.getElementById('monthlyChart');
    if (monthlyCtx) {
        const monthlyData = @json($monthlyActivity);
        const polygonsData = @json($polygonsByMonth);

        // Unir etiquetas de meses
        const allMonths = [...new Set([...monthlyData.map(d => d.month), ...polygonsData.map(d => d.month)])];

        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: allMonths,
                datasets: [
                    {
                        label: 'Actividades',
                        data: allMonths.map(m => monthlyData.find(d => d.month === m)?.count ?? 0),
                        backgroundColor: 'rgba(168,85,247,0.7)',
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                    {
                        label: 'Polígonos',
                        data: allMonths.map(m => polygonsData.find(d => d.month === m)?.count ?? 0),
                        backgroundColor: 'rgba(16,185,129,0.7)',
                        borderRadius: 6,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { backgroundColor: tooltipBg, titleColor: tooltipText, bodyColor: tooltipText } },
                scales: {
                    x: { grid: { color: gridColor }, ticks: { color: labelColor, font: { size: 11 } } },
                    y: { grid: { color: gridColor }, ticks: { color: labelColor, font: { size: 11 } }, beginAtZero: true }
                }
            }
        });
    }

    // ---- Gráfico por hora ----
    const hourlyCtx = document.getElementById('hourlyChart');
    if (hourlyCtx) {
        const hourlyData = @json($hourlyChartData);
        new Chart(hourlyCtx, {
            type: 'line',
            data: {
                labels: hourlyData.map(d => d.label),
                datasets: [{
                    label: 'Actividades',
                    data: hourlyData.map(d => d.count),
                    borderColor: 'rgba(99,102,241,0.9)',
                    backgroundColor: 'rgba(99,102,241,0.15)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 2,
                    pointHoverRadius: 5,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { backgroundColor: tooltipBg, titleColor: tooltipText, bodyColor: tooltipText } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: labelColor, font: { size: 10 }, maxTicksLimit: 8 } },
                    y: { grid: { color: gridColor }, ticks: { color: labelColor, font: { size: 10 } }, beginAtZero: true }
                }
            }
        });
    }
    @endif

    // ---- Gráfico diario técnico ----
    @if(auth()->check() && auth()->user()->role === 'tecnico')
    const myDailyCtx = document.getElementById('myDailyChart');
    if (myDailyCtx) {
        const myData = @json($myDailyChartData ?? []);
        new Chart(myDailyCtx, {
            type: 'bar',
            data: {
                labels: myData.map(d => d.date),
                datasets: [{
                    label: 'Mis acciones',
                    data: myData.map(d => d.count),
                    backgroundColor: 'rgba(99,102,241,0.7)',
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { backgroundColor: tooltipBg, titleColor: tooltipText, bodyColor: tooltipText } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: labelColor, font: { size: 11 } } },
                    y: { grid: { color: gridColor }, ticks: { color: labelColor, font: { size: 11 } }, beginAtZero: true }
                }
            }
        });
    }
    @endif
});
</script>

<style>
.dark #monthlyChart, .dark #hourlyChart, .dark #myDailyChart { filter: brightness(1.05); }
</style>
<x-app-layout>
    @if(auth()->check() && auth()->user()->role === 'administrador')
    
    <!-- Header del Dashboard -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Panel de Inicio</h1>
        <p class="text-gray-600 dark:text-gray-400">
            {{ now()->translatedFormat('l, d \\d\\e F \\d\\e Y') }}
        </p>
    </div>
    
    <!-- Grid de Métricas Principales CON CÍRCULOS DE PROGRESO ANIMADOS -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        <!-- Tarjeta MODIFICADA: Usuarios Activos -->
        <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow-lg p-6 hover:shadow-xl hover:-translate-y-1 hover:transition-all hover:duration-300">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Usuarios Conectados</p>
                    <h3 id="user-count" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                        {{ $activeUsersCount }}<span class="text-lg text-gray-500">/{{ $totalUsers }}</span>
                    </h3>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16 11 2 2 4-4"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle stroke-linecap="round" stroke-linejoin="round" stroke-width="2" cx="9" cy="7" r="4"/>
                    </svg>
                </div>
            </div>
            
            <div class="flex items-center justify-between">
                <div class="info">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Porcentaje activo</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        {{ $activeUsersCount }} de {{ $totalUsers }} usuarios
                    </p>
                </div>
                
                <!-- Círculo de progreso ANIMADO -->
                <div class="progresss relative w-20 h-20">
                    <svg class="w-20 h-20 transform -rotate-90">
                        <circle cx="40" cy="40" r="32" 
                                class="fill-none stroke-gray-200 dark:stroke-gray-700" 
                                stroke-width="11"></circle>
                        <circle id="user-activity-circle" cx="40" cy="40" r="32" 
                                class="fill-none stroke-blue-600 dark:stroke-blue-400"
                                stroke-width="11" 
                                stroke-dasharray="218" 
                                stroke-dashoffset="218" 
                                stroke-linecap="round"></circle>
                    </svg>
                    <div class="percentage absolute inset-0 flex items-center justify-center">
                        <p id="user-activity-percentage" class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                            {{ $activeUsersPercentage }}%
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tarjeta: Total Actividades CON CÍRCULO ANIMADO -->
        <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow-lg p-6 hover:shadow-xl hover:-translate-y-1 hover:transition-all hover:duration-300">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Actividades</p>
                    <h3 id="activity-count" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalActivities }}</h3>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            
            <div class="flex items-center justify-between">
                <div class="info">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Crecimiento semanal</p>
                </div>
                
                <!-- Círculo de progreso ANIMADO -->
                <div class="progresss relative w-20 h-20">
                    <svg class="w-20 h-20 transform -rotate-90">
                        <circle cx="40" cy="40" r="32" 
                                class="fill-none stroke-green-100 dark:stroke-gray-700" 
                                stroke-width="11"></circle>
                        <circle id="activity-growth-circle" cx="40" cy="40" r="32" 
                                class="fill-none stroke-green-600 dark:stroke-green-400" 
                                stroke-width="11" 
                                stroke-dasharray="220" 
                                stroke-dashoffset="220" 
                                stroke-linecap="round"></circle>
                    </svg>
                    <div class="percentage absolute inset-0 flex items-center justify-center">
                        <p id="activity-growth-percentage" class="text-sm font-semibold text-green-600 dark:text-green-400">
                            {{ $activityGrowthPercentage >= 0 ? '+' : '' }}{{ $activityGrowthPercentage }}%
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Tarjeta: Productores Activos CON CÍRCULO ANIMADO -->
        <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow-lg p-6 hover:shadow-xl hover:-translate-y-1 hover:transition-all hover:duration-300">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Productores Activos</p>
                    <h3 id="producer-count" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $activeProducers }}</h3>
                </div>
                <div class="w-12 h-12 bg-teal-100 dark:bg-teal-900/30 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-teal-600 dark:text-teal-400">
                        <path d="M7 20h10"/><path d="M10 20c5.5-2.5.8-6.4 3-10"/><path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"/><path d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2z"/>
                    </svg>
                </div>
            </div>
            
            <div class="flex items-center justify-between">
                <div class="info">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Crecimiento semanal</p>
                </div>
                
                <!-- Círculo de progreso ANIMADO -->
                <div class="progresss relative w-20 h-20">
                    <svg class="w-20 h-20 transform -rotate-90">
                        <circle cx="40" cy="40" r="32" 
                                class="fill-none stroke-teal-100 dark:stroke-gray-700" 
                                stroke-width="11"></circle>
                        <circle id="producer-growth-circle" cx="40" cy="40" r="32" 
                                class="fill-none stroke-teal-600 dark:stroke-teal-400" 
                                stroke-width="11" 
                                stroke-dasharray="218" 
                                stroke-dashoffset="218" 
                                stroke-linecap="round"></circle>
                    </svg>
                    <div class="percentage absolute inset-0 flex items-center justify-center">
                        <p id="producer-growth-percentage" class="text-sm font-semibold text-teal-600 dark:text-teal-400">
                            {{ $activeProducersPercentage }}%
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tarjeta: Actividad Hoy CON CÍRCULO ANIMADO -->
        <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow-lg p-6 hover:shadow-xl hover:-translate-y-1 hover:transition-all hover:duration-300">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Actividad Hoy</p>
                    <h3 id="today-activity-count" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $activitiesToday }}</h3>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            
            <div class="flex items-center justify-between">
                <div class="info">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Nuevos usuarios hoy</p>
                </div>
                
                <!-- Círculo de progreso ANIMADO -->
                <div class="progresss relative w-20 h-20">
                    <svg class="w-20 h-20 transform -rotate-90">
                        <circle cx="40" cy="40" r="32" 
                                class="fill-none stroke-purple-100 dark:stroke-gray-700" 
                                stroke-width="11"></circle>
                        <circle id="today-progress-circle" cx="40" cy="40" r="32" 
                                class="fill-none stroke-purple-600 dark:stroke-purple-400" 
                                stroke-width="11" 
                                stroke-dasharray="220" 
                                stroke-dashoffset="220" 
                                stroke-linecap="round"></circle>
                    </svg>
                    <div class="percentage absolute inset-0 flex items-center justify-center">
                        <p id="today-percentage" class="text-sm font-semibold text-purple-600 dark:text-purple-400">
                            {{ $newUsersToday }} 
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
       
        
    </div>
    
    <!-- Segunda Fila: Gráficos y Distribuciones -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        
        <!-- Distribución de Roles -->
        <div class="bg-stone-100/90 dark:bg-custom-gray rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Distribución de Roles</h3>
                <span class="text-sm text-gray-500">{{ $totalUsers }} usuarios</span>
            </div>
            
            <div class="space-y-4">
                <!-- Administradores -->
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Administradores</span>
                        <span class="text-sm font-bold text-blue-600">{{ $roleDistribution['administrador'] ?? 0 }} ({{ $adminPercentage }}%)</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $adminPercentage }}%"></div>
                    </div>
                </div>
                
                <!-- Básicos -->
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Usuarios Básicos</span>
                        <span class="text-sm font-bold text-green-600">{{ $roleDistribution['basico'] ?? 0 }} ({{ $basicPercentage }}%)</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $basicPercentage }}%"></div>
                    </div>
                </div>
                
                <!-- Estado -->
                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <p class="text-sm text-gray-500">Habilitados</p>
                            <p class="text-xl font-bold text-blue-600">{{ $totalUsers }}</p>
                        </div>                        <div class="text-center p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                            <p class="text-sm text-gray-500">Deshabilitados</p>
                            <p class="text-xl font-bold text-red-600">{{ $trashedUsers }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tipos de Actividad -->
        <div class="bg-stone-100/90 dark:bg-custom-gray rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Tipos de Actividad</h3>
            
            <div class="space-y-4">
                @php
                    $activityColors = [
                        'created' => 'bg-green-500',
                        'updated' => 'bg-blue-500',
                        'deleted' => 'bg-red-500',
                        'restored' => 'bg-yellow-500',
                        'login' => 'bg-purple-500',
                        'logout' => 'bg-orange-500'
                    ];
                    
                    $activityLabels = [
                        'created' => 'Creados',
                        'updated' => 'Actualizados',
                        'deleted' => 'Eliminados',
                        'restored' => 'Restaurados',
                        'login' => 'Inicios de sesión',
                        'logout' => 'Cierres de sesión'
                    ];
                    
                    $totalActivityTypes = $activityTypes->sum();
                @endphp
                
                @foreach($activityTypes as $event => $count)
                    @if($count > 0 && isset($activityColors[$event]))
                        @php
                            $percentage = $totalActivityTypes > 0 
                                ? round(($count / $totalActivityTypes) * 100, 1) 
                                : 0;
                        @endphp
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center">
                                    <span class="w-3 h-3 {{ $activityColors[$event] }} rounded-full mr-2"></span>
                                    {{ $activityLabels[$event] }}
                                </span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">
                                    {{ $count }} ({{ $percentage }}%)
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="{{ $activityColors[$event] }} h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endif
                @endforeach
                
                @if($totalActivityTypes == 0)
                <div class="text-center py-8 text-gray-500">
                    No hay datos de actividad disponibles
                </div>
                @endif
            </div>
        </div>
        
    </div>
    
    <!-- Tercera Fila: Usuarios Activos y Actividad Reciente -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        
        <!-- Top Usuarios Más Activos -->
        <div class="bg-stone-100/90 dark:bg-custom-gray rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Usuarios Más Activos (7 días)</h3>
            
            <div class="space-y-4">
                @foreach($topActiveUsers as $index => $user)
                <div class="flex items-center justify-between p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold mr-3">
                            {{ $index + 1 }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $user->user_name ?? 'Usuario #' . $user->causer_id }}</p>
                            <p class="text-sm text-gray-500">{{ $user->activity_count }} actividades</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-blue-600">
                            {{ round(($user->activity_count / max($activitiesThisWeek, 1)) * 100, 1) }}%
                        </div>
                        <div class="text-xs text-gray-500">del total</div>
                    </div>
                </div>
                @endforeach
                
                @if($topActiveUsers->isEmpty())
                <div class="text-center py-8 text-gray-500">
                    No hay datos de usuarios activos
                </div>
                @endif
            </div>
        </div>
        
        <!-- Estadísticas Rápidas -->
        <div class="bg-stone-100/90 dark:bg-custom-gray rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Estadísticas del Mes</h3>
            
            <div class="grid grid-cols-2 gap-4">
                <!-- Usuarios este mes -->
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        <span class="text-xs font-medium text-blue-600 bg-blue-100 dark:bg-blue-900/30 px-2 py-1 rounded-full">
                            {{ $usersThisMonth }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Nuevos usuarios</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $usersThisMonth }}</p>
                    <p class="text-xs text-gray-500 mt-1">este mes</p>
                </div>
                
                <!-- Actividades este mes -->
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span class="text-xs font-medium text-green-600 bg-green-100 dark:bg-green-900/30 px-2 py-1 rounded-full">
                            {{ $activitiesThisMonth }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Actividades</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $activitiesThisMonth }}</p>
                    <p class="text-xs text-gray-500 mt-1">este mes</p>
                </div>
                
                <!-- Tasa diaria -->
                <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        <span class="text-xs font-medium text-purple-600 bg-purple-100 dark:bg-purple-900/30 px-2 py-1 rounded-full">
                            {{ $dailyActivityRate }}%
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Tasa diaria</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $dailyActivityRate }}%</p>
                    <p class="text-xs text-gray-500 mt-1">actividad/user/día</p>
                </div>
                
                <!-- Semana vs Semana pasada -->
                <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-xs font-medium {{ $activityGrowthPercentage >= 0 ? 'text-green-600' : 'text-red-600' }} 
                            {{ $activityGrowthPercentage >= 0 ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }} 
                            px-2 py-1 rounded-full">
                            {{ $activityGrowthPercentage >= 0 ? '+' : '' }}{{ $activityGrowthPercentage }}%
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Crecimiento</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $activityGrowthPercentage >= 0 ? '+' : '' }}{{ $activityGrowthPercentage }}%</p>
                    <p class="text-xs text-gray-500 mt-1">vs. semana pasada</p>
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- Cuarta Fila: Actividad Reciente -->
    <div class="bg-stone-100/90 dark:bg-custom-gray rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Actividad Reciente</h3>
            <a href="{{ route('admin.audit') }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                Ver todas →
            </a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                        <th class="pb-3 text-sm font-semibold text-gray-900 dark:text-gray-300">Usuario</th>
                        <th class="pb-3 text-sm font-semibold text-gray-900 dark:text-gray-300">Acción</th>
                        <th class="pb-3 text-sm font-semibold text-gray-900 dark:text-gray-300">Modelo</th>
                        <th class="pb-3 text-sm font-semibold text-gray-900 dark:text-gray-300 text-right">Hora</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentActivities as $activity)
                    <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="py-3">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-300">
                                        {{ substr($activity->causer?->name ?? 'S', 0, 1) }}
                                    </span>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $activity->causer?->name ?? 'Sistema' }}
                                </span>
                            </div>
                        </td>
                        <td class="py-3">
                            @php
                                // DETECTAR TIPO DE ACTIVIDAD - ACTUALIZADO
                                $activityType = 'other';
                                $activityText = '';
                                
                                if ($activity->event) {
                                    // Para actividades de modelos
                                    $activityType = $activity->event;
                                } else {
                                    // Para actividades de sesión
                                    if (str_contains($activity->description, 'ha iniciado sesión')) {
                                        $activityType = 'login';
                                    } elseif (str_contains($activity->description, 'ha cerrado sesión')) {
                                        $activityType = 'logout';
                                    }
                                }
                                
                                // Configurar colores y textos
                                $badgeColors = [
                                    'created' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                    'updated' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                    'deleted' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                    'restored' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                    'login' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                    'logout' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300'
                                ];
                                
                                $eventLabels = [
                                    'created' => 'Creado',
                                    'updated' => 'Actualizado',
                                    'deleted' => 'Eliminado',
                                    'restored' => 'Restaurado',
                                    'login' => 'Inicio de sesión',
                                    'logout' => 'Cierre de sesión'
                                ];
                            @endphp
                            <span class="text-xs px-2 py-1 rounded-full {{ $badgeColors[$activityType] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                {{ $eventLabels[$activityType] ?? 'Actividad' }}
                            </span>
                        </td>

                        <td class="py-3 text-sm text-gray-600 dark:text-gray-400">
                            {{ class_basename($activity->subject_type ?? 'Sistema') }}
                        </td>
                        <td class="py-3 text-sm text-gray-500 text-right">
                            {{ $activity->created_at->diffForHumans() }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-gray-500 dark:text-gray-400">
                            No hay actividad reciente para mostrar
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @else
    <!-- Vista para usuarios no administradores -->
    <div class=" mx-auto">
        <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow-lg p-8 text-center">
            <div class="w-20 h-20 mx-auto mb-6 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                ¡Bienvenido, {{ Auth::user()->name }}!
            </h2>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Has iniciado sesión como usuario básico. Contacta con un administrador para acceder a más funciones.
            </p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8">
                <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Tu rol</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ Auth::user()->role }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Correo</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ Auth::user()->email }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Miembro desde</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ Auth::user()->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</x-app-layout>

<!-- Script para animar los círculos de progreso (como en la versión anterior) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuración de las métricas
    const metrics = {
        // NUEVO: Porcentaje de usuarios activos

        activeUsersPercentage: {{ $activeUsersPercentage }},
        activityGrowth: {{ $activityGrowthPercentage }},
        todayActivity: {{ $activitiesToday }},
        completionRate: {{ $completionRate }},
        activeProducersPercentage: {{ $activeProducersPercentage }}, 

        // Calcular porcentaje para actividad de hoy (vs promedio diario del mes)
        todayPercentage: function() {
            const avgDailyActivity = {{ $activitiesThisMonth }} > 0 ? 
                {{ $activitiesThisMonth }} / {{ now()->daysInMonth }} : 1;
            const percentage = avgDailyActivity > 0 ? 
                Math.min(100, ({{ $activitiesToday }} / avgDailyActivity) * 100) : 0;
            return Math.round(percentage);
        }
    };

    // Configuración de las animaciones
    const progressCircles = [
         {
            id: 'user-activity-circle',  // ¡ESTE ES EL QUE FALTA!
            percentage: Math.min(metrics.activeUsersPercentage, 100),
            duration: 1000,
            colorClass: 'green'
        },
        {
            id: 'activity-growth-circle',
            percentage: Math.min(Math.abs(metrics.activityGrowth), 100),
            duration: 1200,
            colorClass: metrics.activityGrowth >= 0 ? 'green' : 'red'
        },
        {
            id: 'today-progress-circle',
            percentage: metrics.todayPercentage(),
            duration: 1400,
            colorClass: 'purple'
        },
        {
            id: 'completion-progress-circle',
            percentage: Math.min(metrics.completionRate, 100),
            duration: 1600,
            colorClass: 'yellow'
        },
        {
            id: 'producer-growth-circle', // Agregar este objeto
            percentage: Math.min(metrics.activeProducersPercentage, 100),
            duration: 1200,
            colorClass: 'teal'
        }
    ];

    // Función para animar el círculo de progreso (como en la versión anterior)
    const animateProgressCircle = (circleId, percentage, duration = 1000) => {
        const circle = document.getElementById(circleId);
        if (!circle) return;
        
        const circumference = 235; // 2 * π * r (35 * 2 * 3.14 ≈ 220)
        const offset = circumference - (percentage / 100) * circumference;
        
        // Animar con requestAnimationFrame para suavidad
        let start = null;
        const startOffset = 235;
        const endOffset = offset;
        
        const animate = (timestamp) => {
            if (!start) start = timestamp;
            const progress = Math.min((timestamp - start) / duration, 1);
            
            // Easing function para animación suave
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const currentOffset = startOffset + (endOffset - startOffset) * easeOut;
            
            circle.style.strokeDashoffset = currentOffset;
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    };

    // Función para actualizar colores de círculos negativos
    const updateCircleColors = (circleId, isPositive, originalColor) => {
        const circleElement = document.getElementById(circleId);
        const textElement = document.getElementById(circleId.replace('-circle', '-percentage'));
        
        if (!isPositive) {
            if (circleElement) {
                // Remover clases de color original
                circleElement.classList.remove(
                    'stroke-blue-600', 'stroke-green-600', 
                    'dark:stroke-blue-400', 'dark:stroke-green-400'
                );
                // Agregar color rojo
                circleElement.classList.add('stroke-red-600', 'dark:stroke-red-400');
            }
            
            if (textElement) {
                textElement.classList.remove(
                    'text-blue-600', 'text-green-600',
                    'dark:text-blue-400', 'dark:text-green-400'
                );
                textElement.classList.add('text-red-600', 'dark:text-red-400');
            }
        }
    };

    // Animar todos los círculos con retraso escalonado
    progressCircles.forEach((circle, index) => {
        setTimeout(() => {
            animateProgressCircle(circle.id, circle.percentage, circle.duration);
            
            // Actualizar colores según si es positivo o negativo (solo para growth circles)
            if (circle.id === 'user-growth-circle' || circle.id === 'activity-growth-circle') {
                const isPositive = circle.id === 'user-growth-circle' ? 
                    metrics.userGrowth >= 0 : metrics.activityGrowth >= 0;
                
                updateCircleColors(circle.id, isPositive, circle.colorClass);
            }
        }, index * 200); // Retraso escalonado de 200ms entre cada círculo
    });

    // También exponer funciones para actualizar desde otros scripts
    window.updateDashboardStats = function(newMetrics) {
        if (newMetrics.userGrowth !== undefined) {
            metrics.userGrowth = newMetrics.userGrowth;
            animateProgressCircle('user-growth-circle', Math.min(Math.abs(newMetrics.userGrowth), 100));
            document.getElementById('user-growth-percentage').textContent = 
                (newMetrics.userGrowth >= 0 ? '+' : '') + newMetrics.userGrowth + '%';
            
            // Actualizar color si es negativo
            updateCircleColors('user-growth-circle', newMetrics.userGrowth >= 0, 'blue');
        }
        // ... puedes agregar más actualizaciones aquí para otras métricas
    };
});
</script>

<style>
    /* Estilos para los círculos de progreso */
.progresss svg circle {
    transition: stroke-dashoffset 0.5s ease;
}

/* Animación al cargar */
@keyframes dash {
    from {
        stroke-dashoffset: 220;
    }
    to {
        stroke-dashoffset: var(--dash-offset);
    }
}

/* Colores para diferentes estados */
.positive {
    color: #10b981; /* green-500 */
}

.negative {
    color: #ef4444; /* red-500 */
}

.neutral {
    color: #6b7280; /* gray-500 */
}

/* Animación para los círculos de progreso */
@keyframes progressAnimation {
    0% {
        stroke-dashoffset: 220;
    }
    100% {
        stroke-dashoffset: calc(220 - (220 * var(--progress-percent) / 100));
    }
}

/* Animación para los porcentajes */
@keyframes countUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.text-2xl.font-bold {
    animation: countUp 0.5s ease-out;
}

/* Estilos para los círculos de progreso */
.progresss circle {
    transition: stroke-dashoffset 1.5s ease-out;
}

/* Ajustes específicos para modo oscuro */
.dark .progresss circle:first-child {
    stroke: rgba(75, 85, 99, 0.5);
}


</style>


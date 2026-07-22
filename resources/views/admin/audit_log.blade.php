@php
$roleTranslations = [
    'administrador' => 'Administrador',
    'basico'        => 'Básico',
];
@endphp
<x-app-layout>
    <div class="mx-auto">
        <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden shadow-sm rounded-2xl shadow-soft p-4 md:p-6 lg:p-6 mb-6">
            <div class="text-gray-900 dark:text-gray-100">
                <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-200 mb-2 md:mb-2">
                    {{ __('Registro de Actividades') }}
                </h2>

                <div class="flex justify-between items-center mb-4">
                    <div class="space-x-4">
                        
                    </div>
                    
                    <div class="flex space-x-4">
                         
                           <a href="{{ route('admin.audit.pdf', request()->query()) }}" 
                            title="Descargar PDF" 
                            class="group px-2.5 py-1.5 bg-stone-200/80 hover:bg-red-600/80 dark:hover:bg-red-500/70 text-stone-700 hover:text-white border border-stone-300/70 hover:border-transparent dark:bg-gray-700/40 dark:text-gray-300 dark:hover:text-white dark:border-gray-600/50 rounded-md flex items-center hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 overflow-hidden">
                            
                                <!-- Contenedor del ícono - se contrae en hover -->
                                <span class="flex items-center justify-center w-6 h-6 transition-all duration-300 group-hover:w-6 group-hover:h-6 flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2002/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-icon lucide-map w-6 h-6 text-red-700/70 group-hover:text-white dark:text-red-400/70 transition-colors duration-300">
                                
                                        <path d="M4 4C4 3.44772 4.44772 3 5 3H14H14.5858C14.851 3 15.1054 3.10536 15.2929 3.29289L19.7071 7.70711C19.8946 7.89464 20 8.149 20 8.41421V20C20 20.5523 19.5523 21 19 21H5C4.44772 21 4 20.5523 4 20V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        <path d="M20 8H15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M11.5 13H11V17H11.5C12.6046 17 13.5 16.1046 13.5 15C13.5 13.8954 12.6046 13 11.5 13Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M15.5 17V13L17.5 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M16 15H17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M7 17L7 15.5M7 15.5L7 13L7.75 13C8.44036 13 9 13.5596 9 14.25V14.25C9 14.9404 8.44036 15.5 7.75 15.5H7Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>  
                                </span>
                               
                                <!-- Texto - oculto en estado normal, visible en hover -->
                                <span class="text-base font-medium transition-all duration-300 w-0 opacity-0 group-hover:w-8 group-hover:opacity-100 group-hover:ml-1 whitespace-nowrap overflow-hidden text-inherit">
                                    PDF
                                </span>
                            </a>
                        
                    </div>
                </div>
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <!-- Filtros -->
                @php
                    $advancedFilterFields = ['date_from', 'date_to', 'user_id', 'event_type', 'subject_type'];
                    $activeAdvancedFilters = collect($advancedFilterFields)->filter(fn($field) => request()->filled($field) && request($field) !== 'all')->count();
                    if (request()->filled('role') && request('role') !== 'all') $activeAdvancedFilters++;
                    $hasAnyFilter = request()->filled('search') || $activeAdvancedFilters > 0;
                    $hl = 'ring-2 ring-gray-500 !border-gray-500 !bg-gray-300/70 dark:!bg-gray-400/50 dark:!border-gray-300';
                @endphp
                <form method="GET" action="{{ route('admin.audit') }}" class="mb-6">
                    <div class="flex flex-wrap gap-4">
                        <input type="text" name="search" class="form-input w-56 sm:w-64 rounded-md bg-gray-200 border-gray-300 focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70" 
                                placeholder="Usuario o actividad..." value="{{ $search ?? '' }}">

                            <!-- Buscar -->
                        <button type="submit" title="Buscar" class="px-4 py-2 bg-gray-600/90 hover:bg-gray-600 text-white rounded-lg transition-all flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                            </svg>
                        </button>

                        <!-- Alternar panel de filtros avanzados -->
                        <button type="button" title="Filtros avanzados"
                            onclick="toggleAdvancedFilters()"
                            class="relative px-4 py-2 bg-gray-600/90 hover:bg-gray-600 text-white rounded-lg transition-all flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-funnel-icon lucide-funnel w-5 h-5">
                                <path d="M10 20a1 1 0 0 0 .553.895l2 1A1 1 0 0 0 14 21v-7a2 2 0 0 1 .517-1.341L21.74 4.67A1 1 0 0 0 21 3H3a1 1 0 0 0-.742 1.67l7.225 7.989A2 2 0 0 1 10 14z"/>
                            </svg>
                            @if($activeAdvancedFilters > 0)
                                <span class="absolute -top-2 -right-2 flex items-center justify-center w-5 h-5 text-[10px] font-bold bg-red-500/90 text-white rounded-full ring-2 ring-white dark:ring-custom-gray">
                                    {{ $activeAdvancedFilters }}
                                </span>
                            @endif
                        </button>

                        @if($hasAnyFilter)
                            <a href="{{ route('admin.audit') }}" class="px-4 py-2 bg-gray-400/90 hover:bg-gray-300 text-white rounded-lg transition-all flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-brush-cleaning-icon lucide-brush-cleaning w-5 h-5">
                                    <path d="m16 22-1-4"/><path d="M19 14a1 1 0 0 0 1-1v-1a2 2 0 0 0-2-2h-3a1 1 0 0 1-1-1V4a2 2 0 0 0-4 0v5a1 1 0 0 1-1 1H6a2 2 0 0 0-2 2v1a1 1 0 0 0 1 1"/><path d="M19 14H5l-1.973 6.767A1 1 0 0 0 4 22h16a1 1 0 0 0 .973-1.233z"/><path d="m8 22 1-4"/>
                                </svg>
                            </a>
                        @endif
                    </div>

                    <!-- Panel colapsable de filtros avanzados -->
                    <div id="advanced-filters-panel" class="grid transition-all duration-300 ease-in-out {{ $activeAdvancedFilters > 0 ? 'grid-rows-[1fr] opacity-100 mt-3' : 'grid-rows-[0fr] opacity-0' }}">
                        <div class="overflow-hidden">
                            <div class="border border-gray-300/80 dark:border-gray-700 rounded-lg bg-gray-100/20 dark:bg-gray-700/10 shadow-sm">
                                <!-- Encabezado del panel -->
                                <div class="bg-gray-200 dark:bg-gray-700 px-4 py-2.5 rounded-t-lg">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-black  dark:text-white">
                                                <path d="M10 20a1 1 0 0 0 .553.895l2 1A1 1 0 0 0 14 21v-7a2 2 0 0 1 .517-1.341L21.74 4.67A1 1 0 0 0 21 3H3a1 1 0 0 0-.742 1.67l7.225 7.989A2 2 0 0 1 10 14z"/>
                                            </svg>
                                            <h2 class="text-sm font-semibold text-black  dark:text-white">Filtros avanzados</h2>
                                        </div>
                                        <button type="button" onclick="toggleAdvancedFilters(false)" class="text-black/80 dark:text-white hover:text-black dark:hover:text-white p-2.5 rounded-full hover:bg-gray-300 dark:hover:bg-gray-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Contenido del panel -->
                                <div class="p-4">
                                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                                        <!-- Fechas -->
                                        <div>
                                            <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-0.5">Fecha desde</label>
                                            <input type="date" name="date_from" class="form-input w-full text-xs py-1.5 px-2 rounded-md bg-gray-200 border-gray-300 focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70 {{ !empty($dateFrom) ? $hl : '' }}" 
                                                value="{{ $dateFrom ?? '' }}" max="{{ now()->toDateString() }}">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-0.5">Fecha hasta</label>
                                            <input type="date" name="date_to" class="form-input w-full text-xs py-1.5 px-2 rounded-md bg-gray-200 border-gray-300 focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70 {{ !empty($dateTo) ? $hl : '' }}" 
                                                value="{{ $dateTo ?? '' }}" max="{{ now()->toDateString() }}">
                                        </div>

                                        <!-- Rol del usuario -->
                                        <div>
                                            <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-0.5">Rol</label>
                                            <select name="role" class="form-select w-full text-xs py-1.5 px-2 rounded-md bg-gray-200 border-gray-300 focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70 {{ ($role ?? 'all') !== 'all' ? $hl : '' }}">
                                                <option value="all" {{ ($role ?? 'all') == 'all' ? 'selected' : '' }}>Todos</option>
                                                <option value="administrador" {{ ($role ?? '') == 'administrador' ? 'selected' : '' }}>Administrador</option>
                                                <option value="basico" {{ ($role ?? '') == 'basico' ? 'selected' : '' }}>Básico</option>
                                            </select>
                                        </div>

                                        <!-- Usuario específico -->
                                        <div>
                                            <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-0.5">Usuario</label>
                                            <select name="user_id" class="form-select w-full text-xs py-1.5 px-2 rounded-md bg-gray-200 border-gray-300 focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70 {{ ($userId ?? 'all') !== 'all' ? $hl : '' }}">
                                                <option value="all" {{ ($userId ?? 'all') == 'all' ? 'selected' : '' }}>Todos</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}" {{ ($userId ?? '') == $user->id ? 'selected' : '' }}>
                                                        {{ $user->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Tipo de evento -->
                                        <div>
                                            <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-0.5">Evento</label>
                                            <select name="event_type" class="form-select w-full text-xs py-1.5 px-2 rounded-md bg-gray-200 border-gray-300 focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70 {{ ($eventType ?? 'all') !== 'all' ? $hl : '' }}">
                                                <option value="all" {{ ($eventType ?? 'all') == 'all' ? 'selected' : '' }}>Todos</option>
                                                <option value="created" {{ ($eventType ?? '') == 'created' ? 'selected' : '' }}>Creado</option>
                                                <option value="updated" {{ ($eventType ?? '') == 'updated' ? 'selected' : '' }}>Actualizado</option>
                                                <option value="deleted" {{ ($eventType ?? '') == 'deleted' ? 'selected' : '' }}>Eliminado</option>
                                                <option value="restored" {{ ($eventType ?? '') == 'restored' ? 'selected' : '' }}>Restaurado</option>
                                                <option value="login" {{ ($eventType ?? '') == 'login' ? 'selected' : '' }}>Inicio de sesión</option>
                                                <option value="logout" {{ ($eventType ?? '') == 'logout' ? 'selected' : '' }}>Cierre de sesión</option>
                                                <option value="role_change" {{ ($eventType ?? '') == 'role_change' ? 'selected' : '' }}>Cambio de rol</option>
                                            </select>
                                        </div>

                                        <!-- Modelo afectado -->
                                        <div>
                                            <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-0.5">Modelo</label>
                                            <select name="subject_type" class="form-select w-full text-xs py-1.5 px-2 rounded-md bg-gray-200 border-gray-300 focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70 {{ ($subjectType ?? 'all') !== 'all' ? $hl : '' }}">
                                                <option value="all" {{ ($subjectType ?? 'all') == 'all' ? 'selected' : '' }}>Todos</option>
                                                <option value="User" {{ ($subjectType ?? '') == 'User' ? 'selected' : '' }}>Usuario</option>
                                                <option value="Polygon" {{ ($subjectType ?? '') == 'Polygon' ? 'selected' : '' }}>Polígono</option>
                                                <option value="Producer" {{ ($subjectType ?? '') == 'Producer' ? 'selected' : '' }}>Productor</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pie del panel -->
                                <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-3 rounded-b-lg">
                                    <div class="flex justify-end space-x-2">

                                        @if($hasAnyFilter)
                                            <a href="{{ route('admin.audit') }}"
                                            title="Limpiar filtros"
                                            class="group px-2.5 py-1.5 bg-stone-200/80 hover:bg-gray-500/70 dark:hover:bg-gray-500/60 text-stone-700 hover:text-white border border-stone-300/70 hover:border-transparent dark:bg-gray-700/40 dark:text-gray-300 dark:hover:text-white dark:border-gray-600/50 rounded-md flex items-center hover:shadow-md hover:-translate-y-0.5 overflow-hidden">

                                                <!-- Contenedor del ícono - se contrae en hover -->
                                                <span class="flex items-center justify-center w-6 h-6 transition-all duration-300 group-hover:w-6 group-hover:h-6 flex-shrink-0">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-brush-cleaning-icon lucide-brush-cleaning w-6 h-6 text-gray-700/70 group-hover:text-white dark:text-gray-400/70">
                                                        <path d="m16 22-1-4"/><path d="M19 14a1 1 0 0 0 1-1v-1a2 2 0 0 0-2-2h-3a1 1 0 0 1-1-1V4a2 2 0 0 0-4 0v5a1 1 0 0 1-1 1H6a2 2 0 0 0-2 2v1a1 1 0 0 0 1 1"/><path d="M19 14H5l-1.973 6.767A1 1 0 0 0 4 22h16a1 1 0 0 0 .973-1.233z"/><path d="m8 22 1-4"/>
                                                    </svg>
                                                </span>

                                                <!-- Texto - oculto en estado normal, visible en hover -->
                                                <span class="text-base font-medium transition-all duration-300 w-0 opacity-0 group-hover:w-24 group-hover:opacity-100 group-hover:ml-1 whitespace-nowrap overflow-hidden text-inherit">
                                                    Limpiar filtros
                                                </span>
                                            </a>
                                        @endif

                                        <button type="submit"
                                        title="filtros avanzados y búsqueda" 
                                        class="group px-2.5 py-1.5 bg-stone-200/80 hover:bg-blue-800/70 dark:hover:bg-blue-500/60 text-stone-700 hover:text-white border border-stone-300/70 hover:border-transparent dark:bg-gray-700/40 dark:text-gray-300 dark:hover:text-white dark:border-gray-600/50 rounded-md flex items-center hover:shadow-md hover:-translate-y-0.5 overflow-hidden">

                                            <!-- Contenedor del ícono - se contrae en hover -->
                                            <span class="flex items-center justify-center w-6 h-6 transition-all duration-300 group-hover:w-6 group-hover:h-6 flex-shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-blue-700/70 group-hover:text-white dark:text-blue-500/70">
                                                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                                                </svg>
                                            </span>

                                            <!-- Texto - oculto en estado normal, visible en hover -->
                                            <span class="text-base font-medium transition-all duration-300 w-0 opacity-0 group-hover:w-24 group-hover:opacity-100 group-hover:ml-1 whitespace-nowrap overflow-hidden text-inherit">
                                                Aplicar filtros
                                            </span>
                                        </button>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                @if($activities->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-stone-100/90 dark:bg-custom-gray">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">Usuario</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">Rol</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">Actividad</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">Fecha y Hora</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">Detalles</th>
                                </tr>
                            </thead>
                            <tbody class="bg-stone-100/90 dark:bg-custom-gray divide-y divide-gray-200">
                                @foreach($activities as $activity)
                                    <tr class="hover:bg-gray-200/60 dark:hover:bg-gray-700/30 hover:shadow-lg hover:transition-all hover:duration-200">
                                        <!-- Columna Usuario (sin cambios) -->
                                        <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-2 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-400">
                                                        {{ $activity->causer ? $activity->causer->name : 'Sistema' }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-500">
                                                        {{ $activity->causer ? $activity->causer->email : 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Columna Rol (sin cambios) -->
                                        <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-2 whitespace-nowrap">
                                            @if($activity->causer && $activity->causer->role)
                                                @php
                                                    $roleColors = [
                                                        'administrador' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                                        'basico' => 'bg-green-200 text-green-900 dark:bg-green-900 dark:text-green-200',
                                                        'default' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                                    ];
                                                    $roleKey = strtolower($activity->causer->role);
                                                    $roleColor = $roleColors[$roleKey] ?? $roleColors['default'];
                                                    $roleName = $roleTranslations[$roleKey] ?? ucfirst($activity->causer->role);
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $roleColor }}">
                                                    {{ $roleName }}
                                                </span>
                                            @elseif($activity->causer)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                    Sin rol
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                    Sistema
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Columna Actividad (MEJORADA) -->
                                        <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-2">
                                            <div class="flex items-center">
                                                @php
                                                    // 1. Icono y color basados en $activity->event
                                                    $icon = match($activity->event) {
                                                        'created'  => 'plus',
                                                        'updated'  => 'edit',
                                                        'deleted'  => 'trash',
                                                        'restored' => 'rotate-ccw',
                                                        default    => 'activity'
                                                    };
                                                    $color = match($activity->event) {
                                                        'created'  => 'text-green-500',
                                                        'updated'  => 'text-blue-500',
                                                        'deleted'  => 'text-red-500',
                                                        'restored' => 'text-yellow-500',
                                                        default    => 'text-gray-500'
                                                    };

                                                    // 2. Traducción de la actividad basada en evento + modelo
                                                    $eventTranslations = [
                                                        'created'  => 'creado',
                                                        'updated'  => 'actualizado',
                                                        'deleted'  => 'eliminado',
                                                        'restored' => 'restaurado',
                                                    ];
                                                    $modelName = $activity->subject_type ? class_basename($activity->subject_type) : null;
                                                    $modelTranslations = [
                                                        'User'     => 'Usuario',
                                                        'Polygon'  => 'Polígono',
                                                        'Producer' => 'Productor',
                                                    ];
                                                    $modelTranslation = $modelTranslations[$modelName] ?? $modelName ?? '';

                                                    // Caso especial: cambio de rol (detectado por la descripción)
                                                    if (str_contains($activity->description, 'fue actualizado su rol')) {
                                                        $translated = 'Rol actualizado';
                                                    } else {
                                                        // Construir la frase: "Modelo evento" (ej. "Polígono creado")
                                                        $translated = trim($modelTranslation . ' ' . ($eventTranslations[$activity->event] ?? $activity->event));
                                                        // Si no hay modelo, usar la descripción original
                                                        if (empty($modelTranslation)) {
                                                            $translated = $activity->description;
                                                        }
                                                    }
                                                @endphp

                                                <div class="flex-shrink-0 mr-3">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $color }}">
                                                        @if($icon == 'plus')
                                                            <line x1="12" y1="5" x2="12" y2="19"/>
                                                            <line x1="5" y1="12" x2="19" y2="12"/>
                                                        @elseif($icon == 'edit')
                                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                                        @elseif($icon == 'trash')
                                                            <path d="M3 6h18"/>
                                                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                                        @elseif($icon == 'rotate-ccw')
                                                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                                                            <path d="M3 3v5h5"/>
                                                        @else
                                                            <circle cx="12" cy="12" r="10"/>
                                                            <polyline points="12 6 12 12 16 14"/>
                                                        @endif
                                                    </svg>
                                                </div>

                                                <div>
                                                    <div class="text-sm text-gray-900 dark:text-gray-400">
                                                        {{ Str::limit($translated, 30) }}
                                                    </div>
                                                    @if($activity->subject_type)
                                                        <div class="text-xs text-gray-500 dark:text-gray-500">
                                                            {{ $modelTranslation }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Columna Fecha (sin cambios) -->
                                        <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-2 whitespace-nowrap text-gray-900 dark:text-gray-400">
                                            <div>{{ $activity->created_at->format('d/m/Y') }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-500">{{ $activity->created_at->format('H:i:s') }}</div>
                                        </td>

                                        <!-- Columna Detalles (MEJORADA) -->
                                        <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-2">
                                            {{-- Único caso: cambios automáticos (estructura estándar: attributes + old) --}}
                                            @if($activity->properties && $activity->properties->has('attributes') && $activity->properties->has('old'))
                                                @php
                                                    // Campos que no queremos mostrar
                                                    $excludedFields = ['description', 'updated_at', 'created_at'];

                                                    // Orden preferente para los campos
                                                    $preferredOrder = ['name', 'description', 'is_active', 'producer_id', 'parish_id', 'email', 'role', 'rut', 'phone', 'address', 'geometry', 'area'];

                                                    // Función para formatear valores
                                                    $formatValue = function($value, $attribute) use ($activity) {
                                                        if (is_null($value)) return 'N/A';
                                                        if (is_bool($value) || $value === '0' || $value === '1' || $value === 0 || $value === 1) {
                                                            return $value ? 'Activo' : 'Inactivo';
                                                        }
                                                        // Intentar obtener nombre para producer_id y parish_id si la relación está cargada
                                                        if ($attribute === 'producer_id' && $activity->subject && method_exists($activity->subject, 'producer')) {
                                                            $producer = $activity->subject->producer;
                                                            if ($producer && $producer->id == $value) {
                                                                return $producer->name ?? $value;
                                                            }
                                                        }
                                                        if ($attribute === 'parish_id' && $activity->subject && method_exists($activity->subject, 'parish')) {
                                                            $parish = $activity->subject->parish;
                                                            if ($parish && $parish->id == $value) {
                                                                return $parish->name ?? $value;
                                                            }
                                                        }
                                                        return $value;
                                                    };

                                                    // Traducción de nombres de campo
                                                    $translateField = function($field) {
                                                        $translations = [
                                                            'is_active' => 'Estado',
                                                            'name' => 'Nombre',
                                                            'email' => 'Correo',
                                                            'role' => 'Rol',
                                                            'password' => 'Contraseña',
                                                            'created_at' => 'Creado',
                                                            'updated_at' => 'Actualizado',
                                                            'deleted_at' => 'Eliminado',
                                                            'email_verified_at' => 'Verificado',
                                                            'polygon_id' => 'ID Polígono',
                                                            'producer_id' => 'Productor',
                                                            'parish_id' => 'Parroquia',
                                                            'geometry' => 'Geometría',
                                                            'area' => 'Área',
                                                            'description' => 'Descripción',
                                                            'rut' => 'RUT',
                                                            'phone' => 'Teléfono',
                                                            'address' => 'Dirección'
                                                        ];
                                                        return $translations[$field] ?? ucfirst(str_replace('_', ' ', $field));
                                                    };

                                                    // Obtener todos los cambios (sin límite), ordenados según preferencia
                                                    $changes = collect($activity->properties['attributes'])
                                                        ->filter(function($newValue, $attribute) use ($activity, $excludedFields) {
                                                            // 1. Excluir campos de la lista negra
                                                            if (in_array($attribute, $excludedFields)) return false;
                                                            // 2. Verificar que realmente haya cambiado
                                                            $oldValue = $activity->properties['old'][$attribute] ?? null;
                                                            return $newValue != $oldValue;
                                                        })
                                                        ->sortBy(function($value, $key) use ($preferredOrder) {
                                                            $pos = array_search($key, $preferredOrder);
                                                            return $pos === false ? 999 : $pos;
                                                        });
                                                        // NOTA: Ya no se aplica ->take(3), se muestran todos
                                                @endphp

                                                @if($changes->count() > 0)
                                                    <div class="text-xs space-y-1 max-w-xs">
                                                        @foreach($changes as $attribute => $newValue)
                                                            @php
                                                                $oldValue = $activity->properties['old'][$attribute] ?? null;
                                                                $label = $translateField($attribute);
                                                                $formattedOld = $formatValue($oldValue, $attribute);
                                                                $formattedNew = $formatValue($newValue, $attribute);
                                                            @endphp
                                                            <div class="flex items-center gap-1">
                                                                <span class="font-medium text-gray-700 dark:text-gray-300 min-w-[50px]">{{ $label }}:</span>
                                                                @if($formattedOld !== 'N/A')
                                                                    <span class="text-red-500 line-through truncate max-w-[60px]">{{ $formattedOld }}</span>
                                                                    <span class="text-gray-400 dark:text-gray-500">→</span>
                                                                @else
                                                                    <span class="text-gray-400 dark:text-gray-500 text-[10px]">[Nuevo]</span>
                                                                    <span class="text-gray-400 dark:text-gray-500">→</span>
                                                                @endif
                                                                <span class="text-green-600 dark:text-green-400 font-medium truncate max-w-[60px]">{{ $formattedNew }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    {{-- Si no hay cambios relevantes (filtrados) --}}
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                        Sin detalles
                                                    </span>
                                                @endif

                                            {{-- Fallback para cualquier otro formato (incluye old_role/new_role, updated_fields, etc.) --}}
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                    Sin detalles
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $activities->links() }}
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 0 1 5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
                        </svg>
                        <p class="text-gray-600 dark:text-gray-400">No se encontraron actividades.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Animación de apertura/cierre del panel de filtros avanzados (CSS Grid trick, sin medir alturas)
        function toggleAdvancedFilters(forceOpen) {
            const panel = document.getElementById('advanced-filters-panel');
            if (!panel) return;
            const isOpen = panel.classList.contains('grid-rows-[1fr]');
            const shouldOpen = typeof forceOpen === 'boolean' ? forceOpen : !isOpen;
            if (shouldOpen) {
                panel.classList.remove('grid-rows-[0fr]', 'opacity-0');
                panel.classList.add('grid-rows-[1fr]', 'opacity-100', 'mt-3');
            } else {
                panel.classList.remove('grid-rows-[1fr]', 'opacity-100', 'mt-3');
                panel.classList.add('grid-rows-[0fr]', 'opacity-0');
            }
        }

            document.addEventListener('DOMContentLoaded', function() {
            const dateFrom = document.querySelector('input[name="date_from"]');
            const dateTo = document.querySelector('input[name="date_to"]');

            function updateMinDate() {
                if (dateFrom.value) {
                    dateTo.setAttribute('min', dateFrom.value);
                } else {
                    dateTo.removeAttribute('min');
                }
            }

            dateFrom.addEventListener('change', updateMinDate);
            dateTo.addEventListener('change', function() {
                if (dateFrom.value && dateTo.value < dateFrom.value) {
                    alert('La fecha "Hasta" no puede ser anterior a la fecha "Desde".');
                    dateTo.value = '';
                }
            });

            // Inicializar
            updateMinDate();
        });
    </script>
    
    @push('styles')
    <style>
        /* Estilos para la paginación igual que en productores */
        .pagination {
            display: flex;
            justify-content: center;
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }
        
        .pagination li {
            margin: 0 2px;
        }
        
        .pagination li a,
        .pagination li span {
            display: block;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            border: 1px solid #d1d5db;
            color: #4b5563;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .pagination li.active span {
            background-color: #4f46e5;
            color: white;
            border-color: #4f46e5;
        }
        
        .pagination li a:hover {
            background-color: #f3f4f6;
        }
        
        .dark .pagination li a,
        .dark .pagination li span {
            border-color: #4b5563;
            color: #d1d5db;
        }
        
        .dark .pagination li.active span {
            background-color: #6366f1;
            border-color: #6366f1;
            color: white;
        }
        
        .dark .pagination li a:hover {
            background-color: #374151;
        }
    </style>
    @endpush
</x-app-layout>
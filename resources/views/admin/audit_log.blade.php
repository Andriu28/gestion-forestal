@php
$roleTranslations = [
    'administrador' => 'Administrador',
    'tecnico'       => 'Técnico',
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
                        
                         <!-- Botón para generar PDF -->
                        <a href="{{ route('admin.audit.pdf', request()->query()) }}" 
                            class="px-4 py-2 bg-red-600/90 text-white rounded-md hover:bg-red-600 flex items-center space-x-2"
                            title="Generar PDF">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-down-icon lucide-file-down">
                            <path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"/><path d="M14 2v5a1 1 0 0 0 1 1h5"/><path d="M12 18v-6"/><path d="m9 15 3 3 3-3"/>
                            </svg>
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
                <form method="GET" action="{{ route('admin.audit') }}" class="mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Búsqueda por texto -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Buscar</label>
                            <input type="text" name="search" class="w-full form-input rounded-md bg-gray-200 border-gray-300 focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70" 
                                placeholder="Usuario o actividad..." value="{{ $search ?? '' }}">
                        </div>

                        <!-- Rango de fechas -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha desde</label>
                            <input type="date" name="date_from" 
                                class="w-full form-input rounded-md bg-gray-200 border-gray-300 focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70" 
                                value="{{ $dateFrom ?? '' }}"
                                max="{{ now()->toDateString() }}">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha hasta</label>
                            <input type="date" name="date_to" 
                                class="w-full form-input rounded-md bg-gray-200 border-gray-300 focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70" 
                                value="{{ $dateTo ?? '' }}"
                                max="{{ now()->toDateString() }}">
                        </div>

                        <!-- Rol del usuario -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Rol</label>
                            <select name="role" class="w-full form-select rounded-md bg-gray-200 border-gray-300 focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70">
                                <option value="all" {{ ($role ?? 'all') == 'all' ? 'selected' : '' }}>Todos</option>
                                <option value="administrador" {{ ($role ?? '') == 'administrador' ? 'selected' : '' }}>Administrador</option>
                                <option value="tecnico" {{ ($role ?? '') == 'tecnico' ? 'selected' : '' }}>Técnico</option>
                                <option value="basico" {{ ($role ?? '') == 'basico' ? 'selected' : '' }}>Básico</option>
                                <option value="system" {{ ($role ?? '') == 'system' ? 'selected' : '' }}>Sistema</option>
                            </select>
                        </div>

                        <!-- Usuario específico -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Usuario</label>
                            <select name="user_id" class="w-full form-select rounded-md bg-gray-200 border-gray-300 focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70">
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
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Evento</label>
                            <select name="event_type" class="w-full form-select rounded-md bg-gray-200 border-gray-300 focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70">
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
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Modelo</label>
                            <select name="subject_type" class="w-full form-select rounded-md bg-gray-200 border-gray-300 focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70">
                                <option value="all" {{ ($subjectType ?? 'all') == 'all' ? 'selected' : '' }}>Todos</option>
                                <option value="User" {{ ($subjectType ?? '') == 'User' ? 'selected' : '' }}>Usuario</option>
                                <option value="Polygon" {{ ($subjectType ?? '') == 'Polygon' ? 'selected' : '' }}>Polígono</option>
                                <option value="Producer" {{ ($subjectType ?? '') == 'Producer' ? 'selected' : '' }}>Productor</option>
                            </select>
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="px-4 py-2 bg-gray-600/90 hover:bg-gray-600 text-white rounded-lg transition-all flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-funnel-icon lucide-funnel w-5 h-5">
                                    <path d="M10 20a1 1 0 0 0 .553.895l2 1A1 1 0 0 0 14 21v-7a2 2 0 0 1 .517-1.341L21.74 4.67A1 1 0 0 0 21 3H3a1 1 0 0 0-.742 1.67l7.225 7.989A2 2 0 0 1 10 14z"/>
                                </svg>
                            </button>
                            @if(request()->anyFilled(['search', 'date_from', 'date_to', 'role', 'user_id', 'event_type', 'subject_type']))
                                <a href="{{ route('admin.audit') }}" class="px-4 py-2 bg-gray-400/90 hover:bg-gray-300 text-white rounded-lg transition-all flex items-center space-x-2"> 
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-brush-cleaning-icon lucide-brush-cleaning w-5 h-5">
                                        <path d="m16 22-1-4"/><path d="M19 14a1 1 0 0 0 1-1v-1a2 2 0 0 0-2-2h-3a1 1 0 0 1-1-1V4a2 2 0 0 0-4 0v5a1 1 0 0 1-1 1H6a2 2 0 0 0-2 2v1a1 1 0 0 0 1 1"/><path d="M19 14H5l-1.973 6.767A1 1 0 0 0 4 22h16a1 1 0 0 0 .973-1.233z"/><path d="m8 22 1-4"/>
                                    </svg>
                                </a>
                            @endif
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
                                        <!-- Nueva columna para el rol -->
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
                                        <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-2">
                                            <div class="flex items-center">
                                                <!-- Icono según tipo de actividad -->
                                                @php
                                                    $icon = match(true) {
                                                        str_contains($activity->description, 'created') => 'plus',
                                                        str_contains($activity->description, 'updated') => 'edit',
                                                        str_contains($activity->description, 'deleted') => 'trash',
                                                        str_contains($activity->description, 'restored') => 'rotate-ccw',
                                                        default => 'activity'
                                                    };
                                                    
                                                    $color = match(true) {
                                                        str_contains($activity->description, 'created') => 'text-green-500',
                                                        str_contains($activity->description, 'updated') => 'text-blue-500',
                                                        str_contains($activity->description, 'deleted') => 'text-red-500',
                                                        str_contains($activity->description, 'restored') => 'text-yellow-500',
                                                        default => 'text-gray-500'
                                                    };
                                                    
                                                    // Traducción de la actividad
                                                    $translations = [
                                                        'El usuario ha sido updated' => 'Usuario actualizado',
                                                        'El usuario ha sido restored' => 'Usuario restaurado',
                                                        'El usuario ha sido created' => 'Usuario creado',
                                                        'El usuario ha sido deleted' => 'Usuario eliminado',
                                                        'Polygon created' => 'Polígono creado',
                                                        'Polygon updated' => 'Polígono actualizado',
                                                        'Polygon deleted' => 'Polígono eliminado',
                                                        'Polygon restored' => 'Polígono restaurado',
                                                        'Producer created' => 'Productor creado',
                                                        'Producer updated' => 'Productor actualizado',
                                                        'Producer deleted' => 'Productor eliminado',
                                                        'Producer restored' => 'Productor restaurado',
                                                    ];
                                                    
                                                    $description = $activity->description;
                                                    $translated = $translations[$description] ?? $description;
                                                    
                                                    if (str_contains($description, "fue actualizado su rol")) {
                                                        $userName = '';
                                                        if (preg_match("/Usuario '(.+?)' fue/", $description, $matches)) {
                                                            $userName = $matches[1];
                                                        }
                                                        $translated = "Rol actualizado";
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
                                                        {{ $translated }}
                                                    </div>
                                                    @if($activity->subject_type)
                                                        <div class="text-xs text-gray-500 dark:text-gray-500">
                                                            @php
                                                                $modelName = class_basename($activity->subject_type);
                                                                $modelTranslations = [
                                                                    'User' => 'Usuario',
                                                                    'Polygon' => 'Polígono',
                                                                    'Producer' => 'Productor',
                                                                ];
                                                                echo $modelTranslations[$modelName] ?? $modelName;
                                                            @endphp
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-2 whitespace-nowrap text-gray-900 dark:text-gray-400">
                                            <div>{{ $activity->created_at->format('d/m/Y') }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-500">{{ $activity->created_at->format('H:i:s') }}</div>
                                        </td> 
                                        <!-- Nueva columna para detalles para la vista del historial -->
                                        <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-2">
                                            {{-- 1. Cambio de rol manual (legacy) --}}
                                            @if($activity->properties && $activity->properties->has('old_role') && $activity->properties->has('new_role'))
                                                <div class="flex items-center gap-1 text-xs">
                                                    <span class="font-medium text-gray-700 dark:text-gray-300">Rol:</span>
                                                    <span class="text-red-500 line-through">{{ $activity->properties['old_role'] ?? 'N/A' }}</span>
                                                    <span class="text-gray-400 dark:text-gray-500">→</span>
                                                    <span class="text-green-600 dark:text-green-400 font-medium">{{ $activity->properties['new_role'] ?? 'N/A' }}</span>
                                                </div>

                                            {{-- 2. Cambios automáticos (estructura real: attributes + old) --}}
                                            @elseif($activity->properties && $activity->properties->has('attributes') && $activity->properties->has('old'))
                                                @php
                                                    // 🔥 Lista de campos que NO quieres mostrar
                                                    $excludedFields = ['description', 'updated_at', 'created_at'];
                                                    
                                                    // Función para formatear valores booleanos
                                                    $formatValue = function($value) {
                                                        if (is_null($value)) return 'N/A';
                                                        if (is_bool($value) || $value === '0' || $value === '1' || $value === 0 || $value === 1) {
                                                            return $value ? 'Activo' : 'Inactivo';
                                                        }
                                                        return $value;
                                                    };
                                                    
                                                    // Función para traducir nombres de campos
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
                                                            'producer_id' => 'ID Productor',
                                                            'geometry' => 'Geometría',
                                                            'area' => 'Área',
                                                            'description' => 'Descripción',
                                                            'rut' => 'RUT',
                                                            'phone' => 'Teléfono',
                                                            'address' => 'Dirección'
                                                        ];
                                                        return $translations[$field] ?? ucfirst(str_replace('_', ' ', $field));
                                                    };
                                                    
                                                    // FILTRAR: Solo mostrar campos que cambiaron Y no están excluidos
                                                    $changes = collect($activity->properties['attributes'])
                                                        ->filter(function($newValue, $attribute) use ($activity, $excludedFields) {
                                                            // 1. Excluir campos de la lista negra
                                                            if (in_array($attribute, $excludedFields)) {
                                                                return false;
                                                            }
                                                            
                                                            // 2. Verificar que realmente haya cambiado
                                                            $oldValue = $activity->properties['old'][$attribute] ?? null;
                                                            return $newValue != $oldValue;
                                                        })
                                                        ->take(3); // Limitar a 3 cambios
                                                @endphp
                                                
                                                @if($changes->count() > 0)
                                                    <div class="text-xs space-y-1 max-w-xs">
                                                        @foreach($changes as $attribute => $newValue)
                                                            @php
                                                                $oldValue = $activity->properties['old'][$attribute] ?? null;
                                                                $label = $translateField($attribute);
                                                                $formattedOld = $formatValue($oldValue);
                                                                $formattedNew = $formatValue($newValue);
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
                                                        
                                                        @if(count($activity->properties['attributes']) - count($excludedFields) > 3)
                                                            <div class="text-gray-500 text-[10px]">
                                                                +{{ count($activity->properties['attributes']) - count($excludedFields) - 3 }} campo(s) más
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    {{-- Mostrar "Sin detalles" cuando no hay cambios relevantes --}}
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                        Sin detalles
                                                    </span>
                                                @endif

                                            {{-- 3. Actualización de campos (legacy) --}}
                                            @elseif($activity->properties && $activity->properties->has('updated_fields'))
                                                @php
                                                    $fields = $activity->properties['updated_fields'];
                                                    $count = is_array($fields) ? count($fields) : $fields;
                                                @endphp
                                                <div class="flex items-center gap-1 text-xs">
                                                    <span class="font-medium text-gray-700 dark:text-gray-300">Campos:</span>
                                                    <span class="text-blue-600 dark:text-blue-400 font-medium">{{ $count }}</span>
                                                    <span class="text-gray-400 dark:text-gray-500">actualizado(s)</span>
                                                </div>

                                            {{-- 4. Sin detalles --}}
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                    Sin detalles
                                                </span>
                                            @endif
                                        </td>
                                        <!-- Fin de la nueva columna para detalles -->
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
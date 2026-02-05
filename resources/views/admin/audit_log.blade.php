<x-app-layout>
    <div class="mx-auto">
        <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden shadow-sm rounded-2xl shadow-soft p-4 md:p-6 lg:p-6 mb-6">
            <div class="text-gray-900 dark:text-gray-100">
                <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-200 mb-2 md:mb-2">
                    {{ __('Registro de Actividades') }}
                </h2>
                
                <!-- Filtros -->
                <form method="GET" action="{{ route('admin.audit') }}" class="mb-6">
                    <div class="flex flex-wrap gap-4">
                        <input type="text" name="search" class="form-input rounded-md bg-gray-200 border-gray-300 focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70" 
                               placeholder="Buscar por usuario o actividad..." value="{{ $search ?? '' }}">
                        <button type="submit" class="px-4 py-2 bg-stone-600 hover:bg-stone-700 text-white rounded-lg transition-all">Filtrar</button>
                        @if(request('search'))
                            <a href="{{ route('admin.audit') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">Limpiar</a>
                        @endif
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
                                        <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-2">
                                            @if($activity->properties && $activity->properties->has('old_role') && $activity->properties->has('new_role'))
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                    Rol: {{ $activity->properties['old_role'] }} -> {{ $activity->properties['new_role'] }}
                                                </span>
                                            @elseif($activity->properties && $activity->properties->has('updated_fields'))
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    {{ count($activity->properties['updated_fields']) }} campo(s) actualizado(s)
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
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
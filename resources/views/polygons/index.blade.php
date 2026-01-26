{{-- [file name]: index.blade.php --}}
<x-app-layout>
    <div class="">
        <div class=" mx-auto">
            <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden shadow-sm rounded-2xl shadow-soft p-4 md:p-6 lg:p-6 mb-6">
                <div class="text-gray-900 dark:text-gray-100">
                    <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-200 mb-2 md:mb-2">
                        {{ __('Gestión de Polígonos') }}
                    </h2>
                    
                    <div class="flex justify-end mb-4 space-x-4">
                        <div class="flex space-x-4">
                            
                            <a href="{{ route('polygons.map') }}" class="px-4 py-2 bg-blue-600/90 text-white rounded-md hover:bg-blue-600 flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                <span>{{ __('Mapa') }}</span>
                            </a>
                            <a href="{{ route('polygons.create') }}" class="px-4 py-2 bg-lime-600/90 text-white rounded-md hover:bg-lime-600 flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                    <circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/>
                                </svg>
                                <span>{{ __('Nuevo Polígono') }}</span>
                            </a>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <form method="GET" action="{{ route('polygons.index') }}" class="mb-6">
                        <div class="flex flex-wrap gap-4">
                            <input type="text" name="search" class="form-input rounded-md bg-gray-200 border-gray-300" 
                                   placeholder="Buscar por nombre, descripción o productor..." value="{{ $search ?? '' }}">
                            
                            <select name="status" class="form-select rounded-md bg-gray-200 border-gray-300">
                                <option value="all" {{ ($status ?? '') == 'all' ? 'selected' : '' }}>Todos los estados</option>
                                <option value="active" {{ ($status ?? '') == 'active' ? 'selected' : '' }}>Activos</option>
                                <option value="inactive" {{ ($status ?? '') == 'inactive' ? 'selected' : '' }}>Inactivos</option>
                                <option value="deleted" {{ ($status ?? '') == 'deleted' ? 'selected' : '' }}>Eliminados</option>
                            </select>
                            
                            <select name="type" class="form-select rounded-md bg-gray-200 border-gray-300">
                                <option value="all" {{ ($type ?? '') == 'all' ? 'selected' : '' }}>Todos los tipos</option>
                                <option value="with_producer" {{ ($type ?? '') == 'with_producer' ? 'selected' : '' }}>Con productor</option>
                                <option value="without_producer" {{ ($type ?? '') == 'without_producer' ? 'selected' : '' }}>Sin productor</option>
                            </select>
                            
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Filtrar</button>

                            @if(request('search') || (request('status') != 'all' && request()->has('status')) || (request('type') != 'all' && request()->has('type')))
                                <a href="{{ route('polygons.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">Limpiar</a>
                            @endif
                        </div>
                    </form>

                    @if($polygons->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-stone-100/90 dark:bg-custom-gray">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">Nombre</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">Productor</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">Área (Ha)</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">Descripción</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-stone-100/90 dark:bg-custom-gray divide-y divide-gray-200">
                                    @foreach($polygons as $polygon)
                                        <tr id="polygon-row-{{ $polygon->id }}" class="hover:bg-gray-200/60 dark:hover:bg-gray-700/30 hover:shadow-lg hover:transition-all hover:duration-200">
                                            <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20  px-6 py-2 whitespace-nowrap text-gray-900 dark:text-gray-400">{{ $polygon->name }}</td>
                                            <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20  px-6 py-2 whitespace-nowrap text-gray-900 dark:text-gray-400">
                                                {{ $polygon->producer_name }}
                                            </td>
                                            <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20  px-6 py-2 whitespace-nowrap text-gray-900 dark:text-gray-400">
                                                {{ $polygon->area_formatted }}
                                            </td>
                                            <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-2 text-gray-900 dark:text-gray-400">
                                                {{ Str::limit($polygon->description, 50) ?? 'Sin descripción' }}
                                            </td>
                                             <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-2 whitespace-nowrap">
                                                @if($polygon->trashed())
                                                    <span class="inline-block px-3 py-1 text-xs font-semibold bg-red-600 text-white rounded-full">Eliminado</span>
                                                @else
                                                    <span class="inline-block px-3 py-1 text-xs font-semibold {{ $polygon->is_active ? 'bg-green-600 text-white' : 'bg-yellow-500 text-white' }} rounded-full">
                                                        {{ $polygon->is_active ? 'Activo' : 'Inactivo' }}
                                                    </span>
                                                @endif
                                            </td>
                                            {{-- En polygons/index.blade.php - modificar la columna de Acciones --}}
                                            <td class="px-6 py-2 whitespace-nowrap">
                                                <div class="flex items-center gap-2">
                                                    @if(!$polygon->trashed())
                                                        <!-- Botón Ver - Ahora abre modal -->
                                                        <button type="button" 
                                                                class="inline-flex items-center text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 transition-colors p-1 hover:bg-gray-600 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110" 
                                                                title="Ver detalles"
                                                                onclick="showPolygonDetails({{ $polygon->id }})">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                                                                <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/>
                                                                <circle cx="12" cy="12" r="3"/>
                                                            </svg>
                                                        </button>

                                                        <!-- Botón Editar -->
                                                        <a href="{{ route('polygons.edit', $polygon) }}" 
                                                        class="inline-flex items-center text-indigo-600 hover:text-indigo-900 dark:text-indigo-500 dark:hover:text-indigo-300 transition-colors p-1 hover:bg-gray-600 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110"
                                                        title="Editar">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                                                                <path d="M13 21h8"/><path d="m15 5 4 4"/><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>
                                                            </svg>
                                                        </a>
                                                        
                                                        <!-- Botón Cambiar Estado (activo/inactivo) - AJAX -->
                                                        <button type="button" 
                                                                class="inline-flex items-center transition-colors p-1 hover:bg-gray-600 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110" 
                                                                title="{{ $polygon->is_active ? 'Desactivar' : 'Activar' }}"
                                                                onclick="handleTogglePolygonStatus({{ $polygon->id }}, '{{ addslashes($polygon->name) }}', {{ $polygon->is_active ? 'true' : 'false' }})">
                                                            @if($polygon->is_active)
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-500 w-7 h-7">
                                                                    <circle cx="12" cy="12" r="10" class="fill-yellow-100"/>
                                                                    <line x1="15" y1="9" x2="9" y2="15" class="stroke-yellow-600"/>
                                                                    <line x1="9" y1="9" x2="15" y2="15" class="stroke-yellow-600"/>
                                                                </svg>
                                                            @else
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500 w-7 h-7">
                                                                    <circle cx="12" cy="12" r="10" class="fill-green-100"/>
                                                                    <path d="m8 12 2.5 2.5L16 9" class="stroke-green-600"/>
                                                                </svg>
                                                            @endif
                                                        </button>

                                                        <!-- Botón Eliminar (soft delete) - AJAX -->
                                                        <button type="button" 
                                                                class="inline-flex items-center text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-300 transition-colors p-1 hover:bg-gray-600 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110" 
                                                                title="Eliminar"
                                                                onclick="handleDeletePolygon({{ $polygon->id }}, '{{ addslashes($polygon->name) }}')">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                                                                <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                                            </svg>
                                                        </button>
                                                    @else
                                                        <!-- Botón Restaurar - AJAX -->
                                                        <button type="button" 
                                                                class="inline-flex items-center text-green-600 hover:text-green-900 dark:text-green-500 dark:hover:text-green-300 transition-colors p-1 hover:bg-gray-600 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110" 
                                                                title="Restaurar"
                                                                onclick="handleRestorePolygon({{ $polygon->id }}, '{{ addslashes($polygon->name) }}')">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                                                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>
                                                            </svg>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $polygons->links() }}
                        </div>

                    @else
                        <div class="text-center py-8">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-gray-600 dark:text-gray-400">No se encontraron polígonos.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
   
<!-- Modal mejorado con diseño profesional -->
<x-modal name="view-polygon-details" maxWidth="2xl" :showClose="true">
    <div class="p-0 overflow-hidden">
        <!-- Encabezado del modal -->
        <div class="bg-[linear-gradient(135deg,_#3f2c1bdc_0%,_#30201b_100%)]  px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">
                            Detalles del Polígono
                        </h2>
                        <p class="text-blue-100 text-sm">Información completa y detallada</p>
                    </div>
                </div>
                <button x-on:click="$dispatch('close')" class="text-white/80 hover:text-white p-1 rounded-full hover:bg-white/10 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Contenido del modal -->
        <div class="p-6" id="polygon-details-content">
            <!-- Spinner de carga -->
            <div class="flex flex-col items-center justify-center py-12">
                <div class="relative">
                    <div class="animate-spin rounded-full h-16 w-16 border-4 border-blue-200"></div>
                    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                        <div class="animate-ping h-8 w-8 rounded-full bg-blue-400"></div>
                    </div>
                </div>
                <p class="mt-4 text-gray-600 dark:text-gray-400 font-medium">Cargando información del polígono...</p>
            </div>
        </div>
    </div>
</x-modal>
</x-app-layout>


<script>
// Función para hacer peticiones fetch (similar a productores)
async function makePolygonRequest(url, method = 'POST', data = null) {
    try {
        const options = {
            method: method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        };
        
        if (data) {
            options.body = JSON.stringify(data);
            options.headers['Content-Type'] = 'application/json';
        }
        
        const response = await fetch(url, options);
        return await response.json();
    } catch (error) {
        console.error('Fetch error:', error);
        return { success: false, message: 'Error de conexión' };
    }
}

// Función para cambiar estado de polígono
async function handleTogglePolygonStatus(polygonId, polygonName, isCurrentlyActive) {
    const action = isCurrentlyActive ? 'desactivar' : 'activar';
    
    const result = await showCustomConfirmation(
        !isCurrentlyActive,
        `¿Estás seguro de que deseas ${action} el polígono <b>${polygonName}</b>?`
    );
    
    if (result.isConfirmed) {
        const data = await makePolygonRequest(`/polygons/${polygonId}/toggle-status`, 'POST');
        
        if (data.success) {
            updatePolygonStatusUI(polygonId, polygonName, data.is_active, data.status_text);
            showCustomAlert('success', 'Éxito', `Polígono ${action} exitosamente.`);
        } else {
            showCustomAlert('error', 'Error', data.message);
        }
    }
}

// Función para actualizar la UI del estado del polígono
function updatePolygonStatusUI(polygonId, polygonName, isActive, statusText = null) {
    const row = document.getElementById(`polygon-row-${polygonId}`);
    if (!row) {
        console.error(`Fila no encontrada: polygon-row-${polygonId}`);
        return;
    }
    
    // Encontrar la celda de estado (5ta columna)
    const statusCell = row.querySelector('td:nth-child(5)');
    const toggleButton = row.querySelector('button[onclick*="handleTogglePolygonStatus"]');
    
    // Actualizar badge de estado
    if (statusCell) {
        if (isActive) {
            statusCell.innerHTML = '<span class="inline-block px-3 py-1 text-xs font-semibold bg-green-600 text-white rounded-full">Activo</span>';
        } else {
            statusCell.innerHTML = '<span class="inline-block px-3 py-1 text-xs font-semibold bg-yellow-500 text-white rounded-full">Inactivo</span>';
        }
    }
    
    // Actualizar botón de toggle - CORRECCIÓN IMPORTANTE
    if (toggleButton) {
        // Crear nuevo SVG basado en el estado
        let newSvg;
        let newTitle;
        let newOnclick;
        
        if (isActive) {
            // Icono para desactivar
            newSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-500 w-7 h-7">
                <circle cx="12" cy="12" r="10" class="fill-yellow-100"/>
                <line x1="15" y1="9" x2="9" y2="15" class="stroke-yellow-600"/>
                <line x1="9" y1="9" x2="15" y2="15" class="stroke-yellow-600"/>
            </svg>`;
            newTitle = 'Desactivar';
            newOnclick = `handleTogglePolygonStatus(${polygonId}, '${polygonName.replace(/'/g, "\\'")}', true)`;
        } else {
            // Icono para activar
            newSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500 w-7 h-7">
                <circle cx="12" cy="12" r="10" class="fill-green-100"/>
                <path d="m8 12 2.5 2.5L16 9" class="stroke-green-600"/>
            </svg>`;
            newTitle = 'Activar';
            newOnclick = `handleTogglePolygonStatus(${polygonId}, '${polygonName.replace(/'/g, "\\'")}', false)`;
        }
        
        // Reemplazar el contenido del botón
        toggleButton.innerHTML = newSvg;
        toggleButton.setAttribute('title', newTitle);
        toggleButton.setAttribute('onclick', newOnclick);
    }
}

// Función para eliminar polígono
async function handleDeletePolygon(polygonId, polygonName) {
    const result = await showCustomConfirmation(
        false,
        `¿Estás seguro de que deseas eliminar el polígono <b>${polygonName}</b>?<br><br>
         <small>El polígono será movido a la lista de eliminados.</small>`
    );
    
    if (result.isConfirmed) {
        const data = await makePolygonRequest(`/polygons/${polygonId}`, 'DELETE');
        
        if (data.success) {
            updatePolygonRowForDeleted(polygonId, polygonName);
            showCustomAlert('success', 'Éxito', 'Polígono eliminado exitosamente.');
        } else {
            showCustomAlert('error', 'Error', data.message);
        }
    }
}

// Función para restaurar polígono
async function handleRestorePolygon(polygonId, polygonName) {
    const result = await showCustomConfirmation(
        true,
        `¿Estás seguro de que deseas restaurar el polígono <b>${polygonName}</b>?`
    );
    
    if (result.isConfirmed) {
        const data = await makePolygonRequest(`/polygons/${polygonId}/restore`, 'POST');
        
        if (data.success) {
            updatePolygonRowForRestored(polygonId, polygonName, data.is_active);
            showCustomAlert('success', 'Éxito', 'Polígono restaurado exitosamente.');
        } else {
            showCustomAlert('error', 'Error', data.message);
        }
    }
}

// Función para actualizar fila cuando se elimina - CORREGIDA
function updatePolygonRowForDeleted(polygonId, polygonName) {
    const row = document.getElementById(`polygon-row-${polygonId}`);
    if (!row) {
        console.error(`Fila no encontrada para eliminar: polygon-row-${polygonId}`);
        return;
    }
    
    // Actualizar estado a "Eliminado"
    const statusCell = row.querySelector('td:nth-child(5)');
    if (statusCell) {
        statusCell.innerHTML = '<span class="inline-block px-3 py-1 text-xs font-semibold bg-red-600 text-white rounded-full">Eliminado</span>';
    }
    
    // Actualizar acciones
    const actionsCell = row.querySelector('td:nth-child(6)');
    if (actionsCell) {
        actionsCell.innerHTML = `
            <div class="flex items-center gap-2">
                <button type="button" 
                        class="inline-flex items-center text-green-600 hover:text-green-900 dark:text-green-500 dark:hover:text-green-300 transition-colors p-1 hover:bg-gray-600 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110" 
                        title="Restaurar"
                        onclick="handleRestorePolygon(${polygonId}, '${polygonName.replace(/'/g, "\\'")}')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>
                    </svg>
                </button>
            </div>
        `;
    }
}

// Función para actualizar fila cuando se restaura - CORREGIDA
function updatePolygonRowForRestored(polygonId, polygonName, isActive = true) {
    const row = document.getElementById(`polygon-row-${polygonId}`);
    if (!row) {
        console.error(`Fila no encontrada para restaurar: polygon-row-${polygonId}`);
        return;
    }
    
    // Actualizar estado
    const statusCell = row.querySelector('td:nth-child(5)');
    if (statusCell) {
        if (isActive) {
            statusCell.innerHTML = '<span class="inline-block px-3 py-1 text-xs font-semibold bg-green-600 text-white rounded-full">Activo</span>';
        } else {
            statusCell.innerHTML = '<span class="inline-block px-3 py-1 text-xs font-semibold bg-yellow-500 text-white rounded-full">Inactivo</span>';
        }
    }
    
    // Actualizar acciones
    const actionsCell = row.querySelector('td:nth-child(6)');
    if (actionsCell) {
        const activeStatus = isActive ? 'true' : 'false';
        const escapedName = polygonName.replace(/'/g, "\\'");
        
        actionsCell.innerHTML = `
            <div class="flex items-center gap-2">
                <a href="/polygons/${polygonId}" 
                   class="inline-flex items-center text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 transition-colors p-1 hover:bg-gray-600 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110" 
                   title="Ver">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                        <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </a>

                <a href="/polygons/${polygonId}/edit" 
                   class="inline-flex items-center text-indigo-600 hover:text-indigo-900 dark:text-indigo-500 dark:hover:text-indigo-300 transition-colors p-1 hover:bg-gray-600 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110"
                   title="Editar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                        <path d="M13 21h8"/><path d="m15 5 4 4"/><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>
                    </svg>
                </a>
        
                <button type="button" 
                        class="inline-flex items-center transition-colors p-1 hover:bg-gray-600 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110" 
                        title="${isActive ? 'Desactivar' : 'Activar'}"
                        onclick="handleTogglePolygonStatus(${polygonId}, '${escapedName}', ${activeStatus})">
                    ${isActive ? 
                        `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-500 w-7 h-7">
                            <circle cx="12" cy="12" r="10" class="fill-yellow-100"/>
                            <line x1="15" y1="9" x2="9" y2="15" class="stroke-yellow-600"/>
                            <line x1="9" y1="9" x2="15" y2="15" class="stroke-yellow-600"/>
                        </svg>` :
                        `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500 w-7 h-7">
                            <circle cx="12" cy="12" r="10" class="fill-green-100"/>
                            <path d="m8 12 2.5 2.5L16 9" class="stroke-green-600"/>
                        </svg>`
                    }
                </button>

                <button type="button" 
                        class="inline-flex items-center text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-300 transition-colors p-1 hover:bg-gray-600 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110" 
                        title="Eliminar"
                        onclick="handleDeletePolygon(${polygonId}, '${escapedName}')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                        <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                    </svg>
                </button>
            </div>
        `;
    }
}

// Función para mostrar detalles del polígono en modal
async function showPolygonDetails(polygonId) {
    try {
        // Mostrar loader en el modal
        const modalContent = document.getElementById('polygon-details-content');
        modalContent.innerHTML = `
            <div class="flex justify-center items-center py-8">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>
        `;
        
        // Abrir el modal - FORMA CORRECTA PARA LIVEWIRE/ALPINE
        const event = new CustomEvent('open-modal', { 
            detail: 'view-polygon-details' 
        });
        window.dispatchEvent(event);
        
        // Obtener datos del polígono
        const response = await fetch(`/polygons/${polygonId}/details`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        if (!response.ok) throw new Error('Error al cargar datos');
        
        const data = await response.json();
        
        if (data.success) {
            // Formatear y mostrar los datos
            modalContent.innerHTML = formatPolygonDetails(data.polygon);
        } else {
            throw new Error(data.message);
        }
        
    } catch (error) {
        console.error('Error al cargar detalles:', error);
        modalContent.innerHTML = `
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-red-700 dark:text-red-300">Error al cargar los detalles del polígono</span>
                </div>
                <p class="text-sm text-red-600 dark:text-red-400 mt-2">${error.message}</p>
            </div>
        `;
    }
}

// Función para formatear los detalles del polígono
function formatPolygonDetails(polygon) {
    const formatDate = (dateString) => {
        if (!dateString) return 'No disponible';
        const date = new Date(dateString);
        
        // Formato: DD/MM/YYYY HH:mm
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        
        return `${day}/${month}/${year} ${hours}:${minutes}`;
    };

    // Determinar color del estado
    let statusColor, statusBg;
    if (polygon.is_active) {
        statusColor = 'text-green-800 dark:text-green-300';
        statusBg = 'bg-green-100 dark:bg-green-900/30';
    } else {
        statusColor = 'text-yellow-800 dark:text-yellow-300';
        statusBg = 'bg-yellow-100 dark:bg-yellow-900/30';
    }

    return `
        <div class="space-y-6">
            <!-- Tarjeta de información principal -->
            <div class="bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">${polygon.name || 'Sin nombre'}</h3>
                        <div class="flex items-center space-x-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${statusColor} ${statusBg} border border-green-200 dark:border-green-800">
                                <span class="w-2 h-2 rounded-full ${polygon.is_active ? 'bg-green-500' : 'bg-yellow-500'} mr-2"></span>
                                ${polygon.is_active ? 'Activo' : 'Inactivo'}
                            </span>
                            ${polygon.producer ? `
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Con productor
                            </span>
                            ` : ''}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-gray-900 dark:text-white">${polygon.area_formatted || (polygon.area_ha ? polygon.area_ha + ' Ha' : 'N/D')}</div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Área total</p>
                    </div>
                </div>

                <!-- Grid de información -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
                    <!-- Información del productor -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center mb-2">
                            <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg mr-3">
                                <svg class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-700 dark:text-gray-300">Productor</h4>
                                <p class="text-gray-900 dark:text-white text-lg font-medium mt-1">
                                    ${polygon.producer ? polygon.producer.name : 'No asignado'}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Ubicación -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center mb-2">
                            <div class="p-2 bg-green-50 dark:bg-green-900/20 rounded-lg mr-3">
                                <svg class="w-7 h-7 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-700 dark:text-gray-300">Ubicación</h4>
                                <p class="text-gray-900 dark:text-white text-lg font-medium mt-1">
                                    ${polygon.parish ? polygon.parish.name : 'No especificada'}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Coordenadas -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center mb-2">
                            <div class="p-2 bg-purple-50 dark:bg-purple-900/20 rounded-lg mr-3">
                                <svg class="w-7 h-7 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-700 dark:text-gray-300">Coordenadas</h4>
                                <p class="text-gray-900 dark:text-white text-sm font-medium mt-1">
                                    ${polygon.centroid_lat && polygon.centroid_lng ? 
                                        `Lat:&nbsp;${parseFloat(polygon.centroid_lat).toFixed(5)} Lon:&nbsp;${parseFloat(polygon.centroid_lng).toFixed(5)}` : 
                                        'No disponibles'}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Descripción -->
            ${polygon.description ? `
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Descripción</h3>
                </div>
               

                

                <!-- Detección automática -->
                    <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">${polygon.description}</p>
                    </div>
                        ${polygon.parish ? `
                        <div class="flex items-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <div class="flex items-center">
                                <!-- Icono de cruz para parroquia -->
                                <svg class="w-5 h-5 text-blue-500 dark:text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">  
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205 3 1m1.5.5-1.5-.5M6.75 7.364V3h-3v18m3-13.636 10.5-3.819" />
                                </svg>

                                <span class="text-blue-700 dark:text-blue-300">Parroquia:&nbsp;</span>
                            </div>
                            <span class="font-medium text-blue-900 dark:text-blue-200"> ${polygon.parish.name}</span>
                        </div>
                        ` : ''}

                        ${polygon.parish.municipality ? `
                        <div class="flex items-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <div class="flex items-center">
                                <!-- Icono de edificios para municipio -->
                                <svg class="w-5 h-5 text-blue-500 dark:text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <span class="text-blue-700 dark:text-blue-300">Municipio:&nbsp;</span>
                            </div>
                            <span class="font-medium text-blue-900 dark:text-blue-200">${polygon.parish.municipality.name}</span>
                        </div>
                        ` : ''}

                        ${polygon.parish.municipality.state ? `
                        <div class="flex items-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <div class="flex items-center">
                                <!-- Icono de bandera para estado -->
                                <svg class="w-5 h-5 text-blue-500 dark:text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                                </svg>
                                <span class="text-blue-700 dark:text-blue-300">Estado:&nbsp;</span>
                            </div>
                            <span class="font-medium text-blue-900 dark:text-blue-200">${polygon.parish.municipality.state.name}</span>
                        </div>
                        ` : ''}
                    </div>
            </div>
            ` : ''}

            <!-- Información adicional -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información del Sistema</h3>
                
                <div class="grid grid-cols-1 gap-4">
                    
                
                    <!-- Fechas -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">Creado</span>
                            </div>
                            <span class="font-medium text-gray-900 dark:text-white">${formatDate(polygon.created_at)}</span>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">Actualizado</span>
                            </div>
                            <span class="font-medium text-gray-900 dark:text-white">${formatDate(polygon.updated_at)}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex flex-wrap gap-3 justify-between items-center">
                    <div class="space-x-2">
                       
                        
                        ${polygon.producer ? `
                        <a href="/producers/${polygon.producer.id}" 
                           class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-all hover:shadow-lg hover:-translate-y-0.5">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Ver productor
                        </a>
                        ` : ''}
                        
                        <a href="#" 
                           class="inline-flex items-center px-4 py-2.5 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-medium rounded-lg transition-all">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Editar
                        </a>
                    </div>
                    
                    <button x-on:click="$dispatch('close')" 
                            class="inline-flex items-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    `;
}

// Mostrar alertas de éxito/error de Laravel
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        showCustomAlert('success', 'Éxito', '{{ session('success') }}');
    @endif
    
    @if(session('error'))
        showCustomAlert('error', 'Error', '{{ session('error') }}');
    @endif
});
</script>


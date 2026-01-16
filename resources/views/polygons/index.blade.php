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
                                                        <!-- Botón Ver -->
                                                        <a href="{{ route('polygons.show', $polygon) }}" 
                                                        class="inline-flex items-center text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 transition-colors p-1 hover:bg-gray-600 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110" 
                                                        title="Ver">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                                                                <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/>
                                                                <circle cx="12" cy="12" r="3"/>
                                                            </svg>
                                                        </a>

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
// Función para actualizar la UI del estado del polígono - CORREGIDA
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


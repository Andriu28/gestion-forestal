<x-app-layout>
    <div class="mx-auto">
        <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden shadow-sm rounded-2xl shadow-soft p-4 md:p-6 lg:p-6 mb-6">
            <div class="text-gray-900 dark:text-gray-100">
                <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-200 mb-4 md:mb-4">
                    {{ __('Polígonos Deshabilitados') }}
                </h2>

                <div class="flex justify-end mb-6">
                    <a href="{{ route('polygons.index') }}" 
                       class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>{{ __('Habilitados') }}</span>
                    </a>
                </div>

                <!-- Filtros -->
                <form method="GET" action="{{ route('polygons.deleted') }}" class="mb-6">
                    <div class="flex flex-wrap gap-4 items-center">
                        <input type="text" 
                               name="search" 
                               class="form-input rounded-md bg-gray-200 border-gray-300 focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70" 
                               placeholder="Buscar por nombre, descripción o productor..." 
                               value="{{ $search ?? '' }}">
                        <button type="submit" class="px-4 py-2 bg-stone-600 hover:bg-stone-700 text-white rounded-lg transition-all">
                            Buscar
                        </button>
                       
                        @if(request('search'))
                            <a href="{{ route('polygons.deleted') }}" 
                               class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                                Limpiar
                            </a>
                        @endif
                    </div>
                </form>

                @if($polygons->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-stone-100/90 dark:bg-custom-gray">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">NOMBRE</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">PRODUCTOR</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">ÁREA (Ha)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">DESCRIPCIÓN</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">ESTADO</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody class="bg-stone-100/90 dark:bg-custom-gray divide-y divide-gray-200">
                                @foreach($polygons as $polygon)
                                    <tr id="polygon-row-{{ $polygon->id }}" class="hover:bg-gray-200/60 dark:hover:bg-gray-700/30 hover:shadow-lg hover:transition-all hover:duration-200">
                                        <td class="px-6 py-2 whitespace-nowrap text-gray-900 dark:text-gray-400">{{ $polygon->name }}</td>
                                        <td class="px-6 py-2 whitespace-nowrap text-gray-900 dark:text-gray-400">
                                            {{ $polygon->producer_name ?? 'Sin productor' }}
                                        </td>
                                        <td class="px-6 py-2 whitespace-nowrap text-gray-900 dark:text-gray-400">
                                            {{ $polygon->area_formatted }}
                                        </td>
                                        <td class="px-6 py-2 text-gray-900 dark:text-gray-400">
                                            {{ Str::limit($polygon->description, 50) ?? 'Sin descripción' }}
                                        </td>
                                        <td class="px-6 py-2 whitespace-nowrap text-gray-900 dark:text-gray-400">
                                            <span class="inline-block px-3 py-1 text-xs font-semibold bg-red-600 text-white rounded-full">
                                                Deshabilitado
                                            </span>
                                            <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                                {{ $polygon->deleted_at->format('d/m/Y H:i') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-2 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <button type="button" 
                                                        class="inline-flex items-center text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 transition-colors p-1 hover:bg-gray-500 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110" 
                                                        title="Ver detalles"
                                                        onclick="showPolygonDetails({{ $polygon->id }})">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                                                        <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/>
                                                        <circle cx="12" cy="12" r="3"/>
                                                    </svg>
                                                </button>

                                                <button type="button" 
                                                        class="inline-flex items-center text-green-600 hover:text-green-900 dark:text-green-500 dark:hover:text-green-300 transition-colors p-1 hover:bg-gray-500 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110" 
                                                        title="Restaurar"
                                                        onclick="handleRestorePolygon({{ $polygon->id }}, '{{ addslashes($polygon->name) }}')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                                                        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>
                                                    </svg>
                                                </button>
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
                        <p class="text-gray-600 dark:text-gray-400">No hay polígonos eliminados.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <x-modal name="view-polygon-details" maxWidth="2xl" :showClose="true">
        <div class="p-0 overflow-hidden">
            <div class="bg-[linear-gradient(135deg,_#3f2c1bdc_0%,_#30201b_100%)] px-6 py-4">
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

            <div class="p-6" id="polygon-details-content">
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

async function handleDeletePolygon(polygonId, polygonName) {
    const result = await showCustomConfirmation(
        false,
        `¿Estás seguro de que deseas eliminar el polígono <b>${polygonName}</b>?<br><br>
         <small>El polígono será movido a la lista de deshabilitados.</small>`
    );
    
    if (result.isConfirmed) {
        const data = await makePolygonRequest(`/polygons/${polygonId}`, 'DELETE');
        
        if (data.success) {
            const row = document.getElementById(`polygon-row-${polygonId}`);
            if (row) {
                row.remove();
            }
            
            showCustomAlert('success', 'Éxito', 'Polígono eliminado exitosamente.');

            const tableBody = document.querySelector('table tbody');
            if (tableBody && tableBody.children.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-8">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-gray-600 dark:text-gray-400">No se encontraron polígonos.</p>
                        </td>
                    </tr>
                `;
            }
        } else {
            showCustomAlert('error', 'Error', data.message);
        }
    }
}

async function handleRestorePolygon(polygonId, polygonName) {
    const result = await showCustomConfirmation(
        true,
        `¿Estás seguro de que deseas restaurar el polígono <b>${polygonName}</b>?`
    );
    
    if (result.isConfirmed) {
        const data = await makePolygonRequest(`/polygons/${polygonId}/restore`, 'POST');
        
        if (data.success) {
            const row = document.getElementById(`polygon-row-${polygonId}`);
            if (row) {
                row.remove();
            }
            
            showCustomAlert('success', 'Éxito', 'Polígono restaurado exitosamente.');

            const tableBody = document.querySelector('table tbody');
            if (tableBody && tableBody.children.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-8">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-gray-600 dark:text-gray-400">No hay polígonos eliminados.</p>
                            
                        </td>
                    </tr>
                `;
            }
        } else {
            showCustomAlert('error', 'Error', data.message);
        }
    }
}

async function handleTogglePolygonStatus(polygonId, polygonName, isCurrentlyActive) {
    const action = isCurrentlyActive ? 'desactivar' : 'activar';
    
    const result = await showCustomConfirmation(
        !isCurrentlyActive,
        `¿Estás seguro de que deseas ${action} el polígono <b>${polygonName}</b>?`
    );
    
    if (result.isConfirmed) {
        const data = await makePolygonRequest(`/polygons/${polygonId}/toggle-status`, 'POST');
        
        if (data.success) {
            updatePolygonStatusUI(polygonId, polygonName, data.is_active);
            showCustomAlert('success', 'Éxito', `Polígono ${action} exitosamente.`);
        } else {
            showCustomAlert('error', 'Error', data.message);
        }
    }
}

function updatePolygonStatusUI(polygonId, polygonName, isActive) {
    const row = document.getElementById(`polygon-row-${polygonId}`);
    if (!row) return;
    
    const statusCell = row.querySelector('td:nth-child(5)');
    const toggleButton = row.querySelector('button[onclick*="handleTogglePolygonStatus"]');
    
    if (statusCell) {
        if (isActive) {
            statusCell.innerHTML = '<span class="inline-block px-3 py-1 text-xs font-semibold bg-green-600 text-white rounded-full">Activo</span>';
        } else {
            statusCell.innerHTML = '<span class="inline-block px-3 py-1 text-xs font-semibold bg-yellow-500 text-white rounded-full">Inactivo</span>';
        }
    }
    
    if (toggleButton) {
        let newSvg;
        let newTitle;
        let newOnclick;
        
        if (isActive) {
            newSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-500 w-7 h-7">
                <circle cx="12" cy="12" r="10" class="fill-yellow-100"/>
                <line x1="15" y1="9" x2="9" y2="15" class="stroke-yellow-600"/>
                <line x1="9" y1="9" x2="15" y2="15" class="stroke-yellow-600"/>
            </svg>`;
            newTitle = 'Desactivar';
            newOnclick = `handleTogglePolygonStatus(${polygonId}, '${polygonName.replace(/'/g, "\\'")}', true)`;
        } else {
            newSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500 w-7 h-7">
                <circle cx="12" cy="12" r="10" class="fill-green-100"/>
                <path d="m8 12 2.5 2.5L16 9" class="stroke-green-600"/>
            </svg>`;
            newTitle = 'Activar';
            newOnclick = `handleTogglePolygonStatus(${polygonId}, '${polygonName.replace(/'/g, "\\'")}', false)`;
        }
        
        toggleButton.innerHTML = newSvg;
        toggleButton.setAttribute('title', newTitle);
        toggleButton.setAttribute('onclick', newOnclick);
    }
}
</script>
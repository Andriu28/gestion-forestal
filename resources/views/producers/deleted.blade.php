
<x-app-layout>
     <div class="mx-auto">
        <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden shadow-sm rounded-2xl shadow-soft p-4 md:p-6 lg:p-6 mb-6">
            <div class="text-gray-900 dark:text-gray-100">
                <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-200 mb-4 md:mb-4">
                    {{ __('Productores Deshabilitados') }}
                </h2>
                        
                    

                <div class="flex justify-end mb-6">
                    <a href="{{ route('producers.index') }}" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>{{ __('Habilitados') }}</span>
                    </a>
                </div>

               

                <!-- Filtros -->
                <form method="GET" action="{{ route('producers.deleted') }}" class="mb-6">
                    <div class="flex flex-wrap gap-4 items-center">
                        <input type="text" 
                               name="search" 
                               class="form-input rounded-md bg-gray-200 border-gray-300 focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70" 
                               placeholder="Buscar por nombre, apellido..." 
                               value="{{ $search ?? '' }}">
                        <button type="submit" class="px-4 py-2 bg-stone-600 hover:bg-stone-700 text-white rounded-lg transition-all">
                            Buscar
                        </button>
                       
                        @if(request('search'))
                            <a href="{{ route('producers.deleted') }}" 
                               class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                                Limpiar
                            </a>
                        @endif
                    </div>
                </form>

                @if($producers->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-stone-100/90 dark:bg-custom-gray">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">NOMBRE</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">APELLIDO</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">DESCRIPCIÓN</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">ESTADO</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody class="bg-stone-100/90 dark:bg-custom-gray divide-y divide-gray-200">
                                @foreach($producers as $producer)
                                    <tr class="hover:bg-gray-200/60 dark:hover:bg-gray-700/30 hover:shadow-lg hover:transition-all hover:duration-200">
                                        <td class="px-6 py-2 whitespace-nowrap text-gray-900 dark:text-gray-400">{{ $producer->name }}</td>
                                        <td class="px-6 py-2 whitespace-nowrap text-gray-900 dark:text-gray-400">{{ $producer->lastname ?? 'N/A' }}</td>
                                        <td class="px-6 py-2 text-gray-900 dark:text-gray-400">
                                            {{ Str::limit($producer->description, 50) ?? 'Sin descripción' }}
                                        </td>
                                        <td class="px-6 py-2 whitespace-nowrap text-gray-900 dark:text-gray-400">
                                            <span class="inline-block px-3 py-1 text-xs font-semibold bg-red-600 text-white rounded-full">
                                               
                                                Deshabilitado
                                            </span>
                                            <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                                {{ $producer->deleted_at->format('d/m/Y H:i') }}
                                            </div>
                                         
                                        </td>
                                        <td class="px-6 py-2 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                

                                                <!-- Botón Ver Detalles -->
                                                <button type="button" 
                                                        class="inline-flex items-center text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 transition-colors p-1 hover:bg-gray-500 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110" 
                                                        title="Ver detalles"
                                                        onclick="showProducerDetails({{ $producer->id }})">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                                                        <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/>
                                                        <circle cx="12" cy="12" r="3"/>
                                                    </svg>
                                                </button>

                                                <!-- Botón Restaurar -->
                                                <button type="button" 
                                                        class="inline-flex items-center text-green-600 hover:text-green-900 dark:text-green-500 dark:hover:text-green-300 transition-colors p-1 hover:bg-gray-500 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110" 
                                                        title="Restaurar"
                                                        onclick="handleRestoreProducer({{ $producer->id }}, '{{ addslashes($producer->name) }}')">
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
                        {{ $producers->links() }}
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-gray-600 dark:text-gray-400">No hay productores eliminados.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles del productor (mismo que en index) -->
    <x-modal name="view-producer-details" maxWidth="2xl" :showClose="true">
        <div class="p-0 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="bg-white/20 p-2 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white">
                                Detalles del Productor
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

            <div class="p-6" id="producer-details-content">
                <div class="flex flex-col items-center justify-center py-12">
                    <div class="relative">
                        <div class="animate-spin rounded-full h-16 w-16 border-4 border-blue-200"></div>
                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                            <div class="animate-ping h-8 w-8 rounded-full bg-blue-400"></div>
                        </div>
                    </div>
                    <p class="mt-4 text-gray-600 dark:text-gray-400 font-medium">Cargando información del productor...</p>
                </div>
            </div>
        </div>
    </x-modal>
</x-app-layout>

<script>
// JavaScript para la vista de eliminados
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

async function makeRequest(url, method = 'POST', data = null) {
    try {
        const options = {
            method: method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
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

// Función para restaurar productor desde vista de eliminados - ACTUALIZADA
async function handleRestoreProducer(producerId, producerName) {
const result = await showCustomConfirmation(
    true,
    `¿Estás seguro de que deseas restaurar al productor <b>${producerName}</b>?<br><br>
        <small>El productor será movido a la lista de productores activos.</small>`
);

if (result.isConfirmed) {
    const data = await makeRequest(`/producers/${producerId}/restore`, 'POST');
    
    if (data.success) {
        // Eliminar la fila de la tabla inmediatamente
        const row = document.querySelector(`tr:has(button[onclick*="handleRestoreProducer(${producerId}"])`);
        if (row) {
            row.remove();
        }
        
        showCustomAlert('success', 'Éxito', 'Productor habilitado exitosamente.');
        
        // Si no quedan productores, mostrar mensaje
        const tableBody = document.querySelector('table tbody');
        if (tableBody && tableBody.children.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-gray-600 dark:text-gray-400">No hay productores eliminados.</p>
                        <a href="{{ route('producers.index') }}" 
                            class="inline-flex items-center mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-2">
                                <path d="M19 12H5M12 19l-7-7 7-7"/>
                            </svg>
                            Volver a productores activos
                        </a>
                    </td>
                </tr>
            `;
        }
    } else {
        showCustomAlert('error', 'Error', data.message);
    }
}
}

// Función para mostrar detalles (reutilizada del index)
async function showProducerDetails(producerId) {
    try {
        const modalContent = document.getElementById('producer-details-content');
        modalContent.innerHTML = `
            <div class="flex justify-center items-center py-8">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>
        `;
        
        const event = new CustomEvent('open-modal', { 
            detail: 'view-producer-details' 
        });
        window.dispatchEvent(event);
        
        const response = await fetch(`/producers/${producerId}/details`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });
        
        if (!response.ok) throw new Error('Error al cargar datos');
        
        const data = await response.json();
        
        if (data.success) {
            modalContent.innerHTML = formatProducerDetails(data.producer);
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
                    <span class="text-red-700 dark:text-red-300">Error al cargar los detalles del productor</span>
                </div>
                <p class="text-sm text-red-600 dark:text-red-400 mt-2">${error.message}</p>
            </div>
        `;
    }
}

// Función para formatear detalles (reutilizada del index)
function formatProducerDetails(producer) {
    const formatDate = (dateString) => {
        if (!dateString) return 'No disponible';
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    let statusColor, statusBg, statusText;
    if (producer.deleted_at) {
        statusColor = 'text-red-800 dark:text-red-300';
        statusBg = 'bg-red-100 dark:bg-red-900/30';
        statusText = 'Eliminado';
    } else if (producer.is_active) {
        statusColor = 'text-green-800 dark:text-green-300';
        statusBg = 'bg-green-100 dark:bg-green-900/30';
        statusText = 'Activo';
    } else {
        statusColor = 'text-yellow-800 dark:text-yellow-300';
        statusBg = 'bg-yellow-100 dark:bg-yellow-900/30';
        statusText = 'Inactivo';
    }

    return `
        <div class="space-y-6">
            <!-- Información principal -->
            <div class="bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                            ${producer.name} ${producer.lastname ? producer.lastname : ''}
                        </h3>
                        <div class="flex items-center space-x-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${statusColor} ${statusBg} border ${statusText === 'Eliminado' ? 'border-red-200 dark:border-red-800' : 'border-gray-200 dark:border-gray-700'}">
                                <span class="w-2 h-2 rounded-full ${statusText === 'Eliminado' ? 'bg-red-500' : 'bg-gray-500'} mr-2"></span>
                                ${statusText}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Grid de información -->
                <div class="grid grid-cols-1 gap-4 mt-6">
                    <!-- Información básica -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center mb-2">
                            <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg mr-3">
                                <svg class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-700 dark:text-gray-300">Información Básica</h4>
                                <div class="mt-3 space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Nombre:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">${producer.name}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Apellido:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">${producer.lastname || 'N/A'}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                   
                </div>
            </div>

            ${producer.description ? `
            <!-- Descripción -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Descripción</h3>
                </div>
                <div class="prose prose-gray dark:prose-invert max-w-none">
                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">${producer.description}</p>
                </div>
            </div>
            ` : ''}

            <!-- Fechas del sistema -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información del Sistema</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Creado</span>
                        </div>
                        <span class="font-medium text-gray-900 dark:text-white">${formatDate(producer.created_at)}</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Última actualización</span>
                        </div>
                        <span class="font-medium text-gray-900 dark:text-white">${formatDate(producer.updated_at)}</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-500 dark:text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span class="text-red-700 dark:text-red-300">Eliminado</span>
                        </div>
                        <span class="font-medium text-red-900 dark:text-red-200">${formatDate(producer.deleted_at)}</span>
                    </div>
                </div>
            </div>
        </div>
    `;
}
</script>

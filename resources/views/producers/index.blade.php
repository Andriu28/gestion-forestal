<x-app-layout>
    <div class=" mx-auto">
        <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden shadow-sm rounded-2xl shadow-soft p-4 md:p-6 lg:p-6 mb-6">
            <div class="text-gray-900 dark:text-gray-100">
                <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-200 mb-2 md:mb-2">
                    {{ __('Gestión de productores') }}
                </h2>
                
                <div class="flex justify-between items-center mb-4">
                    <div class="space-x-4">
                        
                    </div>
                    
                    <div class="flex space-x-4">
                        
                        <!-- Botón para crear nuevo productor -->
                        <a href="{{ route('producers.create') }}" 
                        title="Crear nuevo productor" 
                        class="group px-2.5 py-1.5 bg-stone-200/80 hover:bg-green-600/70 dark:hover:bg-green-500/60 text-stone-700 hover:text-white border border-stone-300/70 hover:border-transparent dark:bg-gray-700/40 dark:text-gray-300 dark:hover:text-white dark:border-gray-600/50 rounded-md flex items-center hover:shadow-md hover:-translate-y-0.5 overflow-hidden">
                        
                            <!-- Contenedor del ícono - se contrae en hover -->
                            <span class="flex items-center justify-center w-6 h-6 transition-all duration-300 group-hover:w-6 group-hover:h-6 flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2002/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-emerald-700/70 group-hover:text-white dark:text-emerald-500/70">
                                    <circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/>
                                </svg>
                            </span>
                            
                            <!-- Texto - oculto en estado normal, visible en hover -->
                            <span class="text-base font-medium transition-all duration-300 w-0 opacity-0 group-hover:w-10 group-hover:opacity-100 group-hover:ml-1 whitespace-nowrap overflow-hidden text-inherit">
                                Crear
                            </span>
                        </a>

                        <!-- Botón para ver eliminados -->
                        <a href="{{ route('producers.deleted') }}" 
                        title="Ver productores deshabilitados" 
                        class="group px-2.5 py-1.5 bg-stone-200/80 hover:bg-red-600/70 dark:hover:bg-red-500/60 text-stone-700 hover:text-white border border-stone-300/70 hover:border-transparent dark:bg-gray-700/40 dark:text-gray-300 dark:hover:text-white dark:border-gray-600/50 rounded-md flex items-center hover:shadow-md hover:-translate-y-0.5 overflow-hidden">
                            
                            <!-- Contenedor del ícono - se contrae en hover -->
                            <span class="flex items-center justify-center w-6 h-6 transition-all duration-300 group-hover:w-6 group-hover:h-6 flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2002/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-red-700/60 group-hover:text-white dark:text-red-500/60">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="17" x2="22" y1="8" y2="13"/><line x1="22" x2="17" y1="8" y2="13"/>
                                </svg>
                            </span>
                            
                            <!-- Texto - oculto en estado normal, visible en hover -->
                            <span class="text-base font-medium transition-all duration-300 w-0 opacity-0 group-hover:w-28 group-hover:opacity-100 group-hover:ml-1 whitespace-nowrap overflow-hidden text-inherit">
                                Deshabilitados
                            </span>
                        </a>

                        <!-- Botón para generar PDF -->
                        <a href="{{ route('producers.generate.pdf', request()->query()) }}" 
                        title="Descargar PDF" 
                        class="group px-2.5 py-1.5 bg-stone-200/80 hover:bg-red-600/80 dark:hover:bg-red-500/70 text-stone-700 hover:text-white border border-stone-300/70 hover:border-transparent dark:bg-gray-700/40 dark:text-gray-300 dark:hover:text-white dark:border-gray-600/50 rounded-md flex items-center hover:shadow-md hover:-translate-y-0.5 overflow-hidden">
                        
                            <!-- Contenedor del ícono - se contrae en hover -->
                            <span class="flex items-center justify-center w-6 h-6 transition-all duration-300 group-hover:w-6 group-hover:h-6 flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2002/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-icon lucide-map w-6 h-6 text-red-700/70 group-hover:text-white dark:text-red-400/70">
                            
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

                <!-- Filtros -->
                <form method="GET" action="{{ route('producers.index') }}" class="mb-6">
                    <div class="flex flex-wrap gap-4">
                        <input type="text" name="search" class="form-input rounded-md bg-gray-200 border-gray-300 focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70" placeholder="Buscar por nombre, apellido o descripción..." value="{{ $search ?? '' }}">
                        <select name="status" class="form-select rounded-md bg-gray-200 border-gray-300 focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70">
                            <option value="all" {{ ($status ?? '') == 'all' ? 'selected' : '' }}>Todos</option>
                            <option value="active" {{ ($status ?? '') == 'active' ? 'selected' : '' }}>Activos</option>
                            <option value="inactive" {{ ($status ?? '') == 'inactive' ? 'selected' : '' }}>Inactivos</option>
                            <!-- Opción "deleted" eliminada -->
                        </select>

                         <button type="submit" class="px-4 py-2 bg-gray-600/90 hover:bg-gray-600 text-white rounded-lg transition-all flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2002/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-funnel-icon lucide-funnel w-5 h-5">
                                <path d="M10 20a1 1 0 0 0 .553.895l2 1A1 1 0 0 0 14 21v-7a2 2 0 0 1 .517-1.341L21.74 4.67A1 1 0 0 0 21 3H3a1 1 0 0 0-.742 1.67l7.225 7.989A2 2 0 0 1 10 14z"/>
                            </svg>
                        </button>
                   
                        @if(request('search') || (request('status') != 'all' && request()->has('status')))
                            <a href="{{ route('producers.index') }}"class="px-4 py-2 bg-gray-400/90 hover:bg-gray-300 text-white rounded-lg transition-all flex items-center space-x-2"> 
                                <svg xmlns="http://www.w3.org/2002/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-brush-cleaning-icon lucide-brush-cleaning w-5 h-5">
                                    <path d="m16 22-1-4"/><path d="M19 14a1 1 0 0 0 1-1v-1a2 2 0 0 0-2-2h-3a1 1 0 0 1-1-1V4a2 2 0 0 0-4 0v5a1 1 0 0 1-1 1H6a2 2 0 0 0-2 2v1a1 1 0 0 0 1 1"/><path d="M19 14H5l-1.973 6.767A1 1 0 0 0 4 22h16a1 1 0 0 0 .973-1.233z"/><path d="m8 22 1-4"/>
                                </svg>
                            </a>
                        @endif
                     </div>
                </form>

                @if($producers->count() > 0)
                    <div class="overflow-x-auto">
                        <table id="producers-table" class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-stone-100/90 dark:bg-custom-gray">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">Nombre</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">Apellido</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">Descripción</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="bg-stone-100/90 dark:bg-custom-gray divide-y divide-gray-200">
                                @foreach($producers as $producer)
                                    <tr id="producer-row-{{ $producer->id }}" class="hover:bg-gray-200/60 dark:hover:bg-gray-700/30 hover:shadow-lg hover:transition-all hover:duration-200">
                                        <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-2 whitespace-nowrap text-gray-900 dark:text-gray-400">{{ $producer->name }}</td>
                                        <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-2 whitespace-nowrap text-gray-900 dark:text-gray-400">{{ $producer->lastname ?? 'N/A' }}</td>
                                        <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-2 text-gray-900 dark:text-gray-400">
                                            {{ Str::limit($producer->description, 50) ?? 'Sin descripción' }}
                                        </td>
                                        <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-2 whitespace-nowrap">
                                            {{-- ESTILO DE ESTADO IGUAL QUE POLÍGONOS --}}
                                            <span class="inline-block px-3 py-1 text-xs font-semibold {{ $producer->is_active ? 'bg-green-500/70 dark:bg-green-400/50 text-white' : 'bg-yellow-400/60 dark:bg-yellow-400/70 text-white' }} rounded-full">
                                                {{ $producer->is_active ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-2 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <!-- Botón Ver Detalles -->
                                                <button type="button" 
                                                        class="inline-flex items-center text-blue-700/50 hover:text-blue-600 dark:text-blue-400/60 dark:hover:text-blue-300 p-1 hover:bg-gray-600 dark:hover:bg-gray-500/40 rounded-xl hover:bg-opacity-10 hover:scale-110" 
                                                        title="Ver detalles"
                                                        onclick="showProducerDetails({{ $producer->id }})">
                                                    <svg xmlns="http://www.w3.org/2002/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                                                        <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/>
                                                        <circle cx="12" cy="12" r="3"/>
                                                    </svg>
                                                </button>

                                                <!-- Botón Editar - IGUAL QUE POLÍGONOS -->
                                                <a href="{{ route('producers.edit', $producer) }}" 
                                                class="inline-flex items-center text-indigo-800/50 hover:text-indigo-600 dark:text-indigo-400/60 dark:hover:text-indigo-300 p-1 hover:bg-gray-600 dark:hover:bg-gray-500/40 rounded-xl hover:bg-opacity-10 hover:scale-110"
                                                title="Editar">
                                                    <svg xmlns="http://www.w3.org/2002/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-line-icon w-7 h-7 lucide-pencil-line">
                                                        <path d="M13 21h8"/><path d="m15 5 4 4"/><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>
                                                    </svg>
                                                </a>
                                        
                                                <!-- Botón Cambiar Estado - IGUAL QUE POLÍGONOS -->
                                                <button type="button" 
                                                        class="inline-flex items-center transition-colors p-1 hover:bg-gray-600 dark:hover:bg-gray-500/40 rounded-xl hover:bg-opacity-10 hover:scale-110" 
                                                        title="{{ $producer->is_active ? 'Desactivar' : 'Activar' }}"
                                                        onclick="handleToggleStatus({{ $producer->id }}, '{{ addslashes($producer->name) }}', {{ $producer->is_active ? 'true' : 'false' }})">
                                                    @if($producer->is_active)
                                                        {{-- ICONO DE DESACTIVAR - IGUAL QUE POLÍGONOS --}}
                                                        <svg xmlns="http://www.w3.org/2002/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-700/40 hover:text-yellow-500/80 dark:text-yellow-400/60 dark:hover:text-yellow-300 w-7 h-7">
                                                            <circle cx="12" cy="12" r="10" class="fill-none"/>
                                                            <line x1="15" y1="9" x2="9" y2="15"/>
                                                            <line x1="9" y1="9" x2="15" y2="15"/>
                                                        </svg>
                                                    @else
                                                        {{-- ICONO DE ACTIVAR - IGUAL QUE POLÍGONOS --}}
                                                        <svg xmlns="http://www.w3.org/2002/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-700/40 hover:text-green-500/80 dark:text-green-400/60 dark:hover:text-green-300 w-7 h-7">
                                                            <circle cx="12" cy="12" r="10" class="fill-none"/>
                                                            <path d="m8 12 2.5 2.5L16 9"/>
                                                        </svg>
                                                    @endif
                                                </button>

                                                <!-- Botón Deshabilitar - CON COLORES IGUAL QUE EL BOTÓN ELIMINAR DE POLÍGONOS -->
                                                <button type="button" 
                                                        class="inline-flex items-center text-red-700/30 hover:text-red-600 dark:text-red-500/60 dark:hover:text-red-300 p-1 hover:bg-gray-600 dark:hover:bg-gray-500/40 rounded-xl hover:bg-opacity-10 hover:scale-110" 
                                                        title="Deshabilitar"
                                                        onclick="handleDisableProducer({{ $producer->id }}, '{{ addslashes($producer->name) }}')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-x-icon w-7 h-7 lucide-user-x">
                                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="17" x2="22" y1="8" y2="13"/><line x1="22" x2="17" y1="8" y2="13"/>
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
                        <svg xmlns="http://www.w3.org/2002/svg" class="h-20 w-20 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-gray-600 dark:text-gray-400">No se encontraron productores.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles del productor -->
    <x-modal name="view-producer-details" maxWidth="2xl" :showClose="true">
        <div class="p-0 overflow-hidden">
            <!-- Encabezado del modal -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="bg-gray-100/20 dark:bg-custom-gray p-2 rounded-lg">
                            <svg xmlns="http://www.w3.org/2002/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

            <!-- Contenido del modal -->
            <div class="p-6" id="producer-details-content">
                <!-- Spinner de carga -->
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
// JavaScript para manejar las acciones asíncronas
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

// Función para cambiar estado (activo/inactivo)
async function handleToggleStatus(producerId, producerName, isCurrentlyActive) {
    const action = isCurrentlyActive ? 'desactivar' : 'activar';
    
    const result = await showCustomConfirmation(
        !isCurrentlyActive,
        `¿Estás seguro de que deseas ${action} al productor <b>${producerName}</b>?`
    );
    
    if (result.isConfirmed) {
        const data = await makeRequest(`/producers/${producerId}/toggle-status`, 'POST');
    
        if (data.success) {
            updateProducerStatusUI(producerId, producerName, data.is_active);
            showCustomAlert('success', 'Éxito', `Productor ${action} exitosamente.`);
        } else {
            showCustomAlert('error', 'Error', data.message);
        }
    }
}

// Función para actualizar la UI del estado - ACTUALIZADA CON ESTILOS DE POLÍGONOS
function updateProducerStatusUI(producerId, producerName, isActive) {
    const row = document.getElementById(`producer-row-${producerId}`);
    if (!row) return;
    
    // Encontrar la celda de estado (4ta columna)
    const statusCell = row.querySelector('td:nth-child(4)');
    const toggleButton = row.querySelector('button[onclick*="handleToggleStatus"]');
    
    // Actualizar badge de estado - ESTILO IGUAL QUE POLÍGONOS
    if (statusCell) {
        if (isActive) {
            statusCell.innerHTML = '<span class="inline-block px-3 py-1 text-xs font-semibold bg-green-500/70 dark:bg-green-400/50 text-white rounded-full">Activo</span>';
        } else {
            statusCell.innerHTML = '<span class="inline-block px-3 py-1 text-xs font-semibold bg-yellow-400/60 dark:bg-yellow-400/70 text-white rounded-full">Inactivo</span>';
        }
    }
    
    // Actualizar botón de toggle - ESTILO IGUAL QUE POLÍGONOS
    if (toggleButton) {
        const escapedName = producerName.replace(/'/g, "\\'");
        
        if (isActive) {
            // Cambiar a icono de desactivar
            toggleButton.innerHTML = `
                <svg xmlns="http://www.w3.org/2002/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-700/40 hover:text-yellow-500/80 dark:text-yellow-400/60 dark:hover:text-yellow-300 w-7 h-7">
                    <circle cx="12" cy="12" r="10" class="fill-none"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            `;
            toggleButton.setAttribute('title', 'Desactivar');
            toggleButton.setAttribute('onclick', `handleToggleStatus(${producerId}, '${escapedName}', true)`);
        } else {
            // Cambiar a icono de activar
            toggleButton.innerHTML = `
                <svg xmlns="http://www.w3.org/2002/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-700/40 hover:text-green-500/80 dark:text-green-400/60 dark:hover:text-green-300 w-7 h-7">
                    <circle cx="12" cy="12" r="10" class="fill-none"/>
                    <path d="m8 12 2.5 2.5L16 9"/>
                </svg>
            `;
            toggleButton.setAttribute('title', 'Activar');
            toggleButton.setAttribute('onclick', `handleToggleStatus(${producerId}, '${escapedName}', false)`);
        }
    }
}

// Función para deshabilitar productor (AJAX) - CON ESTILOS DE POLÍGONOS
async function handleDisableProducer(producerId, producerName) {
    const result = await showCustomConfirmation(
        false,
        `¿Estás seguro de que deseas deshabilitar al productor <b>${producerName}</b>?<br><br>
         <small>El productor será movido a la lista de deshabilitados.</small>`
    );
    
    if (result.isConfirmed) {
        const data = await makeRequest(`/producers/${producerId}`, 'DELETE');
        
        if (data.success) {
            // Eliminar la fila de la tabla inmediatamente
            const row = document.getElementById(`producer-row-${producerId}`);
            if (row) {
                row.remove();
            }
            
            showCustomAlert('success', 'Éxito', 'Productor deshabilitado exitosamente.');
            
            // Si no quedan productores, mostrar mensaje
            const tableBody = document.querySelector('#producers-table tbody');
            if (tableBody && tableBody.children.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center py-8">
                            <svg xmlns="http://www.w3.org/2002/svg" class="h-20 w-20 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-gray-600 dark:text-gray-400">No se encontraron productores.</p>
                        </td>
                    </tr>
                `;
            }
        } else {
            showCustomAlert('error', 'Error', data.message);
        }
    }
}

// Función para mostrar detalles del productor en modal (igual que antes)
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

// Función para formatear los detalles del productor (igual que antes)
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
    if (producer.is_active) {
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
            <!-- Tarjeta de información principal -->
            <div class="bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                            ${producer.name} ${producer.lastname ? producer.lastname : ''}
                        </h3>
                        <div class="flex items-center space-x-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${statusColor} ${statusBg} border ${statusText === 'Activo' ? 'border-green-200 dark:border-green-800' : 'border-yellow-200 dark:border-yellow-800'}">
                                <span class="w-2 h-2 rounded-full ${statusText === 'Activo' ? 'bg-green-500' : 'bg-yellow-500'} mr-2"></span>
                                ${statusText}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Grid de información -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
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

                    <!-- Estadísticas -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center mb-2">
                            <div class="p-2 bg-green-50 dark:bg-green-900/20 rounded-lg mr-3">
                                <svg class="w-7 h-7 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-700 dark:text-gray-300">Polígonos</h4>
                                <div class="text-center mt-3">
                                    <div class="text-3xl font-bold text-gray-900 dark:text-white">${producer.polygons_count || 0}</div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Total asignados</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Descripción -->
            ${producer.description ? `
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

            <!-- Información adicional -->
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Actualizado</span>
                        </div>
                        <span class="font-medium text-gray-900 dark:text-white">${formatDate(producer.updated_at)}</span>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex flex-wrap gap-3 justify-between items-center">
                    <div class="space-x-2">
                        <a href="/producers/${producer.id}/edit" 
                           class="inline-flex items-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
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
</script>
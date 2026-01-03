<x-app-layout>
    <div class=" mx-auto">
        <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden shadow-sm rounded-2xl shadow-soft p-4 md:p-6 lg:p-6 mb-6">
            <div class="text-gray-900 dark:text-gray-100">
                <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-200 mb-2 md:mb-2">
                    {{ __('Gestión de productores') }}
                </h2>
                <div class="flex justify-end mb-4 space-x-4">
                    <a href="{{ route('producers.create') }}" class="px-4 py-2 bg-lime-600/90 text-white rounded-md hover:bg-lime-600 flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                            <circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/>
                        </svg>
                        <span>{{ __('Nuevo productor') }}</span>
                    </a>
                </div>

                <!-- Filtros -->
                <form method="GET" action="{{ route('producers.index') }}" class="mb-6">
                    <div class="flex flex-wrap gap-4">
                        <input type="text" name="search" class="form-input rounded-md bg-gray-200 border-gray-300" placeholder="Buscar por nombre, apellido o descripción..." value="{{ $search ?? '' }}">
                        <select name="status" class="form-select rounded-md bg-gray-200 border-gray-300 ">
                            <option value="all" {{ ($status ?? '') == 'all' ? 'selected' : '' }}>Todos</option>
                            <option value="active" {{ ($status ?? '') == 'active' ? 'selected' : '' }}>Activos</option>
                            <option value="inactive" {{ ($status ?? '') == 'inactive' ? 'selected' : '' }}>Inactivos</option>
                            <option value="deleted" {{ ($status ?? '') == 'deleted' ? 'selected' : '' }}>Eliminados</option>
                        </select>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Filtrar</button>
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
                                            @if($producer->deleted_at)
                                                <span class="inline-block px-3 py-1 text-xs font-semibold bg-red-600 text-white rounded-full">Eliminado</span>
                                            @else
                                                <span class="inline-block px-3 py-1 text-xs font-semibold {{ $producer->is_active ? 'bg-green-600 text-white' : 'bg-yellow-500 text-white' }} rounded-full">
                                                    {{ $producer->is_active ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-2 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                @if(!$producer->deleted_at)
                                                    <a href="{{ route('producers.show', $producer) }}" class="inline-flex items-center text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 transition-colors p-1 hover:bg-gray-500 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110" title="Ver">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye-icon w-7 h-7 lucide-eye">
                                                            <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/>
                                                        </svg>
                                                    </a>

                                                    <!-- Botón Editar -->
                                                    <a href="{{ route('producers.edit', $producer) }}" 
                                                    class="inline-flex items-center text-indigo-600 hover:text-indigo-900 dark:text-indigo-500 dark:hover:text-indigo-300 transition-colors p-1 hover:bg-gray-500 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110"
                                                    title="Editar">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-line-icon w-7 h-7 lucide-pencil-line">
                                                            <path d="M13 21h8"/><path d="m15 5 4 4"/><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>
                                                        </svg>
                                                    </a>
                                            
                                                    <!-- Botón Cambiar Estado (activo/inactivo) - AJAX -->
                                                    <form action="{{ route('producers.toggle-status', $producer) }}" method="POST" class="inline toggle-status-form">
                                                        @csrf
                                                        <button type="button" class="inline-flex items-center transition-colors p-1 hover:bg-gray-500 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110" 
                                                                title="{{ $producer->is_active ? 'Desactivar' : 'Activar' }}"
                                                                onclick="handleToggleStatus({{ $producer->id }}, '{{ $producer->name }}', {{ $producer->is_active ? 'true' : 'false' }})">
                                                            @if($producer->is_active)
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
                                                    </form>

                                                    <!-- Botón Deshabilitar (soft delete) - AJAX -->
                                                    <button type="button" 
                                                            class="inline-flex items-center text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-300 transition-colors p-1 hover:bg-gray-500 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110" 
                                                            title="Deshabilitar"
                                                            onclick="handleDisableProducer({{ $producer->id }}, '{{ addslashes($producer->name) }}')">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-x-icon w-7 h-7 lucide-user-x">
                                                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="17" x2="22" y1="8" y2="13"/><line x1="22" x2="17" y1="8" y2="13"/>
                                                        </svg>
                                                    </button>
                                                @else
                                                    <!-- Para productores eliminados (soft deleted) -->
                                                    <button type="button" 
                                                            class="inline-flex items-center text-green-600 hover:text-green-900 dark:text-green-500 dark:hover:text-green-300 transition-colors p-1 hover:bg-gray-500 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110" 
                                                            title="Restaurar"
                                                            onclick="handleRestoreProducer({{ $producer->id }}, '{{ addslashes($producer->name) }}')">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-rotate-ccw-icon w-7 h-7 lucide-rotate-ccw">
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
                        {{ $producers->links() }}
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-gray-600 dark:text-gray-400">No se encontraron productores.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

<script>
// JavaScript para manejar las acciones asíncronas usando TUS funciones globales
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Función auxiliar para hacer peticiones fetch
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
        !isCurrentlyActive, // isEnable: true para activar, false para desactivar
        `¿Estás seguro de que deseas ${action} al productor <b>${producerName}</b>?`
    );
    
    if (result.isConfirmed) {
        // Mostrar loading
        Swal.fire({
            title: `${action.charAt(0).toUpperCase() + action.slice(1)}...`,
            text: 'Por favor espere',
            allowOutsideClick: false,
            showConfirmButton: false,
            customClass: {
                popup: 'rounded-xl shadow-lg bg-stone-100/95 dark:bg-custom-gray border border-gray-200 dark:border-gray-700'
            },
            didOpen: () => Swal.showLoading()
        });
        
        const data = await makeRequest(`/producers/${producerId}/toggle-status`, 'POST');
        Swal.close();
        
        if (data.success) {
            // Actualizar interfaz
            updateProducerStatusUI(producerId, producerName, data.is_active);
            showCustomAlert('success', 'Éxito', `Productor ${action} exitosamente.`);
        } else {
            showCustomAlert('error', 'Error', data.message);
        }
    }
}

// Función para actualizar la UI del estado
function updateProducerStatusUI(producerId, producerName, isActive) {
    const row = document.getElementById(`producer-row-${producerId}`);
    if (!row) return;
    
    const statusBadge = document.getElementById(`status-badge-${producerId}`);
    const toggleButton = row.querySelector('button[onclick*="handleToggleStatus"]');
    
    if (statusBadge) {
        if (isActive) {
            statusBadge.innerHTML = 'Activo';
            statusBadge.className = 'inline-block px-3 py-1 text-xs font-semibold bg-green-600 text-white rounded-full';
            if (toggleButton) {
                toggleButton.innerHTML = `/* Icono desactivar */`;
                toggleButton.setAttribute('onclick', `handleToggleStatus(${producerId}, '${producerName.replace(/'/g, "\\'")}', true)`);
                toggleButton.setAttribute('title', 'Desactivar');
            }
        } else {
            statusBadge.innerHTML = 'Inactivo';
            statusBadge.className = 'inline-block px-3 py-1 text-xs font-semibold bg-yellow-500 text-white rounded-full';
            if (toggleButton) {
                toggleButton.innerHTML = `/* Icono activar */`;
                toggleButton.setAttribute('onclick', `handleToggleStatus(${producerId}, '${producerName.replace(/'/g, "\\'")}', false)`);
                toggleButton.setAttribute('title', 'Activar');
            }
        }
    }
}

// Función para deshabilitar productor
async function handleDisableProducer(producerId, producerName) {
    const result = await showCustomConfirmation(
        false,
        `¿Estás seguro de que deseas deshabilitar al productor <b>${producerName}</b>?<br><br>
         <small>El productor será movido a la lista de deshabilitados.</small>`
    );
    
    if (result.isConfirmed) {
        Swal.fire({
            title: 'Deshabilitando...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            showConfirmButton: false,
            customClass: {
                popup: 'rounded-xl shadow-lg bg-stone-100/95 dark:bg-custom-gray border border-gray-200 dark:border-gray-700'
            },
            didOpen: () => Swal.showLoading()
        });
        
        const data = await makeRequest(`/producers/${producerId}`, 'DELETE');
        Swal.close();
        
        if (data.success) {
            removeProducerRow(producerId);
            showCustomAlert('success', 'Éxito', 'Productor deshabilitado exitosamente.');
        } else {
            showCustomAlert('error', 'Error', data.message);
        }
    }
}

// Función para restaurar productor
async function handleRestoreProducer(producerId, producerName) {
    const result = await showCustomConfirmation(
        true,
        `¿Estás seguro de que deseas restaurar al productor <b>${producerName}</b>?`
    );
    
    if (result.isConfirmed) {
        Swal.fire({
            title: 'Restaurando...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            showConfirmButton: false,
            customClass: {
                popup: 'rounded-xl shadow-lg bg-stone-100/95 dark:bg-custom-gray border border-gray-200 dark:border-gray-700'
            },
            didOpen: () => Swal.showLoading()
        });
        
        const data = await makeRequest(`/producers/${producerId}/restore`, 'POST');
        Swal.close();
        
        if (data.success) {
            removeProducerRow(producerId);
            showCustomAlert('success', 'Éxito', 'Productor habilitado exitosamente.');
        } else {
            showCustomAlert('error', 'Error', data.message);
        }
    }
}

// Función para remover fila con animación
function removeProducerRow(producerId) {
    const row = document.getElementById(`producer-row-${producerId}`);
    if (row) {
        row.style.transition = 'all 0.3s ease';
        row.style.opacity = '0';
        row.style.transform = 'translateX(-100px)';
        setTimeout(() => {
            row.remove();
            checkEmptyTable();
        }, 300);
    }
}

// Función para verificar si la tabla está vacía
function checkEmptyTable() {
    const table = document.querySelector('#producers-table tbody');
    if (table && table.children.length === 0) {
        table.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-gray-600 dark:text-gray-400">No se encontraron productores.</p>
                </td>
            </tr>
        `;
    }
}
</script>

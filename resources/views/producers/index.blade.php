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
                                            
                                                    <form action="{{ route('producers.toggle-status', $producer) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center transition-colors p-1 hover:bg-gray-500 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110" 
                                                                title="Cambiar estado">
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

                                                    <form action="{{ route('producers.destroy', $producer) }}" method="POST" class="inline sweet-confirm-form" data-action="deshabilitar">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-300 transition-colors p-1 hover:bg-gray-500 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110" title="Eliminar">
                                                             <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-x-icon w-7 h-7 lucide-user-x">
                                                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="17" x2="22" y1="8" y2="13"/><line x1="22" x2="17" y1="8" y2="13"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('producers.restore', $producer->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center text-green-600 hover:text-green-900 dark:text-green-500 dark:hover:text-green-300 transition-colors p-1 hover:bg-gray-500 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110" title="Restaurar">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-rotate-ccw-icon w-7 h-7 lucide-rotate-ccw">
                                                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>
                                                            </svg>
                                                        </button>
                                                    </form>
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
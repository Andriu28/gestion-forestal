<x-app-layout>
    <div class="mx-auto">
        <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden shadow-sm rounded-2xl shadow-soft p-4 md:p-6 lg:p-6 mb-6">
            <div class="text-gray-900 dark:text-gray-100">
                <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-200 mb-2 md:mb-2">
                    {{ __('Gestión de Usuarios') }}
                </h2>

                <!-- Botones de Acción -->
                <div class="flex justify-end mb-4 space-x-4">
                    <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-lime-600/90 text-white rounded-md hover:bg-lime-600 flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                            <circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/>
                        </svg>
                        <span>{{ __('Nuevo') }}</span>
                    </a>
                    <a href="{{ route('admin.users.disabled') }}" class="px-4 py-2 bg-orange-600/90 text-white rounded-md hover:bg-orange-600 flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                            <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                        </svg>
                        <span>{{ __('Deshabilitados') }}</span>
                    </a>
                </div>

                <!-- Filtros -->
                <form method="GET" action="{{ route('admin.users.index') }}" class="mb-6">
                    <div class="flex flex-wrap gap-4">
                        <input type="text" name="search" class="form-input rounded-md bg-gray-200 border-gray-300 focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70" 
                               placeholder="Buscar por nombre o email..." value="{{ $search ?? '' }}">
                        
                        <select name="role" class="form-select rounded-md bg-gray-200 border-gray-300 focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70">
                            <option value="all" {{ ($role ?? '') == 'all' ? 'selected' : '' }}>Todos los roles</option>
                            <option value="administrador" {{ ($role ?? '') == 'administrador' ? 'selected' : '' }}>Administradores</option>
                            <option value="basico" {{ ($role ?? '') == 'basico' ? 'selected' : '' }}>Básicos</option>
                        </select>
                        
                        <button type="submit" class="px-4 py-2 bg-stone-600 hover:bg-stone-700 text-white rounded-lg transition-all">Filtrar</button>
                        
                        @if(request('search') || request('role') || (request('status') != 'active' && request()->has('status')))
                            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">Limpiar</a>
                        @endif
                    </div>
                </form>

                <!-- Tabla de Usuarios -->
                @if($users->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-stone-100/90 dark:bg-custom-gray">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">#</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">Usuario</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">Rol</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-stone-100/90 dark:bg-custom-gray divide-y divide-gray-200">
                                @foreach($users as $user)
                                    <tr id="user-row-{{ $user->id }}" class="hover:bg-gray-200/60 dark:hover:bg-gray-700/30 hover:shadow-lg hover:transition-all hover:duration-200">
                                        <!-- Número -->
                                        <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-4 whitespace-nowrap text-center text-gray-900 dark:text-gray-400">
                                            <div class="text-sm font-medium">{{ $loop->iteration }}</div>
                                        </td>
                                        
                                        <!-- Usuario -->
                                        <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold mr-3">
                                                    {{ substr($user->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-400">
                                                        {{ $user->name }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <!-- Email -->
                                        <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-400">
                                            <div class="text-sm">{{ $user->email }}</div>
                                        </td>
                                        
                                        <!-- Rol -->
                                        <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-4 whitespace-nowrap">
                                            <form action="{{ route('admin.users.update-role', $user) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <select name="role" 
                                                        data-original-role="{{ $user->role }}"
                                                        onchange="handleRoleChange(
                                                            this, 
                                                            {{ $user->id }}, 
                                                            '{{ $user->name }}', 
                                                            {{ Auth::id() === $user->id ? 'true' : 'false' }}
                                                        )"
                                                        class="block w-full rounded-md bg-gray-200 dark:bg-gray-700 border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:focus:ring-indigo-600 text-sm">
                                                    <option value="basico" @if ($user->role === 'basico') selected @endif>Básico</option>
                                                    <option value="administrador" @if ($user->role === 'administrador') selected @endif>Administrador</option>
                                                </select>
                                            </form>
                                        </td>
                                        
                                        <!-- Estado -->
                                        <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-4 whitespace-nowrap">
                                            @if($user->trashed())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                    </svg>
                                                    Deshabilitado
                                                </span>
                                            @else
                                                <span class="inline-block px-3 py-1 text-xs font-semibold bg-green-600 text-white rounded-full">
                                                    Habilitado
                                                </span>
                                            @endif
                                        </td>
                                        
                                        <!-- Acciones -->
                                        <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('admin.users.edit', $user) }}" 
                                                   class="inline-flex items-center text-indigo-600 hover:text-indigo-900 dark:text-indigo-500 dark:hover:text-indigo-300 transition-colors p-1 hover:bg-gray-600 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110"
                                                   title="Editar">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                                                        <path d="M13 21h8"/><path d="m15 5 4 4"/><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>
                                                    </svg>
                                                </a>
                                                
                                                @if (Auth::id() !== $user->id && !$user->trashed())
                                                    <button onclick="handleUserDisable({{ $user->id }}, '{{ $user->name }}')" 
                                                    class="inline-flex items-center text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-300 transition-colors p-1 hover:bg-gray-600 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110"
                                                    title="Deshabilitar">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                                                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="17" x2="22" y1="8" y2="13"/><line x1="22" x2="17" y1="8" y2="13"/>
                                                        </svg>
                                                    </button>
                                                @elseif($user->trashed())
                                                    <button onclick="handleUserEnable({{ $user->id }}, '{{ $user->name }}')" 
                                                    class="inline-flex items-center text-green-600 hover:text-green-900 dark:text-green-500 dark:hover:text-green-300 transition-colors p-1 hover:bg-gray-600 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110"
                                                    title="Habilitar">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                                                            <path d="m16 11 2 2 4-4"/><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                                                        </svg>
                                                    </button>
                                                @else
                                                    <span class="inline-flex items-center text-gray-400 px-2 py-1 text-sm italic">
                                                        Usuario actual
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación -->
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-gray-600 dark:text-gray-400">No se encontraron usuarios.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

@push('styles')
<style>
    /* Estilos para la paginación (igual que audit_log) */
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
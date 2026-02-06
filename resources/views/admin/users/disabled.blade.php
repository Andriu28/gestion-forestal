<x-app-layout>
    <div class="mx-auto">
        <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden shadow-sm rounded-2xl shadow-soft p-4 md:p-6 lg:p-6 mb-6">
            <div class="text-gray-900 dark:text-gray-100">
                <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-200 mb-4 md:mb-4">
                    {{ __('Usuarios Deshabilitados') }}
                </h2>

                <!-- Botón de acción -->
                <div class="flex justify-end mb-6">
                    <a href="{{ route('admin.users.index') }}" 
                       class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>{{ __('Habilitados') }}</span>
                    </a>
                </div>

                <!-- Filtros -->
                <form method="GET" action="{{ route('admin.users.disabled') }}" class="mb-6">
                    <div class="flex flex-wrap gap-4">
                        <input type="text" name="search" class="form-input rounded-md bg-gray-200 border-gray-300" 
                               placeholder="Buscar por nombre o email..." value="{{ $search ?? '' }}">
                        
                        <select name="role" class="form-select rounded-md bg-gray-200 border-gray-300">
                            <option value="all" {{ ($role ?? '') == 'all' ? 'selected' : '' }}>Todos los roles</option>
                            <option value="administrador" {{ ($role ?? '') == 'administrador' ? 'selected' : '' }}>Administradores</option>
                            <option value="basico" {{ ($role ?? '') == 'basico' ? 'selected' : '' }}>Básicos</option>
                        </select>
                        
                        <button type="submit" class="px-4 py-2 bg-stone-600 hover:bg-stone-700 text-white rounded-lg transition-all">Filtrar</button>
                        
                        @if(request('search') || request('role'))
                            <a href="{{ route('admin.users.disabled') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">Limpiar</a>
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
                                    <tr id="disabled-user-row-{{ $user->id }}" class="hover:bg-gray-200/60 dark:hover:bg-gray-700/30 hover:shadow-lg hover:transition-all hover:duration-200">
                                        <!-- Número -->
                                        <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-4 whitespace-nowrap text-center text-gray-900 dark:text-gray-400">
                                            <div class="text-sm font-medium">{{ $loop->iteration }}</div>
                                        </td>
                                        
                                        <!-- Usuario -->
                                        <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 bg-gray-300 dark:bg-gray-700 rounded-full flex items-center justify-center text-gray-600 dark:text-gray-400 font-bold mr-3">
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
                                                            false, 
                                                            '{{ $user->role }}'
                                                        )"
                                                        class="block w-full rounded-md bg-gray-200 dark:bg-gray-700 border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:focus:ring-indigo-600 text-sm">
                                                    <option value="basico" @if ($user->role === 'basico') selected @endif>Básico</option>
                                                    <option value="administrador" @if ($user->role === 'administrador') selected @endif>Administrador</option>
                                                </select>
                                            </form>
                                        </td>
                                        
                                        <!-- Estado -->
                                        <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-4 whitespace-nowrap">
                                            <span class="inline-block px-3 py-1 text-xs font-semibold bg-red-600 text-white rounded-full">
                                               
                                                Deshabilitado
                                            </span>
                                            <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                                {{ $user->deleted_at->format('d/m/Y H:i') }}
                                            </div>
                                        </td>
                                        
                                        <!-- Acciones -->
                                        <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-4 whitespace-nowrap">
                                            <button onclick="handleUserEnable({{ $user->id }}, '{{ $user->name }}')" 
                                                    class="inline-flex items-center text-green-600 hover:text-green-900 dark:text-green-500 dark:hover:text-green-300 transition-colors p-1 hover:bg-gray-600 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110"
                                                    title="Habilitar">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                                                    <path d="m16 11 2 2 4-4"/><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                                                </svg>
                                            </button>
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
                        <p class="text-gray-600 dark:text-gray-400">No se encontraron usuarios deshabilitados.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>


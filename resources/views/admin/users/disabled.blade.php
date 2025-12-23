<x-app-layout>
    <x-slot name="header">
        
    </x-slot>

    <div class="">
        <div class="max-w-7xl mx-auto ">
            <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 ">
                    <h2 class="font-semibold text-xl text-gray-900 dark:text-gray-100 leading-tight">
                        {{ __('Usuarios Deshabilitados') }}
                    </h2>
                    <div class="flex justify-end mb-4">
                        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-gradient-to-r from-blue-500/90 to-blue-600/90 dark:from-blue-600/50 dark:to-blue-700/50 hover:from-blue-600/90 hover:to-blue-700/90 dark:hover:from-blue-700 dark:hover:to-blue-800  text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-350 ease-out border border-blue-400/30 dark:border-blue-800/40">
                            {{ __('Ver Habilitados') }}
                        </a>
                    </div>

                    <table id="users-table" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-stone-100/90 dark:bg-custom-gray ">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">Nombre</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider">Rol</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-300 uppercase tracking-wider"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-stone-100/90 dark:bg-custom-gray divide-y divide-gray-200">
                            @php
                                $i = 1;
                            @endphp
                            @foreach($users as $user)
                                <tr id="disabled-user-row-{{ $user->id }}" class=" hover:bg-gray-200/60 dark:hover:bg-gray-700/30 hover:shadow-lg hover:transition-all hover:duration-200">
                                    <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-400">{{ $i++ }}</td>
                                    <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-400">{{ $user->name }}</td>
                                    <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-400">{{ $user->email }}</td>
                                    <td class="hover:bg-gray-200 dark:hover:bg-gray-600/20 px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
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
                                                class="block w-full rounded-md bg-gray-200 border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <option value="basico" @if ($user->role === 'basico') selected @endif>Básico</option>
                                            <option value="administrador" @if ($user->role === 'administrador') selected @endif>Administrador</option>
                                        </select>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <!-- Botón Habilitar -->
                                        <button onclick="handleUserEnable({{ $user->id }}, '{{ $user->name }}')" 
                                        class="inline-flex items-center text-green-600 hover:text-green-900 dark:text-green-500 dark:hover:text-green-300 transition-colors p-1 hover:bg-gray-500 dark:hover:bg-gray-500/40 rounded-xl transition-all duration-300 hover:bg-opacity-10 hover:scale-110"
                                        title="Habilitar">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-check-icon w-7 h-7 lucide-user-check">
                                                <path d="m16 11 2 2 4-4"/><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
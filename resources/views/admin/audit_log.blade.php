<x-app-layout>
   

    <div class="">
        <div class="mx-auto ">
            <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden shadow-sm sm:rounded-2xl shadow-soft p-4 md:p-6 lg:p-6 mb-6">
                <div class="text-gray-900 dark:text-gray-100">
                    <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-200 mb-2 md:mb-2">
                        {{ __('Actividades Recientes') }}
                    </h2>
                    
                    <table id="audit-table" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-stone-100/90 dark:bg-custom-gray ">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Usuario
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actividad
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-stone-100/90 dark:bg-custom-gray divide-y divide-gray-200">
                          @foreach ($activities as $activity)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $activity->causer ? $activity->causer->name : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <!-- traduccion para las descriciones -->
                                        @php
                                            $translations = [
                                                
                                                // Usuarios
                                                'El usuario ha sido updated' => 'El usuario ha sido actualizado',
                                                'El usuario ha sido restored' => 'El usuario ha sido restaurado',
                                                'El usuario ha sido created' => 'El usuario ha sido creado',
                                                'El usuario ha sido deleted' => 'El usuario ha sido eliminado',
                                                
                                            ];
                                            
                                            // Buscar traducción exacta primero
                                            $translated = $translations[$activity->description] ?? null;
                                            
                                            echo $translated ?? $activity->description;
                                        @endphp
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $activity->created_at->format('d/m/Y H:i:s') }}
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


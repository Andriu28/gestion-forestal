<x-app-layout>
   
    @if (auth()->check() && auth()->user()->role === 'administrador')
    <!-- Stats Cards -->
     
        <div class="stats-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 lg:gap-8 mb-6 md:mb-8">
        
            <div class="bg-stone-100/90 dark:bg-custom-gray  rounded-2xl shadow-soft p-4 md:p-6 lg:p-8 transition-transform duration-300 ease-in-out hover:-translate-y-1 hover:shadow-[0_8px_25px_rgba(0,0,0,0.15)] dark:hover:shadow-[0_8px_25px_rgba(0,0,0,0.4)]">
                <div class="flex items-center justify-between mb-4 md:mb-6">
                    <h3 class="text-xs md:text-sm font-medium text-gray-500">Usuarios Activos</h3>
                    <div class="w-8 h-8 md:w-10 lg:w-12 md:h-10 lg:h-12 bg-gradient-blue bg-lime-600/90 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users-icon w-4 h-4 md:w-5 lg:w-6 md:h-5 lg:h-6 text-white lucide-users">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><path d="M16 3.128a4 4 0 0 1 0 7.744"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><circle cx="9" cy="7" r="4"/>
                    </svg>
                    </div>
                </div>
                <div class="text-xl md:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-gray-200 mb-2 md:mb-3">{{ $userCount }}</div>
                
            </div>

            <div class="bg-stone-100/90 dark:bg-custom-gray  rounded-2xl shadow-soft p-4 md:p-6 lg:p-8 transition-transform duration-300 ease-in-out hover:-translate-y-1 hover:shadow-[0_8px_25px_rgba(0,0,0,0.15)] dark:hover:shadow-[0_8px_25px_rgba(0,0,0,0.4)]">
                <div class="flex items-center justify-between mb-4 md:mb-6">
                    <h3 class="text-xs md:text-sm font-medium text-gray-500">Actividades realizadas</h3>
                    <div class="w-8 h-8 md:w-10 lg:w-12 md:h-10 lg:h-12 bg-gradient-orange bg-yellow-500/90 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-activity-icon w-4 h-4 md:w-5 lg:w-6 md:h-5 lg:h-6 text-white lucide-activity">
                            <path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"/>
                    </svg>
                    </div>
                </div>
                <div class="text-xl md:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-gray-200  mb-2 md:mb-3">{{ $activityCount }}</div>
              
            </div>
        </div>

        <!-- Charts and Activity -->
        <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow-soft p-4 md:p-6 lg:p-8 transition-transform duration-300 ease-in-out hover:-translate-y-1 hover:shadow-[0_8px_25px_rgba(0,0,0,0.15)] dark:hover:shadow-[0_8px_25px_rgba(0,0,0,0.4)]">
            <div class="mb-4 md:mb-6 lg:mb-8">
                <h3 class="text-lg md:text-xl font-bold text-gray-900 dark:text-gray-200 mb-1 md:mb-2">Actividad Reciente</h3>
                <p class="text-xs md:text-sm text-gray-500 ">Últimas acciones de tu equipo</p>
            </div>
            
            <div class="overflow-x-auto">
                <table id="auditoria-table" class="w-full">
                    <thead>
                        <tr class="text-left border-b border-gray-200">
                            <th class="pb-2 text-xs md:text-sm font-semibold text-gray-900 dark:text-gray-300 ">Usuario</th>
                            <th class="pb-2 text-xs md:text-sm font-semibold text-gray-900 dark:text-gray-300 ">Acción</th>
                            <th class="pb-2 text-xs md:text-sm font-semibold text-gray-900 dark:text-gray-300 text-right">Tiempo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($activities as $activity)
                            <tr class="hover:bg-gray-200 dark:bg-custom-gray dark:hover:bg-gray-700 ">
                                <td class="py-3">
                                    <div class="flex items-center space-x-3 md:space-x-4">
                                        {{-- <img src="resources\img\01db27489ea9a74e7cfdcfb4220832ae.jpg" alt="{{ $activity->causer->name }}" class="w-8 h-8 md:w-10 lg:w-12 md:h-10 lg:h-12 rounded-lg"> --}}
                                        <span class="text-xs md:text-sm font-semibold text-gray-900 dark:text-gray-400 ">{{ $activity->causer ? $activity->causer->name : 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="py-3 text-xs md:text-sm text-gray-500">
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
                                <td class="py-3 text-xs text-gray-500 text-left">{{ $activity->created_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <h2 class="!text-3xl !font-extrabold !text-black dark:text-white !mb-4">
                Bienvenido Usuario {{ Auth::user()->name }}
            </h2>
            @endif

        </div>
     
</x-app-layout>

<!-- prueva rapida que funciona para adactar el tamaño del buscador de datatable -->

   <style>
/* Estilos específicos para el input de búsqueda */
input.dt-input[type="search"] {
    width: 250px !important;
    min-width: 200px !important;
    max-width: 300px !important;
}

/* Para cuando DataTables regenera el DOMss */
.dt-container .dt-search input {
    width: 250px !important;
    min-width: 200px !important;
    max-width: 300px !important;
}

/* Responsive */
@media (max-width: 768px) {
    input.dt-input[type="search"],
    .dt-container .dt-search input {
        width: 80px !important;
        min-width: 150px !important;
        max-width: 250px !important;
    }
}

@media (max-width: 480px) {
    input.dt-input[type="search"],
    .dt-container .dt-search input {
        width: 90px !important;
        min-width: 40px !important;
        max-width: 200px !important;
    }
}
</style>
       
  <!-- Main Content Area -->



<x-app-layout>
    <div class="mx-auto">
        <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden shadow-sm sm:rounded-2xl shadow-soft p-4 md:p-6 lg:p-8">
            <div class="overflow-hidden">
                <!-- Encabezado con título y botón nuevo análisis -->
                <div class="flex flex-wrap justify-between items-start gap-4 mb-6 pt-1">
                    <h2 class="font-semibold text-3xl text-gray-900 dark:text-gray-100 leading-tight">
                        Resultados del Análisis de Deforestación
                        <span class="ml-2 text-sm font-medium text-yellow-600 dark:text-yellow-400 bg-yellow-100 dark:bg-yellow-900/30 px-3 py-1 rounded-full">(no guardado)</span>
                    </h2>
                    
                    <div class="flex space-x-4 mb-0.5">
                        <!-- Botón para nuevo análisis -->
                        <a href="{{ route('deforestation.create') }}" 
                           title="Nuevo análisis" 
                           class="group px-2.5 py-1.5 bg-stone-200/80 hover:bg-blue-700/70 dark:hover:bg-blue-500/60 text-stone-700 hover:text-white border border-stone-300/70 hover:border-transparent dark:bg-gray-700/40 dark:text-gray-300 dark:hover:text-white dark:border-gray-600/50 rounded-md flex items-center hover:shadow-md hover:-translate-y-0.5 overflow-hidden">
                            <span class="flex items-center justify-center w-6 h-6 transition-all duration-300 group-hover:w-6 group-hover:h-6 flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-blue-700/70 group-hover:text-white dark:text-blue-400/70">
                                    <path d="m11 19-1.106-.552a2 2 0 0 0-1.788 0l-3.659 1.83A1 1 0 0 1 3 19.381V6.618a1 1 0 0 1 .553-.894l4.553-2.277a2 2 0 0 1 1.788 0l4.212 2.106a2 2 0 0 0 1.788 0l3.659-1.83A1 1 0 0 1 21 4.619V12"/><path d="M15 5.764V12"/><path d="M18 15v6"/><path d="M21 18h-6"/><path d="M9 3.236v15"/>
                                </svg>
                            </span>
                            <span class="text-base font-medium transition-all duration-300 w-0 opacity-0 group-hover:w-12 group-hover:opacity-100 group-hover:ml-1 whitespace-nowrap overflow-hidden text-inherit">Nuevo</span>
                        </a>
                    </div>
                </div>

                <!-- Información del Área de Estudio -->
                <div class="mb-8 p-4 bg-grey-300 dark:bg-gray-600/10 rounded-lg">
                    <h3 class="font-semibold text-xl text-grey-800 dark:text-grey-100 mb-2">
                        Nombre del Polígono: {{ $dataToPass['polygon_name'] ?? 'Área sin nombre' }}
                    </h3>
                </div>

                <!-- Tarjetas de estadísticas -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <x-cards.stats-card 
                        title="Área Total del Polígono" 
                        color="green" 
                        value="{{ number_format($dataToPass['polygon_area_ha'] ?? 0, 4, ',', '.') }}" 
                        unit="ha" 
                    />

                    <x-cards.stats-card 
                        title="Área Deforestada ({{ $dataToPass['start_year'] ?? 'N/A' }} - {{ $dataToPass['end_year'] ?? 'N/A' }})" 
                        color="red" 
                        value="{{ number_format($dataToPass['total_deforested'] ?? 0, 4, ',', '.') }}" 
                        unit="ha" 
                    />

                    <x-cards.stats-card 
                        title="Porcentaje de Pérdida" 
                        color="purple" 
                        value="{{ number_format($dataToPass['total_percentage'] ?? 0, 2, ',', '.') }}" 
                        unit="%" 
                    />

                    <x-cards.stats-card 
                        title="Estado del Análisis" 
                        color="yellow" 
                        value="No guardado" 
                        unit=""
                    />
                </div>

                <!-- Mensaje informativo -->
                <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/10 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600 dark:text-yellow-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="text-sm text-yellow-700 dark:text-yellow-300 font-semibold">
                                Este análisis no fue guardado en la base de datos.
                            </p>
                            <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                                Los datos mostrados son un resumen basado en la información registrada en el historial.
                                No se puede visualizar el mapa ni los detalles anuales porque la geometría no se conservó.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="mt-8 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('deforestation.create') }}" 
                           class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-blue-600 dark:bg-blue-800 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                            Nuevo Análisis
                        </a>
                        <a href="{{ route('admin.audit') }}" 
                           class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-base font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition duration-150 ease-in-out">
                            Volver al historial
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
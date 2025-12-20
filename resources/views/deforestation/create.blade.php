<!-- resources/views/deforestation/create.blade.php -->

<x-app-layout>
    <div class=" mx-auto ">
        <div class="bg-stone-100/90 dark:bg-custom-gray  shadow-sm sm:rounded-2xl shadow-soft p-4 md:p-6 lg:p-6 mb-6">
            <div class="text-gray-900 dark:text-gray-100 ">
                <h3 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-200 mb-2 md:mb-2">
                    {{('Análisis de Deforestación') }}
                </h3>
   
                    <!-- CARGA DEL MAPA AQUÍ -->
                    <div id="map" style="height: 80vh; border: 1px solid #dededeff; border-radius: 0.5rem; position: relative;">
                        <!-- Controles del mapa -->
                        <div id="map-controls">
                            <!-- Contenedor para los botones superiores (Cambiar Mapa y Pantalla Completa) -->
                            <div class="flex flex-col items-end space-y-2">
                                <!-- Fila superior: Cambiar Mapa y Pantalla Completa -->
                                <div class="flex space-x-2">

                                    {{-- boton para mostrar u ocultar areas en deforestacion --}}

                                    <div class="relative">
                                        <button id="visibility-toggle-button" class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                            
                                            {{-- 1. OJO ABIERTO: AHORA OCULTO POR DEFECTO --}}
                                            <span id="icon-eye-open" class="hidden"> 
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye w-6 h-6">
                                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                                    <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                            </span>
                                            
                                            {{-- 2. OJO CERRADO: AHORA VISIBLE POR DEFECTO --}}
                                            <span id="icon-eye-closed"> 
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye-off w-6h-6">
                                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                                                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                                                    <line x1="2" x2="22" y1="2" y2="22"/>
                                                </svg>
                                            </span>
                                        </button>
                                    </div>

                                    {{-- // AGREGAR AQUÍ EL NUEVO CONTROL DE OPACIDAD // --}}
                                        <!-- Contenedor para controles de opacidad -->
                                        <div class="relative">
                                            <button id="opacity-control-button" title="Ajustar Opacidad" class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg ">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layers w-6 h-6">
                                                    <path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/>
                                                    <path d="m22 17.46-8.58-3.91a2 2 0 0 0-1.66 0L3 17.46"/>
                                                    <path d="m22 12.46-8.58-3.91a2 2 0 0 0-1.66 0L3 12.46"/>
                                                </svg>
                                            </button>
                                            
                                            <!-- Panel de control de opacidad -->
                                            <div id="opacity-control-panel" 
                                                class="absolute mt-4 w-48 rounded-xl shadow-lg bg-gray-50 dark:bg-custom-gray ring-1 ring-black ring-opacity-5 z-10 right-0
                                                        transition-all duration-400 ease-out scale-95 opacity-0 pointer-events-none hidden">
                                                <div class="absolute -top-2 right-6 w-4 h-2 z-100 pointer-events-none">
                                                    <svg viewBox="0 0 16 8" class="w-4 h-2 text-white dark:text-custom-gray">
                                                        <polygon points="8,0 16,8 0,8" fill="currentColor"/>
                                                    </svg>
                                                </div>
                                                
                                                <!-- Contenido del panel -->
                                                <div class="p-4 z-100">
                                                    <div class="flex items-center justify-between mb-2">
                                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Opacidad GFW</span>
                                                        <span id="opacity-value" class="text-xs font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">75%</span>
                                                    </div>
                                                    
                                                    <!-- Slider de opacidad -->
                                                    <input type="range" 
                                                        id="opacity-slider" 
                                                        min="0" 
                                                        max="100" 
                                                        value="75"
                                                        class="w-full h-2 bg-gray-200 dark:bg-gray-600 rounded-lg appearance-none cursor-pointer slider-thumb">
                                                    
                                                    <!-- Botones predefinidos -->
                                                    <div class="flex space-x-2 mt-3">
                                                        <button type="button" data-opacity="25" class="flex-1 py-1  bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-xs">
                                                            25%
                                                        </button>
                                                        <button type="button" data-opacity="50" class="flex-1 py-1  bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-xs">
                                                            50%
                                                        </button>
                                                        <button type="button" data-opacity="75" class="flex-1 py-1  bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-xs">
                                                            75%
                                                        </button>
                                                        <button type="button" data-opacity="100" class="flex-1 py-1  bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-xs">
                                                            100%
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- // FIN DEL NUEVO CONTROL DE OPACIDAD // --}}

                                    {{-- ///////////////////////////////////////////////////// --}}

                                    <!-- Contenedor para Cambiar Mapa con menú -->
                                    <div class="relative">
                                        
                                        <!-- Botón de cambio de mapa -->
                                        <button id="base-map-toggle" title="Cambiar mapa" class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                            </svg>
                                            Mapas
                                        </button>
                                        
                                        <!-- Menú de cambio de mapa - POSICIONADO RELATIVO AL BOTÓN -->
                                        <div id="base-map-menu"
                                             class="absolute mt-4 w-40 rounded-xl shadow-lg bg-white dark:bg-custom-gray ring-1 ring-black ring-opacity-5 z-1 right-0
                                                    transition-all duration-400 ease-out scale-95 opacity-0 pointer-events-none">
                                            <!-- Flechita -->
                                            <div class="absolute -top-2 right-6 w-4 h-2 z-100 pointer-events-none">
                                                <svg viewBox="0 0 16 8" class="w-4 h-2 text-white dark:text-custom-gray">
                                                    <polygon points="8,0 16,8 0,8" fill="currentColor"/>
                                                </svg>
                                            </div>
                                            <!-- Menú -->
                                             <div class="py-2 z-100" role="menu" aria-orientation="vertical">
                                                <button data-layer="osm" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">OpenStreetMap</button>
                                                <button data-layer="satellite" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">Satélite Esri</button>
                                                <button data-layer="maptiler_satellite" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">MapTiler Satélite</button>
                                                <button data-layer="terrain" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">Relieve</button>
                                                <button data-layer="dark" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">Oscuro</button>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ///////////////////////////////////////////////////// --}}
                                    
                                    <!-- Botón de pantalla completa -->

                                    <button id="fullscreen-toggle" title="Pantalla Completa"class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5"></path>
                                        </svg>
                                    </button>

                                    {{-- ///////////////////////////////////////////////////// --}}
                                </div>
                                
                                <!-- Fila inferior: Solo el botón de coordenadas manuales -->

        
                            
                                   <!-- Botón para formulario manual -->
                                </div>
                                <!-- BOTONES DE CONTROL AÑADIDOS -->
                                <div class=" flex space-x-2"> 
                                 
                                 <button id="manual-polygon-toggle" title="Escribir Cordenadas" class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-line-icon w-6 h-6 lucide-pencil-line">
                                         <path d="M13 21h8"/><path d="m15 5 4 4"/><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>
                                     </svg>
                                    
                                 </button>
                             </div>
                                <div class=" flex space-x-2">
                                    <button id="draw-polygon" title="Dibujar Cordenadas" class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-line-icon w-6 h-6 lucide-plus">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        
                                    </button>
                                </div>
                                <div class="flex space-x-2">
                                    <button id="clear-map" title="Limpiar Mapa" class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-line-icon w-6 h-6 lucide-clear">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>

                                {{-- ///////////////////////////////////////////////////// --}}
                                
                            
                    </div>

                <!-- Modal moderno para coordenadas UTM universal -->
                <div id="manual-polygon-modal" class="hidden">
                    <div class="bg-white dark:bg-custom-gray rounded-xl shadow-2xl w-full max-w-lg">
                        <!-- Header -->
                        <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-600">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Ingresar Coordenadas UTM</h3>
                            <button id="close-modal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 ">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Formulario UTM Universal -->
                        <form id="manual-polygon-form" class="p-6 space-y-4">
                            
                            <!-- Método de entrada -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Método de entrada:</label>
                                <div class="flex space-x-2">
                                    <button type="button" id="method-single" class="flex-1 py-2 px-3 bg-blue-600 text-white rounded-lg text-sm font-medium">Una por una</button>
                                    <button type="button" id="method-bulk" class="flex-1 py-2 px-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium">Lote</button>
                                </div>
                            </div>

                            <!-- Entrada individual -->
                            <div id="single-input" class="space-y-3">
                                <div class="grid grid-cols-4 gap-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Zona</label>
                                        <input type="number" id="single-zone" class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm p-2" 
                                            min="1" max="60" placeholder="20" value="20">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Hemisferio</label>
                                        <select id="single-hemisphere" class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm">
                                            <option value="N">Norte (N)</option>
                                            <option value="S">Sur (S)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Este</label>
                                        <input type="text" id="single-easting" class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm p-2" placeholder="500000">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Norte</label>
                                        <input type="text" id="single-northing" class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm p-2" placeholder="10000000">
                                    </div>
                                </div>
                                <button type="button" id="add-coord" class="w-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 py-2 rounded-lg text-sm">
                                    + Agregar coordenada
                                </button>
                            </div>

                            <!-- Entrada por lote -->
                            <div id="bulk-input" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Coordenadas UTM (Zona,Hemisferio,Este,Norte por línea):
                                </label>
                                <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg mb-2">
                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                        <strong>Formato Universal:</strong> Zona,Hemisferio,Este,Norte<br>
                                        <strong>Ejemplos:</strong><br>
                                        <code class="text-green-600">20,N,500000,10000000</code> (Venezuela Norte)<br>
                                        <code class="text-blue-600">18,S,300000,8000000</code> (Argentina Sur)<br>
                                    </p>
                                </div>
                               
                               <textarea id="bulk-coords" rows="6" class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm p-2 font-mono text-xs" 
          placeholder="Ejemplo:&#10;&#9;Zona,Hemisferio,Este,Norte&#10;&#9;20,N,476097.904,1157477.299&#10;&#9;20,N,476181.804,1157432.362&#10;&#9;20,N,475211.522,1157534.959"></textarea>
                            </div>

                            <!-- Lista de coordenadas agregadas -->
                            <div id="coords-list" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Coordenadas UTM agregadas:</label>
                                <div class="max-h-32 overflow-y-auto border border-gray-200 dark:border-gray-500 rounded-md p-2 bg-gray-50 dark:bg-gray-800/80">
                                    <div id="coords-container" class="space-y-1"></div>
                                </div>
                                <button type="button" id="clear-list" class="text-red-600 hover:text-red-700 text-xs mt-1">Limpiar lista</button>
                            </div>

                            <div class="flex space-x-3 pt-2">
                                <button type="button" id="cancel-modal" class="flex-1 py-2 px-4 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium">
                                    Cancelar
                                </button>
                                <button type="submit" class="flex-1 py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                                    Dibujar
                            </div>
                        </form>
                    </div>
                </div> 
            </div>
            
            <!-- Columna del Formulario -->
            <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden  sm:rounded-2xl  p-4 md:p-6 lg:p-8 ">
                <div class="text-gray-900 dark:text-gray-100 ">
                    <h2 class="text-lg font-semibold mb-4">Configuración del Análisis</h2>
                    
                    <form id="analysis-form" action="{{ route('deforestation.analyze') }}" method="POST">
                        @csrf
                        
                        <div class="mt-4">
                            <x-input-label for="name" :value="__('Nombre del Área:')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" autocomplete="name" placeholder="Ej: Reserva Natural XYZ" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="description" :value="__('Descripción:')" />
                            <textarea id="description" name="description" rows="2" class="block mt-1 w-full rounded-md border-gray-300 dark:bg-custom-gray dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 @error('address') border-red-500 @enderror" placeholder="Descripción del área de estudio..."></textarea>
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="start_year" class="block text-sm font-medium text-gray-700 mb-1">Año Inicio:</label>
                            <select class="block w-full bg-gray-200 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                    id="start_year" name="start_year" required>
                                @for($i = 2001; $i <= 2024; $i++)
                                    <option value="{{ $i }}" {{ $i == 2020 ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        
                        <div>
                            <label for="end_year" class="block text-sm font-medium text-gray-700 mb-1">Año Fin:</label>
                            <select class="block w-full bg-gray-200 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                    id="end_year" name="end_year" required>
                                @for($i = 2001; $i <= 2024; $i++)
                                    <option value="{{ $i }}" {{ $i == 2024 ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                        
                        <div class="mb-4">
                            <label for="import-area" class="block text-sm font-medium text-gray-700 mb-1">Importar área (GeoJSON, KML, SHP):</label>
                            <input type="file" id="import-area" accept=".geojson,.json,.kml,.shp,.zip,application/geo+json,application/vnd.google-earth.kml+xml,application/zip" class="block w-full text-sm text-gray-500" />
                        </div>
                        
                        <input type="hidden" name="geometry" id="geometry">
                        <input type="hidden" name="area_ha" id="area_ha">
                        
                        <button type="submit" id="submit-button" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md flex items-center justify-center">
                            <span id="loading-spinner" class="hidden mr-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </span>
                            <span id="button-text">Analizar Deforestación</span>
                        </button>
                    </form>
                    <!-- create.blade.php - Añadir después del formulario -->
                    <div id="producers-info" class="mt-6 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg hidden">
                        <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">Información de Productores Importados</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Productor</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Localidad</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Área (Ha)</th>
                                    </tr>
                                </thead>
                                <tbody id="producers-list" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <!-- Los datos se llenarán con JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div id="results" class="mt-4"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loader overlay -->
    
<div id="loader-overlay" class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl transform transition-all duration-300">
        <div class="flex flex-col items-center">
            <!-- Spinner mejorado -->
            <div class="relative mb-6">
                <div class="w-20 h-20 border-4 border-green-100 dark:border-green-900 rounded-full"></div>
                <div class="absolute top-0 left-0 w-20 h-20 border-4 border-transparent border-t-green-600 rounded-full animate-spin"></div>
                <div class="absolute top-2 left-2 w-16 h-16 border-4 border-transparent border-b-green-400 rounded-full animate-spin" style="animation-direction: reverse; animation-duration: 1.5s"></div>
            </div>
            
            <!-- Texto principal -->
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 text-center">Analizando Deforestación</h3>
            <p class="text-gray-600 dark:text-gray-300 text-center mb-6">
                Procesando el área seleccionada. Esto puede tomar unos segundos...
            </p>
            
            <!-- Barra de progreso mejorada -->
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 mb-2 overflow-hidden">
                <div id="progress-bar" class="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full w-0 transition-all duration-500 ease-out relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent animate-pulse"></div>
                </div>
            </div>
            
            <!-- Información de progreso -->
            <div class="flex justify-between w-full text-xs text-gray-500 dark:text-gray-400 mb-1">
                <span id="progress-text">Iniciando...</span>
                <span id="progress-percentage">0%</span>
            </div>
            
            <!-- Tiempo estimado -->
            <div class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                <span id="time-estimate">Tiempo estimado: 15-30 segundos</span>
            </div>
        </div>
    </div>
</div>
</x-app-layout>

<!-- Incluir OpenLayers -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/css/ol.css">
<script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/build/ol.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.8.0/proj4.js"></script>

<!-- Incluir nuestro JavaScript -->
<script src="{{ asset('js/deforestation/map.js') }}"></script>

<script>
// Variables globales para coordenadas UTM universales
let coordinatesList = [];

// Configurar Proj4 para UTM dinámicamente
function setupUTMProjection(zone, hemisphere) {
    const epsgCode = hemisphere === 'N' ? `EPSG:326${zone}` : `EPSG:327${zone}`;
    
    if (!proj4.defs(epsgCode)) {
        const proj4String = `+proj=utm +zone=${zone} +${hemisphere === 'S' ? '+south ' : ''}datum=WGS84 +units=m +no_defs`;
        proj4.defs(epsgCode, proj4String);
    }
    
    return epsgCode;
}

// Función para validar coordenadas UTM
function validateUTMCoordinates(zone, hemisphere, easting, northing) {
    // Validar zona (1-60)
    if (zone < 1 || zone > 60) {
        return 'Zona UTM debe estar entre 1 y 60';
    }
    
    // Validar hemisferio
    if (hemisphere !== 'N' && hemisphere !== 'S') {
        return 'Hemisferio debe ser N (Norte) o S (Sur)';
    }
    
    // Validar easting (generalmente 100,000 - 900,000)
    if (easting < 0 || easting > 1000000) {
        return 'Este (Easting) debe estar entre 0 y 1,000,000';
    }
    
    // Validar northing según hemisferio
    if (hemisphere === 'N') {
        if (northing < 0 || northing > 10000000) {
            return 'Norte (Northing) en hemisferio Norte debe estar entre 0 y 10,000,000';
        }
    } else {
        if (northing < 1000000 || northing > 10000000) {
            return 'Norte (Northing) en hemisferio Sur debe estar entre 1,000,000 y 10,000,000';
        }
    }
    
    return null; // Sin errores
}

// Mostrar/ocultar menú de cambio de mapa
document.getElementById('base-map-toggle').addEventListener('click', function(e) {
    e.stopPropagation();
    const menu = document.getElementById('base-map-menu');
    const isShowing = menu.classList.contains('show');
    
    toggleMenu('base-map-menu', !isShowing);
});

// Función para abrir el modal con animación
function openCoordinateModal() {
    const modal = document.getElementById('manual-polygon-modal');
    modal.classList.remove('hidden');
    
    // Forzar reflow para que la animación se ejecute
    void modal.offsetWidth;
    
    setTimeout(() => {
        const firstInput = document.getElementById('single-easting');
        if (firstInput) firstInput.focus();
    }, 100);
}

// Función para cerrar el modal con animación
function closeCoordinateModal() {
    const modal = document.getElementById('manual-polygon-modal');
    
    // Aplicar animación de salida
    modal.classList.add('closing');
    
    // Esperar a que termine la animación antes de ocultar
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('closing');
        coordinatesList = [];
        updateCoordinatesList();
        document.getElementById('bulk-coords').value = '';
        document.getElementById('single-easting').value = '';
        document.getElementById('single-northing').value = '';
        
        // Resetear al método individual por defecto
        setInputMethod('single');
    }, 300);
}

// Abrir modal de coordenadas UTM
document.getElementById('manual-polygon-toggle').addEventListener('click', function(e) {
    e.stopPropagation();
    closeMenu('base-map-menu');
    openCoordinateModal();
});

// Cerrar modal de coordenadas UTM
document.getElementById('close-modal').addEventListener('click', closeCoordinateModal);
document.getElementById('cancel-modal').addEventListener('click', closeCoordinateModal);

// Cambiar método de entrada en el modal
document.getElementById('method-single').addEventListener('click', function() {
    setInputMethod('single');
});

document.getElementById('method-bulk').addEventListener('click', function() {
    setInputMethod('bulk');
});

// Agregar coordenada UTM individual universal
document.getElementById('add-coord').addEventListener('click', function() {
    const zone = parseInt(document.getElementById('single-zone').value);
    const hemisphere = document.getElementById('single-hemisphere').value;
    const easting = document.getElementById('single-easting').value.trim();
    const northing = document.getElementById('single-northing').value.trim();
    
    if (!zone || !easting || !northing) {
        showAlert('Debe ingresar Zona, Este y Norte', 'warning');
        return;
    }
    
    if (isNaN(zone) || isNaN(easting) || isNaN(northing)) {
        showAlert('Zona, Este y Norte deben ser números válidos', 'warning');
        return;
    }
    
    // Validar coordenadas UTM
    const validationError = validateUTMCoordinates(zone, hemisphere, parseFloat(easting), parseFloat(northing));
    if (validationError) {
        showAlert(validationError, 'warning');
        return;
    }
    
    coordinatesList.push({ 
        zone: zone,
        hemisphere: hemisphere,
        easting: parseFloat(easting), 
        northing: parseFloat(northing) 
    });
    updateCoordinatesList();
    
    // Limpiar inputs pero mantener zona y hemisferio
    document.getElementById('single-easting').value = '';
    document.getElementById('single-northing').value = '';
    
    showAlert(`Coordenada agregada (Zona ${zone}${hemisphere})`, 'success');
});

// Limpiar lista de coordenadas UTM
document.getElementById('clear-list').addEventListener('click', function() {
    coordinatesList = [];
    updateCoordinatesList();
});

// Enviar formulario del modal UTM universal
document.getElementById('manual-polygon-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    let utmCoords = [];
    
    if (document.getElementById('method-single').classList.contains('bg-blue-600')) {
        // Modo coordenada por coordenada
        if (coordinatesList.length < 3) {
            showAlert('Se necesitan al menos 3 coordenadas', 'warning');
            return;
        }
        utmCoords = coordinatesList.map(coord => [
            coord.easting, 
            coord.northing, 
            coord.zone, 
            coord.hemisphere
        ]);
    } else {
        // Modo lote
        const coordsText = document.getElementById('bulk-coords').value.trim();
        if (!coordsText) {
            showAlert('Debe ingresar coordenadas UTM', 'warning');
            return;
        }
        
        const lines = coordsText.split('\n');
        let hasErrors = false;
        
        lines.forEach((line, index) => {
            const parts = line.split(',').map(s => s.trim());
            if (parts.length === 4) {
                const [zoneStr, hemisphere, eastingStr, northingStr] = parts;
                const zone = parseInt(zoneStr);
                const easting = parseFloat(eastingStr);
                const northing = parseFloat(northingStr);
                
                if (!isNaN(zone) && !isNaN(easting) && !isNaN(northing) && 
                    (hemisphere === 'N' || hemisphere === 'S')) {
                    
                    // Validar coordenadas
                    const validationError = validateUTMCoordinates(zone, hemisphere, easting, northing);
                    if (validationError) {
                        showAlert(`Línea ${index + 1}: ${validationError}`, 'warning');
                        hasErrors = true;
                        return;
                    }
                    
                    utmCoords.push([easting, northing, zone, hemisphere]);
                } else {
                    showAlert(`Línea ${index + 1}: Formato inválido`, 'warning');
                    hasErrors = true;
                }
            } else if (line.trim() !== '') {
                showAlert(`Línea ${index + 1}: Debe tener 4 valores (Zona,Hemisferio,Este,Norte)`, 'warning');
                hasErrors = true;
            }
        });
        
        if (hasErrors) return;
        
        if (utmCoords.length < 3) {
            showAlert('Se necesitan al menos 3 coordenadas UTM válidas', 'warning');
            return;
        }
    }
    
    // Dibujar el polígono universal
    drawUniversalUTMPolygon(utmCoords);
    closeCoordinateModal();
});

// Funciones para el modal de coordenadas UTM
function setInputMethod(method) {
    const singleBtn = document.getElementById('method-single');
    const bulkBtn = document.getElementById('method-bulk');
    const singleInput = document.getElementById('single-input');
    const bulkInput = document.getElementById('bulk-input');
    const coordsList = document.getElementById('coords-list');
    
    if (method === 'single') {
        singleBtn.classList.add('bg-blue-600', 'text-white');
        singleBtn.classList.remove('bg-gray-200', 'text-gray-700', 'dark:bg-gray-700', 'dark:text-gray-300');
        bulkBtn.classList.remove('bg-blue-600', 'text-white');
        bulkBtn.classList.add('bg-gray-200', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
        singleInput.classList.remove('hidden');
        bulkInput.classList.add('hidden');
        
        // MOSTRAR LA LISTA SOLO SI HAY COORDENADAS EN EL MODO INDIVIDUAL
        if (coordinatesList.length > 0) {
            coordsList.classList.remove('hidden');
        } else {
            coordsList.classList.add('hidden');
        }
    } else {
        bulkBtn.classList.add('bg-blue-600', 'text-white');
        bulkBtn.classList.remove('bg-gray-200', 'text-gray-700', 'dark:bg-gray-700', 'dark:text-gray-300');
        singleBtn.classList.remove('bg-blue-600', 'text-white');
        singleBtn.classList.add('bg-gray-200', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
        bulkInput.classList.remove('hidden');
        singleInput.classList.add('hidden');
        
        // OCULTAR LA LISTA EN EL MODO LOTE
        coordsList.classList.add('hidden');
    }
}

function updateCoordinatesList() {
    const container = document.getElementById('coords-container');
    const listSection = document.getElementById('coords-list');
    const isSingleMode = document.getElementById('method-single').classList.contains('bg-blue-600');
    
    container.innerHTML = '';
    
    if (coordinatesList.length === 0) {
        listSection.classList.add('hidden');
        return;
    }
    
    // SOLO MOSTRAR LA LISTA SI ESTAMOS EN MODO INDIVIDUAL
    if (isSingleMode) {
        listSection.classList.remove('hidden');
    } else {
        listSection.classList.add('hidden');
    }
    
    coordinatesList.forEach((coord, index) => {
        const coordElement = document.createElement('div');
        coordElement.className = 'flex justify-between items-center text-xs font-mono';
        coordElement.innerHTML = `
            <span>${index + 1}. Z${coord.zone}${coord.hemisphere} | E:${coord.easting} | N:${coord.northing}</span>
            <button type="button" onclick="removeCoordinate(${index})" class="text-red-500 hover:text-red-700 text-xs">✕</button>
        `;
        container.appendChild(coordElement);
    });
}

function removeCoordinate(index) {
    coordinatesList.splice(index, 1);
    updateCoordinatesList();
}

// FUNCIÓN CLAVE: Dibujar polígono UTM universal

function drawUniversalUTMPolygon(utmCoordinates) {
    if (window.deforestationMapInstance) {
        window.deforestationMapInstance.drawFromUTMCoordinates(utmCoordinates);
    }
}

// Funciones para los menús desplegables
function toggleMenu(menuId, show) {
    const menu = document.getElementById(menuId);
    if (show) {
        menu.classList.remove('scale-95', 'opacity-0', 'pointer-events-none');
        menu.classList.add('scale-100', 'opacity-100', 'pointer-events-auto', 'show');
    } else {
        menu.classList.remove('scale-100', 'opacity-100', 'pointer-events-auto', 'show');
        menu.classList.add('scale-95', 'opacity-0', 'pointer-events-none');
    }
}

function closeMenu(menuId) {
    toggleMenu(menuId, false);
}

// Cerrar menús al hacer clic fuera
document.addEventListener('click', function(e) {
    const baseMapToggle = document.getElementById('base-map-toggle');
    const baseMapMenu = document.getElementById('base-map-menu');
    const modal = document.getElementById('manual-polygon-modal');
    
    if (modal.classList.contains('hidden')) {
        if (!baseMapToggle.contains(e.target) && !baseMapMenu.contains(e.target)) {
            closeMenu('base-map-menu');
        }
    }
    
    // Cerrar modal si se hace clic fuera de él
    if (!modal.classList.contains('hidden') && e.target === modal) {
        closeCoordinateModal();
    }
});

// Nombres de capas para mostrar en el botón
const layerNames = {
    'osm': 'OpenStreetMap',
    'satellite': 'Satélite Esri', 
    'maptiler_satellite': 'MapTiler Satélite',
    'terrain': 'Relieve',
    'dark': 'Oscuro'
};

// Cambiar capa base
document.querySelectorAll('#base-map-menu button').forEach(button => {
    button.addEventListener('click', function(e) {
        e.stopPropagation();
        const layerKey = this.getAttribute('data-layer');
        
        if (window.deforestationMapInstance && window.deforestationMapInstance.changeBaseLayer) {
            window.deforestationMapInstance.changeBaseLayer(layerKey);
        }
        
        closeMenu('base-map-menu');
        
        const button = document.getElementById('base-map-toggle');
        const svg = button.querySelector('svg').cloneNode(true);
        button.innerHTML = '';
        button.appendChild(svg);
        button.appendChild(document.createTextNode(' ' + layerNames[layerKey]));
    });
});

// Función para pantalla completa
document.getElementById('fullscreen-toggle').addEventListener('click', function() {
    const mapElement = document.getElementById('map');
    if (!document.fullscreenElement) {
        if (mapElement.requestFullscreen) {
            mapElement.requestFullscreen();
        } else if (mapElement.webkitRequestFullscreen) {
            mapElement.webkitRequestFullscreen();
        } else if (mapElement.msRequestFullscreen) {
            mapElement.msRequestFullscreen();
        }
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    }
});

function toggleOpacityPanel(show) {
    const panel = document.getElementById('opacity-control-panel');
    
    if (show) {
        panel.classList.remove('hidden', 'scale-95', 'opacity-0', 'pointer-events-none');  // AGREGAR 'hidden'
        panel.classList.add('scale-100', 'opacity-100', 'pointer-events-auto', 'show');
    } else {
        panel.classList.remove('scale-100', 'opacity-100', 'pointer-events-auto', 'show');
        panel.classList.add('hidden','scale-95', 'opacity-0', 'pointer-events-none');
    }
}

// Función para actualizar la opacidad
function updateOpacity(value) {
    const opacity = value / 100;
    
    // Actualizar la capa GFW si existe
    if (window.deforestationMapInstance && window.deforestationMapInstance.gfwLossLayer) {
        window.deforestationMapInstance.gfwLossLayer.setOpacity(opacity);
    }
    
    // Actualizar la interfaz
    document.getElementById('opacity-value').textContent = `${value}%`;
    document.getElementById('opacity-slider').value = value;
    
    // Actualizar botones predefinidos
    document.querySelectorAll('[data-opacity]').forEach(btn => {
        const btnOpacity = parseInt(btn.getAttribute('data-opacity'));
        if (btnOpacity === value) {
            btn.classList.add('bg-blue-600', 'text-white');
            btn.classList.remove('bg-gray-200', 'text-gray-700', 'dark:bg-gray-700', 'dark:text-gray-300');
        } else {
            btn.classList.remove('bg-blue-600', 'text-white');
            btn.classList.add('bg-gray-200', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
        }
    });
    
    // Actualizar el track del slider visualmente
    const slider = document.getElementById('opacity-slider');
    const progress = (value / slider.max) * 100;
    slider.style.background = `linear-gradient(to right, #4f46e5 ${progress}%, #e5e7eb ${progress}%)`;
}

// Event listener para el botón de opacidad
document.getElementById('opacity-control-button').addEventListener('click', function(e) {
    e.stopPropagation();
    const panel = document.getElementById('opacity-control-panel');
    const isShowing = panel.classList.contains('show');
    
    // Cerrar otros menús abiertos
    closeMenu('base-map-menu');
    
    // Alternar este menú
    toggleOpacityPanel(!isShowing);
});

// Slider de opacidad
document.getElementById('opacity-slider').addEventListener('input', function(e) {
    updateOpacity(parseInt(e.target.value));
});

// Botones predefinidos de opacidad
document.querySelectorAll('[data-opacity]').forEach(button => {
    button.addEventListener('click', function() {
        const opacityValue = parseInt(this.getAttribute('data-opacity'));
        updateOpacity(opacityValue);
    });
});

// Actualizar la función que cierra menús al hacer clic fuera
document.addEventListener('click', function(e) {
    const baseMapToggle = document.getElementById('base-map-toggle');
    const baseMapMenu = document.getElementById('base-map-menu');
    const opacityButton = document.getElementById('opacity-control-button');
    const opacityPanel = document.getElementById('opacity-control-panel');
    const modal = document.getElementById('manual-polygon-modal');
    
    // Solo procesar si el modal está cerrado
    if (modal.classList.contains('hidden')) {
        // Cerrar menú de cambio de mapa si se hace clic fuera
        if (!baseMapToggle.contains(e.target) && !baseMapMenu.contains(e.target)) {
            closeMenu('base-map-menu');
        }
        
        // Cerrar panel de opacidad si se hace clic fuera
        if (!opacityButton.contains(e.target) && !opacityPanel.contains(e.target)) {
            toggleOpacityPanel(false);
        }
    }
    
    // Cerrar modal si se hace clic fuera de él
    if (!modal.classList.contains('hidden') && e.target === modal) {
        closeCoordinateModal();
    }
});

// Inicializar opacidad cuando el mapa esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Esperar a que el mapa se inicialice
    setTimeout(() => {
        if (window.deforestationMapInstance && window.deforestationMapInstance.gfwLossLayer) {
            const currentOpacity = window.deforestationMapInstance.gfwLossLayer.getOpacity() * 100;
            updateOpacity(currentOpacity || 75); // Valor por defecto 75%
        }
    }, 1000);
});

// Función de alerta auxiliar
function showAlert(message, type) {
    if (window.deforestationMapInstance && window.deforestationMapInstance.showAlert) {
        window.deforestationMapInstance.showAlert(message, type);
    } else {
        alert(message);
    }
}

// Inicializar el modal con método individual por defecto
setInputMethod('single');

// Manejo de importación de archivos
document.getElementById('import-area').addEventListener('change', async function(e) {
    const file = e.target.files[0];
    if (!file) return;

    const ext = file.name.split('.').pop().toLowerCase();

    if (ext === 'geojson' || ext === 'json') {
        const reader = new FileReader();
        reader.onload = function(event) {
            try {
                const geojson = JSON.parse(event.target.result);
                window.deforestationMapInstance.importGeoJSON(geojson);
            } catch (err) {
                window.deforestationMapInstance.showAlert('Archivo GeoJSON inválido.', 'error');
            }
        };
        reader.readAsText(file);
    }
    else if (ext === 'kml') {
        const reader = new FileReader();
        reader.onload = function(event) {
            try {
                const kmlText = event.target.result;
                window.deforestationMapInstance.importKML(kmlText);
            } catch (err) {
                window.deforestationMapInstance.showAlert('Archivo KML inválido.', 'error');
            }
        };
        reader.readAsText(file);
    }
    else if (ext === 'shp' || ext === 'zip') {
        try {
            const arrayBuffer = await file.arrayBuffer();
            shp(arrayBuffer).then(function(geojson) {
                window.deforestationMapInstance.importGeoJSON(geojson);
            }).catch(function() {
                window.deforestationMapInstance.showAlert('Archivo SHP/ZIP inválido.', 'error');
            });
        } catch (err) {
            window.deforestationMapInstance.showAlert('Error al leer el archivo SHP/ZIP.', 'error');
        }
    }
    else {
        window.deforestationMapInstance.showAlert('Formato no soportado.', 'error');
    }
});

// Cerrar modal con tecla Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCoordinateModal();
    }
});

// Event listeners para botones de dibujo y limpieza
document.getElementById('draw-polygon').addEventListener('click', function() {
    if (window.deforestationMapInstance) {
        window.deforestationMapInstance.activateDrawing();
    }
});

document.getElementById('clear-map').addEventListener('click', function() {
    if (window.deforestationMapInstance) {
        window.deforestationMapInstance.clearMap();
    }
});

// Toggle visibilidad de áreas en deforestación
document.getElementById('visibility-toggle-button').addEventListener('click', function() {
    if (window.deforestationMapInstance) {
        window.deforestationMapInstance.toggleGFWVisibility();
        
        // Alternar iconos de ojo abierto/cerrado
        const iconOpen = document.getElementById('icon-eye-open');
        const iconClosed = document.getElementById('icon-eye-closed');
        
        if (iconOpen.classList.contains('hidden')) {
            iconOpen.classList.remove('hidden');
            iconClosed.classList.add('hidden');
        } else {
            iconOpen.classList.add('hidden');
            iconClosed.classList.remove('hidden');
        }
    }
});

// ===== LOADER DURANTE LA CONSULTA =====


function showEnhancedLoader() {
    const loaderOverlay = document.getElementById('loader-overlay');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const progressPercentage = document.getElementById('progress-percentage');
    const timeEstimate = document.getElementById('time-estimate');
    
    // Mostrar loader con animación
    loaderOverlay.classList.remove('hidden');
    
    // Configurar progreso
    let progress = 0;
    const messages = [
        "Preparando análisis...",
        "Calculando área de estudio...", 
        "Consultando datos de deforestación...",
        "Procesando imágenes satelitales...",
        "Generando resultados..."
    ];
    
    window.loaderProgressInterval = setInterval(() => {
        if (progress < 85) { // No pasar del 85% hasta que termine realmente
            progress += (85 / 25); // 25 segundos para llegar al 85%
            progressBar.style.width = `${progress}%`;
            progressPercentage.textContent = `${Math.round(progress)}%`;
            
            // Cambiar mensaje según progreso
            const messageIndex = Math.floor(progress / (100 / messages.length));
            if (messageIndex < messages.length) {
                progressText.textContent = messages[messageIndex];
            }
            
            // Actualizar tiempo estimado
            const remaining = Math.max(1, Math.round((100 - progress) / 3));
            timeEstimate.textContent = `Tiempo estimado: ${remaining} segundos`;
        }
    }, 1000);
}

// Función para ocultar el loader mejorado
function hideEnhancedLoader() {
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const progressPercentage = document.getElementById('progress-percentage');
    const timeEstimate = document.getElementById('time-estimate');
    
    // Completar al 100%
    progressBar.style.width = '100%';
    progressPercentage.textContent = '100%';
    progressText.textContent = '¡Completado!';
    timeEstimate.textContent = 'Finalizando...';
    
    // Limpiar intervalo
    if (window.loaderProgressInterval) {
        clearInterval(window.loaderProgressInterval);
    }
    
    // Ocultar después de un breve delay
    setTimeout(() => {
        document.getElementById('loader-overlay').classList.add('hidden');
        
        // Resetear después de ocultar
        setTimeout(() => {
            progressBar.style.width = '0%';
            progressPercentage.textContent = '0%';
            progressText.textContent = 'Iniciando...';
            timeEstimate.textContent = 'Tiempo estimado: 15-30 segundos';
        }, 500);
    }, 1500);
}

// Manejar el envío del formulario con AJAX
document.getElementById('analysis-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitButton = document.getElementById('submit-button');
    const buttonText = document.getElementById('button-text');
    const spinner = document.getElementById('loading-spinner');
    const resultsDiv = document.getElementById('results');
    
    // Validar que haya un polígono dibujado
    const geometry = document.getElementById('geometry').value;
    if (!geometry) {
        window.deforestationMapInstance.showAlert('Debe dibujar un polígono en el mapa antes de analizar.', 'warning');
        return;
    }
    
    // Mostrar loader
    showEnhancedLoader();
    
    // Deshabilitar el botón y mostrar spinner en el botón
    submitButton.disabled = true;
    spinner.classList.remove('hidden');
    buttonText.textContent = 'Analizando...';
    
    // Recoger datos del formulario
    const formData = new FormData(this);
    
    // Enviar con fetch
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la red');
        }
        return response.json();
    })
    .then(data => {
        // Ocultar loader
        hideEnhancedLoader();
        
        // Habilitar el botón y ocultar spinner en el botón
        submitButton.disabled = false;
        spinner.classList.add('hidden');
        buttonText.textContent = 'Analizar Deforestación';
        
        if (data.success) {
            resultsDiv.innerHTML = `<div class="mt-4 p-4 bg-green-100 text-green-800 rounded-md">${data.message}</div>`;
            
            // Si hay datos de productores, mostrarlos
            if (data.producers && data.producers.length > 0) {
                const producersInfo = document.getElementById('producers-info');
                const producersList = document.getElementById('producers-list');
                
                producersList.innerHTML = '';
                data.producers.forEach(producer => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">${producer.name}</td>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">${producer.location}</td>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">${producer.area_ha.toFixed(2)}</td>
                    `;
                    producersList.appendChild(row);
                });
                
                producersInfo.classList.remove('hidden');
            }
        } else {
            resultsDiv.innerHTML = `<div class="mt-4 p-4 bg-red-100 text-red-800 rounded-md">${data.message}</div>`;
        }
    })
    .catch(error => {
        // Ocultar loader
        hideEnhancedLoader();
        
        // Habilitar el botón y ocultar spinner en el botón
        submitButton.disabled = false;
        spinner.classList.add('hidden');
        buttonText.textContent = 'Analizar Deforestación';
        
        resultsDiv.innerHTML = `<div class="mt-4 p-4 bg-red-100 text-red-800 rounded-md">Error: ${error.message}</div>`;
    });
});

// Función para calcular área en hectáreas desde coordenadas WGS84
function calculateAreaHectares(coordinates) {
    if (!coordinates || coordinates.length < 3) return 0;
    
    try {
        // Usar la fórmula del shoelace para calcular área en metros cuadrados
        let area = 0;
        const n = coordinates.length;
        
        for (let i = 0; i < n; i++) {
            const j = (i + 1) % n;
            const xi = coordinates[i][0];
            const yi = coordinates[i][1];
            const xj = coordinates[j][0];
            const yj = coordinates[j][1];
            
            area += xi * yj - xj * yi;
        }
        
        // El área está en grados², necesitamos convertir a metros²
        // Aproximación: 1 grado ≈ 111,320 metros (para latitud)
        area = Math.abs(area) / 2;
        
        // Conversión más precisa considerando la latitud media
        const avgLat = coordinates.reduce((sum, coord) => sum + coord[1], 0) / n;
        const metersPerDegreeLat = 111320; // metros por grado de latitud
        const metersPerDegreeLon = 111320 * Math.cos(avgLat * Math.PI / 180);
        
        // Convertir a metros cuadrados
        const areaM2 = area * metersPerDegreeLat * metersPerDegreeLon;
        
        // Convertir a hectáreas (1 ha = 10,000 m²)
        const areaHa = areaM2 / 10000;
        
        return Math.abs(areaHa);
    } catch (error) {
        console.error('Error calculando área:', error);
        return 0;
    }
}

// Función para actualizar el display del área


// Función para calcular área desde un polígono OpenLayers
function calculatePolygonArea(feature) {
    if (!feature || !feature.getGeometry()) return 0;
    
    const geometry = feature.getGeometry();
    if (geometry.getType() !== 'Polygon') return 0;
    
    try {
        // Obtener coordenadas en WGS84
        const polygon = geometry.clone().transform('EPSG:3857', 'EPSG:4326');
        const coordinates = polygon.getCoordinates()[0]; // Primer anillo (exterior)
        
        return calculateAreaHectares(coordinates);
    } catch (error) {
        console.error('Error calculando área del polígono:', error);
        return 0;
    }
}
</script>

<style>
/* Estilos para el loader */
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* Estilos para el slider de opacidad */
.slider-thumb::-webkit-slider-thumb {
    appearance: none;
    height: 16px;
    width: 16px;
    border-radius: 50%;
    background: #4f46e5;
    cursor: pointer;
    border: 2px solid #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

.slider-thumb::-moz-range-thumb {
    height: 16px;
    width: 16px;
    border-radius: 50%;
    background: #4f46e5;
    cursor: pointer;
    border: 2px solid #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

/* Estilos adicionales para el loader mejorado */
.backdrop-blur-sm {
    backdrop-filter: blur(4px);
}

.bg-black\/70 {
    background-color: rgba(0, 0, 0, 0.7);
}

.from-green-500 {
    --tw-gradient-from: #10b981;
    --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(16, 185, 129, 0));
}

.to-green-600 {
    --tw-gradient-to: #059669;
}

.via-white\/30 {
    --tw-gradient-stops: var(--tw-gradient-from), rgba(255, 255, 255, 0.3), var(--tw-gradient-to, rgba(255, 255, 255, 0.3));
}

/* Mejorar la legibilidad del texto en polígonos */
.ol-layer canvas {
    font-smooth: always;
    -webkit-font-smoothing: antialiased;
}

/* Asegurar que el texto se renderice correctamente */
.ol-viewport {
    text-rendering: geometricPrecision;
}
</style>
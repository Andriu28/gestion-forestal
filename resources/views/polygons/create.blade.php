{{-- [file name]: create.blade.php --}}
<x-app-layout>
    <div class="mx-auto">
        <div class="bg-stone-100/90 dark:bg-custom-gray shadow-sm sm:rounded-2xl shadow-soft p-4 md:p-6 lg:p-6 mb-6">
            <div class="text-gray-900 dark:text-gray-100">
                <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-200 mb-2 md:mb-2">
                    <i class="fas fa-draw-polygon mr-2"></i> Crear Nuevo Polígono
                </h2>

                <form action="{{ route('polygons.store') }}" method="POST" id="polygon-form" novalidate>
                    @csrf

                    <div class="grid grid-cols-1  gap-6">
                        <!-- Columna del Mapa -->
                        <div>
                            <x-input-label for="map" />
                            <div class="relative rounded-lg overflow-hidden mb-6 border border-gray-200 dark:border-gray-700 mt-1" style="height: 70vh;  border: 1px solid #dededeff; border-radius: 0.5rem; position: relative;">
                                <div id="map" class="h-full w-full"></div>

                                <!-- Controles del mapa (similares a deforestación) -->
                                <div id="map-controls" class="absolute top-4 right-4 z-50 flex flex-col space-y-2">
                                    <div class="flex space-x-2">
                                        <!-- Botón para mostrar/ocultar polígono -->
                                        <div class="relative">
                                            <button id="visibility-toggle-button" class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                                <span id="icon-eye-open">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye w-6 h-6">
                                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                                        <circle cx="12" cy="12" r="3"/>
                                                    </svg>
                                                </span>
                                                <span id="icon-eye-closed" class="hidden">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye-off w-6 h-6">
                                                        <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                                        <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                                                        <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                                                        <line x1="2" x2="22" y1="2" y2="22"/>
                                                    </svg>
                                                </span>
                                            </button>
                                        </div>

                                        <!-- Contenedor para controles de opacidad -->
                                        <div class="relative">
                                            <button id="opacity-control-button" title="Ajustar Opacidad" class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layers w-6 h-6">
                                                    <path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/>
                                                    <path d="m22 17.46-8.58-3.91a2 2 0 0 0-1.66 0L3 17.46"/>
                                                    <path d="m22 12.46-8.58-3.91a2 2 0 0 0-1.66 0L3 12.46"/>
                                                </svg>
                                            </button>
                                            
                                            <div id="opacity-control-panel" 
                                                class="absolute mt-3 w-48 rounded-xl shadow-lg bg-gray-50 dark:bg-custom-gray ring-1 ring-black ring-opacity-5 z-10 
                                                transition-all duration-400 ease-out scale-95 opacity-0 pointer-events-none hidden"
                                                style="right: -97px;">
                                                <div class="absolute -top-2 right-6 w-[6.4rem] h-2 z-100 pointer-events-none">
                                                    <svg viewBox="0 0 16 8" class="w-4 h-2 text-white dark:text-custom-gray">
                                                        <polygon points="8,0 16,8 0,8" fill="currentColor"/>
                                                    </svg>
                                                </div>
                                                
                                                <div class="p-4 z-100">
                                                    <div class="flex items-center justify-between mb-2">
                                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Opacidad Polígono</span>
                                                        <span id="opacity-value" class="text-xs font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">75%</span>
                                                    </div>
                                                    
                                                    <input type="range" 
                                                        id="opacity-slider" 
                                                        min="0" 
                                                        max="100" 
                                                        value="75"
                                                        class="w-full h-2 bg-gray-200 dark:bg-gray-600 rounded-lg appearance-none cursor-pointer slider-thumb">
                                                    
                                                    <div class="flex space-x-2 mt-3">
                                                        <button type="button" data-opacity="25" class="flex-1 py-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-xs">
                                                            25%
                                                        </button>
                                                        <button type="button" data-opacity="50" class="flex-1 py-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-xs">
                                                            50%
                                                        </button>
                                                        <button type="button" data-opacity="75" class="flex-1 py-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-xs">
                                                            75%
                                                        </button>
                                                        <button type="button" data-opacity="100" class="flex-1 py-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-xs">
                                                            100%
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Cambiar Mapa -->
                                        <div class="relative">
                                            <button id="base-map-toggle" title="Cambiar mapa" class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                                </svg>
                                                Mapas
                                            </button>
                                            
                                            <div id="base-map-menu"
                                                class="absolute mt-3 w-40 rounded-xl shadow-lg bg-gray-100 dark:bg-custom-gray ring-1 ring-black ring-opacity-5 z-10 right-0
                                                        transition-all duration-400 ease-out scale-95 opacity-0 pointer-events-none hidden">
                                                <div class="absolute -top-2 right-6 w-8 h-2 pointer-events-none">
                                                    <svg viewBox="0 0 16 8" class="w-4 h-2 text-white dark:text-custom-gray">
                                                        <polygon points="8,0 16,8 0,8" fill="currentColor"/>
                                                    </svg>
                                                </div>
                                                <div class="py-2" role="menu" aria-orientation="vertical">
                                                    <button data-layer="osm" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">OpenStreetMap</button>
                                                    <button data-layer="satellite" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">Satélite Esri</button>
                                                    <button data-layer="maptiler_satellite" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">MapTiler Satélite</button>
                                                    <button data-layer="terrain" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">Relieve</button>
                                                    <button data-layer="dark" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">Oscuro</button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Pantalla Completa -->
                                        <button id="fullscreen-toggle" title="Pantalla Completa" class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Segunda fila de controles -->
                                    <div class="flex space-x-2">
                                        <!-- Coordenadas Manuales -->
                                        <button id="manual-polygon-toggle" title="Escribir Coordenadas" class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-line-icon w-6 h-6">
                                                <path d="M13 21h8"/>
                                                <path d="m15 5 4 4"/>
                                                <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>
                                            </svg>
                                        </button>

                                        <!-- Dibujar Polígono -->
                                        <button type="button" id="draw-polygon" class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-line-icon w-6 h-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            Dibujar
                                        </button>

                                        <!-- Limpiar Mapa -->
                                        <button type="button" id="clear-map" class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-line-icon w-6 h-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Limpiar
                                        </button>
                                    </div>
                                </div>

                                <!-- Coordenadas en tiempo real -->
                                <div class="absolute left-1/2 bottom-4 transform -translate-x-1/2 z-40 bg-white/90 dark:bg-gray-800 p-2 rounded text-sm shadow">
                                    <span id="coordinates-display">Lat: 0.000000 | Lng: 0.000000</span>
                                </div>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('geometry')" />
                        </div>

                        <!-- Columna del Formulario -->
                        <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden sm:rounded-2xl p-4 md:p-6 lg:p-8">
                            <div class="text-gray-900 dark:text-gray-100">
                                <h2 class="text-lg font-semibold mb-4">Datos del Polígono</h2>
                                
                                <div class="space-y-6">
                                    <div>
                                        <x-input-label for="name" :value="__('Nombre del Polígono *')" />
                                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                            :value="old('name')" required autofocus placeholder="Ej: Finca La Esperanza" />
                                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                                    </div>

                                    <div>
                                        <x-input-label for="description" :value="__('Descripción')" />
                                        <textarea id="description" name="description" rows="3"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                            placeholder="Descripción del polígono...">{{ old('description') }}</textarea>
                                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                                    </div>

                                    <div>
                                        <x-input-label for="producer_id" :value="__('Productor (Opcional)')" />
                                        <select id="producer_id" name="producer_id"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            <option value="">Seleccione un productor</option>
                                            @foreach($producers as $producer)
                                                <option value="{{ $producer->id }}" {{ old('producer_id') == $producer->id ? 'selected' : '' }}>
                                                    {{ $producer->name }} {{ $producer->lastname }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <x-input-error class="mt-2" :messages="$errors->get('producer_id')" />
                                    </div>

                                    <div>
                                        <x-input-label for="parish_id" :value="__('Parroquia (Opcional)')" />
                                        <select id="parish_id" name="parish_id"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            <option value="">Seleccione una parroquia</option>
                                            @foreach($parishes as $parish)
                                                <option value="{{ $parish->id }}" {{ old('parish_id') == $parish->id ? 'selected' : '' }}>
                                                    {{ $parish->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <x-input-error class="mt-2" :messages="$errors->get('parish_id')" />
                                    </div>

                                    <div>
                                        <x-input-label for="area_ha" :value="__('Área en Hectáreas')" />
                                        <x-text-input id="area_ha" name="area_ha" type="number" step="0.01"
                                            class="mt-1 block w-full" :value="old('area_ha')" placeholder="Se calculará automáticamente" />
                                        <x-input-error class="mt-2" :messages="$errors->get('area_ha')" />
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Dejar vacío para calcular automáticamente desde el mapa</p>
                                    </div>

                                    <!-- Campos ocultos para la detección y el envío -->
                                    <input type="hidden" id="geometry" name="geometry" value="{{ old('geometry', '') }}" required>
                                    <input type="hidden" id="detected_parish" name="detected_parish" value="{{ old('detected_parish') }}">
                                    <input type="hidden" id="detected_municipality" name="detected_municipality" value="{{ old('detected_municipality') }}">
                                    <input type="hidden" id="detected_state" name="detected_state" value="{{ old('detected_state') }}">
                                    <input type="hidden" id="centroid_lat" name="centroid_lat" value="{{ old('centroid_lat') }}">
                                    <input type="hidden" id="centroid_lng" name="centroid_lng" value="{{ old('centroid_lng') }}">
                                    <input type="hidden" id="location_data" name="location_data" value="{{ old('location_data') }}">

                                    <div id="location-info" class="p-4 bg-blue-50 dark:bg-blue-900 rounded-lg hidden">
                                        <h3 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">📍 Ubicación detectada</h3>
                                        <div class="text-sm space-y-1">
                                            <div><strong>Parroquia:</strong> <span id="detected-parish-text">-</span></div>
                                            <div><strong>Municipio:</strong> <span id="detected-municipality-text">-</span></div>
                                            <div><strong>Estado:</strong> <span id="detected-state-text">-</span></div>
                                            <div><strong>Coordenadas:</strong> <span id="detected-coords-text">-</span></div>
                                        </div>
                                    </div>

                                    <!-- Botón de detección de ubicación -->
                                    <div class="pt-4">
                                        <button type="button" id="detect-location" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300" disabled>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                            </svg>
                                            <span id="detect-button-text">Detectar Ubicación</span>
                                        </button>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Dibuja un polígono primero para habilitar la detección automática
                                        </p>
                                    </div>

                                    <!-- Botones de acción -->
                                    <div class="flex items-center justify-end space-x-4 pt-6">
                                        <x-go-back-button />
                                        <x-primary-button type="submit" id="submit-btn" class="bg-green-600 hover:bg-green-700">
                                            Crear Polígono
                                        </x-primary-button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para coordenadas manuales (similar a deforestación) -->
    <div id="manual-polygon-modal" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-white dark:bg-custom-gray rounded-xl shadow-2xl w-full max-w-lg mx-4">
            <!-- Header -->
            <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Ingresar Coordenadas UTM</h3>
                <button id="close-modal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
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
                        placeholder="Ejemplo:&#10;Zona,Hemisferio,Este,Norte&#10;20,N,476097.904,1157477.299&#10;20,N,476181.804,1157432.362&#10;20,N,475211.522,1157534.959"></textarea>
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
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

<!-- Estilos y librerías -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.8.0/proj4.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let map = L.map('map').setView([8.0, -66.0], 6);
    
    // Capas base similares a deforestación
    const baseLayers = {
        osm: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { 
            attribution: '© OpenStreetMap contributors' 
        }),
        satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: '© Esri'
        }),
        maptiler_satellite: L.tileLayer('https://api.maptiler.com/maps/satellite/{z}/{x}/{y}.jpg?key={key}', {
            attribution: '© MapTiler',
            key: 'your_maptiler_key' // Reemplazar con tu clave si tienes
        }),
        terrain: L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenTopoMap'
        }),
        dark: L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '© CARTO'
        })
    };
    
    // Añadir capa por defecto
    baseLayers.osm.addTo(map);

    const drawnItems = new L.FeatureGroup().addTo(map);
    let currentPolygonLayer = null;
    
    // Configurar controles de dibujo
    const drawControl = new L.Control.Draw({
        draw: {
            polygon: { 
                allowIntersection: false, 
                showArea: true, 
                shapeOptions: { 
                    color: '#2b6cb0', 
                    fillColor: '#2b6cb0', 
                    fillOpacity: 0.25, 
                    weight: 3 
                } 
            },
            polyline: false, 
            rectangle: false, 
            circle: false, 
            circlemarker: false, 
            marker: false
        },
        edit: { featureGroup: drawnItems }
    });

    map.addControl(drawControl);

    // Referencias a elementos
    const drawBtn = document.getElementById('draw-polygon');
    const detectBtn = document.getElementById('detect-location');
    const clearBtn = document.getElementById('clear-map');
    const geometryInput = document.getElementById('geometry');
    const coordsDisplay = document.getElementById('coordinates-display');
    const visibilityToggle = document.getElementById('visibility-toggle-button');
    const opacitySlider = document.getElementById('opacity-slider');
    const opacityValue = document.getElementById('opacity-value');
    const opacityButtons = document.querySelectorAll('[data-opacity]');

    // Variables para control de visibilidad
    let isPolygonVisible = true;

    function showMessage(text, type = 'info') {
        const el = coordsDisplay;
        el.textContent = text;
        el.classList.toggle('text-red-600', type === 'error');
        el.classList.toggle('text-green-700', type === 'success');
        setTimeout(() => {
            if (!geometryInput.value) el.textContent = 'Lat: 0.000000 | Lng: 0.000000';
            el.classList.remove('text-red-600', 'text-green-700');
        }, 3000);
    }

    function setFeatureToInput(feature) {
        if (!feature) {
            geometryInput.value = '';
            return;
        }
        try {
            geometryInput.value = JSON.stringify(feature);
        } catch (e) {
            geometryInput.value = '';
            console.error('Error serializing feature', e);
        }
    }

    function calcCentroidFromFeature(feature) {
        try {
            const coords = feature.geometry.coordinates;
            let ring = null;
            if (feature.geometry.type === 'Polygon') ring = coords[0];
            else if (feature.geometry.type === 'MultiPolygon') ring = coords[0][0];
            if (!ring || ring.length === 0) return null;
            let latSum = 0, lngSum = 0, count = 0;
            ring.forEach(pt => { lngSum += pt[0]; latSum += pt[1]; count++; });
            return { lat: latSum / count, lng: lngSum / count };
        } catch (e) {
            return null;
        }
    }

    // Función para calcular área
    function calculateArea(feature) {
        if (!feature || !feature.geometry) return 0;
        
        const coords = feature.geometry.coordinates[0];
        if (!coords || coords.length < 3) return 0;
        
        let area = 0;
        const n = coords.length;
        
        for (let i = 0; i < n; i++) {
            const j = (i + 1) % n;
            const xi = coords[i][1]; // latitud
            const yi = coords[i][0]; // longitud
            const xj = coords[j][1];
            const yj = coords[j][0];
            
            area += xi * yj - xj * yi;
        }
        
        area = Math.abs(area) / 2;
        
        // Conversión aproximada a hectáreas
        // 1 grado² ≈ 12,383 km² en el ecuador, menos hacia los polos
        const avgLat = coords.reduce((sum, coord) => sum + coord[1], 0) / n;
        const kmPerDegreeLat = 111.32;
        const kmPerDegreeLon = 111.32 * Math.cos(avgLat * Math.PI / 180);
        
        const areaKm2 = area * kmPerDegreeLat * kmPerDegreeLon;
        const areaHa = areaKm2 * 100; // 1 km² = 100 ha
        
        return Math.round(areaHa * 100) / 100; // Redondear a 2 decimales
    }

    // Actualizar área en el formulario
    function updateArea(feature) {
        const areaHaInput = document.getElementById('area_ha');
        if (feature) {
            const area = calculateArea(feature);
            areaHaInput.value = area;
        } else {
            areaHaInput.value = '';
        }
    }

    // Dibujar polígono desde coordenadas UTM
    function drawUTMPolygon(utmCoordinates) {
        if (!utmCoordinates || utmCoordinates.length < 3) {
            showMessage('Se necesitan al menos 3 coordenadas', 'error');
            return;
        }
        
        try {
            // Convertir coordenadas UTM a WGS84
            const wgs84Coords = utmCoordinates.map(coord => {
                const [easting, northing, zone, hemisphere] = coord;
                const epsgCode = hemisphere === 'N' ? `EPSG:326${zone}` : `EPSG:327${zone}`;
                
                if (!proj4.defs(epsgCode)) {
                    const proj4String = `+proj=utm +zone=${zone} +${hemisphere === 'S' ? '+south ' : ''}datum=WGS84 +units=m +no_defs`;
                    proj4.defs(epsgCode, proj4String);
                }
                
                const wgs84 = proj4('WGS84');
                const utm = proj4(epsgCode);
                const point = proj4(utm, wgs84, [easting, northing]);
                
                return [point[0], point[1]]; // [lng, lat]
            });
            
            // Crear polígono cerrado
            if (wgs84Coords[0][0] !== wgs84Coords[wgs84Coords.length-1][0] || 
                wgs84Coords[0][1] !== wgs84Coords[wgs84Coords.length-1][1]) {
                wgs84Coords.push(wgs84Coords[0]);
            }
            
            // Limpiar mapa existente
            drawnItems.clearLayers();
            
            // Crear y añadir polígono
            const polygon = L.polygon(wgs84Coords, {
                color: '#2b6cb0',
                fillColor: '#2b6cb0',
                fillOpacity: 0.25,
                weight: 3
            }).addTo(drawnItems);
            
            // Ajustar vista
            map.fitBounds(polygon.getBounds());
            
            // Crear feature GeoJSON
            const feature = {
                type: 'Feature',
                geometry: {
                    type: 'Polygon',
                    coordinates: [wgs84Coords]
                },
                properties: {}
            };
            
            setFeatureToInput(feature);
            updateArea(feature);
            detectBtn.disabled = false;
            
            // Actualizar coordenadas del centroide
            const centroid = calcCentroidFromFeature(feature);
            if (centroid) {
                coordsDisplay.textContent = `Lat: ${centroid.lat.toFixed(6)} | Lng: ${centroid.lng.toFixed(6)}`;
            }
            
            currentPolygonLayer = polygon;
            showMessage('Polígono dibujado desde coordenadas UTM', 'success');
            
        } catch (error) {
            console.error('Error dibujando polígono UTM:', error);
            showMessage('Error dibujando polígono', 'error');
        }
    }

    // Event Listeners para controles de mapa
    drawBtn.addEventListener('click', () => {
        new L.Draw.Polygon(map, drawControl.options.draw.polygon).enable();
    });

    clearBtn.addEventListener('click', () => {
        drawnItems.clearLayers();
        geometryInput.value = '';
        detectBtn.disabled = true;
        document.getElementById('location-info').classList.add('hidden');
        document.getElementById('area_ha').value = '';
        coordsDisplay.textContent = 'Lat: 0.000000 | Lng: 0.000000';
        currentPolygonLayer = null;
        showMessage('Mapa limpiado', 'info');
    });

    // Toggle visibilidad del polígono
    visibilityToggle.addEventListener('click', () => {
        if (currentPolygonLayer) {
            isPolygonVisible = !isPolygonVisible;
            if (isPolygonVisible) {
                map.addLayer(currentPolygonLayer);
                document.getElementById('icon-eye-open').classList.remove('hidden');
                document.getElementById('icon-eye-closed').classList.add('hidden');
            } else {
                map.removeLayer(currentPolygonLayer);
                document.getElementById('icon-eye-open').classList.add('hidden');
                document.getElementById('icon-eye-closed').classList.remove('hidden');
            }
        }
    });

    // Control de opacidad
    opacitySlider.addEventListener('input', (e) => {
        const value = parseInt(e.target.value);
        opacityValue.textContent = `${value}%`;
        
        if (currentPolygonLayer) {
            currentPolygonLayer.setStyle({ fillOpacity: value / 100 });
        }
    });

    opacityButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const value = parseInt(btn.dataset.opacity);
            opacitySlider.value = value;
            opacityValue.textContent = `${value}%`;
            
            if (currentPolygonLayer) {
                currentPolygonLayer.setStyle({ fillOpacity: value / 100 });
            }
        });
    });

    // Evento de creación de polígono
    map.on(L.Draw.Event.CREATED, function (e) {
        const layer = e.layer;
        drawnItems.clearLayers();
        drawnItems.addLayer(layer);

        const feature = layer.toGeoJSON();
        setFeatureToInput(feature);
        updateArea(feature);

        detectBtn.disabled = false;
        const centroid = calcCentroidFromFeature(feature);
        if (centroid) coordsDisplay.textContent = `Lat: ${centroid.lat.toFixed(6)} | Lng: ${centroid.lng.toFixed(6)}`;
        
        currentPolygonLayer = layer;
    });

    map.on('mousemove', (e) => {
        if (!geometryInput.value) {
            coordsDisplay.textContent = `Lat: ${e.latlng.lat.toFixed(6)} | Lng: ${e.latlng.lng.toFixed(6)}`;
        }
    });

    // Detectar ubicación
    // Detectar ubicación
    detectBtn.addEventListener('click', async () => {
        const val = geometryInput.value;
        if (!val) { showMessage('Dibuja un polígono primero', 'error'); return; }

        let feature;
        try { feature = JSON.parse(val); } catch (e) { showMessage('GeoJSON inválido', 'error'); return; }

        const centroid = calcCentroidFromFeature(feature);
        if (!centroid) { showMessage('No se pudo calcular centroid', 'error'); return; }

        document.getElementById('centroid_lat').value = centroid.lat;
        document.getElementById('centroid_lng').value = centroid.lng;

        detectBtn.disabled = true;
        const detectButtonText = document.getElementById('detect-button-text');
        const originalText = detectButtonText.textContent;
        detectButtonText.textContent = 'Detectando...';

        try {
            const resp = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${centroid.lat}&lon=${centroid.lng}&zoom=18&addressdetails=1&accept-language=es`, {
                headers: { 'User-Agent': 'PolygonApp/1.0' }
            });
            if (!resp.ok) throw new Error('Error en Nominatim');
            const data = await resp.json();
            document.getElementById('location_data').value = JSON.stringify(data);

            const address = data.address || {};
            const municipality = address.county || address.suburb || address.village || address.town || address.city || '';
            const parish = address.municipality || address.county || address.city || '';
            const state = address.state || address.region || '';

            // Limpiar prefijos de las variables
            const cleanParish = parish ? parish.toString().replace('Parroquia ', '').replace('Municipio ', '').trim() : '';
            const cleanMunicipality = municipality ? municipality.toString().replace('Municipio ', '').trim() : '';
            const cleanState = state ? state.toString().replace('Estado ', '').trim() : '';

            // CORRECCIÓN: Asignar las variables LIMPIAS a los campos ocultos
            document.getElementById('detected_parish').value = cleanParish;
            document.getElementById('detected_municipality').value = cleanMunicipality;
            document.getElementById('detected_state').value = cleanState;

            // Actualizar la interfaz con los valores limpios
            document.getElementById('detected-parish-text').textContent = cleanParish || 'No detectado';
            document.getElementById('detected-municipality-text').textContent = cleanMunicipality || 'No detectado';
            document.getElementById('detected-state-text').textContent = cleanState || 'No detectado';
            document.getElementById('detected-coords-text').textContent = `${centroid.lat.toFixed(6)}, ${centroid.lng.toFixed(6)}`;

            document.getElementById('location-info').classList.remove('hidden');

            // Intentar asignar parroquia automáticamente con los valores limpios
            try {
                const assignResp = await fetch('{{ route("polygons.find-parish-api") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    // CORRECCIÓN: Usar los valores limpios
                    body: JSON.stringify({
                        parish_name: cleanParish ? cleanParish.toLowerCase().trim() : '',
                        municipality_name: cleanMunicipality ? cleanMunicipality.toLowerCase().trim() : '',
                        state_name: cleanState ? cleanState.toLowerCase().trim() : ''
                    })
                });
                const assignJson = await assignResp.json();
                if (assignJson.success && assignJson.parish) {
                    document.getElementById('parish_id').value = assignJson.parish.id;
                    showMessage('Parroquia encontrada y asignada', 'success');
                } else {
                    showMessage('No se encontró parroquia exacta. Selecciona manualmente.', 'info');
                }
            } catch (e) {
                console.warn('Asignación parroquia fallida', e);
            }
        } catch (err) {
            console.error(err);
            showMessage('Error detectando ubicación', 'error');
        } finally {
            detectBtn.disabled = false;
            detectButtonText.textContent = originalText;
        }
    });

    // Cambiar capa base
    document.querySelectorAll('#base-map-menu button').forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const layerKey = this.getAttribute('data-layer');
            
            // Remover todas las capas base
            Object.values(baseLayers).forEach(layer => {
                if (map.hasLayer(layer)) {
                    map.removeLayer(layer);
                }
            });
            
            // Añadir nueva capa
            baseLayers[layerKey].addTo(map);
            
            // Actualizar botón
            const layerNames = {
                'osm': 'OpenStreetMap',
                'satellite': 'Satélite Esri', 
                'maptiler_satellite': 'MapTiler Satélite',
                'terrain': 'Relieve',
                'dark': 'Oscuro'
            };
            
            const button = document.getElementById('base-map-toggle');
            const svg = button.querySelector('svg').cloneNode(true);
            button.innerHTML = '';
            button.appendChild(svg);
            button.appendChild(document.createTextNode(' ' + layerNames[layerKey]));
            
            // Cerrar menú
            closeMenu('base-map-menu');
        });
    });

    // Función para abrir/cerrar menús
    function toggleMenu(menuId, show) {
        const menu = document.getElementById(menuId);
        
        if (show) {
            menu.classList.remove('hidden');
            void menu.offsetWidth;
            menu.classList.remove('scale-95', 'opacity-0', 'pointer-events-none');
            menu.classList.add('scale-100', 'opacity-100', 'pointer-events-auto', 'show');
        } else {
            menu.classList.remove('scale-100', 'opacity-100', 'pointer-events-auto', 'show');
            menu.classList.add('scale-95', 'opacity-0', 'pointer-events-none');
            
            setTimeout(() => {
                if (menu.classList.contains('scale-95')) {
                    menu.classList.add('hidden');
                }
            }, 400);
        }
    }

    function closeMenu(menuId) {
        const menu = document.getElementById(menuId);
        menu.classList.remove('scale-100', 'opacity-100', 'pointer-events-auto', 'show');
        menu.classList.add('scale-95', 'opacity-0', 'pointer-events-none');
        
        setTimeout(() => {
            if (menu.classList.contains('scale-95')) {
                menu.classList.add('hidden');
            }
        }, 400);
    }

    // Cerrar menús al hacer clic fuera
    document.addEventListener('click', function(e) {
        const baseMapToggle = document.getElementById('base-map-toggle');
        const baseMapMenu = document.getElementById('base-map-menu');
        const opacityButton = document.getElementById('opacity-control-button');
        const opacityPanel = document.getElementById('opacity-control-panel');
        const modal = document.getElementById('manual-polygon-modal');
        
        if (modal.classList.contains('hidden')) {
            if (!baseMapToggle.contains(e.target) && !baseMapMenu.contains(e.target)) {
                closeMenu('base-map-menu');
            }
            
            if (!opacityButton.contains(e.target) && !opacityPanel.contains(e.target)) {
                toggleOpacityPanel(false);
            }
        }
    });

    // Panel de opacidad
    function toggleOpacityPanel(show) {
        const panel = document.getElementById('opacity-control-panel');
        
        if (show) {
            panel.classList.remove('hidden');
            void panel.offsetWidth;
            panel.classList.remove('scale-95', 'opacity-0', 'pointer-events-none');
            panel.classList.add('scale-100', 'opacity-100', 'pointer-events-auto', 'show');
        } else {
            panel.classList.remove('scale-100', 'opacity-100', 'pointer-events-auto', 'show');
            panel.classList.add('scale-95', 'opacity-0', 'pointer-events-none');
            
            setTimeout(() => {
                if (panel.classList.contains('scale-95')) {
                    panel.classList.add('hidden');
                }
            }, 400);
        }
    }

    document.getElementById('opacity-control-button').addEventListener('click', function(e) {
        e.stopPropagation();
        const panel = document.getElementById('opacity-control-panel');
        const isShowing = panel.classList.contains('show');
        
        closeMenu('base-map-menu');
        toggleOpacityPanel(!isShowing);
    });

    // Pantalla completa
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

    // Validación del formulario
    document.getElementById('polygon-form').addEventListener('submit', function (e) {
        const val = geometryInput.value;
        if (!val) {
            e.preventDefault();
            showMessage('❌ Debes dibujar un polígono en el mapa', 'error');
            return false;
        }
        
        const nameInput = document.getElementById('name');
        if (!nameInput.value.trim()) {
            e.preventDefault();
            nameInput.focus();
            showMessage('❌ El nombre del polígono es requerido', 'error');
            return false;
        }
        
        try {
            const parsed = JSON.parse(val);
            const feature = (parsed.type && parsed.type === 'Feature') ? parsed : { type: 'Feature', geometry: parsed };
            const geom = feature.geometry;
            if (!geom || !geom.type || !['Polygon', 'MultiPolygon'].includes(geom.type)) {
                e.preventDefault();
                showMessage('❌ La geometría debe ser Polygon o MultiPolygon', 'error');
                return false;
            }
            
            // Mostrar carga
            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Creando...';
            
            return true;
        } catch (err) {
            e.preventDefault();
            showMessage('❌ Geometría inválida (JSON)', 'error');
            return false;
        }
    });
});

// ===== CÓDIGO PARA EL MODAL DE COORDENADAS MANUALES =====
// (Mismo código que en deforestación, ajustado para este contexto)

let coordinatesList = [];

function setupUTMProjection(zone, hemisphere) {
    const epsgCode = hemisphere === 'N' ? `EPSG:326${zone}` : `EPSG:327${zone}`;
    
    if (!proj4.defs(epsgCode)) {
        const proj4String = `+proj=utm +zone=${zone} +${hemisphere === 'S' ? '+south ' : ''}datum=WGS84 +units=m +no_defs`;
        proj4.defs(epsgCode, proj4String);
    }
    
    return epsgCode;
}

function validateUTMCoordinates(zone, hemisphere, easting, northing) {
    if (zone < 1 || zone > 60) {
        return 'Zona UTM debe estar entre 1 y 60';
    }
    
    if (hemisphere !== 'N' && hemisphere !== 'S') {
        return 'Hemisferio debe ser N (Norte) o S (Sur)';
    }
    
    if (easting < 0 || easting > 1000000) {
        return 'Este (Easting) debe estar entre 0 y 1,000,000';
    }
    
    if (hemisphere === 'N') {
        if (northing < 0 || northing > 10000000) {
            return 'Norte (Northing) en hemisferio Norte debe estar entre 0 y 10,000,000';
        }
    } else {
        if (northing < 1000000 || northing > 10000000) {
            return 'Norte (Northing) en hemisferio Sur debe estar entre 1,000,000 y 10,000,000';
        }
    }
    
    return null;
}

// ... (resto del código del modal igual que en deforestación)
// Solo cambiar las referencias de showAlert para usar la función local

function showAlert(message, type) {
    const el = document.getElementById('coordinates-display');
    el.textContent = message;
    el.classList.toggle('text-red-600', type === 'error' || type === 'warning');
    el.classList.toggle('text-green-700', type === 'success');
    setTimeout(() => {
        el.classList.remove('text-red-600', 'text-green-700');
    }, 3000);
}

// Inicialización del modal (igual que en deforestación)
document.addEventListener('DOMContentLoaded', function() {
    // ... código del modal igual que en deforestación
    // Solo asegurar que las funciones de dibujo llamen a drawUTMPolygon
});
</script>

<style>
/* Estilos similares a deforestación */
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

#manual-polygon-modal {
    transition: opacity 0.3s ease;
}

#manual-polygon-modal.closing {
    opacity: 0;
}

#icon-eye-open, #icon-eye-closed {
    transition: opacity 0.3s ease;
}



/* Estilos para controles de mapa */
#map-controls {
    pointer-events: auto;
}

.absolute {
    position: absolute;
}

.z-50 {
    z-index: 50;
}

.bg-white\/90 {
    background-color: rgba(255, 255, 255, 0.9);
}

.dark\:bg-gray-800 {
    @media (prefers-color-scheme: dark) {
        background-color: #1f2937;
    }
}

/* Animaciones suaves */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

.duration-300 {
    transition-duration: 300ms;
}

/* Sombras y bordes */
.shadow-lg {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

</style>
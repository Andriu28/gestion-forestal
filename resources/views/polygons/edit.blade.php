<x-app-layout>
    <div class="mx-auto ">
        <div class="bg-stone-100/90 dark:bg-custom-gray shadow-sm sm:rounded-2xl shadow-soft p-4 md:p-6 lg:p-6 mb-6">
            <div class="text-gray-900 dark:text-gray-100">
                <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-200 mb-2 md:mb-2">
                   Editar Polígono: {{ $polygon->name }}
                </h2>

                <form action="{{ route('polygons.update', $polygon) }}" method="POST" id="polygon-form" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                        <!-- Columna del Mapa (ocupa 2/3 en pantallas grandes) -->
                        <div class="lg:col-span-3">
                            <x-input-label for="map" class="sr-only">Mapa</x-input-label>
                            <div class="relative rounded-lg overflow-hidden mb-6 border border-gray-200 dark:border-gray-700 mt-1" style="height: 77vh; border: 1px solid #dededeff; border-radius: 0.5rem; position: relative;">
                                <div id="map" class="h-full w-full"></div>

                                <!-- Controles del mapa - CON BOTÓN DE EDICIÓN -->
                                <div id="map-controls">
                                    <div class="flex flex-col items-end space-y-2">
                                        <!-- Fila superior: Cambiar Mapa y Pantalla Completa -->
                                        <div class="flex space-x-2">
                                            <!-- Botón para cambiar mapa base -->
                                            <!-- Botón de edición (se activará cuando haya un polígono) -->
                                            <button type="button" id="edit-polygon" title="Editar Polígono" class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg hidden">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-edit w-6 h-6">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                                </svg>
                                                
                                            </button>

                                            <div class="relative">

                                                <button type="button" id="base-map-toggle" title="Cambiar mapa" class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                                    </svg>
                                                    Mapas
                                                </button>
                                                
                                                <!-- Menú de cambio de mapa -->
                                                <div id="base-map-menu"
                                                    class="absolute mt-3 w-40 rounded-xl shadow-lg bg-gray-50 dark:bg-custom-gray ring-1 ring-black ring-opacity-5 z-10 
                                                            transition-all duration-400 ease-out scale-95 opacity-0 pointer-events-none hidden"
                                                    style="right: 1px;">
                                                    <!-- Flechita -->
                                                    <div class="absolute -top-2 right-6 w-8 h-2 pointer-events-none">
                                                        <svg viewBox="0 0 16 8" class="w-4 h-2 text-white dark:text-custom-gray">
                                                            <polygon points="8,0 16,8 0,8" fill="currentColor"/>
                                                        </svg>
                                                    </div>
                                                    <!-- Menú -->
                                                    <div class="py-2" role="menu" aria-orientation="vertical">
                                                        <button data-layer="osm" type="button" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">OpenStreetMap</button>
                                                        <button data-layer="satellite" type="button" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">Satélite Esri</button>
                                                        <button data-layer="maptiler_satellite" type="button" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">MapTiler Satélite</button>
                                                        <button data-layer="terrain" type="button" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">Relieve</button>
                                                        <button data-layer="dark" type="button" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">Oscuro</button>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Botón de pantalla completa -->
                                            <button type="button" id="fullscreen-toggle" title="Pantalla Completa" class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        
                                        <!-- Fila media: Botones de acción principal -->
                                        <div class="flex space-x-2">
                                            <button type="button" id="manual-polygon-toggle" title="Escribir Coordenadas" class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-line-icon w-6 h-6 lucide-pencil-line">
                                                    <path d="M13 21h8"/>
                                                    <path d="m15 5 4 4"/>
                                                    <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>
                                                </svg>
                                            </button>
                                        </div>
                                        <!-- Botón de  -->
                                        <div class="flex space-x-2">
                                            <button type="button" id="draw-polygon" title="Dibujar Nuevo Polígono" class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-line-icon w-6 h-6 lucide-plus">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                                
                                            </button>
                                        </div>
                                        
                                        <!-- Fila inferior: Botones de limpieza -->
                                        <div class="flex space-x-2">
                                            <button type="button" id="clear-map" title="Limpiar Mapa" class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-line-icon w-6 h-6 lucide-clear">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Display de coordenadas -->
                                <div class="absolute bottom-3 left-3 z-10">
                                    <div id="coordinate-display" class="bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 px-3 py-2 rounded-lg shadow-lg text-sm font-mono hidden">
                                        Lat: 0.000000, Lng: 0.000000
                                    </div>
                                </div>

                                <!-- Información del punto seleccionado (para edición) -->
                                <div id="point-info" class="absolute top-3 left-3 z-10 bg-white dark:bg-gray-800 p-3 rounded-lg shadow-lg max-w-xs hidden">
                                    <h4 class="font-bold text-gray-900 dark:text-white mb-2">Punto seleccionado</h4>
                                    <div class="space-y-1 text-sm">
                                        <div><span class="font-semibold">Lat:</span> <span id="selected-lat">0.000000</span></div>
                                        <div><span class="font-semibold">Lng:</span> <span id="selected-lng">0.000000</span></div>
                                        <div><span class="font-semibold">Índice:</span> <span id="selected-index">-</span></div>
                                    </div>
                                    <div class="mt-3 space-y-2">
                                        <button type="button" id="delete-point" class="w-full bg-red-600 hover:bg-red-700 text-white py-1 px-3 rounded text-sm">
                                            Eliminar punto
                                        </button>
                                        <button type="button" id="update-point" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-1 px-3 rounded text-sm">
                                            Actualizar coordenadas
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('geometry')" />
                        </div>

                        <!-- Panel lateral de puntos del polígono (ocupa 1/3 en pantallas grandes) -->
                        <div class="lg:col-span-1">
                            <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden sm:rounded-2xl h-full">
                                <div class="text-gray-900 dark:text-gray-100 h-full flex flex-col">
                                    <h2 class="text-lg font-semibold mb-4 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                        </svg>
                                        Puntos del Polígono
                                        <span id="points-count" class="ml-2 px-2 py-1 text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full">0</span>
                                    </h2>
                                    
                                    <div class="flex-1 overflow-hidden">
                                        <!-- Lista de puntos -->
                                        <div id="points-container" class="space-y-3 overflow-y-auto max-h-[40vh] pr-2">
                                            <div id="no-points-message" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                                </svg>
                                                <p>No hay puntos para mostrar</p>
                                                <p class="text-sm mt-1">Carga un polígono o dibuja uno nuevo</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Resumen del polígono -->
                                    <div id="polygon-summary" class="mt-4 p-3 bg-blue-50 dark:bg-blue-900 rounded-lg hidden">
                                        <h3 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">Resumen</h3>
                                        <div class="text-sm space-y-1">
                                            <div><strong>Área:</strong> <span id="summary-area">0.00</span> Ha</div>
                                            <div><strong>Número de puntos:</strong> <span id="summary-points">0</span></div>
                                        </div>
                                    </div>

                                    <!-- Controles de edición -->
                                    <div id="edit-controls" class="mt-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg hidden">
                                        <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Controles de edición</h3>
                                        <div class="space-y-2">
                                            <button type="button" id="add-point" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-3 rounded text-sm flex items-center justify-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                                Agregar punto
                                            </button>
                                            <button type="button" id="finish-edit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-3 rounded text-sm">
                                                Finalizar edición
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna del Formulario (ocupa todo el ancho debajo del mapa) -->
                        <div class="lg:col-span-4">
                            <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden sm:rounded-2xl p-4 md:p-6 lg:p-8">
                                <div class="text-gray-900 dark:text-gray-100">
                                    <h2 class="text-lg font-semibold mb-4">Datos del Polígono</h2>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Columna izquierda -->
                                        <div class="space-y-6">
                                            <div>
                                                <x-input-label for="name" :value="__('Nombre del Polígono *')" />
                                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                                    value="{{ old('name', $polygon->name) }}" required placeholder="Ej: Finca La Esperanza" />
                                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                                            </div>

                                            <div>
                                                <x-input-label for="description" :value="__('Descripción')" />
                                                <textarea id="description" name="description" rows="3"
                                                    class="w-full px-2.5 sm:px-3 py-1.5 mt-1 sm:py-2 text-xs sm:text-sm border border-stone-400/80 dark:border-gray-600 !bg-stone-50 dark:!bg-gray-800/50 text-custom-gray dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70 "
                                                    placeholder="Descripción del polígono...">{{ old('description', $polygon->description) }}</textarea>
                                                <x-input-error class="mt-2" :messages="$errors->get('description')" />
                                            </div>

                                            <div>
                                                <x-input-label for="producer_id" :value="__('Productor (Opcional)')" />
                                                <select id="producer_id" name="producer_id"
                                                    class="w-full px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm border border-stone-400/80 dark:border-gray-600 !bg-stone-50 dark:!bg-gray-800/50 text-custom-gray dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70 ">
                                                    <option value="">Seleccione un productor</option>
                                                    @foreach($producers as $producer)
                                                        <option value="{{ $producer->id }}" {{ old('producer_id', $polygon->producer_id) == $producer->id ? 'selected' : '' }}>
                                                            {{ $producer->name }} {{ $producer->lastname }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <x-input-error class="mt-2" :messages="$errors->get('producer_id')" />
                                            </div>
                                        </div>

                                        <!-- Columna derecha -->
                                        <div class="space-y-6">
                                            <div>
                                                <x-input-label for="parish_id" :value="__('Parroquia (Opcional)')" />
                                                <select id="parish_id" name="parish_id"
                                                    class="w-full px-2.5 sm:px-3 py-1.5 mt-1 sm:py-2 text-xs sm:text-sm border border-stone-400/80 dark:border-gray-600 !bg-stone-50 dark:!bg-gray-800/50 text-custom-gray dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70 ">
                                                    <option value="">Seleccione una parroquia</option>
                                                    @foreach($parishes as $parish)
                                                        <option value="{{ $parish->id }}" {{ old('parish_id', $polygon->parish_id) == $parish->id ? 'selected' : '' }}>
                                                            {{ $parish->name }}
                                                            @if($parish->municipality && $parish->municipality->state)
                                                                ({{ $parish->municipality->name }}, {{ $parish->municipality->state->name }})
                                                            @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <x-input-error class="mt-2" :messages="$errors->get('parish_id')" />
                                            </div>

                                            <div>
                                                <x-input-label for="area_ha" :value="__('Área en Hectáreas')" />
                                                <x-text-input id="area_ha" name="area_ha" type="number" step="0.01"
                                                    class="mt-1  w-full" value="{{ old('area_ha', $polygon->area_ha) }}" placeholder="Se calculará automáticamente" />
                                                <x-input-error class="mt-2" :messages="$errors->get('area_ha')" />
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Dejar vacío para calcular automáticamente desde el mapa</p>
                                            </div>

                                            <div>
                                                <x-input-label for="is_active" :value="__('Estado')" />
                                                <div class="flex items-center space-x-4 mt-2">
                                                    <label class="inline-flex items-center">
                                                        <input type="radio" name="is_active" value="1" 
                                                            {{ old('is_active', $polygon->is_active) ? 'checked' : '' }} 
                                                            class="text-indigo-600 border-gray-300 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                                        <span class="ml-2 text-gray-700 dark:text-gray-300">Activo</span>
                                                    </label>
                                                    <label class="inline-flex items-center">
                                                        <input type="radio" name="is_active" value="0" 
                                                            {{ !old('is_active', $polygon->is_active) ? 'checked' : '' }} 
                                                            class="text-indigo-600 border-gray-300 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                                        <span class="ml-2 text-gray-700 dark:text-gray-300">Inactivo</span>
                                                    </label>
                                                </div>
                                                <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Campos ocultos para la geometría y detección -->
                                    <input type="hidden" id="geometry" name="geometry" value="{{ old('geometry', json_encode($polygon->getGeometryGeoJson())) }}" required>
                                    <input type="hidden" id="detected_parish" name="detected_parish" value="{{ old('detected_parish', $polygon->detected_parish) }}">
                                    <input type="hidden" id="detected_municipality" name="detected_municipality" value="{{ old('detected_municipality', $polygon->detected_municipality) }}">
                                    <input type="hidden" id="detected_state" name="detected_state" value="{{ old('detected_state', $polygon->detected_state) }}">
                                    <input type="hidden" id="centroid_lat" name="centroid_lat" value="{{ old('centroid_lat', $polygon->centroid_lat) }}">
                                    <input type="hidden" id="centroid_lng" name="centroid_lng" value="{{ old('centroid_lng', $polygon->centroid_lng) }}">
                                    
                                    <!-- Mostrar información de detección si existe -->
                                    <div id="location-info" class="p-4 bg-blue-50 dark:bg-blue-900 rounded-lg mt-6 {{ !$polygon->detected_parish && !$polygon->detected_municipality && !$polygon->detected_state ? 'hidden' : '' }}">
                                        <h3 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">📍 Ubicación detectada originalmente</h3>
                                        <div class="text-sm space-y-1">
                                            <div><strong>Parroquia:</strong> <span id="detected-parish-text">{{ $polygon->detected_parish ?? '-' }}</span></div>
                                            <div><strong>Municipio:</strong> <span id="detected-municipality-text">{{ $polygon->detected_municipality ?? '-' }}</span></div>
                                            <div><strong>Estado:</strong> <span id="detected-state-text">{{ $polygon->detected_state ?? '-' }}</span></div>
                                            @if($polygon->centroid_lat && $polygon->centroid_lng)
                                                <div><strong>Coordenadas:</strong> <span id="detected-coords-text">{{ number_format($polygon->centroid_lat, 6) }}, {{ number_format($polygon->centroid_lng, 6) }}</span></div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Botón de detección de ubicación -->
                                    <div class="pt-6">
                                        <button type="button" id="detect-location" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300">
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
                                        <a href="{{ route('polygons.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md">
                                            Cancelar
                                        </a>
                                        <x-primary-button type="submit" id="submit-btn" class="bg-green-600 hover:bg-green-700">
                                            Actualizar Polígono
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

    <!-- Modal para coordenadas manuales -->
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
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para editar punto individual -->
    <div id="edit-point-modal" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-white dark:bg-custom-gray rounded-xl shadow-2xl w-full max-w-md mx-4">
            <!-- Header -->
            <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    <span id="edit-point-title">Editar Punto</span>
                </h3>
                <button id="close-edit-modal" type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <!-- Formulario para editar punto -->
            <form id="edit-point-form" class="p-6 space-y-4">
                <input type="hidden" id="edit-point-index" value="">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Latitud</label>
                        <input type="number" id="edit-point-lat" step="0.000001" 
                            class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm p-2" 
                            placeholder="Ej: 10.123456">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Longitud</label>
                        <input type="number" id="edit-point-lng" step="0.000001"
                            class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm p-2" 
                            placeholder="Ej: -66.123456">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Zona UTM</label>
                        <input type="number" id="edit-point-zone" min="1" max="60"
                            class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm p-2" 
                            placeholder="20" value="20">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Hemisferio</label>
                        <select id="edit-point-hemisphere" class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm">
                            <option value="N">Norte (N)</option>
                            <option value="S">Sur (S)</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nota (opcional)</label>
                    <textarea id="edit-point-note" rows="2"
                        class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm p-2"
                        placeholder="Descripción del punto..."></textarea>
                </div>
                
                <!-- Botones de acción -->
                <div class="flex space-x-3 pt-4">
                    <button type="button" id="delete-point-btn" class="flex-1 py-2 px-4 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium">
                        Eliminar Punto
                    </button>
                    
                    <div class="flex space-x-2">
                        <button type="button" id="cancel-edit-point" class="py-2 px-4 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium">
                            Cancelar
                        </button>
                        <button type="submit" class="py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                            Guardar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

<!-- Incluir OpenLayers PRIMERO -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/css/ol.css">
<script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/build/ol.js"></script>
<script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.8.0/proj4.js"></script>

<!-- SweetAlert2 para notificaciones -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// CLASE PRINCIPAL PARA EDITAR POLÍGONOS CON OPENLAYERS
class PolygonEditor {
    constructor(polygonGeoJSON = null) {
        this.map = null;
        this.source = null;
        this.vectorLayer = null;
        this.modify = null;
        this.draw = null;
        this.select = null;
        this.currentFeature = null;
        this.coordinateDisplay = null;
        this.baseLayers = {};
        this.currentBaseLayer = null;
        this.existingPolygon = polygonGeoJSON;
        this.isEditMode = false;
        this.polygonPoints = [];
        this.selectedPointIndex = -1;
        this.selectedFeature = null;

        // Coordenadas de Venezuela por defecto
        this.INITIAL_CENTER = [-63.172905251869125, 10.555594747510682];
        this.INITIAL_ZOOM = 15;
        this.MINZOOM = 5;
        this.MAXZOOM = 18;

        console.log('Inicializando PolygonEditor...');
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.init());
        } else {
            this.init();
        }
    }

    init() {
        console.log('Ejecutando init()...');
        
        const mapElement = document.getElementById('map');
        if (!mapElement) {
            console.error('ERROR: No se encontró el elemento #map');
            return;
        }

        this.defineCustomProjections();
        this.initializeMap();
        this.setupEventListeners();
        this.setupCoordinateDisplay();
        this.setupMapResizeObserver();
        
        // Cargar polígono existente si está disponible
        if (this.existingPolygon) {
            setTimeout(() => this.loadExistingPolygon(), 500);
        }
        
        setTimeout(() => {
            if (this.map) {
                this.map.updateSize();
            }
        }, 500);
    }

    setupMapResizeObserver() {
        if ('ResizeObserver' in window) {
            const mapElement = document.getElementById('map');
            if (mapElement && mapElement.parentElement) {
                const observer = new ResizeObserver(entries => {
                    for (let entry of entries) {
                        if (entry.contentRect.width > 0 && entry.contentRect.height > 0) {
                            this.updateMapSize();
                        }
                    }
                });
                
                observer.observe(mapElement.parentElement);
            }
        }
    }
    
    updateMapSize() {
        if (this.map) {
            setTimeout(() => {
                this.map.updateSize();
                
                if (this.source && this.source.getFeatures().length > 0) {
                    const extent = this.source.getExtent();
                    if (extent && extent[0] !== Infinity) {
                        this.map.getView().fit(extent, {
                            padding: [50, 50, 50, 50],
                            duration: 500
                        });
                    }
                }
            }, 100);
        }
    }

    defineCustomProjections() {
        if (typeof proj4 !== 'undefined') {
            proj4.defs('EPSG:2203', 
                '+proj=utm +zone=20 +south +ellps=intl +towgs84=-288,175,-376,0,0,0,0 +units=m +no_defs'
            );
            
            proj4.defs('EPSG:32620',
                '+proj=utm +zone=20 +ellps=WGS84 +datum=WGS84 +units=m +no_defs'
            );
            
            if (typeof ol !== 'undefined') {
                ol.proj.proj4.register(proj4);
            }
        }
    }

    initializeMap() {
        try {
            this.setupBaseLayers();
            this.setupVectorLayer();
            this.setupMapInstance();
            console.log('Mapa inicializado correctamente');
        } catch (error) {
            console.error('Error al inicializar el mapa:', error);
            this.showAlert('Error al cargar el mapa: ' + error.message, 'error');
        }
    }

    setupBaseLayers() {
        this.baseLayers = {
            osm: new ol.layer.Tile({
                title: 'OpenStreetMap',
                visible: true,
                source: new ol.source.XYZ({
                    url: 'https://{a-c}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
                    attributions: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>'
                })
            }),
            satellite: new ol.layer.Tile({
                source: new ol.source.XYZ({
                    url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                    attributions: 'Tiles © Esri'
                }),
                visible: false,
                title: 'Satélite Esri'
            }),
            terrain: new ol.layer.Tile({
                source: new ol.source.XYZ({
                    url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Shaded_Relief/MapServer/tile/{z}/{y}/{x}',
                    attributions: 'Tiles © Esri'
                }),
                visible: false,
                title: 'Relieve'
            }),
            dark: new ol.layer.Tile({
                source: new ol.source.XYZ({
                    url: 'https://{a-c}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}.png',
                    attributions: '© CartoDB'
                }),
                visible: false,
                title: 'Oscuro'
            }),
            maptiler_satellite: new ol.layer.Tile({
                source: new ol.source.XYZ({
                    url: 'https://api.maptiler.com/maps/satellite/{z}/{x}/{y}.jpg?key=scUozK4fig7bE6jg7TPi',
                    attributions: '© MapTiler & OpenStreetMap',
                    tileSize: 512,
                    maxZoom: 20
                }),
                visible: false,
                title: 'MapTiler Satélite'
            })
        };
    }

    setupVectorLayer() {
        this.source = new ol.source.Vector();

        this.vectorLayer = new ol.layer.Vector({
            source: this.source,
            style: (feature) => this.getFeatureStyle(feature)
        });
    }

    getFeatureStyle(feature) {
        const styles = [];
        const areaHa = feature.get('area') || 0;
        
        // Estilo base del polígono
        styles.push(new ol.style.Style({
            stroke: new ol.style.Stroke({
                color: this.isEditMode ? '#8b5cf6' : '#10b981',
                width: this.isEditMode ? 4 : 3,
                lineDash: this.isEditMode ? [10, 5] : null,
                lineCap: 'round'
            }),
            fill: new ol.style.Fill({
                color: this.isEditMode ? 'rgba(139, 92, 246, 0.2)' : 'rgba(16, 185, 129, 0.3)'
            })
        }));
        
        // Texto del área
        if (areaHa > 0) {
            styles.push(new ol.style.Style({
                text: new ol.style.Text({
                    text: `${areaHa.toFixed(6)} ha`,
                    font: 'bold 14px Arial, sans-serif',
                    fill: new ol.style.Fill({ color: '#1f2937' }),
                    stroke: new ol.style.Stroke({ color: '#ffffff', width: 3 }),
                    backgroundFill: new ol.style.Fill({ color: 'rgba(255, 255, 255, 0.7)' }),
                    padding: [4, 8, 4, 8],
                    textBaseline: 'middle',
                    textAlign: 'center',
                    offsetY: 0
                })
            }));
        }
        
        // Puntos de vértice en modo edición
        if (this.isEditMode && feature === this.currentFeature) {
            const geometry = feature.getGeometry();
            if (geometry.getType() === 'Polygon') {
                const coordinates = geometry.getCoordinates()[0];
                
                coordinates.forEach((coord, index) => {
                    if (index < coordinates.length - 1) { // No mostrar el último punto duplicado
                        styles.push(new ol.style.Style({
                            image: new ol.style.Circle({
                                radius: 8,
                                fill: new ol.style.Fill({
                                    color: index === this.selectedPointIndex ? '#ef4444' : '#3b82f6'
                                }),
                                stroke: new ol.style.Stroke({
                                    color: '#ffffff',
                                    width: 3
                                })
                            }),
                            geometry: new ol.geom.Point(coord),
                            text: new ol.style.Text({
                                text: (index + 1).toString(),
                                font: 'bold 12px Arial, sans-serif',
                                fill: new ol.style.Fill({ color: '#ffffff' }),
                                offsetY: 0
                            })
                        }));
                    }
                });
            }
        }
        
        return styles;
    }

    setupMapInstance() {
        const baseLayerGroup = new ol.layer.Group({
            layers: Object.values(this.baseLayers)
        });

        const initialCenter = ol.proj.fromLonLat(this.INITIAL_CENTER);

        this.map = new ol.Map({
            target: 'map',
            layers: [baseLayerGroup, this.vectorLayer],
            view: new ol.View({
                center: initialCenter,
                zoom: this.INITIAL_ZOOM,
                minZoom: this.MINZOOM,
                maxZoom: this.MAXZOOM,
                smoothResolutionConstraint: true
            }),
            controls: ol.control.defaults({
                attributionOptions: {
                    collapsible: true
                }
            })
        });

        this.currentBaseLayer = this.baseLayers.osm;
    }

    setupEventListeners() {
        // Evento para clic en el mapa (selección de puntos)
        this.map.on('click', (evt) => {
            if (!this.isEditMode || !this.currentFeature) return;
            
            const pixel = evt.pixel;
            const feature = this.map.forEachFeatureAtPixel(pixel, (feature) => {
                return feature;
            });
            
            if (feature === this.currentFeature) {
                // Verificar si se hizo clic en un punto de vértice
                const geometry = feature.getGeometry();
                if (geometry.getType() === 'Polygon') {
                    const coordinates = geometry.getCoordinates()[0];
                    const clickedCoord = evt.coordinate;
                    
                    // Buscar el punto más cercano
                    let minDistance = Infinity;
                    let closestIndex = -1;
                    
                    for (let i = 0; i < coordinates.length - 1; i++) {
                        const distance = this.calculateDistance(coordinates[i], clickedCoord);
                        if (distance < minDistance && distance < 20) { // 20 píxeles de tolerancia
                            minDistance = distance;
                            closestIndex = i;
                        }
                    }
                    
                    if (closestIndex !== -1) {
                        this.selectPoint(closestIndex, coordinates[closestIndex]);
                    } else {
                        this.deselectPoint();
                    }
                }
            } else {
                this.deselectPoint();
            }
        });
        
        // Evento para mover puntos
        this.map.on('pointermove', (evt) => {
            if (!this.isEditMode || !this.currentFeature || this.selectedPointIndex === -1) return;
            
            if (evt.dragging) {
                const geometry = this.currentFeature.getGeometry();
                const coordinates = geometry.getCoordinates()[0];
                
                // Actualizar coordenada seleccionada
                coordinates[this.selectedPointIndex] = evt.coordinate;
                coordinates[coordinates.length - 1] = coordinates[0]; // Cerrar el polígono
                
                geometry.setCoordinates([coordinates]);
                
                // Actualizar información del punto
                const lonLat = ol.proj.toLonLat(evt.coordinate);
                this.updatePointInfo(this.selectedPointIndex, lonLat[1], lonLat[0]);
                
                // Recalcular área
                const areaHa = this.calculateArea(this.currentFeature);
                this.updateAreaDisplay(areaHa);
                this.currentFeature.set('area', areaHa);
                
                // Actualizar lista de puntos
                this.updatePolygonPoints();
                this.updateGeoJSON();
            }
        });
    }

    setupCoordinateDisplay() {
        this.createCoordinateDisplayElement();
        
        this.map.on('pointermove', (evt) => {
            if (evt.dragging) return;
            this.updateCoordinateDisplay(evt.coordinate);
        });
    }

    createCoordinateDisplayElement() {
        const existingDisplays = document.querySelectorAll('.coordinate-display');
        existingDisplays.forEach(display => display.remove());
        
        this.coordinateDisplay = document.getElementById('coordinate-display');
        if (!this.coordinateDisplay) {
            this.coordinateDisplay = document.createElement('div');
            this.coordinateDisplay.id = 'coordinate-display';
            this.coordinateDisplay.className = 'coordinate-display';
            this.coordinateDisplay.style.cssText = 'position: absolute; bottom: 10px; left: 10px; background-color: rgba(255, 255, 255, 0.9); padding: 5px 10px; border-radius: 4px; font-size: 12px; z-index: 1; font-family: monospace; display: none; border: 1px solid #e5e7eb; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);';
            
            const mapContainer = this.map.getTargetElement();
            if (mapContainer) {
                mapContainer.style.position = 'relative';
                mapContainer.appendChild(this.coordinateDisplay);
            }
        }
    }

    updateCoordinateDisplay(coordinate) {
        if (!this.coordinateDisplay) return;
        
        try {
            const lonLat = ol.proj.toLonLat(coordinate);
            const lon = lonLat[0];
            const lat = lonLat[1];
            
            const zone = Math.floor((lon + 180) / 6) + 1;
            const hemisphere = lat >= 0 ? 'N' : 'S';
            
            const epsgCode = this.setupUTMProjection(zone, hemisphere);
            const [easting, northing] = proj4('EPSG:4326', epsgCode, [lon, lat]);
            
            if (this.isValidUTM(easting, northing, zone, hemisphere)) {
                this.coordinateDisplay.textContent = 
                    `Zona ${zone}${hemisphere} | ` +
                    `Este: ${easting.toFixed(3)} | ` +
                    `Norte: ${northing.toFixed(3)}`;
                this.coordinateDisplay.style.display = 'block';
            } else {
                this.coordinateDisplay.style.display = 'none';
            }
        } catch (error) {
            console.warn('Error en conversión UTM:', error);
            this.coordinateDisplay.style.display = 'none';
        }
    }

    setupUTMProjection(zone, hemisphere) {
        const epsgCode = hemisphere === 'N' ? `EPSG:326${zone}` : `EPSG:327${zone}`;
        
        if (!proj4.defs(epsgCode)) {
            const proj4String = `+proj=utm +zone=${zone} +${hemisphere === 'S' ? '+south ' : ''}datum=WGS84 +units=m +no_defs`;
            proj4.defs(epsgCode, proj4String);
        }
        
        return epsgCode;
    }

    isValidUTM(easting, northing, zone, hemisphere) {
        if (easting < 0 || easting > 1000000) return false;
        
        if (hemisphere === 'N') {
            return northing >= 0 && northing <= 10000000;
        } else {
            return northing >= 1000000 && northing <= 10000000;
        }
    }

    calculateDistance(coord1, coord2) {
        const dx = coord1[0] - coord2[0];
        const dy = coord1[1] - coord2[1];
        return Math.sqrt(dx * dx + dy * dy);
    }

    // =============================================
    // CARGA DE POLÍGONO EXISTENTE
    // =============================================

    loadExistingPolygon() {
        if (!this.existingPolygon || !this.map) return;
        
        try {
            console.log('Cargando polígono existente...');
            
            // Limpiar cualquier polígono existente
            this.source.clear();
            
            // Parsear el GeoJSON
            const geojsonFormat = new ol.format.GeoJSON();
            const features = geojsonFormat.readFeatures(this.existingPolygon, {
                dataProjection: 'EPSG:4326',
                featureProjection: 'EPSG:3857'
            });
            
            if (features.length > 0) {
                this.currentFeature = features[0];
                
                // Calcular área
                const areaHa = this.calculateArea(this.currentFeature);
                this.currentFeature.set('area', areaHa);
                
                // Agregar al mapa
                this.source.addFeature(this.currentFeature);
                
                // Ajustar vista al polígono
                this.map.getView().fit(this.currentFeature.getGeometry().getExtent(), {
                    padding: [50, 50, 50, 50],
                    duration: 1000
                });
                
                // Actualizar campos del formulario
                this.updateAreaDisplay(areaHa);
                this.updateGeoJSON();
                
                // Habilitar botón de detección y edición
                const detectBtn = document.getElementById('detect-location');
                const editBtn = document.getElementById('edit-polygon');
                if (detectBtn) detectBtn.disabled = false;
                if (editBtn) editBtn.classList.remove('hidden');
                
                // Actualizar lista de puntos
                this.updatePolygonPoints();
            }
        } catch (error) {
            console.error('Error cargando polígono existente:', error);
            this.showAlert('Error cargando polígono existente: ' + error.message, 'error');
        }
    }

    // =============================================
    // FUNCIONALIDADES DE DIBUJO
    // =============================================

    activateDrawing() {
        console.log('Activando dibujo de polígonos...');
        
        // Limpiar interacciones existentes
        this.deactivateModify();
        this.deactivateDrawing();
        
        // Salir del modo edición
        this.exitEditMode();
        
        this.draw = new ol.interaction.Draw({
            source: this.source,
            type: 'Polygon',
            style: new ol.style.Style({
                stroke: new ol.style.Stroke({
                    color: '#3b82f6',
                    width: 3,
                    lineDash: [5, 10],
                    lineCap: 'round'
                }),
                fill: new ol.style.Fill({
                    color: 'rgba(59, 130, 246, 0.2)'
                }),
                image: new ol.style.Circle({
                    radius: 6,
                    fill: new ol.style.Fill({ color: '#ffffff' }),
                    stroke: new ol.style.Stroke({ color: '#3b82f6', width: 2 })
                })
            })
        });

        this.draw.on('drawstart', () => {
            this.source.clear();
            this.currentFeature = null;
            this.updateAreaDisplay(0);
            
            const detectBtn = document.getElementById('detect-location');
            const editBtn = document.getElementById('edit-polygon');
            if (detectBtn) detectBtn.disabled = true;
            if (editBtn) editBtn.classList.add('hidden');
            
            const locationInfo = document.getElementById('location-info');
            if (locationInfo) locationInfo.classList.add('hidden');
        });

        this.draw.on('drawend', (event) => {
            this.finalizeDrawing(event.feature);
        });

        this.map.addInteraction(this.draw);
        
        this.showAlert('Modo dibujo activado. Haz clic en el mapa para dibujar el polígono.', 'info');
    }

    finalizeDrawing(feature) {
        const areaHa = this.calculateArea(feature);
        
        feature.set('area', areaHa);
        this.currentFeature = feature;
        
        this.updateAreaDisplay(areaHa);
        this.updateGeoJSON();
        
        // Habilitar botones
        const detectBtn = document.getElementById('detect-location');
        const editBtn = document.getElementById('edit-polygon');
        if (detectBtn) detectBtn.disabled = false;
        if (editBtn) editBtn.classList.remove('hidden');
        
        // Actualizar lista de puntos
        this.updatePolygonPoints();
        
        // Remover interacción de dibujo
        this.deactivateDrawing();
        
        this.showAlert(`Polígono completado. Área: ${areaHa.toFixed(6)} ha`, 'success');
    }

    deactivateDrawing() {
        if (this.draw) {
            this.map.removeInteraction(this.draw);
            this.draw = null;
        }
    }

    // =============================================
    // FUNCIONALIDADES DE EDICIÓN MEJORADAS
    // =============================================

    activateEditMode() {
        if (!this.currentFeature) {
            this.showAlert('Primero debes cargar o dibujar un polígono', 'warning');
            return;
        }
        
        console.log('Activando modo edición...');
        
        // Desactivar dibujo si está activo
        this.deactivateDrawing();
        
        this.isEditMode = true;
        
        // Actualizar estilo del polígono
        this.vectorLayer.setStyle((feature) => this.getFeatureStyle(feature));
        
        // Mostrar controles de edición
        document.getElementById('edit-controls').classList.remove('hidden');
        document.getElementById('edit-polygon').innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check w-6 h-6">
                <path d="M20 6 9 17l-5-5"/>
            </svg>
            
        `;
        document.getElementById('edit-polygon').classList.remove('bg-purple-600', 'hover:bg-purple-700');
        document.getElementById('edit-polygon').classList.add('bg-green-600', 'hover:bg-green-700');
        
        // Actualizar puntos del polígono
        this.updatePolygonPoints();
        
        // Activar interacción de modificación
        this.activateModify();
        
        this.showAlert('Modo edición activado. Haz clic en los puntos para seleccionarlos y arrástralos para moverlos.', 'info');
    }

    exitEditMode() {
        console.log('Saliendo del modo edición...');
        
        this.isEditMode = false;
        this.selectedPointIndex = -1;
        this.selectedFeature = null;
        
        // Ocultar información del punto
        document.getElementById('point-info').classList.add('hidden');
        
        // Ocultar controles de edición
        document.getElementById('edit-controls').classList.add('hidden');
        document.getElementById('edit-polygon').innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-edit w-6 h-6">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>

        `;
        document.getElementById('edit-polygon').classList.remove('bg-green-600', 'hover:bg-green-700');
        document.getElementById('edit-polygon').classList.add('bg-gray-600', 'hover:bg-gray-700');
        
        // Desactivar interacción de modificación
        this.deactivateModify();
        
        // Actualizar estilo del polígono
        this.vectorLayer.setStyle((feature) => this.getFeatureStyle(feature));
        
        this.showAlert('Modo edición desactivado', 'info');
    }

    activateModify() {
        if (!this.currentFeature) return;
        
        this.modify = new ol.interaction.Modify({
            source: this.source,
            style: new ol.style.Style({
                image: new ol.style.Circle({
                    radius: 8,
                    fill: new ol.style.Fill({ color: '#3b82f6' }),
                    stroke: new ol.style.Stroke({
                        color: '#ffffff',
                        width: 3
                    })
                })
            })
        });
        
        this.modify.on('modifyend', (event) => {
            const feature = event.features.getArray()[0];
            if (feature === this.currentFeature) {
                // Recalcular área
                const areaHa = this.calculateArea(feature);
                this.updateAreaDisplay(areaHa);
                feature.set('area', areaHa);
                
                // Actualizar GeoJSON
                this.updateGeoJSON();
                
                // Actualizar lista de puntos
                this.updatePolygonPoints();
                
                this.showAlert('Polígono modificado', 'success');
            }
        });
        
        this.map.addInteraction(this.modify);
    }

    deactivateModify() {
        if (this.modify) {
            this.map.removeInteraction(this.modify);
            this.modify = null;
        }
    }

    selectPoint(index, coordinate) {
        this.selectedPointIndex = index;
        
        // Convertir coordenadas a lat/lng
        const lonLat = ol.proj.toLonLat(coordinate);
        
        // Actualizar información del punto
        this.updatePointInfo(index, lonLat[1], lonLat[0]);
        
        // Mostrar panel de información
        document.getElementById('point-info').classList.remove('hidden');
        
        // Actualizar estilo del polígono para resaltar el punto seleccionado
        this.vectorLayer.setStyle((feature) => this.getFeatureStyle(feature));
    }

    deselectPoint() {
        this.selectedPointIndex = -1;
        document.getElementById('point-info').classList.add('hidden');
        
        // Actualizar estilo del polígono
        this.vectorLayer.setStyle((feature) => this.getFeatureStyle(feature));
    }

    updatePointInfo(index, lat, lng) {
        document.getElementById('selected-lat').textContent = lat.toFixed(6);
        document.getElementById('selected-lng').textContent = lng.toFixed(6);
        document.getElementById('selected-index').textContent = index + 1;
    }

    deleteSelectedPoint() {
        if (this.selectedPointIndex === -1 || !this.currentFeature) return;
        
        const geometry = this.currentFeature.getGeometry();
        const coordinates = geometry.getCoordinates()[0];
        
        if (coordinates.length <= 4) { // 3 puntos + el punto de cierre
            this.showAlert('El polígono debe tener al menos 3 puntos', 'warning');
            return;
        }
        
        // Eliminar el punto seleccionado
        coordinates.splice(this.selectedPointIndex, 1);
        
        // Actualizar el último punto para cerrar el polígono
        coordinates[coordinates.length - 1] = coordinates[0];
        
        geometry.setCoordinates([coordinates]);
        
        // Recalcular área
        const areaHa = this.calculateArea(this.currentFeature);
        this.updateAreaDisplay(areaHa);
        this.currentFeature.set('area', areaHa);
        
        // Actualizar GeoJSON
        this.updateGeoJSON();
        
        // Actualizar lista de puntos
        this.updatePolygonPoints();
        
        // Deseleccionar punto
        this.deselectPoint();
        
        this.showAlert(`Punto ${this.selectedPointIndex + 1} eliminado`, 'success');
    }

    addPointAtClick(event) {
        if (!this.currentFeature || !this.isEditMode) return;
        
        const coordinate = event.coordinate;
        const geometry = this.currentFeature.getGeometry();
        const coordinates = geometry.getCoordinates()[0];
        
        // Encontrar el segmento más cercano
        let minDistance = Infinity;
        let insertIndex = -1;
        
        for (let i = 0; i < coordinates.length - 1; i++) {
            const segmentStart = coordinates[i];
            const segmentEnd = coordinates[i + 1];
            const distance = this.pointToSegmentDistance(coordinate, segmentStart, segmentEnd);
            
            if (distance < minDistance && distance < 25) { // 25 píxeles de tolerancia
                minDistance = distance;
                insertIndex = i + 1;
            }
        }
        
        if (insertIndex !== -1) {
            // Insertar nuevo punto
            coordinates.splice(insertIndex, 0, coordinate);
            
            // Actualizar el último punto para cerrar el polígono
            coordinates[coordinates.length - 1] = coordinates[0];
            
            geometry.setCoordinates([coordinates]);
            
            // Recalcular área
            const areaHa = this.calculateArea(this.currentFeature);
            this.updateAreaDisplay(areaHa);
            this.currentFeature.set('area', areaHa);
            
            // Actualizar GeoJSON
            this.updateGeoJSON();
            
            // Actualizar lista de puntos
            this.updatePolygonPoints();
            
            this.showAlert('Nuevo punto agregado', 'success');
        }
    }

    pointToSegmentDistance(point, segmentStart, segmentEnd) {
        const A = point[0] - segmentStart[0];
        const B = point[1] - segmentStart[1];
        const C = segmentEnd[0] - segmentStart[0];
        const D = segmentEnd[1] - segmentStart[1];

        const dot = A * C + B * D;
        const lenSq = C * C + D * D;
        let param = -1;
        
        if (lenSq !== 0) {
            param = dot / lenSq;
        }

        let xx, yy;

        if (param < 0) {
            xx = segmentStart[0];
            yy = segmentStart[1];
        } else if (param > 1) {
            xx = segmentEnd[0];
            yy = segmentEnd[1];
        } else {
            xx = segmentStart[0] + param * C;
            yy = segmentStart[1] + param * D;
        }

        const dx = point[0] - xx;
        const dy = point[1] - yy;
        
        return Math.sqrt(dx * dx + dy * dy);
    }

    updatePolygonPoints() {
        if (!this.currentFeature) {
            this.polygonPoints = [];
            this.updatePointsList();
            return;
        }
        
        const geometry = this.currentFeature.getGeometry();
        if (geometry.getType() !== 'Polygon') return;
        
        const coordinates = geometry.getCoordinates()[0];
        this.polygonPoints = [];
        
        for (let i = 0; i < coordinates.length - 1; i++) { // Excluir el último punto duplicado
            const coord = coordinates[i];
            const lonLat = ol.proj.toLonLat(coord);
            
            this.polygonPoints.push({
                lat: lonLat[1],
                lng: lonLat[0],
                index: i
            });
        }
        
        this.updatePointsList();
    }

    updatePointsList() {
        const container = document.getElementById('points-container');
        const noPointsMessage = document.getElementById('no-points-message');
        const pointsCount = document.getElementById('points-count');
        const summaryPoints = document.getElementById('summary-points');
        
        if (!container || !pointsCount) return;
        
        // Actualizar contador
        pointsCount.textContent = this.polygonPoints.length;
        if (summaryPoints) summaryPoints.textContent = this.polygonPoints.length;
        
        // Ocultar/mostrar mensaje
        if (noPointsMessage) {
            noPointsMessage.classList.toggle('hidden', this.polygonPoints.length > 0);
        }
        
        // Mostrar/ocultar resumen
        const summary = document.getElementById('polygon-summary');
        if (summary) {
            summary.classList.toggle('hidden', this.polygonPoints.length === 0);
        }
        
        // Limpiar contenedor
        container.innerHTML = '';
        
        // Agregar cada punto
        this.polygonPoints.forEach((point, index) => {
            const pointElement = this.createPointElement(point, index);
            container.appendChild(pointElement);
        });
        
        // Calcular y actualizar resumen
        if (this.polygonPoints.length > 2) {
            this.updatePolygonSummary();
        }
    }

    createPointElement(point, index) {
        const element = document.createElement('div');
        element.className = 'bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm border border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-700 ';
        element.dataset.index = index;
        
        // Convertir a UTM
        const zone = Math.floor((point.lng + 180) / 6) + 1;
        const hemisphere = point.lat >= 0 ? 'N' : 'S';
        const epsgCode = this.setupUTMProjection(zone, hemisphere);
        const [easting, northing] = proj4('EPSG:4326', epsgCode, [point.lng, point.lat]);
        
        element.innerHTML = `
            <div class="flex justify-between items-start mb-2">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center mr-2 relative">
                        <span class="text-blue-600 dark:text-blue-300 font-semibold text-sm">${index + 1}</span>
                        <div class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white">Punto ${index + 1}</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Click para seleccionar</p>
                    </div>
                </div>
                <button type="button" class="text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 edit-point-btn p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700" data-index="${index}" title="Editar punto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                </button>
            </div>

            <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700 gap-1 mb-2">
                <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span class="text-gray-600 dark:text-gray-400 text-sm ">Zona: ${zone} ${hemisphere}</span>
                </div>
            </div>
            
            <div class="grid grid-cols-1 gap-1 text-sm mb-2">
                <div>
                    <span class="text-gray-600 dark:text-gray-400">Este:</span>
                    <span class="font-mono text-blue-600 dark:text-blue-400 ml-1">${easting.toFixed(3)}</span>
                </div>
                <div>
                    <span class="text-gray-600 dark:text-gray-400">Norte:</span>
                    <span class="font-mono text-blue-600 dark:text-blue-400 ml-1">${northing.toFixed(3)}</span>
                </div>
            </div> 
            
        `;
        
        // Evento para seleccionar el punto
        element.addEventListener('click', (e) => {
            if (!e.target.closest('button')) {
                this.selectPoint(index, ol.proj.fromLonLat([point.lng, point.lat]));
            }
        });
        
        // Evento para el botón de editar
        const editBtn = element.querySelector('.edit-point-btn');
        if (editBtn) {
            editBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                openEditPointModal(index, point);
            });
        }
        
        return element;
    }

    updatePolygonSummary() {
        if (this.polygonPoints.length < 3) return;
        
        // Calcular perímetro
        let perimeter = 0;
        for (let i = 0; i < this.polygonPoints.length; i++) {
            const nextIndex = (i + 1) % this.polygonPoints.length;
            const point1 = this.polygonPoints[i];
            const point2 = this.polygonPoints[nextIndex];
            
            // Calcular distancia en kilómetros
            const R = 6371; // Radio de la Tierra en km
            const dLat = (point2.lat - point1.lat) * Math.PI / 180;
            const dLon = (point2.lng - point1.lng) * Math.PI / 180;
            const a = 
                Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(point1.lat * Math.PI / 180) * Math.cos(point2.lat * Math.PI / 180) * 
                Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            perimeter += R * c;
        }
        
        // Actualizar elementos
        const summaryPerimeter = document.getElementById('summary-perimeter');
        const summaryArea = document.getElementById('summary-area');
        
        if (summaryPerimeter) {
            summaryPerimeter.textContent = perimeter.toFixed(2);
        }
        
        if (summaryArea && this.currentFeature) {
            const area = this.currentFeature.get('area') || 0;
            summaryArea.textContent = area.toFixed(2);
        }
    }

    // =============================================
    // UTILIDADES
    // =============================================

    calculateArea(feature) {
        if (!feature || !feature.getGeometry) return 0;
        
        const geometry = feature.getGeometry();
        if (!geometry) return 0;
        
        if (typeof turf === 'undefined') {
            console.error('Turf.js no está disponible');
            return 0;
        }
        
        try {
            const wgs84Geometry = geometry.clone().transform('EPSG:3857', 'EPSG:4326');
            const coordinates = wgs84Geometry.getCoordinates();
            
            if (!coordinates || coordinates.length === 0) return 0;
            
            const turfFeature = turf.polygon(coordinates);
            const areaM2 = turf.area(turfFeature);
            
            if (isNaN(areaM2) || areaM2 <= 0) return 0;
            
            const areaHa = areaM2 / 10000;
            return parseFloat(areaHa.toFixed(6));
            
        } catch (error) {
            console.error('Error en cálculo de área:', error);
            return 0;
        }
    }

    updateAreaDisplay(areaHa) {
        const areaInput = document.getElementById('area_ha');
        if (areaInput) {
            areaInput.value = areaHa > 0 ? areaHa.toFixed(6) : '';
        }
    }

    updateGeoJSON() {
        if (!this.currentFeature) return;
        
        try {
            const format = new ol.format.GeoJSON();
            const geojson = format.writeFeature(this.currentFeature, {
                dataProjection: 'EPSG:4326',
                featureProjection: 'EPSG:3857'
            });
            const geojsonObj = JSON.parse(geojson);
            
            if (geojsonObj.geometry) {
                document.getElementById('geometry').value = JSON.stringify(geojsonObj.geometry);
            }
        } catch (error) {
            console.error('Error al convertir GeoJSON:', error);
        }
    }

    clearMap() {
        this.source.clear();
        this.currentFeature = null;
        this.isEditMode = false;
        this.selectedPointIndex = -1;
        
        document.getElementById('geometry').value = '';
        document.getElementById('area_ha').value = '';
        
        const detectBtn = document.getElementById('detect-location');
        const editBtn = document.getElementById('edit-polygon');
        if (detectBtn) detectBtn.disabled = true;
        if (editBtn) editBtn.classList.add('hidden');
        
        const locationInfo = document.getElementById('location-info');
        if (locationInfo) locationInfo.classList.add('hidden');
        
        this.deactivateDrawing();
        this.deactivateModify();
        this.deselectPoint();
        
        this.updateAreaDisplay(0);
        this.updatePolygonPoints();
        
        this.showAlert('Mapa limpiado', 'info');
    }

    changeBaseLayer(layerKey) {
        if (!this.baseLayers[layerKey]) {
            this.showAlert(`Capa base no encontrada: ${layerKey}`, 'error');
            return;
        }
        
        Object.values(this.baseLayers).forEach(layer => {
            layer.setVisible(false);
        });
        
        this.baseLayers[layerKey].setVisible(true);
        this.currentBaseLayer = this.baseLayers[layerKey];
        
        const buttonElement = document.getElementById('base-map-toggle');
        if (buttonElement) {
            const layerTitle = this.baseLayers[layerKey].get('title') || layerKey;
            buttonElement.innerHTML = `
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                </svg>
                ${layerTitle}
            `;
        }
    }

    showAlert(message, icon = 'info') {
        if (window.Swal) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: icon,
                title: message,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        } else {
            alert(message);
        }
    }

    // =============================================
    // DETECCIÓN DE UBICACIÓN
    // =============================================

    calculateCentroidFromGeoJSON(geojson) {
        try {
            const geometry = typeof geojson === 'string' ? JSON.parse(geojson) : geojson;
            
            if (!geometry || !geometry.coordinates) {
                console.error('GeoJSON inválido para calcular centroide');
                return null;
            }

            let coordinates = geometry.coordinates;
            
            if (geometry.type === 'Polygon') {
                coordinates = coordinates[0];
            }
            
            let sumLat = 0;
            let sumLng = 0;
            let count = 0;
            
            for (const coord of coordinates) {
                if (Array.isArray(coord[0])) {
                    for (const subCoord of coord) {
                        sumLng += subCoord[0];
                        sumLat += subCoord[1];
                        count++;
                    }
                } else {
                    sumLng += coord[0];
                    sumLat += coord[1];
                    count++;
                }
            }
            
            if (count === 0) return null;
            
            return {
                lat: sumLat / count,
                lng: sumLng / count
            };
            
        } catch (error) {
            console.error('Error calculando centroide:', error);
            return null;
        }
    }

    async detectLocation() {
        const geometryInput = document.getElementById('geometry');
        if (!geometryInput || !geometryInput.value) {
            this.showAlert('Debes tener un polígono en el mapa', 'error');
            return;
        }
        
        const centroid = this.calculateCentroidFromGeoJSON(geometryInput.value);
        if (!centroid) {
            this.showAlert('No se pudo calcular el centroide del polígono', 'error');
            return;
        }
        
        document.getElementById('centroid_lat').value = centroid.lat;
        document.getElementById('centroid_lng').value = centroid.lng;
        
        const detectBtn = document.getElementById('detect-location');
        const detectButtonText = document.getElementById('detect-button-text');
        const originalText = detectButtonText.textContent;
        detectButtonText.textContent = 'Detectando...';
        detectBtn.disabled = true;
        
        try {
            const locationData = await this.reverseGeocode(centroid.lat, centroid.lng);
            this.processLocationData(locationData, centroid);
            
        } catch (error) {
            console.error('Error en detección de ubicación:', error);
            this.showAlert('Error detectando ubicación: ' + error.message, 'error');
        } finally {
            detectBtn.disabled = false;
            detectButtonText.textContent = originalText;
        }
    }

    async reverseGeocode(lat, lng) {
        try {
            const targetUrl = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=10&addressdetails=1&accept-language=es`;
            
            const response = await fetch(targetUrl, {
                headers: {
                    'User-Agent': 'PolygonSystem/1.0',
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            
            const data = await response.json();
            return data;
            
        } catch (error) {
            console.error('Error en geocodificación inversa:', error);
            throw error;
        }
    }

    processLocationData(data, centroid) {
        const address = data.address || {};
        
        let parish = address.village || address.town || address.city || address.municipality || '';
        let municipality = address.county || address.state_district || address.region || '';
        let state = address.state || address.region || '';
        
        const cleanParish = this.removePrefixes(parish, ['Parroquia', 'Sector', 'Zona']);
        const cleanMunicipality = this.removePrefixes(municipality, ['Municipio', 'Distrito', 'County']);
        const cleanState = this.removePrefixes(state, ['Estado', 'State', 'Departamento']);
        
        document.getElementById('detected_parish').value = cleanParish;
        document.getElementById('detected_municipality').value = cleanMunicipality;
        document.getElementById('detected_state').value = cleanState;
        
        document.getElementById('location_data').value = JSON.stringify(data);
        
        this.updateLocationInfoUI(cleanParish, cleanMunicipality, cleanState, centroid);
        this.findParishInDatabase(cleanParish, cleanMunicipality, cleanState);
        
        this.showAlert('Ubicación detectada correctamente', 'success');
    }

    removePrefixes(str, prefixes) {
        if (!str) return '';
        
        let result = str.trim();
        
        prefixes.forEach(prefix => {
            const regex = new RegExp(`^${prefix}\\s+`, 'i');
            
            if (regex.test(result)) {
                const match = result.match(regex);
                if (match) {
                    result = result.substring(match[0].length);
                }
            }
        });
        
        result = result.replace(/\s+/g, ' ').trim();
        
        return result;
    }

    findParishInDatabase(parishName, municipalityName, stateName) {
        try {
            this.updateParishSelect(parishName);
        } catch (error) {
            console.error('Error buscando parroquia:', error);
        }
    }

    updateParishSelect(parishName) {
        const parishSelect = document.getElementById('parish_id');
        if (!parishSelect) return;
        
        let foundOption = null;
        
        for (let i = 0; i < parishSelect.options.length; i++) {
            const option = parishSelect.options[i];
            const optionText = option.text;
            
            if (optionText === parishName) {
                foundOption = option;
                break;
            }
            
            if (optionText.includes(parishName)) {
                foundOption = option;
                break;
            }
            
            if (optionText.toLowerCase() === parishName.toLowerCase()) {
                foundOption = option;
                break;
            }
        }
        
        if (foundOption) {
            parishSelect.value = foundOption.value;
            this.showAlert(`Parroquia "${foundOption.text}" asignada automáticamente`, 'success');
        } else {
            this.showAlert('No se encontró parroquia exacta. Selecciona manualmente.', 'info');
        }
    }

    updateLocationInfoUI(parish, municipality, state, centroid) {
        document.getElementById('detected-parish-text').textContent = parish || 'No detectado';
        document.getElementById('detected-municipality-text').textContent = municipality || 'No detectado';
        document.getElementById('detected-state-text').textContent = state || 'No detectado';
        document.getElementById('detected-coords-text').textContent = 
            `${centroid.lat.toFixed(6)}, ${centroid.lng.toFixed(6)}`;
        
        const locationInfo = document.getElementById('location-info');
        if (locationInfo) {
            locationInfo.classList.remove('hidden');
        }
    }
}

// =============================================
// FUNCIONES GLOBALES Y CONFIGURACIÓN
// =============================================

let polygonEditor = null;
let coordinatesList = [];

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM completamente cargado, inicializando editor...');
    
    // Obtener el polígono existente del campo hidden
    const geometryInput = document.getElementById('geometry');
    let existingPolygon = null;
    
    if (geometryInput && geometryInput.value) {
        try {
            existingPolygon = JSON.parse(geometryInput.value);
        } catch (error) {
            console.error('Error parseando polígono existente:', error);
        }
    }
    
    // Inicializar el editor con el polígono existente
    polygonEditor = new PolygonEditor(existingPolygon);
    
    // Configurar event listeners
    setupEventListeners();
    setupModalFunctions();
    setupFormValidation();
    setupMapResizeHandler();
});

function setupEventListeners() {
    // Botón de dibujar
    document.getElementById('draw-polygon').addEventListener('click', () => {
        if (polygonEditor) {
            polygonEditor.activateDrawing();
        }
    });
    
    // Botón de editar
    document.getElementById('edit-polygon').addEventListener('click', () => {
        if (polygonEditor) {
            if (polygonEditor.isEditMode) {
                polygonEditor.exitEditMode();
            } else {
                polygonEditor.activateEditMode();
            }
        }
    });
    
    // Botón de limpiar
    document.getElementById('clear-map').addEventListener('click', () => {
        if (polygonEditor) {
            polygonEditor.clearMap();
        }
    });
    
    // Botón de agregar punto
    document.getElementById('add-point').addEventListener('click', () => {
        if (polygonEditor && polygonEditor.isEditMode) {
            // Activar modo para agregar punto al hacer clic
            const mapElement = document.getElementById('map');
            mapElement.style.cursor = 'crosshair';
            
            const clickHandler = (event) => {
                polygonEditor.addPointAtClick(event);
                mapElement.style.cursor = '';
                polygonEditor.map.un('click', clickHandler);
            };
            
            polygonEditor.map.on('click', clickHandler);
            
            showAlert('Haz clic en el segmento donde quieres agregar un nuevo punto', 'info');
        }
    });
    
    // Botón de finalizar edición
    document.getElementById('finish-edit').addEventListener('click', () => {
        if (polygonEditor) {
            polygonEditor.exitEditMode();
        }
    });
    
    // Botón de eliminar punto
    document.getElementById('delete-point').addEventListener('click', () => {
        if (polygonEditor) {
            polygonEditor.deleteSelectedPoint();
        }
    });
    
    // Botón de actualizar coordenadas
    document.getElementById('update-point').addEventListener('click', () => {
        if (polygonEditor && polygonEditor.selectedPointIndex !== -1) {
            const point = polygonEditor.polygonPoints[polygonEditor.selectedPointIndex];
            if (point) {
                openEditPointModal(polygonEditor.selectedPointIndex, point);
            }
        }
    });
    
    // Botón de detección de ubicación
    document.getElementById('detect-location').addEventListener('click', async () => {
        if (polygonEditor) {
            await polygonEditor.detectLocation();
        }
    });
    
    // Menú de capas base
    document.getElementById('base-map-toggle').addEventListener('click', (e) => {
        e.stopPropagation();
        const menu = document.getElementById('base-map-menu');
        const isShowing = menu.classList.contains('show');
        toggleMenu('base-map-menu', !isShowing);
    });
    
    document.querySelectorAll('#base-map-menu button').forEach(button => {
        button.addEventListener('click', (e) => {
            e.stopPropagation();
            const layerKey = button.getAttribute('data-layer');
            if (polygonEditor) {
                polygonEditor.changeBaseLayer(layerKey);
            }
            closeMenu('base-map-menu');
        });
    });
    
    // Pantalla completa
    document.getElementById('fullscreen-toggle').addEventListener('click', () => {
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
    
    // Cerrar menús al hacer clic fuera
    document.addEventListener('click', (e) => {
        const baseMapToggle = document.getElementById('base-map-toggle');
        const baseMapMenu = document.getElementById('base-map-menu');
        const modal = document.getElementById('manual-polygon-modal');
        
        if (modal.classList.contains('hidden')) {
            if (!baseMapToggle?.contains(e.target) && !baseMapMenu?.contains(e.target)) {
                closeMenu('base-map-menu');
            }
        }
    });
}

// Resto de las funciones auxiliares (toggleMenu, closeMenu, etc.)
// ... [Mantén todas las funciones auxiliares del código anterior]

// Función para abrir modal de edición de punto
function openEditPointModal(index, point) {
    const modal = document.getElementById('edit-point-modal');
    const title = document.getElementById('edit-point-title');
    const latInput = document.getElementById('edit-point-lat');
    const lngInput = document.getElementById('edit-point-lng');
    const zoneInput = document.getElementById('edit-point-zone');
    const hemisphereSelect = document.getElementById('edit-point-hemisphere');
    const noteInput = document.getElementById('edit-point-note');
    const indexInput = document.getElementById('edit-point-index');
    
    if (!modal || !point) return;
    
    title.textContent = `Editar Punto ${index + 1}`;
    latInput.value = point.lat.toFixed(6);
    lngInput.value = point.lng.toFixed(6);
    
    // Calcular zona UTM
    const zone = Math.floor((point.lng + 180) / 6) + 1;
    const hemisphere = point.lat >= 0 ? 'N' : 'S';
    zoneInput.value = zone;
    hemisphereSelect.value = hemisphere;
    
    noteInput.value = point.note || '';
    indexInput.value = index;
    
    modal.classList.remove('hidden');
    
    setTimeout(() => {
        latInput.focus();
        latInput.select();
    }, 100);
}

// Función para cerrar modal de edición de punto
function closeEditPointModal() {
    const modal = document.getElementById('edit-point-modal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

// Configurar eventos del modal de edición de punto
function setupPointEditModal() {
    const modal = document.getElementById('edit-point-modal');
    const closeBtn = document.getElementById('close-edit-modal');
    const cancelBtn = document.getElementById('cancel-edit-point');
    const deleteBtn = document.getElementById('delete-point-btn');
    const form = document.getElementById('edit-point-form');
    
    if (!modal) return;
    
    [closeBtn, cancelBtn].forEach(btn => {
        if (btn) {
            btn.addEventListener('click', closeEditPointModal);
        }
    });
    
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            const pointIndex = parseInt(document.getElementById('edit-point-index').value);
            if (!isNaN(pointIndex) && polygonEditor) {
                polygonEditor.deleteSelectedPoint();
                closeEditPointModal();
            }
        });
    }
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const pointIndex = parseInt(document.getElementById('edit-point-index').value);
            if (isNaN(pointIndex) || !polygonEditor || !polygonEditor.currentFeature) return;
            
            const lat = parseFloat(document.getElementById('edit-point-lat').value);
            const lng = parseFloat(document.getElementById('edit-point-lng').value);
            
            if (isNaN(lat) || isNaN(lng)) {
                showAlert('Por favor ingresa coordenadas válidas', 'error');
                return;
            }
            
            if (lat < -90 || lat > 90) {
                showAlert('La latitud debe estar entre -90 y 90', 'error');
                return;
            }
            
            if (lng < -180 || lng > 180) {
                showAlert('La longitud debe estar entre -180 y 180', 'error');
                return;
            }
            
            // Actualizar punto en el polígono
            const geometry = polygonEditor.currentFeature.getGeometry();
            const coordinates = geometry.getCoordinates()[0];
            const newCoord = ol.proj.fromLonLat([lng, lat]);
            
            coordinates[pointIndex] = newCoord;
            coordinates[coordinates.length - 1] = coordinates[0];
            
            geometry.setCoordinates([coordinates]);
            
            // Recalcular área
            const areaHa = polygonEditor.calculateArea(polygonEditor.currentFeature);
            polygonEditor.updateAreaDisplay(areaHa);
            polygonEditor.currentFeature.set('area', areaHa);
            
            // Actualizar GeoJSON
            polygonEditor.updateGeoJSON();
            
            // Actualizar lista de puntos
            polygonEditor.updatePolygonPoints();
            
            showAlert(`Punto ${pointIndex + 1} actualizado`, 'success');
            closeEditPointModal();
        });
    }
    
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeEditPointModal();
        }
    });
}

// Llama a esta función en la inicialización
setupPointEditModal();

// Configurar redimensionamiento
function setupMapResizeHandler() {
    window.addEventListener('resize', debounce(function() {
        if (polygonEditor && polygonEditor.map) {
            setTimeout(() => {
                polygonEditor.map.updateSize();
            }, 100);
        }
    }, 250));
}

// Función de debounce
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Función para mostrar alertas
function showAlert(message, type = 'info') {
    if (window.Swal) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: type,
            title: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    } else {
        alert(message);
    }
}

// Mantén todas las funciones de modal (setInputMethod, updateCoordinatesList, etc.)
// del código anterior que sean necesarias para el modal de coordenadas manuales
</script>

<style>
/* Estilos para OpenLayers */
.ol-viewport {
    border-radius: 0.5rem;
}

.ol-control {
    background-color: rgba(255,255,255,0.8);
    border-radius: 4px;
    padding: 2px;
}

.ol-control:hover {
    background-color: rgba(255,255,255,0.9);
}

/* Asegurar que el mapa ocupe todo el espacio */
#map {
    width: 100% !important;
    height: 100% !important;
    position: absolute !important;
    top: 0;
    left: 0;
}

/* Display de coordenadas */
#coordinate-display {
    background-color: rgba(255, 255, 255, 0.95);
    border: 1px solid #e5e7eb;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
}

.dark #coordinate-display {
    background-color: rgba(21, 23, 29, 0.95);
    color: #e5e7eb;
    border: 1px solid #4b5563;
}

/* Puntos de vértice en modo edición */
.ol-vertex {
    cursor: pointer;
}

/* Información del punto */
#point-info {
    max-width: 250px;
}

/* Controles de edición */
#edit-controls {
    transition: all 0.3s ease;
}

/* Estilos para la lista de puntos */
#points-container::-webkit-scrollbar {
    width: 6px;
}

#points-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

#points-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.dark #points-container::-webkit-scrollbar-track {
    background: #374151;
}

.dark #points-container::-webkit-scrollbar-thumb {
    background: #4b5563;
}

/* Resaltar punto seleccionado en la lista */
.bg-white.dark\\:bg-gray-800:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.1);
}

/* Estilos para controles de mapa */
#map-controls {
    pointer-events: auto;
    z-index: 1 !important;
}



/* Modo edición activo */
.edit-mode-active {
    border: 2px solid #8b5cf6 !important;
}

/* Cursor personalizado para modo edición */
.edit-cursor {
    cursor: crosshair !important;
}

/* Responsive */
@media (max-width: 1024px) {
    .lg\\:col-span-2 {
        grid-column: span 3;
    }
    
    .lg\\:col-span-1 {
        grid-column: span 3;
        margin-top: 1rem;
    }
}
</style>
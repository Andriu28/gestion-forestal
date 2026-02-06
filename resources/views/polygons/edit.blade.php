{{-- [file name]: edit.blade.php --}}
<x-app-layout>
    <div class="mx-auto">
        <div class="bg-stone-100/90 dark:bg-custom-gray shadow-sm sm:rounded-2xl shadow-soft p-4 md:p-6 lg:p-6 mb-6">
            <div class="text-gray-900 dark:text-gray-100">
                <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-200 mb-2 md:mb-2">
                    <i class="fas fa-draw-polygon mr-2"></i> Editar Polígono: {{ $polygon->name }}
                </h2>

                <form action="{{ route('polygons.update', $polygon) }}" method="POST" id="polygon-form" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Columna del Mapa (ocupa 2/3 en pantallas grandes) -->
                        <div class="lg:col-span-2">
                            <x-input-label for="map" />
                            <div class="relative rounded-lg overflow-hidden mb-6 border border-gray-200 dark:border-gray-700 mt-1" style="height: 70vh; border: 1px solid #dededeff; border-radius: 0.5rem; position: relative;">
                                <div id="map" class="h-full w-full"></div>

                                <!-- Controles simplificados del mapa -->
                                <div id="map-controls" class="absolute top-4 right-4 z-50 flex flex-col space-y-2">
                                    <!-- Segunda fila de controles -->
                                    <div class="flex space-x-2">
                                        <!-- Coordenadas Manuales -->
                                        <button id="manual-polygon-toggle" type="button" title="Escribir Coordenadas" class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
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

                                        <!-- Botón de edición (se agregará dinámicamente) -->
                                        <button id="toggle-edit" type="button" title="Editar puntos" class="hidden bg-purple-600 hover:bg-purple-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-edit w-6 h-6">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                            </svg>
                                            Editar
                                        </button>
                                    </div>
                                </div>

                                <!-- Coordenadas en tiempo real -->
                                <div class="absolute left-1/2 bottom-4 transform -translate-x-1/2 z-40 bg-gray-50 dark:bg-gray-800 p-2 rounded text-sm shadow">
                                    <span id="coordinates-display">Lat: 0.000000 | Lng: 0.000000</span>
                                </div>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('geometry')" />
                        </div>

                        <!-- Panel lateral de puntos del polígono (ocupa 1/3 en pantallas grandes) -->
                        <div class="lg:col-span-1">
                            <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden sm:rounded-2xl p-4 md:p-6 lg:p-6 h-full">
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
                                        <div id="points-container" class="space-y-3 overflow-y-auto max-h-[400px] pr-2">
                                            <!-- Los puntos se agregarán dinámicamente aquí -->
                                            <div id="no-points-message" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                                </svg>
                                                <p>No hay puntos para mostrar</p>
                                                <p class="text-sm mt-1">Dibuja un polígono o carga uno existente</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Resumen del polígono -->
                                    <div id="polygon-summary" class="mt-4 p-3 bg-blue-50 dark:bg-blue-900 rounded-lg hidden">
                                        <h3 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">Resumen</h3>
                                        <div class="text-sm space-y-1">
                                            <div><strong>Área:</strong> <span id="summary-area">0.00</span> Ha</div>
                                            <div><strong>Perímetro:</strong> <span id="summary-perimeter">0.00</span> km</div>
                                            <div><strong>Número de puntos:</strong> <span id="summary-points">0</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna del Formulario (ocupa todo el ancho debajo del mapa) -->
                        <div class="lg:col-span-3">
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
                                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                    placeholder="Descripción del polígono...">{{ old('description', $polygon->description) }}</textarea>
                                                <x-input-error class="mt-2" :messages="$errors->get('description')" />
                                            </div>

                                            <div>
                                                <x-input-label for="producer_id" :value="__('Productor (Opcional)')" />
                                                <select id="producer_id" name="producer_id"
                                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
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
                                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
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
                                                    class="mt-1 block w-full" value="{{ old('area_ha', $polygon->area_ha) }}" placeholder="Se calculará automáticamente" />
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
    <div id="manual-polygon-modal" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-white dark:bg-custom-gray rounded-xl shadow-2xl w-full max-w-lg mx-4">
            <!-- Header -->
            <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Ingresar Coordenadas UTM</h3>
                <button id="close-modal" type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
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
                
                <div class="grid grid-cols-3 gap-4">
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
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Altura (m)</label>
                        <input type="number" id="edit-point-elevation" step="0.1"
                            class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm p-2" 
                            placeholder="Ej: 100">
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

<!-- Estilos y librerías -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.8.0/proj4.js"></script>

<!-- Cargar utilidades primero -->
<script src="{{ asset('js/polygon/polygon-map-edit.js') }}"></script>

<script>
// Variables globales
let mapManager = null;
let isEditModeActive = false;
let editHandler = null;
let polygonPoints = []; // Array para almacenar los puntos del polígono

/**
 * Convertir coordenadas WGS84 a UTM
 */
function convertWGS84toUTM(lat, lng, zone = 20, hemisphere = 'N') {
    try {
        // Definir proyección UTM (zona 20N para Venezuela por defecto)
        const utmProjection = `+proj=utm +zone=${zone} +${hemisphere === 'N' ? 'north' : 'south'} +ellps=WGS84 +datum=WGS84 +units=m +no_defs`;
        const wgs84Projection = '+proj=longlat +ellps=WGS84 +datum=WGS84 +no_defs';
        
        // Convertir usando proj4
        const point = proj4(wgs84Projection, utmProjection, [lng, lat]);
        
        return {
            easting: point[0].toFixed(3),
            northing: point[1].toFixed(3),
            zone: zone,
            hemisphere: hemisphere
        };
    } catch (error) {
        console.error('Error convirtiendo a UTM:', error);
        return {
            easting: 'Error',
            northing: 'Error',
            zone: zone,
            hemisphere: hemisphere
        };
    }
}

/**
 * Calcular distancia entre dos puntos en kilómetros
 */
function calculateDistance(lat1, lng1, lat2, lng2) {
    const R = 6371; // Radio de la Tierra en km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lng2 - lng1) * Math.PI / 180;
    const a = 
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
        Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

/**
 * Crear marcador personalizado para un punto
 */
function createPointMarker(point, index) {
    // Crear ícono personalizado con número
    const icon = L.divIcon({
        className: 'custom-polygon-point-marker',
        html: `
            <div class="point-marker-container">
                <div class="point-marker-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="point-marker-label">${index + 1}</div>
            </div>
        `,
        iconSize: [40, 40],
        iconAnchor: [20, 43],
        popupAnchor: [0, -45]
    });
    
    // Crear marcador
    const marker = L.marker([point.lat, point.lng], {
        icon: icon,
        draggable: isEditModeActive,
        zIndexOffset: 1000 + index
    });
    
    // Agregar popup informativo
    const utm = convertWGS84toUTM(point.lat, point.lng, point.utmZone || 20, point.utmHemisphere || 'N');
    
    marker.bindPopup(`
        <div class="p-3 min-w-[200px]">
            <div class="flex items-center mb-2">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-2">
                    <span class="text-blue-600 font-semibold">${index + 1}</span>
                </div>
                <div>
                    <h4 class="font-bold text-gray-900">Punto ${index + 1}</h4>
                    <p class="text-xs text-gray-500">${point.note || 'Sin descripción'}</p>
                </div>
            </div>
            
            <div class="space-y-2 text-sm">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <span class="text-gray-600">Lat:</span>
                        <span class="font-mono text-green-600 ml-1">${point.lat.toFixed(6)}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Lng:</span>
                        <span class="font-mono text-green-600 ml-1">${point.lng.toFixed(6)}</span>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <span class="text-gray-600">Este:</span>
                        <span class="font-mono text-blue-600 ml-1">${utm.easting}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Norte:</span>
                        <span class="font-mono text-blue-600 ml-1">${utm.northing}</span>
                    </div>
                </div>
                
                <div class="flex justify-between text-xs text-gray-500 pt-2 border-t">
                    <span>Zona: ${utm.zone}${utm.hemisphere}</span>
                    ${point.elevation ? `<span>Altura: ${point.elevation}m</span>` : ''}
                </div>
            </div>
            
            <div class="mt-3 pt-2 border-t">
                <button class="w-full py-1 px-3 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded edit-point-btn" data-index="${index}">
                    ✏️ Editar este punto
                </button>
            </div>
        </div>
    `);
    
    // Evento para editar desde el popup
    marker.on('popupopen', function() {
        setTimeout(() => {
            const editBtn = document.querySelector('.leaflet-popup-content .edit-point-btn');
            if (editBtn) {
                editBtn.addEventListener('click', function() {
                    const index = parseInt(this.getAttribute('data-index'));
                    openEditPointModal(index);
                });
            }
        }, 100);
    });
    
    // Evento para arrastrar marcador - CORREGIDO
    if (isEditModeActive) {
        marker.on('dragend', function(e) {
            const newLatLng = e.target.getLatLng();
            const pointIndex = index;
            
            // Actualizar punto
            polygonPoints[pointIndex].lat = newLatLng.lat;
            polygonPoints[pointIndex].lng = newLatLng.lng;
            
            // Actualizar polígono - LLAMADA CORREGIDA
            updatePolygonFromPoints(polygonPoints);
            
            // Actualizar lista
            updatePointsList(polygonPoints);
            
            // Mostrar mensaje
            showMessage(`Punto ${pointIndex + 1} movido a nueva ubicación`, 'success');
        });
    }
    
    return marker;
}

/**
 * Actualizar marcadores en el mapa
 */
function updatePointMarkers() {
    // Limpiar marcadores anteriores
    if (window.pointMarkersLayer) {
        window.pointMarkersLayer.clearLayers();
    } else {
        window.pointMarkersLayer = L.layerGroup().addTo(mapManager.map);
    }
    
    // Agregar nuevos marcadores
    polygonPoints.forEach((point, index) => {
        const marker = createPointMarker(point, index);
        marker.addTo(window.pointMarkersLayer);
    });
    
    // Opcional: Conectar puntos con líneas
    updatePointConnections();
}

/**
 * Conectar puntos con líneas para mejor visualización
 */
function updatePointConnections() {
    // Limpiar conexiones anteriores
    if (window.connectionsLayer) {
        window.connectionsLayer.clearLayers();
    } else {
        window.connectionsLayer = L.layerGroup().addTo(mapManager.map);
    }
    
    if (polygonPoints.length < 2) return;
    
    // Crear líneas entre puntos consecutivos
    for (let i = 0; i < polygonPoints.length; i++) {
        const nextIndex = (i + 1) % polygonPoints.length;
        
        const line = L.polyline([
            [polygonPoints[i].lat, polygonPoints[i].lng],
            [polygonPoints[nextIndex].lat, polygonPoints[nextIndex].lng]
        ], {
            color: '#3b82f6',
            weight: 2,
            opacity: 0.5,
            dashArray: '5, 5',
            className: 'point-connection-line'
        });
        
        line.addTo(window.connectionsLayer);
    }
}

/**
 * Alternar visibilidad de marcadores
 */
function togglePointMarkers(show) {
    if (window.pointMarkersLayer) {
        if (show) {
            mapManager.map.addLayer(window.pointMarkersLayer);
        } else {
            mapManager.map.removeLayer(window.pointMarkersLayer);
        }
    }
    
    if (window.connectionsLayer) {
        if (show) {
            mapManager.map.addLayer(window.connectionsLayer);
        } else {
            mapManager.map.removeLayer(window.connectionsLayer);
        }
    }
}

/**
 * Agregar control para mostrar/ocultar marcadores
 */
function addMarkerToggleControl() {
    // Crear botón de control
    const controlDiv = L.DomUtil.create('div', 'leaflet-control leaflet-bar');
    controlDiv.style.marginRight = '10px';
    
    const toggleButton = L.DomUtil.create('a', '', controlDiv);
    toggleButton.href = '#';
    toggleButton.title = 'Mostrar/Ocultar puntos';
    toggleButton.innerHTML = `
        <div style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                <circle cx="12" cy="10" r="3"></circle>
            </svg>
        </div>
    `;
    
    let markersVisible = true;
    
    L.DomEvent.on(toggleButton, 'click', function(e) {
        L.DomEvent.stopPropagation(e);
        L.DomEvent.preventDefault(e);
        
        markersVisible = !markersVisible;
        togglePointMarkers(markersVisible);
        
        // Cambiar color del botón
        if (markersVisible) {
            toggleButton.style.backgroundColor = '';
            toggleButton.style.color = '';
            showMessage('Marcadores de puntos mostrados', 'info');
        } else {
            toggleButton.style.backgroundColor = '#4b5563';
            toggleButton.style.color = 'white';
            showMessage('Marcadores de puntos ocultos', 'info');
        }
    });
    
    // Crear control personalizado
    const MarkerControl = L.Control.extend({
        options: {
            position: 'topright'
        },
        
        onAdd: function(map) {
            return controlDiv;
        }
    });
    
    // Agregar control al mapa si no existe
    if (!window.markerControlAdded) {
        mapManager.map.addControl(new MarkerControl());
        window.markerControlAdded = true;
    }
}

/**
 * Actualizar lista de puntos en el panel lateral
 */
function updatePointsList(points) {
    const container = document.getElementById('points-container');
    const noPointsMessage = document.getElementById('no-points-message');
    const pointsCount = document.getElementById('points-count');
    const summaryPoints = document.getElementById('summary-points');
    
    if (!container || !pointsCount) return;
    
    // Actualizar contador
    pointsCount.textContent = points.length;
    if (summaryPoints) summaryPoints.textContent = points.length;
    
    // Ocultar mensaje de "no hay puntos"
    if (noPointsMessage) {
        noPointsMessage.classList.toggle('hidden', points.length > 0);
    }
    
    // Mostrar/ocultar resumen
    const summary = document.getElementById('polygon-summary');
    if (summary) {
        summary.classList.toggle('hidden', points.length === 0);
    }
    
    // Limpiar contenedor
    container.innerHTML = '';
    
    // Agregar cada punto
    points.forEach((point, index) => {
        const pointElement = createPointElement(point, index);
        container.appendChild(pointElement);
    });
    
    // Actualizar marcadores en el mapa
    updatePointMarkers();
    
    // Calcular y actualizar resumen
    if (points.length > 2) {
        updatePolygonSummary(points);
    }
}

/**
 * Crear elemento HTML para un punto
 */
function createPointElement(point, index) {
    const element = document.createElement('div');
    element.className = 'bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm border border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-700 transition-colors duration-200';
    element.dataset.index = index;
    
    // Convertir a UTM para mostrar
    const utm = convertWGS84toUTM(point.lat, point.lng);
    
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
                    <p class="text-xs text-gray-500 dark:text-gray-400">${point.note || 'Sin descripción'}</p>
                </div>
            </div>
            <button type="button" class="text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 edit-point-btn p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700" data-index="${index}" title="Editar punto">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                </svg>
            </button>
        </div>
        
        <div class="grid grid-cols-2 gap-2 text-sm mb-2">
            <div>
                <span class="text-gray-600 dark:text-gray-400">Lat:</span>
                <span class="font-mono text-green-600 dark:text-green-400 ml-1">${point.lat.toFixed(6)}</span>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">Lng:</span>
                <span class="font-mono text-green-600 dark:text-green-400 ml-1">${point.lng.toFixed(6)}</span>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">Este:</span>
                <span class="font-mono text-blue-600 dark:text-blue-400 ml-1">${utm.easting}</span>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">Norte:</span>
                <span class="font-mono text-blue-600 dark:text-blue-400 ml-1">${utm.northing}</span>
            </div>
        </div>
        
        <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>Zona: ${utm.zone}${utm.hemisphere}</span>
                ${point.elevation ? `<span>Altura: ${point.elevation}m</span>` : ''}
            </div>
        </div>
    `;
    
    // Agregar event listener al botón de editar
    const editBtn = element.querySelector('.edit-point-btn');
    if (editBtn) {
        editBtn.addEventListener('click', () => openEditPointModal(index));
    }
    
    // Agregar efecto hover para destacar el marcador en el mapa
    element.addEventListener('mouseenter', function() {
        if (window.pointMarkersLayer) {
            const marker = window.pointMarkersLayer.getLayers()[index];
            if (marker) {
                marker.openPopup();
                
                // Destacar el marcador
                const iconElement = marker.getElement();
                if (iconElement) {
                    iconElement.style.filter = 'drop-shadow(0 0 8px rgba(59, 130, 246, 0.8))';
                    iconElement.style.transition = 'all 0.3s ease';
                }
            }
        }
    });
    
    element.addEventListener('mouseleave', function() {
        if (window.pointMarkersLayer) {
            const marker = window.pointMarkersLayer.getLayers()[index];
            if (marker && !marker.isPopupOpen()) {
                marker.closePopup();
                
                // Restaurar el marcador
                const iconElement = marker.getElement();
                if (iconElement) {
                    iconElement.style.filter = '';
                    iconElement.style.transform = '';
                }
            }
        }
    });
    
    return element;
}

/**
 * Actualizar resumen del polígono
 */
function updatePolygonSummary(points) {
    if (points.length < 3) return;
    
    // Calcular perímetro
    let perimeter = 0;
    for (let i = 0; i < points.length; i++) {
        const nextIndex = (i + 1) % points.length;
        perimeter += calculateDistance(
            points[i].lat, points[i].lng,
            points[nextIndex].lat, points[nextIndex].lng
        );
    }
    
    // Actualizar elementos
    const summaryPerimeter = document.getElementById('summary-perimeter');
    const summaryArea = document.getElementById('summary-area');
    
    if (summaryPerimeter) {
        summaryPerimeter.textContent = perimeter.toFixed(2);
    }
    
    if (summaryArea && mapManager && mapManager.currentPolygonLayer) {
        // Calcular área usando la función existente
        const geoJSON = mapManager.currentPolygonLayer.toGeoJSON();
        const area = mapManager.calculateArea(geoJSON.geometry);
        if (area) {
            summaryArea.textContent = area.toFixed(2);
        }
    }
}

/**
 * Extraer puntos del polígono actual
 */
function extractPointsFromPolygon(polygonLayer) {
    if (!polygonLayer) return [];
    
    const points = [];
    const latLngs = polygonLayer.getLatLngs()[0]; // Primer anillo del polígono
    
    latLngs.forEach((latLng, index) => {
        // No incluir el último punto si es igual al primero (polígono cerrado)
        if (index === latLngs.length - 1 && 
            latLng.lat === latLngs[0].lat && 
            latLng.lng === latLngs[0].lng) {
            return;
        }
        
        points.push({
            lat: latLng.lat,
            lng: latLng.lng,
            elevation: null,
            note: `Punto ${index + 1} del polígono`,
            originalIndex: index
        });
    });
    
    return points;
}

/**
 * Actualizar polígono desde array de puntos - FUNCIÓN CORREGIDA
 */
function updatePolygonFromPoints(points) {
    if (!mapManager || !mapManager.currentPolygonLayer || points.length < 3) return false;
    
    try {
        // Crear array de coordenadas
        const latLngs = points.map(point => [point.lat, point.lng]);
        
        // Cerrar el polígono (último punto = primer punto)
        latLngs.push([points[0].lat, points[0].lng]);
        
        // Actualizar polígono en el mapa
        mapManager.currentPolygonLayer.setLatLngs([latLngs]);
        
        // Sincronizar con los puntos de edición de Leaflet
        if (mapManager.currentPolygonLayer.editing) {
            const editHandler = mapManager.currentPolygonLayer.editing;
            
            // Obtener los handlers de vértices
            if (editHandler._verticesHandlers && editHandler._verticesHandlers.length > 0) {
                const vertexHandler = editHandler._verticesHandlers[0];
                
                // Actualizar las posiciones de los marcadores de edición
                if (vertexHandler && vertexHandler._markerGroup) {
                    // Limpiar marcadores existentes
                    vertexHandler._markerGroup.clearLayers();
                    
                    // Agregar nuevos marcadores en las posiciones actualizadas
                    latLngs.forEach((latLng, index) => {
                        if (index < latLngs.length - 1) { // No agregar el último punto duplicado
                            const marker = L.marker(latLng, {
                                icon: L.divIcon({
                                    className: 'leaflet-edit-move-icon',
                                    iconSize: [20, 20],
                                    iconAnchor: [10, 10]
                                }),
                                draggable: true,
                                zIndexOffset: 10
                            });
                            
                            // Mantener el comportamiento de arrastre de Leaflet
                            vertexHandler._markerGroup.addLayer(marker);
                        }
                    });
                    
                    // Re-conectar los eventos de Leaflet
                    vertexHandler._initMarkers();
                }
            }
            
            // Forzar actualización del polígono en Leaflet
            mapManager.currentPolygonLayer.fire('edit');
        }
        
        // Actualizar campo oculto
        const geoJSON = mapManager.currentPolygonLayer.toGeoJSON();
        if (mapManager.geometryInput) {
            mapManager.geometryInput.value = JSON.stringify(geoJSON.geometry);
        }
        
        // Recalcular área
        if (mapManager.areaInput) {
            const area = mapManager.calculateArea(geoJSON.geometry);
            if (area) {
                mapManager.areaInput.value = area.toFixed(2);
            }
        }
        
        // Actualizar marcadores personalizados
        updatePointMarkers();
        
        // Actualizar resumen
        updatePolygonSummary(points);
        
        return true;
        
    } catch (error) {
        console.error('Error actualizando polígono desde puntos:', error);
        showMessage('Error actualizando polígono: ' + error.message, 'error');
        return false;
    }
}

/**
 * Abrir modal para editar punto
 */
function openEditPointModal(pointIndex) {
    if (pointIndex < 0 || pointIndex >= polygonPoints.length) return;
    
    const point = polygonPoints[pointIndex];
    const modal = document.getElementById('edit-point-modal');
    const title = document.getElementById('edit-point-title');
    const latInput = document.getElementById('edit-point-lat');
    const lngInput = document.getElementById('edit-point-lng');
    const zoneInput = document.getElementById('edit-point-zone');
    const hemisphereSelect = document.getElementById('edit-point-hemisphere');
    const elevationInput = document.getElementById('edit-point-elevation');
    const noteInput = document.getElementById('edit-point-note');
    const indexInput = document.getElementById('edit-point-index');
    
    if (!modal || !point) return;
    
    // Llenar formulario con datos del punto
    title.textContent = `Editar Punto ${pointIndex + 1}`;
    latInput.value = point.lat.toFixed(6);
    lngInput.value = point.lng.toFixed(6);
    zoneInput.value = point.utmZone || 20;
    hemisphereSelect.value = point.utmHemisphere || 'N';
    elevationInput.value = point.elevation || '';
    noteInput.value = point.note || '';
    indexInput.value = pointIndex;
    
    // Mostrar modal
    modal.classList.remove('hidden');
    
    // Enfocar el primer campo
    setTimeout(() => {
        latInput.focus();
        latInput.select();
    }, 100);
}

/**
 * Cerrar modal de edición de punto
 */
function closeEditPointModal() {
    const modal = document.getElementById('edit-point-modal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

/**
 * Eliminar punto del polígono
 */
function deletePoint(pointIndex) {
    if (polygonPoints.length <= 3) {
        showMessage('No se puede eliminar. El polígono debe tener al menos 3 puntos.', 'error');
        return;
    }
    
    // Eliminar punto del array
    polygonPoints.splice(pointIndex, 1);
    
    // Reindexar puntos
    polygonPoints.forEach((point, index) => {
        point.note = `Punto ${index + 1} del polígono`;
    });
    
    // Actualizar polígono
    updatePolygonFromPoints(polygonPoints);
    
    // Actualizar lista
    updatePointsList(polygonPoints);
    
    showMessage(`Punto ${pointIndex + 1} eliminado`, 'success');
    closeEditPointModal();
}

/**
 * Cargar polígono existente desde la base de datos
 */
function loadExistingPolygonFromDB(geoJSON) {
    if (!geoJSON || !mapManager) return null;
    
    try {
        // Limpiar cualquier polígono existente
        mapManager.drawnItems.clearLayers();
        
        // Crear polígono a partir del GeoJSON
        const polygonLayer = L.geoJSON(geoJSON, {
            style: {
                color: '#2b6cb0',
                fillColor: '#2b6cb0',
                fillOpacity: 0.25,
                weight: 3
            },
            onEachFeature: function(feature, layer) {
                // Guardar referencia al polígono
                mapManager.currentPolygonLayer = layer;
                
                // Asegurarse de que esté en el featureGroup
                mapManager.drawnItems.addLayer(layer);
                
                // Ajustar vista al polígono
                mapManager.map.fitBounds(layer.getBounds());
                
                // Extraer puntos del polígono
                polygonPoints = extractPointsFromPolygon(layer);
                
                // Actualizar lista de puntos
                updatePointsList(polygonPoints);
                
                // Calcular área
                if (mapManager.areaInput && (!mapManager.areaInput.value || mapManager.areaInput.value === '0')) {
                    const area = mapManager.calculateArea(geoJSON);
                    if (area) {
                        mapManager.areaInput.value = area.toFixed(2);
                    }
                }
            }
        });
        
        // Agregar al mapa
        polygonLayer.addTo(mapManager.map);
        
        // Habilitar edición inmediatamente
        setTimeout(() => {
            enablePolygonEditing(mapManager);
            document.getElementById('detect-location').disabled = false;
        }, 100);
        
        return polygonLayer;
    } catch (error) {
        console.error('Error cargando polígono:', error);
        showMessage('Error cargando polígono: ' + error.message, 'error');
        return null;
    }
}

/**
 * Habilita la edición de un polígono existente
 */
function enablePolygonEditing(mapManager) {
    if (!mapManager || !mapManager.currentPolygonLayer) {
        console.warn('No hay polígono para editar');
        return;
    }
    
    try {
        // Mostrar botón de edición
        const toggleEditBtn = document.getElementById('toggle-edit');
        if (toggleEditBtn) {
            toggleEditBtn.classList.remove('hidden');
            toggleEditBtn.onclick = () => toggleEditMode();
        }
        
        // Agregar botón para mostrar/ocultar marcadores
        addMarkerToggleControl();
        
        // Asegurarse de que el polígono esté en el featureGroup
        if (!mapManager.drawnItems.hasLayer(mapManager.currentPolygonLayer)) {
            mapManager.drawnItems.addLayer(mapManager.currentPolygonLayer);
        }
        
        // Inicializar handler de edición
        editHandler = new L.EditToolbar.Edit(mapManager.map, {
            featureGroup: mapManager.drawnItems,
            poly: {
                allowIntersection: false,
                drawError: {
                    color: '#e1e4e8',
                    message: '<strong>Error:</strong> ¡El polígono no puede intersectarse consigo mismo!'
                }
            }
        });
        
        // Preparar el polígono para edición
        if (!mapManager.currentPolygonLayer.editing) {
            mapManager.currentPolygonLayer.editing = new L.EditToolbar.Edit(mapManager.map, {
                featureGroup: mapManager.drawnItems
            });
        }
        
        // Habilitar edición por defecto (para que los puntos sean visibles)
        mapManager.currentPolygonLayer.editing.enable();
        
        // Escuchar eventos de edición
        mapManager.currentPolygonLayer.on('edit', function(e) {
            const updatedLayer = e.target;
            const geoJSON = updatedLayer.toGeoJSON();
            
            // Actualizar campo oculto
            if (mapManager.geometryInput) {
                mapManager.geometryInput.value = JSON.stringify(geoJSON.geometry);
            }
            
            // Recalcular área
            if (mapManager.areaInput) {
                const area = mapManager.calculateArea(geoJSON.geometry);
                if (area) {
                    mapManager.areaInput.value = area.toFixed(2);
                }
            }
            
            // Actualizar lista de puntos
            polygonPoints = extractPointsFromPolygon(updatedLayer);
            updatePointsList(polygonPoints);
            
            console.log('Polígono editado', geoJSON);
            showMessage('Polígono actualizado', 'success');
        });
        
        // Escuchar cuando se mueve un punto
        mapManager.currentPolygonLayer.on('editdrag', function(e) {
            const updatedLayer = e.target;
            const geoJSON = updatedLayer.toGeoJSON();
            
            if (mapManager.geometryInput) {
                mapManager.geometryInput.value = JSON.stringify(geoJSON.geometry);
            }
            
            // Actualizar puntos en tiempo real
            polygonPoints = extractPointsFromPolygon(updatedLayer);
            updatePointsList(polygonPoints);
        });
        
        // Agregar popup informativo
        mapManager.currentPolygonLayer.bindPopup(`
            <div class="p-3 min-w-[250px]">
                <div class="flex items-center mb-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900">{{ $polygon->name }}</h4>
                        <p class="text-sm text-gray-600">${polygonPoints.length} puntos</p>
                    </div>
                </div>
                
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Área:</span>
                        <span class="font-semibold">${mapManager.areaInput ? mapManager.areaInput.value + ' Ha' : 'Calculando...'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Puntos:</span>
                        <span class="font-semibold">${polygonPoints.length}</span>
                    </div>
                </div>
                
                <div class="mt-4 pt-3 border-t">
                    <p class="text-xs text-gray-500 mb-2">Usa el panel lateral para editar puntos individualmente</p>
                    <button class="w-full py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded toggle-edit-btn">
                        ✏️ Editar puntos en mapa
                    </button>
                </div>
            </div>
        `);
        
        // Evento para el botón de edición en el popup
        mapManager.currentPolygonLayer.on('popupopen', function() {
            setTimeout(() => {
                const toggleBtn = document.querySelector('.leaflet-popup-content .toggle-edit-btn');
                if (toggleBtn) {
                    toggleBtn.addEventListener('click', () => {
                        toggleEditMode();
                        mapManager.currentPolygonLayer.closePopup();
                    });
                }
            }, 100);
        });
        
        showMessage('Polígono cargado. Usa el panel lateral para editar puntos individualmente.', 'info');
        
    } catch (error) {
        console.error('Error habilitando edición:', error);
        showMessage('Error habilitando edición: ' + error.message, 'error');
    }
}

/**
 * Alternar modo de edición
 */
function toggleEditMode() {
    if (!mapManager || !mapManager.currentPolygonLayer) return;
    
    const toggleEditBtn = document.getElementById('toggle-edit');
    
    try {
        if (!isEditModeActive) {
            // Activar modo edición
            isEditModeActive = true;
            
            // Asegurar que las herramientas de edición estén activas
            if (mapManager.currentPolygonLayer.editing) {
                mapManager.currentPolygonLayer.editing.enable();
            }
            
            // Hacer marcadores arrastrables
            if (window.pointMarkersLayer) {
                window.pointMarkersLayer.eachLayer(function(marker) {
                    marker.dragging.enable();
                });
            }
            
            // Actualizar botón
            if (toggleEditBtn) {
                toggleEditBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check w-6 h-6">
                        <path d="M20 6 9 17l-5-5"/>
                    </svg>
                    Finalizar
                `;
                toggleEditBtn.classList.remove('bg-purple-600', 'hover:bg-purple-700');
                toggleEditBtn.classList.add('bg-green-600', 'hover:bg-green-700');
                toggleEditBtn.title = 'Finalizar edición';
            }
            
            showMessage('Modo edición activado. Arrastra los puntos para modificar el polígono.', 'info');
            
        } else {
            // Desactivar modo edición
            isEditModeActive = false;
            
            // Desactivar herramientas de edición
            if (mapManager.currentPolygonLayer.editing) {
                mapManager.currentPolygonLayer.editing.disable();
            }
            
            // Hacer marcadores no arrastrables
            if (window.pointMarkersLayer) {
                window.pointMarkersLayer.eachLayer(function(marker) {
                    marker.dragging.disable();
                });
            }
            
            // Actualizar botón
            if (toggleEditBtn) {
                toggleEditBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-edit w-6 h-6">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Editar
                `;
                toggleEditBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                toggleEditBtn.classList.add('bg-purple-600', 'hover:bg-purple-700');
                toggleEditBtn.title = 'Editar puntos';
            }
            
            showMessage('Modo edición desactivado', 'info');
        }
    } catch (error) {
        console.error('Error alternando modo edición:', error);
        showMessage('Error alternando modo edición', 'error');
    }
}

/**
 * Mostrar mensaje en la interfaz
 */
function showMessage(message, type = 'info') {
    let messageDiv = document.getElementById('map-message');
    if (!messageDiv) {
        messageDiv = document.createElement('div');
        messageDiv.id = 'map-message';
        messageDiv.style.cssText = `
            position: absolute;
            top: 70px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: none;
            transition: opacity 0.3s;
            font-size: 14px;
            text-align: center;
            max-width: 80%;
            word-wrap: break-word;
        `;
        document.getElementById('map').parentElement.appendChild(messageDiv);
    }
    
    const colors = {
        info: '#3498db',
        success: '#2ecc71',
        warning: '#f39c12',
        error: '#e74c3c'
    };
    
    messageDiv.style.backgroundColor = colors[type] || colors.info;
    messageDiv.style.color = 'white';
    messageDiv.textContent = message;
    messageDiv.style.display = 'block';
    messageDiv.style.opacity = '1';
    
    setTimeout(() => {
        messageDiv.style.opacity = '0';
        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 300);
    }, 3000);
}

/**
 * Dibujar polígono desde coordenadas UTM
 */
function drawUTMPolygonFromUTM(utmCoordinates, mapManager) {
    if (!utmCoordinates || utmCoordinates.length < 3) {
        showMessage('Se necesitan al menos 3 coordenadas', 'error');
        return;
    }
    
    try {
        const wgs84Coords = UTMCoordinates.convertToWGS84(utmCoordinates);
        
        if (wgs84Coords[0][0] !== wgs84Coords[wgs84Coords.length-1][0] || 
            wgs84Coords[0][1] !== wgs84Coords[wgs84Coords.length-1][1]) {
            wgs84Coords.push(wgs84Coords[0]);
        }
        
        mapManager.drawnItems.clearLayers();
        
        const polygon = L.polygon(wgs84Coords, {
            color: '#2b6cb0',
            fillColor: '#2b6cb0',
            fillOpacity: 0.25,
            weight: 3
        }).addTo(mapManager.drawnItems);
        
        mapManager.map.fitBounds(polygon.getBounds());
        
        // Extraer puntos
        polygonPoints = extractPointsFromPolygon(polygon);
        updatePointsList(polygonPoints);
        
        const feature = {
            type: 'Feature',
            geometry: {
                type: 'Polygon',
                coordinates: [wgs84Coords]
            },
            properties: {}
        };
        
        mapManager.updatePolygonData(polygon);
        mapManager.currentPolygonLayer = polygon;
        
        enablePolygonEditing(mapManager);
        document.getElementById('detect-location').disabled = false;
        
        showMessage('Polígono dibujado. Usa el panel lateral para editar puntos.', 'success');
        
    } catch (error) {
        console.error('Error dibujando polígono UTM:', error);
        showMessage('Error dibujando polígono: ' + error.message, 'error');
    }
}

/**
 * Configurar eventos del modal de edición de puntos
 */
function setupPointEditModal() {
    const modal = document.getElementById('edit-point-modal');
    const closeBtn = document.getElementById('close-edit-modal');
    const cancelBtn = document.getElementById('cancel-edit-point');
    const deleteBtn = document.getElementById('delete-point-btn');
    const form = document.getElementById('edit-point-form');
    
    if (!modal) return;
    
    // Cerrar modal
    [closeBtn, cancelBtn].forEach(btn => {
        if (btn) {
            btn.addEventListener('click', closeEditPointModal);
        }
    });
    
    // Eliminar punto
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            const pointIndex = parseInt(document.getElementById('edit-point-index').value);
            if (!isNaN(pointIndex)) {
                if (confirm('¿Estás seguro de que quieres eliminar este punto?')) {
                    deletePoint(pointIndex);
                }
            }
        });
    }
    
    // Guardar cambios
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const pointIndex = parseInt(document.getElementById('edit-point-index').value);
            if (isNaN(pointIndex) || pointIndex < 0 || pointIndex >= polygonPoints.length) {
                showMessage('Error: Índice de punto inválido', 'error');
                return;
            }
            
            // Obtener nuevos valores
            const lat = parseFloat(document.getElementById('edit-point-lat').value);
            const lng = parseFloat(document.getElementById('edit-point-lng').value);
            const zone = parseInt(document.getElementById('edit-point-zone').value);
            const hemisphere = document.getElementById('edit-point-hemisphere').value;
            const elevation = document.getElementById('edit-point-elevation').value;
            const note = document.getElementById('edit-point-note').value;
            
            // Validar coordenadas
            if (isNaN(lat) || isNaN(lng)) {
                showMessage('Por favor ingresa coordenadas válidas', 'error');
                return;
            }
            
            if (lat < -90 || lat > 90) {
                showMessage('La latitud debe estar entre -90 y 90', 'error');
                return;
            }
            
            if (lng < -180 || lng > 180) {
                showMessage('La longitud debe estar entre -180 y 180', 'error');
                return;
            }
            
            // Actualizar punto
            polygonPoints[pointIndex] = {
                ...polygonPoints[pointIndex],
                lat: lat,
                lng: lng,
                utmZone: zone,
                utmHemisphere: hemisphere,
                elevation: elevation || null,
                note: note || `Punto ${pointIndex + 1} del polígono`
            };
            
            // Actualizar polígono
            updatePolygonFromPoints(polygonPoints);
            
            // Actualizar lista
            updatePointsList(polygonPoints);
            
            showMessage(`Punto ${pointIndex + 1} actualizado`, 'success');
            closeEditPointModal();
        });
    }
    
    // Cerrar modal al hacer clic fuera
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeEditPointModal();
        }
    });
}

/**
 * Configurar modal UTM
 */
function setupUTMModal(utmModal) {
    const methodSingleBtn = document.getElementById('method-single');
    const methodBulkBtn = document.getElementById('method-bulk');
    const singleInput = document.getElementById('single-input');
    const bulkInput = document.getElementById('bulk-input');
    const addCoordBtn = document.getElementById('add-coord');
    const clearListBtn = document.getElementById('clear-list');
    const manualForm = document.getElementById('manual-polygon-form');
    const bulkCoordsTextarea = document.getElementById('bulk-coords');
    
    if (!methodSingleBtn || !methodBulkBtn) return;
    
    methodSingleBtn.addEventListener('click', () => {
        methodSingleBtn.classList.add('bg-blue-600', 'text-white');
        methodSingleBtn.classList.remove('bg-gray-200', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
        methodBulkBtn.classList.remove('bg-blue-600', 'text-white');
        methodBulkBtn.classList.add('bg-gray-200', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
        singleInput.classList.remove('hidden');
        bulkInput.classList.add('hidden');
    });
    
    methodBulkBtn.addEventListener('click', () => {
        methodBulkBtn.classList.add('bg-blue-600', 'text-white');
        methodBulkBtn.classList.remove('bg-gray-200', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
        methodSingleBtn.classList.remove('bg-blue-600', 'text-white');
        methodSingleBtn.classList.add('bg-gray-200', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
        bulkInput.classList.remove('hidden');
        singleInput.classList.add('hidden');
    });
    
    if (addCoordBtn) {
        addCoordBtn.addEventListener('click', () => {
            const zone = parseInt(document.getElementById('single-zone').value);
            const hemisphere = document.getElementById('single-hemisphere').value;
            const easting = parseFloat(document.getElementById('single-easting').value);
            const northing = parseFloat(document.getElementById('single-northing').value);
            
            const error = UTMCoordinates.validate(zone, hemisphere, easting, northing);
            if (error) {
                alert(error);
                return;
            }
            
            utmModal.coordinatesList.push([easting, northing, zone, hemisphere]);
            updateCoordsList(utmModal.coordinatesList);
            
            document.getElementById('single-easting').value = '';
            document.getElementById('single-northing').value = '';
        });
    }
    
    function updateCoordsList(coordinatesList) {
        const coordsList = document.getElementById('coords-list');
        const coordsContainer = document.getElementById('coords-container');
        
        if (!coordsList || !coordsContainer) return;
        
        coordsContainer.innerHTML = '';
        
        if (coordinatesList.length === 0) {
            coordsList.classList.add('hidden');
            return;
        }
        
        coordsList.classList.remove('hidden');
        
        coordinatesList.forEach((coord, index) => {
            const [easting, northing, zone, hemisphere] = coord;
            const div = document.createElement('div');
            div.className = 'flex justify-between items-center p-2 bg-white dark:bg-gray-800 rounded';
            div.innerHTML = `
                <div class="text-xs font-mono">
                    <span class="text-gray-600 dark:text-gray-400">${zone}${hemisphere}</span>
                    <span class="mx-2 text-gray-400">|</span>
                    <span class="text-green-600">E:${easting.toLocaleString()}</span>
                    <span class="mx-2 text-gray-400">|</span>
                    <span class="text-blue-600">N:${northing.toLocaleString()}</span>
                </div>
                <button type="button" class="text-red-500 hover:text-red-700 text-xs" data-index="${index}">
                    ✕
                </button>
            `;
            coordsContainer.appendChild(div);
        });
        
        coordsContainer.querySelectorAll('button[data-index]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = parseInt(e.target.closest('button').dataset.index);
                coordinatesList.splice(index, 1);
                updateCoordsList(coordinatesList);
            });
        });
    }
    
    if (clearListBtn) {
        clearListBtn.addEventListener('click', () => {
            utmModal.coordinatesList = [];
            updateCoordsList(utmModal.coordinatesList);
        });
    }
    
    if (manualForm) {
        manualForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            if (methodSingleBtn.classList.contains('bg-blue-600')) {
                if (utmModal.coordinatesList.length < 3) {
                    alert('Se necesitan al menos 3 coordenadas para formar un polígono');
                    return;
                }
                
                utmModal.drawPolygon(utmModal.coordinatesList);
                utmModal.close();
            } else {
                const bulkText = bulkCoordsTextarea.value.trim();
                if (!bulkText) {
                    alert('Ingresa coordenadas en el área de texto');
                    return;
                }
                
                const lines = bulkText.split('\n').filter(line => line.trim());
                const bulkCoords = [];
                
                for (const line of lines) {
                    const parts = line.split(',').map(part => part.trim());
                    if (parts.length !== 4) continue;
                    
                    const [zoneStr, hemisphere, eastingStr, northingStr] = parts;
                    const zone = parseInt(zoneStr);
                    const easting = parseFloat(eastingStr);
                    const northing = parseFloat(northingStr);
                    
                    const error = UTMCoordinates.validate(zone, hemisphere, easting, northing);
                    if (error) {
                        alert(`Error en línea: ${line}\n${error}`);
                        return;
                    }
                    
                    bulkCoords.push([easting, northing, zone, hemisphere]);
                }
                
                if (bulkCoords.length < 3) {
                    alert('Se necesitan al menos 3 coordenadas válidas');
                    return;
                }
                
                utmModal.drawPolygon(bulkCoords);
                utmModal.close();
            }
        });
    }
}

/**
 * Inicialización principal
 */
document.addEventListener('DOMContentLoaded', () => {
    // Inicializar gestor del mapa
    mapManager = new PolygonMapManager('map', {
        geometryInput: document.getElementById('geometry'),
        coordsDisplay: document.getElementById('coordinates-display'),
        detectBtn: document.getElementById('detect-location'),
        areaInput: document.getElementById('area_ha')
    });
    
    // Configurar modal de edición de puntos
    setupPointEditModal();
    
    // Cargar polígono existente si está disponible
    @if($polygon->getGeometryGeoJson())
        const existingPolygonGeoJSON = @json($polygon->getGeometryGeoJson());
        if (existingPolygonGeoJSON) {
            loadExistingPolygonFromDB(existingPolygonGeoJSON);
        }
    @endif
    
    // Inicializar detector de ubicación
    const locationDetector = new LocationDetector({
        csrfToken: '{{ csrf_token() }}',
        findParishUrl: '{{ route("polygons.find-parish-api") }}'
    });
    
    // Inicializar modal UTM
    const utmModal = new UTMModalManager({
        modalId: 'manual-polygon-modal',
        onDrawPolygon: (utmCoordinates) => {
            drawUTMPolygonFromUTM(utmCoordinates, mapManager);
        }
    });
    
    // Configurar el modal UTM
    setupUTMModal(utmModal);
    
    // Referencias a elementos
    const drawBtn = document.getElementById('draw-polygon');
    const detectBtn = document.getElementById('detect-location');
    const clearBtn = document.getElementById('clear-map');
    const manualPolygonToggle = document.getElementById('manual-polygon-toggle');
    
    // Event Listeners para controles básicos
    drawBtn.addEventListener('click', () => {
        new L.Draw.Polygon(mapManager.map, DrawConfig.polygon).enable();
    });
    
    clearBtn.addEventListener('click', () => {
        mapManager.clearMap();
        document.getElementById('toggle-edit').classList.add('hidden');
        document.getElementById('location-info').classList.add('hidden');
        document.getElementById('detect-location').disabled = true;
        isEditModeActive = false;
        
        // Limpiar puntos y marcadores
        polygonPoints = [];
        updatePointsList(polygonPoints);
        
        if (window.pointMarkersLayer) {
            window.pointMarkersLayer.clearLayers();
        }
        if (window.connectionsLayer) {
            window.connectionsLayer.clearLayers();
        }
    });
    
    // Abrir modal UTM
    if (manualPolygonToggle) {
        manualPolygonToggle.addEventListener('click', () => {
            utmModal.open();
        });
    }
    
    // Escuchar cuando se dibuja un nuevo polígono
    mapManager.map.on(L.Draw.Event.CREATED, function(event) {
        setTimeout(() => {
            enablePolygonEditing(mapManager);
            document.getElementById('detect-location').disabled = false;
            
            // Extraer puntos del nuevo polígono
            polygonPoints = extractPointsFromPolygon(event.layer);
            updatePointsList(polygonPoints);
        }, 100);
    });
    
    // Detectar ubicación
    detectBtn.addEventListener('click', async () => {
        await handleLocationDetection(mapManager, locationDetector);
    });
    
    // Validación del formulario
    document.getElementById('polygon-form').addEventListener('submit', function (e) {
        if (!validatePolygonForm(mapManager, this)) {
            e.preventDefault();
        }
    });
});

// Funciones auxiliares que deben mantenerse igual
async function handleLocationDetection(mapManager, locationDetector) {
    const val = mapManager.geometryInput?.value;
    if (!val) {
        showMessage('❌ Debes tener un polígono en el mapa', 'error');
        return;
    }
    
    let feature;
    try {
        feature = JSON.parse(val);
    } catch (e) {
        showMessage('❌ GeoJSON inválido', 'error');
        return;
    }
    
    const centroid = mapManager.calculateCentroid(feature);
    if (!centroid) {
        showMessage('❌ No se pudo calcular el centroide', 'error');
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
        const data = await locationDetector.detectLocation(centroid.lat, centroid.lng);
        
        const address = data.address || {};
        const municipality = address.county || address.suburb || address.village || address.town || address.city || '';
        const parish = address.municipality || address.county || address.city || '';
        const state = address.state || address.region || '';
        
        const cleanParish = locationDetector.cleanLocationString(parish);
        const cleanMunicipality = locationDetector.cleanLocationString(municipality);
        const cleanState = locationDetector.cleanLocationString(state);
        
        document.getElementById('detected_parish').value = cleanParish;
        document.getElementById('detected_municipality').value = cleanMunicipality;
        document.getElementById('detected_state').value = cleanState;
        
        updateLocationInfoUI(cleanParish, cleanMunicipality, cleanState, centroid);
        
        const assignResult = await locationDetector.findAndAssignParish(
            cleanParish,
            cleanMunicipality,
            cleanState
        );
        
        if (assignResult.success && assignResult.parish) {
            document.getElementById('parish_id').value = assignResult.parish.id;
            showMessage('✅ Parroquia encontrada y asignada', 'success');
        } else {
            showMessage('ℹ️ No se encontró parroquia exacta. Selecciona manualmente.', 'info');
        }
        
    } catch (error) {
        console.error('Error en detección de ubicación:', error);
        showMessage('❌ Error detectando ubicación', 'error');
    } finally {
        detectBtn.disabled = false;
        detectButtonText.textContent = originalText;
    }
}

function updateLocationInfoUI(parish, municipality, state, centroid) {
    document.getElementById('detected-parish-text').textContent = parish || 'No detectado';
    document.getElementById('detected-municipality-text').textContent = municipality || 'No detectado';
    document.getElementById('detected-state-text').textContent = state || 'No detectado';
    
    if (centroid) {
        document.getElementById('detected-coords-text').textContent = 
            `${centroid.lat.toFixed(6)}, ${centroid.lng.toFixed(6)}`;
    }
    
    document.getElementById('location-info').classList.remove('hidden');
}

function validatePolygonForm(mapManager, form) {
    const val = mapManager.geometryInput?.value;
    if (!val) {
        showMessage('❌ Debes tener un polígono en el mapa', 'error');
        return false;
    }
    
    const nameInput = document.getElementById('name');
    if (!nameInput.value.trim()) {
        nameInput.focus();
        showMessage('❌ El nombre del polígono es requerido', 'error');
        return false;
    }
    
    try {
        const parsed = JSON.parse(val);
        const feature = (parsed.type && parsed.type === 'Feature') ? 
            parsed : { type: 'Feature', geometry: parsed };
        const geom = feature.geometry;
        
        if (!geom || !geom.type || !['Polygon', 'MultiPolygon'].includes(geom.type)) {
            showMessage('❌ La geometría debe ser Polygon o MultiPolygon', 'error');
            return false;
        }
        
        if (geom.type === 'Polygon' && geom.coordinates && geom.coordinates[0]) {
            const points = geom.coordinates[0];
            if (points.length < 4) {
                showMessage('❌ El polígono debe tener al menos 3 puntos distintos', 'error');
                return false;
            }
        }
        
        const submitBtn = document.getElementById('submit-btn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Actualizando...';
        }
        
        return true;
    } catch (err) {
        showMessage('❌ Geometría inválida (JSON)', 'error');
        return false;
    }
}
</script>

<style>
/* Estilos para los marcadores de puntos */
.custom-polygon-point-marker {
    background: transparent;
    border: none;
}

.point-marker-container {
    position: relative;
    width: 40px;
    height: 50px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.point-marker-icon {
    width: 44px;
    height: 44px;
    color: #456deeff; /* Rojo para el ícono de ubicación */
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
    transition: all 0.3s ease;
}

.point-marker-label {
    position: absolute;
    top: 9px;
    width: 22px;
    height: 22px;
    background: white;
    border: 2px solid #3b82f6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 12px;
    color: #1e40af;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    z-index: 10;
}

/* Efectos hover para marcadores */
.custom-polygon-point-marker:hover .point-marker-icon {
    color: #9fcffcff; /* Rojo más oscuro al hover */
    transform: scale(1.1);
}

.custom-polygon-point-marker:hover .point-marker-label {
    background: #3b82f6;
    color: white;
    transform: scale(1.1);
}

/* Líneas de conexión entre puntos */
.point-connection-line {
    pointer-events: none;
}

/* Estilos para controles de edición */
#toggle-edit {
    transition: all 0.3s ease;
}

.leaflet-editing-icon {
    background-color: #8b5cf6 !important;
    border-color: #7c3aed !important;
}

.leaflet-marker-icon.leaflet-div-icon {
    background: transparent !important;
    border: none !important;
}

/* Estilo para polígono en modo edición */
.leaflet-polygon-editing {
    stroke-dasharray: 10, 10 !important;
    stroke-width: 4px !important;
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

/* Estilo para puntos de vértice */
.leaflet-div-icon {
    background: transparent !important;
    border: none !important;
}

.leaflet-marker-icon {
    border-radius: 50% !important;
}

/* Scroll personalizado */
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

#points-container::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}

.dark #points-container::-webkit-scrollbar-track {
    background: #374151;
}

.dark #points-container::-webkit-scrollbar-thumb {
    background: #4b5563;
}

.dark #points-container::-webkit-scrollbar-thumb:hover {
    background: #6b7280;
}

/* Estilos para popups de Leaflet */
.leaflet-popup-content {
    margin: 0;
    padding: 0;
}

.leaflet-popup-content-wrapper {
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

/* Estilos para los elementos de puntos en el panel */
.bg-white.dark\\:bg-gray-800:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.1);
}
</style>
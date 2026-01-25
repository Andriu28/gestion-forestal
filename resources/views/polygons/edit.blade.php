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

                    <div class="grid grid-cols-1 gap-6">
                        <!-- Columna del Mapa -->
                        <div>
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
                                    </div>
                                </div>

                                <!-- Coordenadas en tiempo real -->
                                <div class="absolute left-1/2 bottom-4 transform -translate-x-1/2 z-40 bg-gray-50 dark:bg-gray-800 p-2 rounded text-sm shadow">
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

                                    <!-- Campos ocultos para la geometría y detección -->
                                    <input type="hidden" id="geometry" name="geometry" value="{{ old('geometry') }}" required>
                                    <input type="hidden" id="detected_parish" name="detected_parish" value="{{ old('detected_parish', $polygon->detected_parish) }}">
                                    <input type="hidden" id="detected_municipality" name="detected_municipality" value="{{ old('detected_municipality', $polygon->detected_municipality) }}">
                                    <input type="hidden" id="detected_state" name="detected_state" value="{{ old('detected_state', $polygon->detected_state) }}">
                                    <input type="hidden" id="centroid_lat" name="centroid_lat" value="{{ old('centroid_lat', $polygon->centroid_lat) }}">
                                    <input type="hidden" id="centroid_lng" name="centroid_lng" value="{{ old('centroid_lng', $polygon->centroid_lng) }}">
                                    
                                    <!-- Mostrar información de detección si existe -->
                                    <div id="location-info" class="p-4 bg-blue-50 dark:bg-blue-900 rounded-lg {{ !$polygon->detected_parish && !$polygon->detected_municipality && !$polygon->detected_state ? 'hidden' : '' }}">
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
                                    <div class="pt-4">
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
</x-app-layout>

<!-- Estilos y librerías -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.8.0/proj4.js"></script>


<!-- Estilos y librerías -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.8.0/proj4.js"></script>

<!-- Cargar utilidades primero -->

<script src="{{ asset('js/polygon/polygon-map-utils.js') }}"></script>


<script>

document.addEventListener('DOMContentLoaded', () => {
    // Inicializar gestor del mapa
    const mapManager = new PolygonMapManager('map', {
        geometryInput: document.getElementById('geometry'),
        coordsDisplay: document.getElementById('coordinates-display'),
        detectBtn: document.getElementById('detect-location'),
        areaInput: document.getElementById('area_ha')
    });
    
    // Cargar polígono existente si está disponible
    @if($polygon->getGeometryGeoJson())
        const existingPolygonGeoJSON = @json($polygon->getGeometryGeoJson());
        const loaded = mapManager.loadExistingPolygon(existingPolygonGeoJSON);
        
        if (loaded) {
            // Habilitar el botón de detección ya que hay un polígono cargado
            document.getElementById('detect-location').disabled = false;
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
    
    // Configurar el modal UTM (funciones específicas del modal)
    setupUTMModal(utmModal);
    
    // Referencias a elementos
    const drawBtn = document.getElementById('draw-polygon');
    const detectBtn = document.getElementById('detect-location');
    const clearBtn = document.getElementById('clear-map');
    
    // Event Listeners para controles básicos
    drawBtn.addEventListener('click', () => {
        new L.Draw.Polygon(mapManager.map, DrawConfig.polygon).enable();
    });
    
    clearBtn.addEventListener('click', () => {
        mapManager.clearMap();
        // Ocultar información de ubicación al limpiar
        document.getElementById('location-info').classList.add('hidden');
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


function drawUTMPolygonFromUTM(utmCoordinates, mapManager) {
    if (!utmCoordinates || utmCoordinates.length < 3) {
        mapManager.showMessage('Se necesitan al menos 3 coordenadas', 'error');
        return;
    }
    
    try {
        // Convertir coordenadas UTM a WGS84
        const wgs84Coords = UTMCoordinates.convertToWGS84(utmCoordinates);
        
        // Crear polígono cerrado
        if (wgs84Coords[0][0] !== wgs84Coords[wgs84Coords.length-1][0] || 
            wgs84Coords[0][1] !== wgs84Coords[wgs84Coords.length-1][1]) {
            wgs84Coords.push(wgs84Coords[0]);
        }
        
        // Limpiar mapa existente
        mapManager.drawnItems.clearLayers();
        
        // Crear y añadir polígono
        const polygon = L.polygon(wgs84Coords, {
            color: '#2b6cb0',
            fillColor: '#2b6cb0',
            fillOpacity: 0.25,
            weight: 3
        }).addTo(mapManager.drawnItems);
        
        // Ajustar vista
        mapManager.map.fitBounds(polygon.getBounds());
        
        // Crear feature GeoJSON
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
        mapManager.showMessage('Polígono dibujado desde coordenadas UTM', 'success');
        
    } catch (error) {
        console.error('Error dibujando polígono UTM:', error);
        mapManager.showMessage('Error dibujando polígono', 'error');
    }
}


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
    
    // Cambiar entre métodos de entrada
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
    
    // Agregar coordenada individual
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
            
            // Limpiar inputs
            document.getElementById('single-easting').value = '';
            document.getElementById('single-northing').value = '';
        });
    }
    
    // Actualizar lista de coordenadas
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
        
        // Agregar event listeners para eliminar
        coordsContainer.querySelectorAll('button[data-index]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = parseInt(e.target.closest('button').dataset.index);
                coordinatesList.splice(index, 1);
                updateCoordsList(coordinatesList);
            });
        });
    }
    
    // Limpiar lista
    if (clearListBtn) {
        clearListBtn.addEventListener('click', () => {
            utmModal.coordinatesList = [];
            updateCoordsList(utmModal.coordinatesList);
        });
    }
    
    // Manejar envío del formulario
    if (manualForm) {
        manualForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            if (methodSingleBtn.classList.contains('bg-blue-600')) {
                // Método individual
                if (utmModal.coordinatesList.length < 3) {
                    alert('Se necesitan al menos 3 coordenadas para formar un polígono');
                    return;
                }
                
                utmModal.drawPolygon(utmModal.coordinatesList);
                utmModal.close();
            } else {
                // Método por lote
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
 * Manejar detección de ubicación
 */
async function handleLocationDetection(mapManager, locationDetector) {
    const val = mapManager.geometryInput?.value;
    if (!val) {
        mapManager.showMessage('❌ Debes tener un polígono en el mapa', 'error');
        return;
    }
    
    let feature;
    try {
        feature = JSON.parse(val);
    } catch (e) {
        mapManager.showMessage('❌ GeoJSON inválido', 'error');
        return;
    }
    
    const centroid = mapManager.calculateCentroid(feature);
    if (!centroid) {
        mapManager.showMessage('❌ No se pudo calcular el centroide', 'error');
        return;
    }
    
    // Actualizar campos ocultos
    document.getElementById('centroid_lat').value = centroid.lat;
    document.getElementById('centroid_lng').value = centroid.lng;
    
    // Deshabilitar botón y mostrar carga
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
        
        // Limpiar nombres
        const cleanParish = locationDetector.cleanLocationString(parish);
        const cleanMunicipality = locationDetector.cleanLocationString(municipality);
        const cleanState = locationDetector.cleanLocationString(state);
        
        // Actualizar campos ocultos
        document.getElementById('detected_parish').value = cleanParish;
        document.getElementById('detected_municipality').value = cleanMunicipality;
        document.getElementById('detected_state').value = cleanState;
        
        // Actualizar interfaz
        updateLocationInfoUI(cleanParish, cleanMunicipality, cleanState, centroid);
        
        // Intentar asignar parroquia
        const assignResult = await locationDetector.findAndAssignParish(
            cleanParish,
            cleanMunicipality,
            cleanState
        );
        
        if (assignResult.success && assignResult.parish) {
            document.getElementById('parish_id').value = assignResult.parish.id;
            mapManager.showMessage('✅ Parroquia encontrada y asignada', 'success');
        } else {
            mapManager.showMessage('ℹ️ No se encontró parroquia exacta. Selecciona manualmente.', 'info');
        }
        
    } catch (error) {
        console.error('Error en detección de ubicación:', error);
        mapManager.showMessage('❌ Error detectando ubicación', 'error');
    } finally {
        detectBtn.disabled = false;
        detectButtonText.textContent = originalText;
    }
}

/**
 * Actualizar UI de información de ubicación
 */
function updateLocationInfoUI(parish, municipality, state, centroid) {
    // Actualizar texto en la interfaz
    document.getElementById('detected-parish-text').textContent = parish || 'No detectado';
    document.getElementById('detected-municipality-text').textContent = municipality || 'No detectado';
    document.getElementById('detected-state-text').textContent = state || 'No detectado';
    
    if (centroid) {
        document.getElementById('detected-coords-text').textContent = 
            `${centroid.lat.toFixed(6)}, ${centroid.lng.toFixed(6)}`;
    }
    
    // Mostrar el contenedor de información
    document.getElementById('location-info').classList.remove('hidden');
}

/**
 * Validación del formulario
 */
function validatePolygonForm(mapManager, form) {
    const val = mapManager.geometryInput?.value;
    if (!val) {
        mapManager.showMessage('❌ Debes tener un polígono en el mapa', 'error');
        return false;
    }
    
    const nameInput = document.getElementById('name');
    if (!nameInput.value.trim()) {
        nameInput.focus();
        mapManager.showMessage('❌ El nombre del polígono es requerido', 'error');
        return false;
    }
    
    try {
        const parsed = JSON.parse(val);
        const feature = (parsed.type && parsed.type === 'Feature') ? 
            parsed : { type: 'Feature', geometry: parsed };
        const geom = feature.geometry;
        
        if (!geom || !geom.type || !['Polygon', 'MultiPolygon'].includes(geom.type)) {
            mapManager.showMessage('❌ La geometría debe ser Polygon o MultiPolygon', 'error');
            return false;
        }
        
        // Mostrar carga en el botón de envío
        const submitBtn = document.getElementById('submit-btn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Actualizando...';
        }
        
        return true;
    } catch (err) {
        mapManager.showMessage('❌ Geometría inválida (JSON)', 'error');
        return false;
    }
}
</script>
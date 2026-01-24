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

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Inicializar mapa
    let map = L.map('map').setView([8.0, -66.0], 6);
    
    // Capa base simple
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { 
        attribution: '© OpenStreetMap contributors' 
    }).addTo(map);

    const drawnItems = new L.FeatureGroup().addTo(map);
    let currentPolygonLayer = null;
    
    // Cargar el polígono existente
    let existingPolygonGeoJSON = null;
    @if($polygon->getGeometryGeoJson())
        existingPolygonGeoJSON = @json($polygon->getGeometryGeoJson());
    @endif
    
    // Dibujar polígono existente si hay datos
    if (existingPolygonGeoJSON) {
        try {
            // Crear feature a partir del GeoJSON
            const geoJsonLayer = L.geoJSON(existingPolygonGeoJSON, {
                style: {
                    color: '#2b6cb0',
                    fillColor: '#2b6cb0',
                    fillOpacity: 0.25,
                    weight: 3
                }
            }).addTo(drawnItems);
            
            // Ajustar vista al polígono
            map.fitBounds(geoJsonLayer.getBounds());
            
            // Configurar como capa actual
            currentPolygonLayer = geoJsonLayer;
            
            // Llenar el campo geometry
            const feature = geoJsonLayer.toGeoJSON();
            document.getElementById('geometry').value = JSON.stringify(feature);
            
            // Actualizar área
            updateArea(feature);
            
            // Habilitar botón de detección
            document.getElementById('detect-location').disabled = false;
            
            // Mostrar coordenadas del centroide si están disponibles
            const centroid = calcCentroidFromFeature(feature);
            if (centroid) {
                document.getElementById('coordinates-display').textContent = 
                    `Lat: ${centroid.lat.toFixed(6)} | Lng: ${centroid.lng.toFixed(6)}`;
            }
            
        } catch (error) {
            console.error('Error al cargar polígono existente:', error);
            showMessage('Error al cargar el polígono existente', 'error');
        }
    }
    
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
        edit: { 
            featureGroup: drawnItems,
            edit: {
                selectedPathOptions: {
                    maintainColor: true
                }
            }
        }
    });

    map.addControl(drawControl);

    // Referencias a elementos
    const drawBtn = document.getElementById('draw-polygon');
    const detectBtn = document.getElementById('detect-location');
    const clearBtn = document.getElementById('clear-map');
    const geometryInput = document.getElementById('geometry');
    const coordsDisplay = document.getElementById('coordinates-display');

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
        
        const avgLat = coords.reduce((sum, coord) => sum + coord[1], 0) / n;
        const kmPerDegreeLat = 111.32;
        const kmPerDegreeLon = 111.32 * Math.cos(avgLat * Math.PI / 180);
        
        const areaKm2 = area * kmPerDegreeLat * kmPerDegreeLon;
        const areaHa = areaKm2 * 100;
        
        return Math.round(areaHa * 100) / 100;
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

    // Event Listeners para controles de mapa
    drawBtn.addEventListener('click', () => {
        new L.Draw.Polygon(map, drawControl.options.draw.polygon).enable();
    });

    clearBtn.addEventListener('click', () => {
        drawnItems.clearLayers();
        geometryInput.value = '';
        detectBtn.disabled = true;
        document.getElementById('area_ha').value = '';
        coordsDisplay.textContent = 'Lat: 0.000000 | Lng: 0.000000';
        currentPolygonLayer = null;
        showMessage('Mapa limpiado', 'info');
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
        showMessage('Polígono dibujado', 'success');
    });

    // Evento de edición de polígono
    map.on(L.Draw.Event.EDITED, function (e) {
        const layers = e.layers;
        layers.eachLayer(function (layer) {
            const feature = layer.toGeoJSON();
            setFeatureToInput(feature);
            updateArea(feature);
            
            detectBtn.disabled = false;
            const centroid = calcCentroidFromFeature(feature);
            if (centroid) coordsDisplay.textContent = `Lat: ${centroid.lat.toFixed(6)} | Lng: ${centroid.lng.toFixed(6)}`;
            
            currentPolygonLayer = layer;
            showMessage('Polígono editado', 'success');
        });
    });

    map.on('mousemove', (e) => {
        if (!geometryInput.value) {
            coordsDisplay.textContent = `Lat: ${e.latlng.lat.toFixed(6)} | Lng: ${e.latlng.lng.toFixed(6)}`;
        }
    });

    // Detectar ubicación - FUNCIÓN CORREGIDA
    detectBtn.addEventListener('click', async () => {
        const val = geometryInput.value;
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

        const centroid = calcCentroidFromFeature(feature);
        if (!centroid) { 
            showMessage('❌ No se pudo calcular el centroide', 'error'); 
            return; 
        }

        // Actualizar campos ocultos con el nuevo centroide
        document.getElementById('centroid_lat').value = centroid.lat;
        document.getElementById('centroid_lng').value = centroid.lng;

        detectBtn.disabled = true;
        const detectButtonText = document.getElementById('detect-button-text');
        const originalText = detectButtonText.textContent;
        detectButtonText.textContent = 'Detectando...';

        try {
            const resp = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${centroid.lat}&lon=${centroid.lng}&zoom=18&addressdetails=1&accept-language=es`, {
                headers: { 
                    'User-Agent': 'PolygonApp/1.0',
                    'Accept': 'application/json'
                }
            });
            
            if (!resp.ok) throw new Error('Error en Nominatim');
            
            const data = await resp.json();
            console.log('Datos de Nominatim:', data);

            const address = data.address || {};
            const municipality = address.county || address.suburb || address.village || address.town || address.city || '';
            const parish = address.municipality || address.county || address.city || '';
            const state = address.state || address.region || '';

            // Limpiar prefijos de las variables
            const cleanParish = parish ? parish.toString().replace('Parroquia ', '').replace('Municipio ', '').trim() : '';
            const cleanMunicipality = municipality ? municipality.toString().replace('Municipio ', '').trim() : '';
            const cleanState = state ? state.toString().replace('Estado ', '').trim() : '';

            // Actualizar campos ocultos
            document.getElementById('detected_parish').value = cleanParish;
            document.getElementById('detected_municipality').value = cleanMunicipality;
            document.getElementById('detected_state').value = cleanState;

            // Actualizar la interfaz
            document.getElementById('detected-parish-text').textContent = cleanParish || 'No detectado';
            document.getElementById('detected-municipality-text').textContent = cleanMunicipality || 'No detectado';
            document.getElementById('detected-state-text').textContent = cleanState || 'No detectado';
            document.getElementById('detected-coords-text').textContent = `${centroid.lat.toFixed(6)}, ${centroid.lng.toFixed(6)}`;

            document.getElementById('location-info').classList.remove('hidden');

            // Intentar asignar parroquia automáticamente
            try {
                const assignResp = await fetch('{{ route("polygons.find-parish-api") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        parish_name: cleanParish ? cleanParish.toLowerCase().trim() : '',
                        municipality_name: cleanMunicipality ? cleanMunicipality.toLowerCase().trim() : '',
                        state_name: cleanState ? cleanState.toLowerCase().trim() : ''
                    })
                });
                
                const assignJson = await assignResp.json();
                if (assignJson.success && assignJson.parish) {
                    document.getElementById('parish_id').value = assignJson.parish.id;
                    showMessage('✅ Parroquia encontrada y asignada', 'success');
                } else {
                    showMessage('ℹ️ No se encontró parroquia exacta. Selecciona manualmente.', 'info');
                }
            } catch (e) {
                console.warn('Asignación parroquia fallida', e);
                showMessage('⚠️ No se pudo asignar parroquia automáticamente', 'warning');
            }
        } catch (err) {
            console.error('Error detectando ubicación:', err);
            showMessage('❌ Error detectando ubicación', 'error');
        } finally {
            detectBtn.disabled = false;
            detectButtonText.textContent = originalText;
        }
    });

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

    // Validación del formulario
    document.getElementById('polygon-form').addEventListener('submit', function (e) {
        const val = geometryInput.value;
        if (!val) {
            e.preventDefault();
            showMessage('❌ Debes tener un polígono en el mapa', 'error');
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
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Actualizando...';
            
            return true;
        } catch (err) {
            e.preventDefault();
            showMessage('❌ Geometría inválida (JSON)', 'error');
            return false;
        }
    });
});

// ===== CÓDIGO PARA EL MODAL DE COORDENADAS MANUALES =====
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

// Inicialización del modal
document.addEventListener('DOMContentLoaded', function() {
    // Modal functionality
    const modal = document.getElementById('manual-polygon-modal');
    const openModalBtn = document.getElementById('manual-polygon-toggle');
    const closeModalBtn = document.getElementById('close-modal');
    const cancelModalBtn = document.getElementById('cancel-modal');
    const methodSingleBtn = document.getElementById('method-single');
    const methodBulkBtn = document.getElementById('method-bulk');
    const singleInput = document.getElementById('single-input');
    const bulkInput = document.getElementById('bulk-input');
    const coordsList = document.getElementById('coords-list');
    const coordsContainer = document.getElementById('coords-container');
    const addCoordBtn = document.getElementById('add-coord');
    const clearListBtn = document.getElementById('clear-list');
    const manualForm = document.getElementById('manual-polygon-form');

    // Open modal
    openModalBtn.addEventListener('click', () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => modal.classList.add('opacity-100'), 10);
    });

    // Close modal
    function closeModal() {
        modal.classList.remove('opacity-100');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            coordinatesList = [];
            updateCoordsList();
            singleInput.classList.remove('hidden');
            bulkInput.classList.add('hidden');
            methodSingleBtn.classList.add('bg-blue-600', 'text-white');
            methodSingleBtn.classList.remove('bg-gray-200', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
            methodBulkBtn.classList.remove('bg-blue-600', 'text-white');
            methodBulkBtn.classList.add('bg-gray-200', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
        }, 300);
    }

    closeModalBtn.addEventListener('click', closeModal);
    cancelModalBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    // Switch between input methods
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

    // Add coordinate from single input
    addCoordBtn.addEventListener('click', () => {
        const zone = parseInt(document.getElementById('single-zone').value);
        const hemisphere = document.getElementById('single-hemisphere').value;
        const easting = parseFloat(document.getElementById('single-easting').value);
        const northing = parseFloat(document.getElementById('single-northing').value);

        const error = validateUTMCoordinates(zone, hemisphere, easting, northing);
        if (error) {
            alert(error);
            return;
        }

        coordinatesList.push([easting, northing, zone, hemisphere]);
        updateCoordsList();
        
        // Clear inputs
        document.getElementById('single-easting').value = '';
        document.getElementById('single-northing').value = '';
    });

    // Update coordinates list display
    function updateCoordsList() {
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

        // Add remove event listeners
        coordsContainer.querySelectorAll('button[data-index]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = parseInt(e.target.closest('button').dataset.index);
                coordinatesList.splice(index, 1);
                updateCoordsList();
            });
        });
    }

    // Clear list
    clearListBtn.addEventListener('click', () => {
        coordinatesList = [];
        updateCoordsList();
    });

    // Handle form submission
    manualForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        if (methodSingleBtn.classList.contains('bg-blue-600')) {
            // Using single input method
            if (coordinatesList.length < 3) {
                alert('Se necesitan al menos 3 coordenadas para formar un polígono');
                return;
            }
            
            // Draw polygon from single coordinates
            if (typeof drawUTMPolygon === 'function') {
                drawUTMPolygon(coordinatesList);
                closeModal();
            }
        } else {
            // Using bulk input method
            const bulkText = document.getElementById('bulk-coords').value.trim();
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
                
                const error = validateUTMCoordinates(zone, hemisphere, easting, northing);
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
            
            // Draw polygon from bulk coordinates
            if (typeof drawUTMPolygon === 'function') {
                drawUTMPolygon(bulkCoords);
                closeModal();
            }
        }
    });
});
</script>
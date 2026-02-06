{{-- [file name]: create.blade.php --}}
<x-app-layout>
    <div class="mx-auto">
        <div class="bg-stone-100/90 dark:bg-custom-gray shadow-sm sm:rounded-2xl shadow-soft p-4 md:p-6 lg:p-6 mb-6">
            <div class="text-gray-900 dark:text-gray-100">
                <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-200 mb-2 md:mb-2">
                   Crear Nuevo Polígono
                </h2>

                <form action="{{ route('polygons.store') }}" method="POST" id="polygon-form" novalidate>
                    @csrf

                    <div class="grid grid-cols-1 gap-6">
                        <!-- Columna del Mapa -->
                        <div class="lg:col-span-1">
                            <x-input-label for="map" class="sr-only">Mapa</x-input-label>
                            <div class="relative rounded-lg overflow-hidden mb-6 border border-gray-200 dark:border-gray-700 mt-1" style="height: 77vh; border: 1px solid #dededeff; border-radius: 0.5rem; position: relative;">
                                <div id="map" class="h-full w-full"></div>

                                <!-- Controles del mapa -->
                                <div id="map-controls" class="absolute top-4 right-4 z-50 flex flex-col">
                                    <div class="flex space-x-2">
                                        <!-- Cambiar Mapa -->
                                        <div class="relative">
                                            <button id="base-map-toggle" type="button" title="Cambiar mapa" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg flex items-center shadow-lg transition-colors">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                                </svg>
                                                Mapas
                                            </button>
                                            
                                            <div id="base-map-menu"
                                                class="absolute mt-2 w-48 rounded-xl shadow-lg bg-white dark:bg-custom-gray ring-1 ring-black ring-opacity-5 z-10 right-0
                                                        transition-all duration-300 ease-out scale-95 opacity-0 pointer-events-none hidden">
                                                <div class="absolute -top-2 right-6 w-8 h-2 pointer-events-none">
                                                    <svg viewBox="0 0 16 8" class="w-4 h-2 text-white dark:text-custom-gray">
                                                        <polygon points="8,0 16,8 0,8" fill="currentColor"/>
                                                    </svg>
                                                </div>
                                                <div class="py-2" role="menu" aria-orientation="vertical">
                                                    <button data-layer="osm" type="button" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">OpenStreetMap</button>
                                                    <button data-layer="satellite" type="button" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">Satélite Esri</button>
                                                    <button data-layer="maptiler_satellite" type="button" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">MapTiler Satélite</button>
                                                    <button data-layer="terrain" type="button" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">Relieve</button>
                                                    <button data-layer="dark" type="button" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">Oscuro</button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Pantalla Completa -->
                                        <button id="fullscreen-toggle" type="button" title="Pantalla Completa" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg flex items-center justify-center shadow-lg transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Segunda fila de controles -->
                                    <div class="flex flex-col space-y-2">
                                        <!-- Coordenadas Manuales -->
                                        <button id="manual-polygon-toggle" type="button" title="Escribir Coordenadas" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg flex items-center shadow-lg transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M13 21h8"/>
                                                <path d="m15 5 4 4"/>
                                                <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>
                                            </svg>
                                        </button>
                                         
                                        <!-- Dibujar Polígono -->
                                        <button type="button" id="draw-polygon" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg flex items-center shadow-lg transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                            </svg>
                                        </button>

                                        <!-- Limpiar Mapa -->
                                        <button type="button" id="clear-map" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg flex items-center shadow-lg transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Coordenadas UTM en tiempo real -->
                                <div id="coordinate-display" class="absolute left-2 bottom-4 z-10 bg-white/90 dark:bg-gray-800/90 px-3 py-2 rounded-lg text-sm shadow-lg font-mono border border-gray-300 dark:border-gray-600 backdrop-blur-sm">
                                    Zona XXN | Este: 000000.000 | Norte: 0000000.000
                                </div>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('geometry')" />
                        </div>

                        <!-- Columna del Formulario (COMPLETA) -->
                        <div class="lg:col-span-1">
                            <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden sm:rounded-2xl p-4 md:p-6 lg:p-8 h-full">
                                <div class="text-gray-900 dark:text-gray-100">
                                    <h2 class="text-lg font-semibold mb-4">Datos del Polígono</h2>
                                    
                                    <div class="space-y-6">
                                        <div>
                                            <x-input-label for="name" :value="__('Nombre del Polígono *')" />
                                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                                :value="old('name')" required placeholder="Ej: Finca La Esperanza" />
                                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                                        </div>

                                        <div>
                                            <x-input-label for="description" :value="__('Descripción')" />
                                            <textarea id="description" name="description" rows="3"
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                                placeholder="Descripción del polígono...">{{ old('description') }}</textarea>
                                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                                        </div>

                                        <div>
                                            <x-input-label for="producer_id" :value="__('Productor (Opcional)')" />
                                            <select id="producer_id" name="producer_id"
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
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
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
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
                                                class="mt-1 block w-full" :value="old('area_ha')" placeholder="Se calculará automáticamente" readonly />
                                            <x-input-error class="mt-2" :messages="$errors->get('area_ha')" />
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Se calcula automáticamente desde el mapa</p>
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
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para coordenadas manuales -->
    <div id="manual-polygon-modal" class="fixed inset-0 backdrop-blur-sm flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-custom-gray rounded-xl shadow-2xl w-full max-w-lg mx-4">
            <!-- Header -->
            <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Ingresar Coordenadas UTM</h3>
                <button id="close-modal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
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
                        <button type="button" id="method-single" class="flex-1 py-2 px-3 bg-blue-600 text-white rounded-lg text-sm font-medium transition-colors">Una por una</button>
                        <button type="button" id="method-bulk" class="flex-1 py-2 px-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors">Lote</button>
                    </div>
                </div>

                <!-- Entrada individual -->
                <div id="single-input" class="space-y-3">
                    <div class="grid grid-cols-4 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Zona</label>
                            <input type="number" id="single-zone" class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm p-2 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" 
                                min="1" max="60" placeholder="20" value="20">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Hemisferio</label>
                            <select id="single-hemisphere" class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <option value="N">Norte (N)</option>
                                <option value="S">Sur (S)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Este</label>
                            <input type="text" id="single-easting" class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm p-2 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" placeholder="500000">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Norte</label>
                            <input type="text" id="single-northing" class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm p-2 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" placeholder="10000000">
                        </div>
                    </div>
                    <button type="button" id="add-coord" class="w-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 py-2 rounded-lg text-sm transition-colors">
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
                   
                    <textarea id="bulk-coords" rows="6" class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm p-2 font-mono text-xs focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" 
                        placeholder="Ejemplo:&#10;20,N,476097.904,1157477.299&#10;20,N,476181.804,1157432.362&#10;20,N,475211.522,1157534.959"></textarea>
                </div>

                <!-- Lista de coordenadas agregadas -->
                <div id="coords-list" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Coordenadas UTM agregadas:</label>
                    <div class="max-h-32 overflow-y-auto border border-gray-200 dark:border-gray-500 rounded-md p-2 bg-gray-50 dark:bg-gray-800/80">
                        <div id="coords-container" class="space-y-1"></div>
                    </div>
                    <button type="button" id="clear-list" class="text-red-600 hover:text-red-700 text-xs mt-1 transition-colors">Limpiar lista</button>
                </div>

                <div class="flex space-x-3 pt-2">
                    <button type="button" id="cancel-modal" class="flex-1 py-2 px-4 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                        Dibujar
                    </button>
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

<!-- Script personalizado para polígonos (CORREGIDO) -->
<script>
// ARCHIVO polygon-map.js COMPLETO CON DETECCIÓN DE UBICACIÓN Y VALIDACIÓN CORREGIDA
class PolygonMap {
    constructor() {
        this.map = null;
        this.draw = null;
        this.source = null;
        this.polygonStyle = null;
        this.coordinateDisplay = null;
        this.baseLayers = {};
        this.currentBaseLayer = null;
        this.drawingFeature = null;

        // Coordenadas de Venezuela por defecto
        this.INITIAL_CENTER = [-63.172905251869125, 10.555594747510682];
        this.INITIAL_ZOOM = 15;
        this.MINZOOM = 5;
        this.MAXZOOM = 18;

        console.log('Inicializando PolygonMap...');
        
        // Esperar a que el DOM esté completamente listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.init());
        } else {
            this.init();
        }
    }

    init() {
        console.log('Ejecutando init()...');
        
        // Verificar que el elemento map existe
        const mapElement = document.getElementById('map');
        if (!mapElement) {
            console.error('ERROR: No se encontró el elemento #map');
            return;
        }
        console.log('Elemento #map encontrado:', mapElement);

        this.defineCustomProjections();
        this.initializeMap();
        this.setupEventListeners();
        this.setupCoordinateDisplay();
        this.verifyDependencies();
        
        // Forzar redimensionamiento después de la inicialización
        setTimeout(() => {
            if (this.map) {
                this.map.updateSize();
            }
        }, 500);
    }

    verifyDependencies() {
        console.log('=== VERIFICACIÓN DE DEPENDENCIAS ===');
        console.log('OpenLayers:', typeof ol !== 'undefined');
        console.log('Turf.js:', typeof turf !== 'undefined');
        console.log('Proj4:', typeof proj4 !== 'undefined');
        
        if (typeof ol === 'undefined') {
            console.error('ERROR: OpenLayers no está cargado');
            this.showAlert('Error crítico: OpenLayers no se cargó correctamente', 'error');
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
        console.log('Inicializando mapa...');
        
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
        console.log('Configurando capas base...');
        
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
        this.setupStyles();

        this.vectorLayer = new ol.layer.Vector({
            source: this.source,
            style: (feature) => this.getFeatureStyle(feature)
        });
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
        console.log('Instancia del mapa creada:', this.map);
    }

    setupStyles() {
        this.polygonStyle = this.getPolygonStyle('default');
    }

    getPolygonStyle(state = 'default', areaHa = 0) {
        const styles = {
            drawing: new ol.style.Style({
                stroke: new ol.style.Stroke({
                    color: '#3b82f6', width: 3, lineDash: [5, 10], lineCap: 'round'
                }),
                fill: new ol.style.Fill({ color: 'rgba(59, 130, 246, 0.2)' }),
                image: new ol.style.Circle({
                    radius: 6,
                    fill: new ol.style.Fill({ color: '#ffffff' }),
                    stroke: new ol.style.Stroke({ color: '#3b82f6', width: 2 })
                })
            }),
            
            finished: new ol.style.Style({
                stroke: new ol.style.Stroke({
                    color: '#10b981', width: 3, lineDash: null, lineCap: 'round'
                }),
                fill: new ol.style.Fill({ color: 'rgba(16, 185, 129, 0.3)' }),
                image: new ol.style.Circle({
                    radius: 5,
                    fill: new ol.style.Fill({ color: '#10b981' }),
                    stroke: new ol.style.Stroke({ color: '#ffffff', width: 2 })
                }),
                text: new ol.style.Text({
                    text: areaHa > 0 ? `${areaHa.toFixed(6)} ha` : '',
                    font: 'bold 14px Arial, sans-serif',
                    fill: new ol.style.Fill({ color: '#1f2937' }),
                    stroke: new ol.style.Stroke({ color: '#ffffff', width: 3 }),
                    backgroundFill: new ol.style.Fill({ color: 'rgba(255, 255, 255, 0.7)' }),
                    padding: [4, 8, 4, 8],
                    textBaseline: 'middle',
                    textAlign: 'center',
                    offsetY: 0
                })
            }),
            
            default: new ol.style.Style({
                stroke: new ol.style.Stroke({
                    color: '#10b981', width: 3, lineDash: [8, 4], lineCap: 'round'
                }),
                fill: new ol.style.Fill({ color: 'rgba(16, 185, 129, 0.25)' })
            })
        };

        return styles[state] || styles.default;
    }

    getFeatureStyle(feature) {
        const geometry = feature.getGeometry();
        const styles = [];
        const areaHa = feature.get('area') || 0;
        
        const customStyle = feature.getStyle();
        if (customStyle) {
            styles.push(customStyle);
        } else {
            if (geometry.getType() === 'Polygon' && areaHa > 0) {
                styles.push(this.getPolygonStyle('finished', areaHa));
            } else {
                styles.push(this.polygonStyle);
            }
        }

        return styles;
    }

    setupEventListeners() {
        document.addEventListener('keydown', (e) => this.handleKeyDown(e));
    }

    handleKeyDown(event) {
        if (event.key === 'Escape' && this.draw && this.drawingFeature) {
            this.cancelDrawing();
            event.preventDefault();
        }
    }

    cancelDrawing() {
        if (this.draw) {
            this.map.removeInteraction(this.draw);
            this.draw = null;
        }
        this.drawingFeature = null;
        this.updateAreaDisplay(0);
    }

    setupCoordinateDisplay() {
        this.coordinateDisplay = document.getElementById('coordinate-display');
        
        if (!this.coordinateDisplay) {
            console.warn('No se encontró el elemento coordinate-display');
            return;
        }
        
        this.map.on('pointermove', (evt) => {
            if (evt.dragging) return;
            this.updateCoordinateDisplay(evt.coordinate);
        });
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
                this.coordinateDisplay.classList.remove('hidden');
            } else {
                this.coordinateDisplay.textContent = 'Coordenadas fuera de rango';
            }
        } catch (error) {
            console.warn('Error en conversión UTM:', error);
            this.coordinateDisplay.textContent = 'Error en coordenadas';
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

    activateDrawing() {
        console.log('Activando dibujo de polígonos...');
        
        if (this.draw) {
            this.map.removeInteraction(this.draw);
        }

        this.draw = new ol.interaction.Draw({
            source: this.source,
            type: 'Polygon',
            style: this.getPolygonStyle('drawing')
        });

        this.setupDrawEvents();
        this.map.addInteraction(this.draw);
        
        this.showAlert('Modo dibujo activado. Haz clic en el mapa para dibujar el polígono.', 'info');
    }

    setupDrawEvents() {
        this.draw.on('drawstart', (evt) => {
            this.drawingFeature = evt.feature;
            this.source.clear();
            this.updateAreaDisplay(0);
            
            const detectBtn = document.getElementById('detect-location');
            if (detectBtn) detectBtn.disabled = true;
            
            // Ocultar información de ubicación previa
            const locationInfo = document.getElementById('location-info');
            if (locationInfo) locationInfo.classList.add('hidden');
        });

        this.draw.on('drawadd', () => this.refreshArea());
        this.draw.on('drawabort', () => this.resetDrawingState());
        this.draw.on('drawend', (event) => this.finalizeDrawing(event.feature));
    }

    finalizeDrawing(feature) {
        const areaHa = this.refreshArea(feature);
        
        feature.set('area', areaHa);
        feature.setStyle(this.getPolygonStyle('finished', areaHa));
        
        this.convertToGeoJSON(feature, areaHa);
        this.showAlert(`Polígono completado. Área: ${areaHa.toFixed(6)} ha`, 'success');
        
        const detectBtn = document.getElementById('detect-location');
        if (detectBtn) detectBtn.disabled = false;
        
        if (this.draw) {
            this.map.removeInteraction(this.draw);
            this.draw = null;
        }
        this.resetDrawingState();
    }

    removeExistingDrawInteraction() {
        if (this.draw) {
            this.map.removeInteraction(this.draw);
        }
    }

    resetDrawingState() {
        this.drawingFeature = null;
        this.updateAreaDisplay(0);
    }

    calculateArea(feature) {
        if (!feature || !feature.getGeometry) {
            return 0;
        }
        
        const geometry = feature.getGeometry();
        if (!geometry) {
            return 0;
        }
        
        if (typeof turf === 'undefined') {
            console.error('Turf.js no está disponible');
            return 0;
        }
        
        try {
            const wgs84Geometry = geometry.clone().transform('EPSG:3857', 'EPSG:4326');
            const coordinates = wgs84Geometry.getCoordinates();
            
            if (!coordinates || coordinates.length === 0) {
                return 0;
            }
            
            const turfFeature = turf.polygon(coordinates);
            const areaM2 = turf.area(turfFeature);
            
            if (isNaN(areaM2) || areaM2 <= 0) {
                return 0;
            }
            
            const areaHa = areaM2 / 10000;
            return parseFloat(areaHa.toFixed(6));
            
        } catch (error) {
            console.error('Error en cálculo de área:', error);
            return 0;
        }
    }

    refreshArea(feature = this.drawingFeature) {
        if (feature) {
            const areaHa = this.calculateArea(feature);
            this.updateAreaDisplay(areaHa);
            return areaHa;
        }
        return 0;
    }

    updateAreaDisplay(areaHa) {
        const areaInput = document.getElementById('area_ha');
        if (areaInput) {
            areaInput.value = areaHa > 0 ? areaHa.toFixed(6) : '';
        }
    }

    convertToGeoJSON(feature, existingArea = null) {
        try {
            const format = new ol.format.GeoJSON();
            const geojson = format.writeFeature(feature, {
                dataProjection: 'EPSG:4326',
                featureProjection: 'EPSG:3857'
            });
            const geojsonObj = JSON.parse(geojson);
            
            if (!geojsonObj.geometry) {
                throw new Error('El polígono no tiene geometría válida');
            }
            
            document.getElementById('geometry').value = JSON.stringify(geojsonObj.geometry);
            
            const areaHa = existingArea !== null ? existingArea : feature.get('area') || this.calculateArea(feature);
            document.getElementById('area_ha').value = areaHa.toFixed(6);
            
        } catch (error) {
            console.error('Error al convertir GeoJSON:', error);
            this.showAlert('Error al guardar el polígono: ' + error.message, 'error');
        }
    }

    changeBaseLayer(layerKey) {
        console.log('Cambiando capa base a:', layerKey);
        
        if (!this.baseLayers[layerKey]) {
            console.error('Capa no encontrada:', layerKey);
            this.showAlert(`Capa base no encontrada: ${layerKey}`, 'error');
            return;
        }
        
        // Ocultar todas las capas base
        Object.values(this.baseLayers).forEach(layer => {
            layer.setVisible(false);
        });
        
        // Mostrar la nueva capa base
        this.baseLayers[layerKey].setVisible(true);
        this.currentBaseLayer = this.baseLayers[layerKey];
        
        // Actualizar texto del botón
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

    clearMap() {
        console.log('Limpiando mapa...');
        
        this.source.clear();
        document.getElementById('geometry').value = '';
        document.getElementById('area_ha').value = '';
        this.updateAreaDisplay(0);
        
        const detectBtn = document.getElementById('detect-location');
        if (detectBtn) detectBtn.disabled = true;
        
        const locationInfo = document.getElementById('location-info');
        if (locationInfo) locationInfo.classList.add('hidden');
        
        this.removeExistingDrawInteraction();
        this.drawingFeature = null;
        
        this.showAlert('Mapa limpiado', 'info');
    }

    drawFromUTMCoordinates(utmCoordinates) {
        console.log('Dibujando desde coordenadas UTM:', utmCoordinates);
        
        try {
            const wgs84Coordinates = utmCoordinates.map(coord => {
                const [easting, northing, zone, hemisphere] = coord;
                const sourceEpsg = this.setupUTMProjection(zone, hemisphere);
                return proj4(sourceEpsg, 'EPSG:4326', [easting, northing]);
            });

            const invalidCoords = wgs84Coordinates.filter(coord => 
                isNaN(coord[0]) || isNaN(coord[1]) || 
                Math.abs(coord[0]) > 180 || Math.abs(coord[1]) > 90
            );
            
            if (invalidCoords.length > 0) {
                this.showAlert('Algunas coordenadas UTM son inválidas o están fuera de rango', 'error');
                return;
            }

            this.closePolygonIfNeeded(wgs84Coordinates);
            this.createPolygonFromCoordinates(wgs84Coordinates, utmCoordinates);
            
        } catch (error) {
            console.error('Error al procesar coordenadas UTM:', error);
            this.showAlert('Error al procesar coordenadas UTM. Verifique los valores y formatos.', 'error');
        }
    }

    closePolygonIfNeeded(coordinates) {
        const firstCoord = coordinates[0];
        const lastCoord = coordinates[coordinates.length - 1];
        
        if (firstCoord[0] !== lastCoord[0] || firstCoord[1] !== lastCoord[1]) {
            coordinates.push([...firstCoord]);
        }
    }

    createPolygonFromCoordinates(wgs84Coordinates, utmCoordinates) {
        const feature = new ol.Feature({
            geometry: new ol.geom.Polygon([wgs84Coordinates]).transform('EPSG:4326', 'EPSG:3857')
        });
        
        this.clearMap();
        
        const areaHa = this.calculateArea(feature);
        feature.set('area', areaHa);
        feature.setStyle(this.getPolygonStyle('finished', areaHa));
        
        this.source.addFeature(feature);
        this.updateAreaDisplay(areaHa);
        
        const detectBtn = document.getElementById('detect-location');
        if (detectBtn) detectBtn.disabled = false;
        
        this.map.getView().fit(
            feature.getGeometry().getExtent(),
            { padding: [50, 50, 50, 50], duration: 1000 }
        );
        
        this.convertToGeoJSON(feature, areaHa);
        
        const zonesUsed = [...new Set(utmCoordinates.map(coord => 
            `Zona ${coord[2]}${coord[3]}`
        ))];
        const zonesText = zonesUsed.sort().join(', ');
        
        this.showAlert(
            `Polígono dibujado exitosamente (${zonesText}). Área: ${areaHa.toFixed(6)} ha`, 
            'success'
        );
    }

    // =============================================
    // NUEVAS FUNCIONES PARA DETECCIÓN DE UBICACIÓN
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
        console.log('Iniciando detección de ubicación...');
        
        const geometryInput = document.getElementById('geometry');
        if (!geometryInput || !geometryInput.value) {
            this.showAlert('Debes dibujar un polígono primero', 'error');
            return;
        }
        
        const centroid = this.calculateCentroidFromGeoJSON(geometryInput.value);
        if (!centroid) {
            this.showAlert('No se pudo calcular el centroide del polígono', 'error');
            return;
        }
        
        console.log('Centroide calculado:', centroid);
        
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
        console.log('Consultando Nominatim para:', lat, lng);
        
        try {
            // Usar CORS proxy para evitar problemas de CORS
            const proxyUrl = ''; // Puedes añadir un proxy si es necesario
            const targetUrl = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=10&addressdetails=1&accept-language=es`;
            
            const response = await fetch(proxyUrl + targetUrl, {
                headers: {
                    'User-Agent': 'PolygonSystem/1.0',
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Datos de Nominatim:', data);
            
            return data;
            
        } catch (error) {
            console.error('Error en geocodificación inversa:', error);
            throw error;
        }
    }

    processLocationData(data, centroid) {
        const address = data.address || {};
        
        // Buscar los mejores nombres para cada nivel
        const parish = address.village || address.town || address.city || address.municipality || '';
        const municipality = address.county || address.state_district || address.region || '';
        const state = address.state || address.region || '';
        
        console.log('Datos de dirección:', { parish, municipality, state, address });
        
        const cleanParish = this.cleanLocationString(parish);
        const cleanMunicipality = this.cleanLocationString(municipality);
        const cleanState = this.cleanLocationString(state);
        
        document.getElementById('detected_parish').value = cleanParish;
        document.getElementById('detected_municipality').value = cleanMunicipality;
        document.getElementById('detected_state').value = cleanState;
        document.getElementById('location_data').value = JSON.stringify(data);
        
        this.updateLocationInfoUI(cleanParish, cleanMunicipality, cleanState, centroid);
        this.findParishInDatabase(cleanParish, cleanMunicipality, cleanState);
        
        this.showAlert('Ubicación detectada correctamente', 'success');
    }

    cleanLocationString(str) {
        if (!str) return '';
        
        return str
            .trim()
            .replace(/[^\w\sáéíóúÁÉÍÓÚñÑüÜ.,\-\s]/g, '')
            .replace(/\s+/g, ' ')
            .toUpperCase();
    }

    async findParishInDatabase(parishName, municipalityName, stateName) {
        try {
            console.log('Buscando parroquia en base de datos:', { parishName, municipalityName, stateName });
            
            // Limpiar nombres para búsqueda
            const cleanParish = parishName
                .replace(/PARROQUIA\s*/i, '')
                .replace(/MUNICIPIO\s*/i, '')
                .trim();
            
            const cleanMunicipality = municipalityName
                .replace(/MUNICIPIO\s*/i, '')
                .trim();
            
            this.updateParishSelect(cleanParish, cleanMunicipality);
            
        } catch (error) {
            console.error('Error buscando parroquia:', error);
        }
    }

    updateParishSelect(parishName, municipalityName) {
        const parishSelect = document.getElementById('parish_id');
        if (!parishSelect) return;
        
        const searchTerms = [
            parishName,
            municipalityName,
            parishName.replace(/^SANTA\s+|^SAN\s+/i, ''),
            parishName.replace(/\s+\(.*\)$/, '')
        ].filter(term => term.length > 0);
        
        console.log('Términos de búsqueda:', searchTerms);
        
        let foundOption = null;
        
        for (let i = 0; i < parishSelect.options.length; i++) {
            const option = parishSelect.options[i];
            const optionText = option.text.toUpperCase();
            
            // Buscar coincidencias exactas o parciales
            for (const term of searchTerms) {
                if (term && optionText.includes(term) || 
                    (term.length > 3 && optionText.includes(term.substring(0, term.length - 2)))) {
                    foundOption = option;
                    break;
                }
            }
            
            if (foundOption) break;
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
}

// =============================================
// CÓDIGO PARA EL MODAL Y CONTROLES
// =============================================

let coordinatesList = [];

// Función para abrir el modal
function openCoordinateModal() {
    const modal = document.getElementById('manual-polygon-modal');
    modal.classList.remove('hidden');
    
    void modal.offsetWidth;
    
    setTimeout(() => {
        const firstInput = document.getElementById('single-easting');
        if (firstInput) firstInput.focus();
    }, 100);
}

// Función para cerrar el modal
function closeCoordinateModal() {
    const modal = document.getElementById('manual-polygon-modal');
    
    modal.classList.add('closing');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('closing');
        coordinatesList = [];
        updateCoordinatesList();
        document.getElementById('bulk-coords').value = '';
        document.getElementById('single-easting').value = '';
        document.getElementById('single-northing').value = '';
        
        setInputMethod('single');
    }, 300);
}

// Función para validar coordenadas UTM
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

// =============================================
// VALIDACIÓN MEJORADA DEL FORMULARIO - CORREGIDA
// =============================================

function handleSubmitClick(e) {
    console.log('Botón submit clickeado');
    
    // Solo manejar el click del botón de envío
    const submitter = e.target.closest('#submit-btn') || e.target;
    if (submitter && submitter.id === 'submit-btn') {
        // Validar antes de enviar
        if (!validatePolygonForm()) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        
        // Si la validación pasa, permitir el envío normal
        return true;
    }
}

function validatePolygonForm() {
    console.log('Validando formulario de polígono...');
    
    // 1. Validar que haya un polígono dibujado
    const geometry = document.getElementById('geometry');
    if (!geometry || !geometry.value) {
        showAlert('Debe dibujar un polígono en el mapa antes de enviar', 'warning');
        
        // Resaltar el mapa
        const mapContainer = document.getElementById('map');
        if (mapContainer) {
            mapContainer.classList.add('border-2', 'border-red-500');
            setTimeout(() => {
                mapContainer.classList.remove('border-2', 'border-red-500');
            }, 2000);
        }
        
        return false;
    }
    
    // 2. Validar que el nombre no esté vacío
    const nameInput = document.getElementById('name');
    if (!nameInput || !nameInput.value.trim()) {
        showAlert('El nombre del polígono es requerido', 'warning');
        if (nameInput) {
            nameInput.focus();
            nameInput.classList.add('border-red-500');
            setTimeout(() => {
                nameInput.classList.remove('border-red-500');
            }, 2000);
        }
        return false;
    }
    
    // 3. Validar que el área sea un número válido
    const areaInput = document.getElementById('area_ha');
    if (areaInput && areaInput.value) {
        const areaValue = parseFloat(areaInput.value);
        if (isNaN(areaValue) || areaValue <= 0) {
            showAlert('El área debe ser un número mayor a 0', 'warning');
            areaInput.focus();
            areaInput.classList.add('border-red-500');
            setTimeout(() => {
                areaInput.classList.remove('border-red-500');
            }, 2000);
            return false;
        }
    }
    
    console.log('Validación del formulario exitosa');
    return true;
}

function handleFormSubmit(e) {
    console.log('Formulario submit, validando...');
    
    // Solo validar si el submit fue desde el botón de envío del formulario
    const submitter = e.submitter;
    
    // Si no es el botón de submit principal, no validar
    if (submitter && !submitter.matches('#submit-btn, #submit-btn *')) {
        return true;
    }
    
    // Validar antes de enviar
    if (!validatePolygonForm()) {
        e.preventDefault();
        e.stopPropagation();
        return false;
    }
    
    return true;
}

function setupFormValidation() {
    const polygonForm = document.getElementById('polygon-form');
    const submitBtn = document.getElementById('submit-btn');
    
    if (!polygonForm || !submitBtn) return;
    
    // Configurar event listeners SOLO para el botón de submit
    submitBtn.addEventListener('click', handleSubmitClick);
    
    // En su lugar, usar un listener más específico
    polygonForm.addEventListener('submit', handleFormSubmit, true);
}

// Event listeners cuando el DOM está cargado
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM completamente cargado, inicializando mapa...');
    
    // Inicializar el mapa
    window.polygonMapInstance = new PolygonMap();
    
    // Configurar otros event listeners
    setupEventListeners();
    
    // Inicializar modal
    setInputMethod('single');
    
    // Configurar validación del formulario
    setupFormValidation();
    
    // Configurar redimensionamiento del mapa
    setupMapResizeHandler();
    
    // Prevenir que otros botones disparen la validación del formulario
    const formButtons = document.querySelectorAll('#polygon-form button:not(#submit-btn)');
    formButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            // No hacer nada especial, dejar que el botón funcione normalmente
        });
    });
    
    // Prevenir que el formulario se envíe por eventos no deseados
    const polygonForm = document.getElementById('polygon-form');
    polygonForm.addEventListener('submit', function(e) {
        // Solo permitir submit desde el botón de envío
        const submitter = e.submitter;
        if (!submitter || !submitter.closest('#submit-btn')) {
            e.preventDefault();
            return false;
        }
    });
});

function setupEventListeners() {
    console.log('Configurando event listeners...');
    
    // Modal de coordenadas
    const manualToggle = document.getElementById('manual-polygon-toggle');
    if (manualToggle) {
        manualToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            closeMenu('base-map-menu');
            openCoordinateModal();
        });
    } else {
        console.error('No se encontró el botón manual-polygon-toggle');
    }
    
    document.getElementById('close-modal')?.addEventListener('click', closeCoordinateModal);
    document.getElementById('cancel-modal')?.addEventListener('click', closeCoordinateModal);
    
    // Métodos de entrada del modal
    document.getElementById('method-single')?.addEventListener('click', function() {
        setInputMethod('single');
    });
    
    document.getElementById('method-bulk')?.addEventListener('click', function() {
        setInputMethod('bulk');
    });
    
    // Agregar coordenada individual
    document.getElementById('add-coord')?.addEventListener('click', function() {
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
        
        document.getElementById('single-easting').value = '';
        document.getElementById('single-northing').value = '';
        
        showAlert(`Coordenada agregada (Zona ${zone}${hemisphere})`, 'success');
    });
    
    // Limpiar lista de coordenadas
    document.getElementById('clear-list')?.addEventListener('click', function() {
        coordinatesList = [];
        updateCoordinatesList();
    });
    
    // Enviar formulario del modal
    const manualForm = document.getElementById('manual-polygon-form');
    if (manualForm) {
        manualForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            let utmCoords = [];
            
            if (document.getElementById('method-single').classList.contains('bg-blue-600')) {
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
            
            if (window.polygonMapInstance && window.polygonMapInstance.drawFromUTMCoordinates) {
                window.polygonMapInstance.drawFromUTMCoordinates(utmCoords);
            } else {
                console.error('polygonMapInstance no disponible');
                showAlert('Error: El mapa no está inicializado', 'error');
            }
            
            closeCoordinateModal();
        });
    }
    
    // Controles del mapa
    document.getElementById('draw-polygon')?.addEventListener('click', function() {
        console.log('Botón dibujar clickeado');
        if (window.polygonMapInstance) {
            window.polygonMapInstance.activateDrawing();
        } else {
            console.error('polygonMapInstance no disponible');
            showAlert('Error: El mapa no está inicializado', 'error');
        }
    });
    
    document.getElementById('clear-map')?.addEventListener('click', function() {
        console.log('Botón limpiar clickeado');
        if (window.polygonMapInstance) {
            window.polygonMapInstance.clearMap();
        } else {
            console.error('polygonMapInstance no disponible');
            showAlert('Error: El mapa no está inicializado', 'error');
        }
    });
    
    // Detección de ubicación
    document.getElementById('detect-location')?.addEventListener('click', async function() {
        console.log('Botón detectar ubicación clickeado');
        if (window.polygonMapInstance && window.polygonMapInstance.detectLocation) {
            await window.polygonMapInstance.detectLocation();
        } else {
            console.error('polygonMapInstance o detectLocation no disponible');
            showAlert('Error: La detección de ubicación no está disponible', 'error');
        }
    });
    
    // Menú de capas base
    const baseMapToggle = document.getElementById('base-map-toggle');
    if (baseMapToggle) {
        baseMapToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = document.getElementById('base-map-menu');
            const isShowing = menu.classList.contains('show');
            
            console.log('Botón mapa clickeado, mostrar menú:', !isShowing);
            toggleMenu('base-map-menu', !isShowing);
        });
    }
    
    // Cambiar capas base
    document.querySelectorAll('#base-map-menu button').forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const layerKey = this.getAttribute('data-layer');
            console.log('Cambiando a capa:', layerKey);
            
            if (window.polygonMapInstance && window.polygonMapInstance.changeBaseLayer) {
                window.polygonMapInstance.changeBaseLayer(layerKey);
            } else {
                console.error('polygonMapInstance o changeBaseLayer no disponible');
                showAlert('Error: No se puede cambiar la capa base', 'error');
            }
            
            closeMenu('base-map-menu');
        });
    });
    
    // Pantalla completa
    document.getElementById('fullscreen-toggle')?.addEventListener('click', function() {
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
    document.addEventListener('click', function(e) {
        const baseMapToggle = document.getElementById('base-map-toggle');
        const baseMapMenu = document.getElementById('base-map-menu');
        const modal = document.getElementById('manual-polygon-modal');
        
        if (modal.classList.contains('hidden')) {
            if (!baseMapToggle?.contains(e.target) && !baseMapMenu?.contains(e.target)) {
                closeMenu('base-map-menu');
            }
        }
        
        if (!modal.classList.contains('hidden') && e.target === modal) {
            closeCoordinateModal();
        }
    });
    
    // Escape para cerrar modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCoordinateModal();
        }
    });
}

// Funciones auxiliares para el modal
function setInputMethod(method) {
    const singleBtn = document.getElementById('method-single');
    const bulkBtn = document.getElementById('method-bulk');
    const singleInput = document.getElementById('single-input');
    const bulkInput = document.getElementById('bulk-input');
    const coordsList = document.getElementById('coords-list');
    
    if (!singleBtn || !bulkBtn || !singleInput || !bulkInput) {
        console.error('Elementos del modal no encontrados');
        return;
    }
    
    if (method === 'single') {
        singleBtn.classList.add('bg-blue-600', 'text-white');
        singleBtn.classList.remove('bg-gray-200', 'text-gray-700', 'dark:bg-gray-700', 'dark:text-gray-300');
        bulkBtn.classList.remove('bg-blue-600', 'text-white');
        bulkBtn.classList.add('bg-gray-200', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
        singleInput.classList.remove('hidden');
        bulkInput.classList.add('hidden');
        
        if (coordinatesList.length > 0) {
            coordsList?.classList.remove('hidden');
        } else {
            coordsList?.classList.add('hidden');
        }
    } else {
        bulkBtn.classList.add('bg-blue-600', 'text-white');
        bulkBtn.classList.remove('bg-gray-200', 'text-gray-700', 'dark:bg-gray-700', 'dark:text-gray-300');
        singleBtn.classList.remove('bg-blue-600', 'text-white');
        singleBtn.classList.add('bg-gray-200', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
        bulkInput.classList.remove('hidden');
        singleInput.classList.add('hidden');
        
        coordsList?.classList.add('hidden');
    }
}

function updateCoordinatesList() {
    const container = document.getElementById('coords-container');
    const listSection = document.getElementById('coords-list');
    const isSingleMode = document.getElementById('method-single')?.classList.contains('bg-blue-600');
    
    if (!container) return;
    
    container.innerHTML = '';
    
    if (coordinatesList.length === 0) {
        listSection?.classList.add('hidden');
        return;
    }
    
    if (isSingleMode) {
        listSection?.classList.remove('hidden');
    } else {
        listSection?.classList.add('hidden');
    }
    
    coordinatesList.forEach((coord, index) => {
        const coordElement = document.createElement('div');
        coordElement.className = 'flex justify-between items-center text-xs font-mono py-1';
        coordElement.innerHTML = `
            <span>${index + 1}. Z${coord.zone}${coord.hemisphere} | E:${coord.easting.toFixed(3)} | N:${coord.northing.toFixed(3)}</span>
            <button type="button" onclick="removeCoordinate(${index})" class="text-red-500 hover:text-red-700 text-xs transition-colors">✕</button>
        `;
        container.appendChild(coordElement);
    });
}

function removeCoordinate(index) {
    coordinatesList.splice(index, 1);
    updateCoordinatesList();
}

// Funciones para menús desplegables
function toggleMenu(menuId, show) {
    const menu = document.getElementById(menuId);
    if (!menu) {
        console.error('Menú no encontrado:', menuId);
        return;
    }
    
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
    if (!menu) return;
    
    menu.classList.remove('scale-100', 'opacity-100', 'pointer-events-auto', 'show');
    menu.classList.add('scale-95', 'opacity-0', 'pointer-events-none');
    
    setTimeout(() => {
        if (menu.classList.contains('scale-95')) {
            menu.classList.add('hidden');
        }
    }, 400);
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

// =============================================
// FUNCIONES PARA MANEJAR REDIMENSIONAMIENTO DEL MAPA
// =============================================

function setupMapResizeHandler() {
    // Redimensionar mapa cuando cambie el tamaño de la ventana
    window.addEventListener('resize', function() {
        if (window.polygonMapInstance && window.polygonMapInstance.map) {
            setTimeout(() => {
                window.polygonMapInstance.map.updateSize();
            }, 100);
        }
    });
    
    // Redimensionar mapa después de cargar completamente
    window.addEventListener('load', function() {
        if (window.polygonMapInstance && window.polygonMapInstance.map) {
            setTimeout(() => {
                window.polygonMapInstance.map.updateSize();
            }, 500);
        }
    });
    
    // Observar cambios en el DOM que puedan afectar al tamaño del mapa
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' || mutation.type === 'childList') {
                if (window.polygonMapInstance && window.polygonMapInstance.map) {
                    setTimeout(() => {
                        window.polygonMapInstance.map.updateSize();
                    }, 300);
                }
            }
        });
    });
    
    // Observar el contenedor del mapa
    const mapContainer = document.getElementById('map');
    if (mapContainer) {
        observer.observe(mapContainer.parentElement, {
            attributes: true,
            attributeFilter: ['class', 'style'],
            childList: false,
            subtree: false
        });
    }
}
</script>

<style>
/* Estilos CSS adicionales para OpenLayers */
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

/* Estilos para el modal */
#manual-polygon-modal {
    transition: opacity 0.3s ease;
}

#manual-polygon-modal.closing {
    opacity: 0;
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

/* Estilos para el display de coordenadas */
#coordinate-display {
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
    color: #1f2937;
    border: 1px solid #d1d5db;
    background: rgba(255, 255, 255, 0.95) !important;
    backdrop-filter: blur(4px);
}

.dark #coordinate-display {
    color: #f9fafb;
    border-color: #4b5563;
    background: rgba(31, 41, 55, 0.95) !important;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Asegurar visibilidad de los controles de OpenLayers */
.ol-zoom {
    top: 0.5em;
    left: 0.5em;
}

.ol-rotate {
    top: 0.5em;
    right: 0.5em;
}

/* Estilos para botones deshabilitados */
button:disabled {
    cursor: not-allowed;
    opacity: 0.5;
}

/* Mejoras visuales para el formulario */
textarea, select, input[type="text"], input[type="number"] {
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

textarea:focus, select:focus, input[type="text"]:focus, input[type="number"]:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

/* Grid responsivo */
@media (min-width: 1024px) {
    .lg\:grid-cols-2 {
        grid-template-columns: 1fr 1fr;
    }
}

/* Clases de utilidad */
.backdrop-blur-sm {
    backdrop-filter: blur(4px);
}

.ring-1 {
    --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
    --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);
    box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}

.ring-black {
    --tw-ring-color: rgba(0, 0, 0, 1);
}

.ring-opacity-5 {
    --tw-ring-opacity: 0.05;
}

/* Clases para modo oscuro */
.dark .dark\:bg-custom-gray {
    background-color: #1f2937;
}

.dark .dark\:border-gray-600 {
    border-color: #4b5563;
}

.dark .dark\:text-gray-100 {
    color: #f3f4f6;
}

.dark .dark\:text-gray-300 {
    color: #d1d5db;
}

.dark .dark\:text-gray-400 {
    color: #9ca3af;
}

.dark .dark\:hover\:bg-gray-700:hover {
    background-color: #374151;
}

.dark .dark\:bg-gray-800 {
    background-color: #1f2937;
}
</style>
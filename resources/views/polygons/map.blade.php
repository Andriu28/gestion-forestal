{{-- [file name]: map.blade.php --}}
<x-app-layout>
    <div class=" mx-auto ">
        <div class="bg-stone-100/90 dark:bg-custom-gray  shadow-sm sm:rounded-2xl shadow-soft p-4 md:p-6 lg:p-6 mb-6">
            <div class="text-gray-900 dark:text-gray-100 ">
                <div class="flex justify-between items-center mb-2">
                    <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-200 mb-2 md:mb-2">
                       {{ __('Mapa de Polígonos') }} 
                    </h2>
                    
                    <!-- Botón para ver el mapa de polígonos -->
                    <a href="{{ route('polygons.index') }}"
                    title="Ver lista de polígonos" 
                    class="group px-2.5 py-1.5 bg-stone-200/80 hover:bg-blue-600/70 dark:hover:bg-blue-500/60 text-stone-700 hover:text-white border border-stone-300/70 hover:border-transparent dark:bg-gray-700/40 dark:text-gray-300 dark:hover:text-white dark:border-gray-600/50 rounded-md flex items-center hover:shadow-md hover:-translate-y-0.5 overflow-hidden">
                    
                        <!-- Contenedor del ícono - se contrae en hover -->
                        <span class="flex items-center justify-center w-6 h-6 transition-all duration-300 group-hover:w-6 group-hover:h-6 flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2002/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-icon lucide-map w-6 h-6 text-blue-700/70 group-hover:text-white dark:text-blue-400/70">
                                <rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>
                                </svg>
                        </span>
                        
                        <!-- Texto - oculto en estado normal, visible en hover -->
                        <span class="text-base font-medium transition-all duration-300 w-0 opacity-0 group-hover:w-10 group-hover:opacity-100 group-hover:ml-1 whitespace-nowrap overflow-hidden text-inherit">
                            Lista
                        </span>
                    </a>
                </div>

                <!-- Mapa -->
                <div class="relative rounded-lg overflow-hidden mb-6 border border-gray-200 dark:border-gray-700 mt-1" style="height: 75vh; border: 1px solid #dededeff; border-radius: 0.5rem; position: relative;">
                    <div id="map" class="h-full w-full"></div>
                    
                    <!-- Controles del mapa -->
                    <div id="map-controls" style="position: absolute; top: 10px; right: 10px; z-index: 1;">
                        <div class="flex flex-col items-end space-y-2">
                            <div class="flex space-x-2">
                                <!-- Contenedor para Cambiar Mapa con menú -->
                                <div class="relative">
                                    <button id="base-map-toggle" title="Cambiar mapa" class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                        </svg>
                                        Mapas
                                    </button>
                                    
                                    <!-- Menú de cambio de mapa -->
                                    <div id="base-map-menu"
                                        class="absolute mt-3 w-40 rounded-xl shadow-lg bg-gray-100 dark:bg-custom-gray ring-1 ring-black ring-opacity-5 z-10 right-0
                                                transition-all duration-400 ease-out scale-95 opacity-0 pointer-events-none hidden">
                                        <div class="absolute -top-2 right-6 w-8 h-2 pointer-events-none">
                                            <svg viewBox="0 0 16 8" class="w-4 h-2 text-white dark:text-custom-gray">
                                                <polygon points="8,0 16,8 0,8" fill="currentColor"/>
                                            </svg>
                                        </div>
                                        <div class="py-2 " role="menu" aria-orientation="vertical">
                                            <button data-layer="osm" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">OpenStreetMap</button>
                                            <button data-layer="satellite" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">Satélite Esri</button>
                                            <button data-layer="maptiler_satellite" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">MapTiler Satélite</button>
                                            <button data-layer="terrain" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">Relieve</button>
                                            <button data-layer="dark" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">Oscuro</button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Botón de pantalla completa -->
                                <button id="fullscreen-toggle" title="Pantalla Completa" class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leyenda -->
                <div class="mt-4 flex flex-wrap gap-5 items-center">
                    <div class="flex items-center gap-2">
                        <svg viewBox="0 0 24 24" width="32" height="32" class="flex-shrink-0" style="filter: drop-shadow(0 1px 2px rgba(0,0,0,0.25));">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" fill="url(#markerGradientAssigned)" stroke="#ffffff" stroke-width="1"/>
                            <path d="M9.3 9.3l1.8 1.8 3.6-4" stroke="#ffffff" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Con productor asignado</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg viewBox="0 0 24 24" width="32" height="32" class="flex-shrink-0" style="filter: drop-shadow(0 1px 2px rgba(0,0,0,0.25));">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" fill="url(#markerGradientUnassigned)" stroke="#ffffff" stroke-width="1"/>
                            <line x1="12" y1="6" x2="12" y2="9.8" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round"/>
                            <circle cx="12" cy="12" r="1" fill="#ffffff"/>
                        </svg>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Sin productor asignado</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Elemento popup - AHORA VISIBLE POR DEFECTO -->
    <div id="popup" class="ol-popup" style="display: block; position: absolute; visibility: hidden;">
        <div class="popup-content bg-gray-50 dark:bg-gray-800 rounded-lg shadow-lg"></div>
        <div class="popup-arrow" aria-hidden="true"></div>
    </div>

    <!-- Degradados compartidos para los pines del mapa y la leyenda -->
    <svg width="0" height="0" style="position: absolute" aria-hidden="true" focusable="false">
        <defs>
            <linearGradient id="markerGradientAssigned" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#60a5fa"/>
                <stop offset="100%" stop-color="#1d4ed8"/>
            </linearGradient>
            <linearGradient id="markerGradientUnassigned" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#34d399"/>
                <stop offset="100%" stop-color="#047857"/>
            </linearGradient>
        </defs>
    </svg>

    <!-- Incluir OpenLayers -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/css/ol.css">
    <script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/build/ol.js"></script>
    <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.8.0/proj4.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

        /* Estilos para controles de mapa */
        #map-controls {
            pointer-events: auto;
            z-index: 1 !important;
        }

        .absolute {
            position: absolute;
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
        .coordinate-display {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            z-index: 1;
            font-family: monospace;
            display: none;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Estilo para modo oscuro */
        .dark .coordinate-display {
            background-color: rgba(21, 23, 29, 0.9);
            color: #e5e7eb;
            border: 1px solid #4b5563;
        }

        /* Asegurar que el contenedor del mapa se ajuste al sidebar */
        .mx-auto {
            transition: margin-left 0.35s cubic-bezier(.4, 0, .2, 1);
        }

        .sidebar:not(.collapsed) ~ .flex-1 .mx-auto {
            margin-left: 0;
        }

        .sidebar.collapsed ~ .flex-1 .mx-auto {
            margin-left: 0;
        }

        /* Forzar que el contenedor del mapa sea responsivo */
        #map {
            transition: width 0.35s cubic-bezier(.4, 0, .2, 1), height 0.35s cubic-bezier(.4, 0, .2, 1);
        }

        /* Asegurar que el contenedor del mapa tenga dimensiones adecuadas */
        #map {
            min-height: 400px;
        }

        /* Animación suave para el redimensionamiento del mapa */
        #map .ol-viewport {
            transition: transform 0.3s ease-out;
        }

        /* Popup - AHORA CON ESTILOS CORREGIDOS */
        .ol-popup {
            position: absolute;
            transform: translateX(-50%);
            min-width: 260px;
            max-width: 320px;
            left: 50%;
            bottom: 12px;
            pointer-events: auto;
            z-index: 1000;
            display: block;
        }

        .ol-popup .popup-content {
            border-radius: 12px;
            padding: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.12);
            border: 1px solid rgba(0,0,0,0.06);
            color: #111827;
            background-clip: padding-box;
            background-color: #f9fafb;
        }

        .dark .ol-popup .popup-content {
            background-color: #1f2937;
            color: #e5e7eb;
            border: 1px solid rgba(255,255,255,0.04);
        }

        .ol-popup .popup-arrow {
            width: 16px;
            height: 16px;
            background: #f9fafb;
            border-left: 1px solid rgba(0,0,0,0.06);
            border-top: 1px solid rgba(0,0,0,0.06);
            transform: translateX(-50%) rotate(45deg);
            position: absolute;
            left: 50%;
            bottom: -8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.06);
        }

        .dark .ol-popup .popup-arrow {
            background: #1f2937;
            border-left: 1px solid rgba(255,255,255,0.04);
            border-top: 1px solid rgba(255,255,255,0.04);
        }

        /* =============================================
           MARCADORES DE MAPA — estilo profesional
           (pin tipo Google Maps/Mapbox + tarjeta de info)
           ============================================= */
        .map-marker {
            display: flex;
            flex-direction: column;
            align-items: center;
            pointer-events: auto;
            cursor: pointer;
            transform-origin: bottom center;
            opacity: 0;
            animation: markerFadeIn 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
            animation-delay: 0.35s;
        }

        /* Tarjeta flotante: nombre del polígono + estado del productor */
        .map-marker__card {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 128px;
            max-width: 220px;
            padding: 7px 12px 7px 13px;
            margin-bottom: 5px;
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.16), 0 1px 3px rgba(15, 23, 42, 0.08);
            z-index: 2;
            overflow: hidden;
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        }

        /* Barra de color a la izquierda = indicador de estado, patrón típico de tarjetas de estatus */
        .map-marker__card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
        }

        .map-marker--assigned .map-marker__card::before { background: linear-gradient(180deg, #60a5fa, #1d4ed8); }
        .map-marker--unassigned .map-marker__card::before { background: linear-gradient(180deg, #34d399, #047857); }

        .map-marker:hover .map-marker__card {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.22), 0 2px 4px rgba(15, 23, 42, 0.1);
            border-color: rgba(15, 23, 42, 0.14);
        }

        .map-marker__card-title {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.25;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            letter-spacing: -0.01em;
        }

        .map-marker__status {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 10.5px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .map-marker__status svg {
            flex: 0 0 auto;
        }

        .map-marker--assigned .map-marker__status { color: #1d4ed8; }
        .map-marker--unassigned .map-marker__status { color: #047857; }

        /* Pin SVG */
        .map-marker__pin {
            display: block;
            line-height: 0;
            filter: drop-shadow(0 3px 5px rgba(15, 23, 42, 0.35));
            transition: transform 0.15s ease;
        }

        .map-marker:hover .map-marker__pin {
            transform: scale(1.12);
        }

        /* Modo oscuro (clase .dark del layout) */
        .dark .map-marker__card {
            background: #1e293b;
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5), 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        .dark .map-marker:hover .map-marker__card {
            border-color: rgba(255, 255, 255, 0.16);
        }

        .dark .map-marker__card-title {
            color: #f1f5f9;
        }

        .dark .map-marker--assigned .map-marker__status { color: #93c5fd; }
        .dark .map-marker--unassigned .map-marker__status { color: #6ee7b7; }

        @media (max-width: 1024px) {
            .map-marker__card {
                max-width: 175px;
                padding: 6px 10px 6px 12px;
            }

            .map-marker__card-title {
                font-size: 11px;
            }

            .map-marker__status {
                font-size: 10px;
            }

            .map-marker__pin svg {
                width: 50px;
                height: 50px;
            }
        }

        @keyframes markerFadeIn {
            from {
                opacity: 0;
                transform: translateY(6px) scale(0.85);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
    </style>

    <script>
        // =============================================
        // CLASE PRINCIPAL DEL MAPA
        // =============================================
        class PolygonsMap {
            constructor() {
                this.map = null;
                this.polygonsLayer = null;
                this.ownerOverlays = [];
                this.coordinateDisplay = null;
                this.baseLayers = {};
                this.currentBaseLayer = null;
                this.popup = null; // Añadimos referencia al popup

                // Coordenadas de Venezuela por defecto
                this.INITIAL_CENTER = [-66.9036, 10.4806];
                this.INITIAL_ZOOM = 6;
                this.MINZOOM = 3;
                this.MAXZOOM = 18;

                console.log('Inicializando PolygonsMap...');
                
                // Esperar a que el DOM esté completamente listo
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
                console.log('Elemento #map encontrado:', mapElement);

                this.initializeMap();
                this.setupEventListeners();
                this.setupCoordinateDisplay();
                this.setupMapResizeObserver();
                
                setTimeout(() => {
                    if (this.map) {
                        this.map.updateSize();
                    }
                }, 500);
            }

            setupMapResizeObserver() {
                console.log('Configurando observador de redimensionamiento...');
                
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
                    }, 100);
                }
            }

            initializeMap() {
                console.log('Inicializando mapa...');
                
                try {
                    this.setupBaseLayers();
                    this.setupPolygonsLayer();
                    this.setupMapInstance();
                    
                    // Cargar polígonos después de inicializar el mapa
                    this.loadPolygons();
                    
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

            setupPolygonsLayer() {
                this.polygonsLayer = new ol.layer.Vector({
                    source: new ol.source.Vector(),
                    style: (feature) => {
                        const type = feature.get('type');
                        return new ol.style.Style({
                            fill: new ol.style.Fill({
                                color: type === 'with_producer' ? 'rgba(59, 130, 246, 0.3)' : 'rgba(34, 197, 94, 0.3)'
                            }),
                            stroke: new ol.style.Stroke({
                                color: type === 'with_producer' ? '#1d4ed8' : '#15803d',
                                width: 2
                            })
                        });
                    }
                });
            }

            setupMapInstance() {
                const baseLayerGroup = new ol.layer.Group({
                    layers: Object.values(this.baseLayers)
                });

                const initialCenter = ol.proj.fromLonLat(this.INITIAL_CENTER);

                this.map = new ol.Map({
                    target: 'map',
                    layers: [baseLayerGroup, this.polygonsLayer],
                    view: new ol.View({
                        center: initialCenter,
                        zoom: this.INITIAL_ZOOM,
                        minZoom: this.MINZOOM,
                        maxZoom: this.MAXZOOM,
                        smoothResolutionConstraint: true
                    })
                });

                this.currentBaseLayer = this.baseLayers.osm;
                console.log('Instancia del mapa creada:', this.map);
            }

            setupEventListeners() {
                // IMPORTANTE: Configurar el popup ANTES de los controles
                this.setupPopup();
                
                // Luego configurar los controles
                this.setupMapControls();
            }

            // =============================================
            // POPUP CORREGIDO
            // =============================================
            setupPopup() {
                console.log('Configurando popup...');
                
                // Obtener el elemento del popup
                const popupElement = document.getElementById('popup');
                if (!popupElement) {
                    console.error('No se encontró el elemento #popup');
                    return;
                }
                
                console.log('Elemento popup encontrado:', popupElement);
                
                // Crear el overlay del popup
                this.popup = new ol.Overlay({
                    element: popupElement,
                    positioning: 'bottom-center',
                    stopEvent: false,
                    offset: [0, -15],
                    autoPan: true,
                    autoPanAnimation: {
                        duration: 250
                    }
                });
                
                // Agregar el popup al mapa
                this.map.addOverlay(this.popup);
                console.log('Popup agregado al mapa');
                
                // Configurar el evento de clic en el mapa
                this.map.on('click', (evt) => {
                    console.log('Clic en el mapa en coordenadas:', evt.coordinate);
                    
                    // Buscar si hay un polígono en la posición del clic
                    const feature = this.map.forEachFeatureAtPixel(evt.pixel, (feature) => {
                        return feature;
                    });
                    
                    console.log('Feature encontrado:', feature);
                    
                    if (feature) {
                        // Obtener las propiedades del feature
                        const properties = feature.getProperties();
                        console.log('Propiedades del feature:', properties);
                        
                        // Construir el contenido del popup
                        const popupContent = this.buildPopupContent(properties);
                        
                        // Actualizar el contenido del popup
                        const contentEl = popupElement.querySelector('.popup-content');
                        if (contentEl) {
                            contentEl.innerHTML = popupContent;
                        }
                        
                        // Posicionar el popup
                        this.popup.setPosition(evt.coordinate);
                        console.log('Popup posicionado en:', evt.coordinate);
                    } else {
                        // Si no hay feature, ocultar el popup
                        this.popup.setPosition(undefined);
                        console.log('Popup ocultado');
                    }
                });
                
                // Opcional: cambiar el cursor cuando se pasa sobre un polígono
                this.map.on('pointermove', (evt) => {
                    const hit = this.map.hasFeatureAtPixel(evt.pixel);
                    this.map.getTargetElement().style.cursor = hit ? 'pointer' : '';
                });
            }
            
            // Método auxiliar para construir el contenido del popup
            buildPopupContent(properties) {
                const name = properties.name || 'Polígono sin nombre';
                const producer = properties.producer || 'Sin productor asignado';
                const area = properties.area_ha ? properties.area_ha.toFixed(2) : 'No calculada';
                const description = properties.description || '';
                const type = properties.type || 'without_producer';
                const typeText = type === 'with_producer' ? 'Con Productor' : 'Sin Productor';
                const id = properties.id || 'N/A';
                
                // Determinar color de fondo según el tipo
                const typeClass = type === 'with_producer' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800';
                
                return `
                    <div class="p-3">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-bold text-lg text-gray-900 dark:text-white">${this.escapeHtml(name)}</h3>
                            <span class="text-xs px-2 py-1 rounded-full ${typeClass}">${typeText}</span>
                        </div>
                        
                        <div class="space-y-2 text-sm">
                            <p class="text-gray-700 dark:text-gray-300">
                                <span class="font-semibold">ID:</span> ${id}
                            </p>
                            <p class="text-gray-700 dark:text-gray-300">
                                <span class="font-semibold">Productor:</span> ${this.escapeHtml(producer)}
                            </p>
                            <p class="text-gray-700 dark:text-gray-300">
                                <span class="font-semibold">Área:</span> ${area} ha
                            </p>
                            ${description ? `
                                <p class="text-gray-600 dark:text-gray-400 text-sm border-t pt-2 mt-2">
                                    ${this.escapeHtml(description)}
                                </p>
                            ` : ''}
                        </div>
                        
                        <div class="mt-3 flex justify-end">
                            <a href="/polygons/${id}" class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                Ver detalles completos →
                            </a>
                        </div>
                    </div>
                `;
            }

            setupMapControls() {
                console.log('Configurando controles del mapa...');
                
                // Botón de cambio de mapa base
                const baseMapToggle = document.getElementById('base-map-toggle');
                if (baseMapToggle) {
                    baseMapToggle.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const menu = document.getElementById('base-map-menu');
                        const isShowing = menu.classList.contains('show');
                        
                        toggleMenu('base-map-menu', !isShowing);
                    });
                }
                
                // Cambiar capas base
                document.querySelectorAll('#base-map-menu button').forEach(button => {
                    button.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const layerKey = button.getAttribute('data-layer');
                        
                        this.changeBaseLayer(layerKey);
                        closeMenu('base-map-menu');
                    });
                });
                
                // Botón de pantalla completa
                document.getElementById('fullscreen-toggle')?.addEventListener('click', () => {
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
                    
                    if (!baseMapToggle?.contains(e.target) && !baseMapMenu?.contains(e.target)) {
                        closeMenu('base-map-menu');
                    }
                });
            }

            // =============================================
            // DISPLAY DE COORDENADAS
            // =============================================

            setupCoordinateDisplay() {
                console.log('Configurando display de coordenadas...');
                this.createCoordinateDisplayElement();
                
                this.map.on('pointermove', (evt) => {
                    if (evt.dragging) return;
                    this.updateCoordinateDisplay(evt.coordinate);
                });
            }

            createCoordinateDisplayElement() {
                console.log('Creando elemento display de coordenadas...');
                
                const existingDisplays = document.querySelectorAll('.coordinate-display');
                existingDisplays.forEach(display => display.remove());
                
                this.coordinateDisplay = document.createElement('div');
                this.coordinateDisplay.className = 'coordinate-display';
                
                const mapContainer = this.map.getTargetElement();
                if (mapContainer) {
                    mapContainer.style.position = 'relative';
                    mapContainer.appendChild(this.coordinateDisplay);
                    console.log('Display de coordenadas agregado al mapa');
                } else {
                    console.error('No se encontró el contenedor del mapa');
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
                            `Este: ${easting.toFixed(6)} | ` +
                            `Norte: ${northing.toFixed(6)}`;
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

            // =============================================
            // MANEJO DE CAPAS BASE
            // =============================================

            changeBaseLayer(layerKey) {
                console.log('Cambiando capa base a:', layerKey);
                
                if (!this.baseLayers[layerKey]) {
                    console.error('Capa no encontrada:', layerKey);
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

            // =============================================
            // CARGA DE POLÍGONOS
            // =============================================

            clearOwnerOverlays() {
                if (!this.ownerOverlays || this.ownerOverlays.length === 0) return;
                this.ownerOverlays.forEach(o => this.map.removeOverlay(o));
                this.ownerOverlays = [];
            }

            createOwnerBubbleOverlay(feature) {
                const geom = feature.getGeometry();
                let interiorPoint = null;
                if (geom.getInteriorPoint) {
                    interiorPoint = geom.getInteriorPoint().getCoordinates();
                } else {
                    interiorPoint = geom.getClosestPoint(geom.getExtent());
                }

                const producer = feature.get('producer') || '';
                const name = feature.get('name') || 'Polígono sin nombre';
                const type = feature.get('type') || 'with_producer';
                const isAssigned = type === 'with_producer';

                let title = name;
                if (title.length > 26) title = title.slice(0, 24) + '…';

                let statusText = isAssigned ? (producer || 'Con productor') : 'Sin productor';
                if (statusText.length > 26) statusText = statusText.slice(0, 24) + '…';

                const statusClass = isAssigned ? 'map-marker--assigned' : 'map-marker--unassigned';
                const gradientId = isAssigned ? 'markerGradientAssigned' : 'markerGradientUnassigned';

                // Icono grande dentro del pin (blanco) y mini icono dentro de la tarjeta (currentColor)
                const pinIconSvg = isAssigned
                    ? '<path d="M9.3 9.3l1.8 1.8 3.6-4" stroke="#ffffff" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>'
                    : '<line x1="12" y1="6" x2="12" y2="9.8" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="12" r="1" fill="#ffffff"/>';

                const statusIconSvg = isAssigned
                    ? '<svg viewBox="0 0 24 24" width="12" height="12"><path d="M9.3 9.3l1.8 1.8 3.6-4" stroke="currentColor" stroke-width="2.6" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>'
                    : '<svg viewBox="0 0 24 24" width="12" height="12"><line x1="12" y1="6" x2="12" y2="9.8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><circle cx="12" cy="12" r="1.3" fill="currentColor"/></svg>';

                const container = document.createElement('div');
                container.className = `map-marker ${statusClass}`;
                container.setAttribute('title', `${name} — ${isAssigned ? (producer || 'Con productor') : 'Sin productor'}`);
                container.innerHTML = `
                    <div class="map-marker__card">
                        <div class="map-marker__card-title">${this.escapeHtml(title)}</div>
                        <div class="map-marker__status">
                            ${statusIconSvg}
                            <span>${this.escapeHtml(statusText)}</span>
                        </div>
                    </div>
                    <div class="map-marker__pin">
                        <svg viewBox="0 0 24 24" width="44" height="44" xmlns="http://www.w3.org/2000/svg class="w-6 h-6">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" fill="url(#${gradientId})" stroke="#ffffff" stroke-width="1"/>
                            ${pinIconSvg}
                        </svg>
                    </div>
                `;

                // Permite abrir la ficha del polígono también haciendo clic en el marcador
                container.addEventListener('click', (evt) => {
                    evt.stopPropagation();
                    const popupElement = document.getElementById('popup');
                    if (!popupElement || !this.popup) return;
                    const contentEl = popupElement.querySelector('.popup-content');
                    if (contentEl) {
                        contentEl.innerHTML = this.buildPopupContent(feature.getProperties());
                    }
                    this.popup.setPosition(interiorPoint);
                });

                const overlay = new ol.Overlay({
                    element: container,
                    position: interiorPoint,
                    positioning: 'bottom-center',
                    stopEvent: false,
                    offset: [0, 3]
                });

                this.map.addOverlay(overlay);
                this.ownerOverlays.push(overlay);
            }

            escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.appendChild(document.createTextNode(String(text)));
                return div.innerHTML;
            }

            async loadPolygons() {
                try {
                    console.log('Cargando polígonos...');
                    const response = await fetch('{{ route("polygons.geojson") }}');
                    const data = await response.json();
                    
                    console.log('Datos GeoJSON recibidos:', data);
                    
                    const features = new ol.format.GeoJSON().readFeatures(data, {
                        dataProjection: 'EPSG:4326',
                        featureProjection: 'EPSG:3857'
                    });

                    console.log('Features procesados:', features);

                    this.polygonsLayer.getSource().clear();
                    this.clearOwnerOverlays();

                    this.polygonsLayer.getSource().addFeatures(features);

                    features.forEach(f => {
                        if (f.get('producer') || f.get('name')) {
                            this.createOwnerBubbleOverlay(f);
                        }
                    });

                    if (features.length > 0) {
                        const extent = this.polygonsLayer.getSource().getExtent();
                        this.map.getView().fit(extent, { padding: [50, 50, 50, 50], maxZoom: 15 });
                    }
                    
                    console.log(`Polígonos cargados: ${features.length}`);
                } catch (error) {
                    console.error('Error loading polygons:', error);
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
        // FUNCIONES GLOBALES PARA MENÚS
        // =============================================

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

        function setupSidebarObserver() {
            const sidebar = document.getElementById('sidebar');
            if (!sidebar) {
                console.log('No se encontró el sidebar');
                return;
            }
            
            console.log('Configurando observador para sidebar...');
            
            const sidebarObserver = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        console.log('Sidebar cambió de estado, redimensionando mapa...');
                        setTimeout(() => {
                            if (window.polygonsMapInstance && window.polygonsMapInstance.map) {
                                window.polygonsMapInstance.map.updateSize();
                                console.log('Mapa redimensionado después de cambio en sidebar');
                            }
                        }, 400);
                    }
                });
            });
            
            sidebarObserver.observe(sidebar, {
                attributes: true,
                attributeFilter: ['class']
            });
            
            const sidebarToggle = document.getElementById('sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    console.log('Botón del sidebar clickeado, redimensionando mapa...');
                    setTimeout(() => {
                        if (window.polygonsMapInstance && window.polygonsMapInstance.map) {
                            window.polygonsMapInstance.map.updateSize();
                            console.log('Mapa redimensionado después de clic en sidebar toggle');
                        }
                    }, 400);
                });
            }
        }

        function setupWindowResizeHandler() {
            window.addEventListener('resize', debounce(function() {
                if (window.polygonsMapInstance && window.polygonsMapInstance.map) {
                    setTimeout(() => {
                        window.polygonsMapInstance.map.updateSize();
                    }, 100);
                }
            }, 250));
            
            window.addEventListener('load', function() {
                if (window.polygonsMapInstance && window.polygonsMapInstance.map) {
                    setTimeout(() => {
                        window.polygonsMapInstance.map.updateSize();
                    }, 500);
                }
            });
            
            setTimeout(() => {
                if (window.polygonsMapInstance && window.polygonsMapInstance.map) {
                    window.polygonsMapInstance.map.updateSize();
                }
            }, 1000);
        }

        // =============================================
        // INICIALIZACIÓN
        // =============================================

        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM completamente cargado, inicializando mapa...');
            
            window.polygonsMapInstance = new PolygonsMap();
            
            setupSidebarObserver();
            setupWindowResizeHandler();
        });
    </script>
</x-app-layout>
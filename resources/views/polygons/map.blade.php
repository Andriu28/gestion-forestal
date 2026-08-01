{{-- [file name]: map.blade.php --}}
<x-app-layout>
    <div class="mx-auto">
        <div class="bg-stone-100/90 dark:bg-custom-gray shadow-sm sm:rounded-2xl shadow-soft p-4 md:p-6 lg:p-6 mb-6">
            <div class="text-gray-900 dark:text-gray-100">
                <div class="flex justify-between items-center mb-2">
                    <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-200 mb-2 md:mb-2">
                        {{ __('Mapa de Polígonos') }}
                    </h2>

                    <a href="{{ route('polygons.index') }}"
                    title="Ver lista de polígonos" 
                    class="group px-2.5 py-1.5 bg-stone-200/80 hover:bg-blue-600/70 dark:hover:bg-blue-500/60 text-stone-700 hover:text-white border border-stone-300/70 hover:border-transparent dark:bg-gray-700/40 dark:text-gray-300 dark:hover:text-white dark:border-gray-600/50 rounded-md flex items-center hover:shadow-md hover:-translate-y-0.5 overflow-hidden">
                        <span class="flex items-center justify-center w-6 h-6 transition-all duration-300 group-hover:w-6 group-hover:h-6 flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2002/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-icon lucide-map w-6 h-6 text-blue-700/70 group-hover:text-white dark:text-blue-400/70">
                                <rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>
                            </svg>
                        </span>
                        <span class="text-base font-medium transition-all duration-300 w-0 opacity-0 group-hover:w-10 group-hover:opacity-100 group-hover:ml-1 whitespace-nowrap overflow-hidden text-inherit">
                            Lista
                        </span>
                    </a>
                </div>

                <!-- Mapa -->
                <div class="relative rounded-lg overflow-hidden mb-6 border border-gray-200 dark:border-gray-700 mt-1" style="height: 75vh; border: 1px solid #dededeff; border-radius: 0.5rem; position: relative;">
                    <div id="map" class="h-full w-full"></div>
                    
                    <!-- Controles del mapa -->
                    <div id="map-controls" style="position: absolute; top: 10px; right: 10px; z-index: 1000;">
                        <div class="flex flex-col items-end space-y-2">
                            <div class="flex space-x-2">
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
                <div class="mt-4 flex flex-wrap gap-6 items-center">
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 rounded-full bg-red-500 border-2 border-white shadow-md"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Con deforestación</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 rounded-full bg-green-500 border-2 border-white shadow-md"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Sin deforestación</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 rounded-full bg-gray-400 border-2 border-white shadow-md"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Sin datos de deforestación</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 rounded-full bg-blue-500 border-2 border-white shadow-md"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Sin productor asignado</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Incluir OpenLayers -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/css/ol.css">
    <script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/build/ol.js"></script>
    <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.8.0/proj4.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
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

        #map {
            width: 100% !important;
            height: 100% !important;
            position: absolute !important;
            top: 0;
            left: 0;
        }

        #map-controls {
            pointer-events: auto;
            z-index: 1000 !important;
        }

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

        .dark .coordinate-display {
            background-color: rgba(21, 23, 29, 0.9);
            color: #e5e7eb;
            border: 1px solid #4b5563;
        }

        #map {
            min-height: 400px;
        }

        /* =============================================
           MARCADORES DE MAPA — CON TARJETA FLOTANTE
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
            position: relative;
        }

        .map-marker__pin {
            display: block;
            line-height: 0;
            filter: drop-shadow(0 3px 5px rgba(15, 23, 42, 0.35));
            transition: transform 0.15s ease;
            pointer-events: auto;
            cursor: pointer;
            position: relative;
        }

        .map-marker:hover .map-marker__pin {
            transform: scale(1.12);
        }

        .map-marker__card {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(4px) scale(0.95);
            width: 320px;
            padding: 8px 14px 8px 16px;
            margin-bottom: 8px;
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.18), 0 1px 4px rgba(15, 23, 42, 0.08);
            z-index: 10;
            overflow: hidden;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease, transform 0.25s ease, box-shadow 0.15s ease;
        }

        .map-marker:hover .map-marker__card {
            opacity: 1;
            transform: translateX(-50%) translateY(0) scale(1);
        }

        /* Barra de color a la izquierda */
        .map-marker__card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            border-radius: 12px 0 0 12px;
        }

        /* Colores de la barra según estado */
        .map-marker--has-deforestation .map-marker__card::before {
            background: linear-gradient(180deg, #ef4444, #dc2626);
        }
        .map-marker--no-deforestation .map-marker__card::before {
            background: linear-gradient(180deg, #22c55e, #16a34a);
        }
        .map-marker--no-data .map-marker__card::before {
            background: linear-gradient(180deg, #9ca3af, #6b7280);
        }
        .map-marker--no-producer .map-marker__card::before {
            background: linear-gradient(180deg, #3b82f6, #2563eb);
        }

        /* Título */
        .map-marker__card-title {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.3;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            letter-spacing: -0.01em;
        }

        .dark .map-marker__card-title {
            color: #f1f5f9;
        }

        /* Productor y área */
        .map-marker__producer-area {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #374151;
            margin-top: 2px;
            gap: 6px;
            flex-wrap: wrap;
        }

        .dark .map-marker__producer-area {
            color: #d1d5db;
        }

        .map-marker__producer-area .area-badge {
            background: rgba(59, 130, 246, 0.1);
            padding: 1px 8px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            color: #2563eb;
            white-space: nowrap;
        }

        .dark .map-marker__producer-area .area-badge {
            background: rgba(59, 130, 246, 0.2);
            color: #93c5fd;
        }

        /* =============================================
           ESTILOS PARA INFORMACIÓN DE DEFORESTACIÓN
           ============================================= */

        .deforestation-info {
            font-size: 12px;
            margin-top: 4px;
            padding-top: 4px;
            border-top: 1px solid rgba(0,0,0,0.06);
        }

        .dark .deforestation-info {
            border-top-color: rgba(255,255,255,0.06);
        }

        .deforestation-stats {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .deforestation-meta {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            color: #6b7280;
            font-size: 12px;
            margin-top: 2px;
        }

        .dark .deforestation-meta {
            color: #9ca3af;
        }

        .deforestation-meta span {
            display: inline-flex;
            align-items: center;
            gap: 2px;
        }

        /* Colores de las estadísticas */
        .deforestation-info .loss {
            font-weight: 600;
        }

        .deforestation-info .loss.avg {
            color: #2563eb;
        }

        .dark .deforestation-info .loss.avg {
            color: #93c5fd;
        }

        .deforestation-info .loss.max {
            color: #dc2626;
        }

        .dark .deforestation-info .loss.max {
            color: #fca5a5;
        }

        /* Sin deforestación - verde */
        .deforestation-info.no-deforestation .loss {
            color: #16a34a;
        }

        .dark .deforestation-info.no-deforestation .loss {
            color: #86efac;
        }

        /* Sin datos - gris */
        .deforestation-info.no-data .loss {
            color: #6b7280;
        }

        .dark .deforestation-info.no-data .loss {
            color: #9ca3af;
        }

        /* Colores de los estados */
        .map-marker--has-deforestation .map-marker__status { color: #dc2626; }
        .map-marker--no-deforestation .map-marker__status { color: #16a34a; }
        .map-marker--no-data .map-marker__status { color: #6b7280; }
        .map-marker--no-producer .map-marker__status { color: #2563eb; }

        .map-marker--has-deforestation .marker-fill { fill: #ef4444; }
        .map-marker--no-deforestation .marker-fill { fill: #22c55e; }
        .map-marker--no-data .marker-fill { fill: #9ca3af; }
        .map-marker--no-producer .marker-fill { fill: #3b82f6; }

        /* Modo oscuro */
        .dark .map-marker__card {
            background: #1e293b;
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.5), 0 1px 4px rgba(0, 0, 0, 0.3);
        }

        .dark .map-marker--has-deforestation .map-marker__status { color: #fca5a5; }
        .dark .map-marker--no-deforestation .map-marker__status { color: #86efac; }
        .dark .map-marker--no-data .map-marker__status { color: #9ca3af; }
        .dark .map-marker--no-producer .map-marker__status { color: #93c5fd; }

        .dark .map-marker__pin {
            filter: drop-shadow(0 3px 6px rgba(0, 0, 0, 0.5));
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

        /* Responsive */
        @media (max-width: 1024px) {
            .map-marker__card {
                max-width: 280px;
                min-width: 170px;
                padding: 6px 10px 6px 12px;
            }
            .map-marker__card-title {
                font-size: 12px;
            }
            .map-marker__producer-area {
                font-size: 10px;
            }
            .deforestation-info {
                font-size: 9px;
            }
            .map-marker__pin svg {
                width: 50px;
                height: 50px;
            }
        }

        @media (max-width: 640px) {
            .map-marker__card {
                max-width: 220px;
                min-width: 150px;
                padding: 5px 8px 5px 10px;
            }
            .map-marker__card-title {
                font-size: 11px;
            }
            .map-marker__producer-area {
                font-size: 9px;
            }
            .deforestation-info {
                font-size: 8px;
            }
            .deforestation-stats {
                gap: 4px;
            }
            .deforestation-meta {
                font-size: 7.5px;
                gap: 4px;
            }
            .map-marker__pin svg {
                width: 40px;
                height: 40px;
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
                this.markerOverlays = [];
                this.coordinateDisplay = null;
                this.baseLayers = {};
                this.currentBaseLayer = null;

                this.INITIAL_CENTER = [-66.9036, 10.4806];
                this.INITIAL_ZOOM = 6;
                this.MINZOOM = 3;
                this.MAXZOOM = 18;

                console.log('Inicializando PolygonsMap...');
                
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
                try {
                    this.setupBaseLayers();
                    this.setupPolygonsLayer();
                    this.setupMapInstance();
                    this.loadPolygons();
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

            setupPolygonsLayer() {
                this.polygonsLayer = new ol.layer.Vector({
                    source: new ol.source.Vector(),
                    style: new ol.style.Style({
                        fill: new ol.style.Fill({
                            color: 'rgba(59, 130, 246, 0.15)'
                        }),
                        stroke: new ol.style.Stroke({
                            color: '#1d4ed8',
                            width: 2
                        })
                    })
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

                // Recalcular el apilamiento (z-index) de los marcadores cada vez
                // que cambia la vista, para que el orden "quién tapa a quién"
                // siga correspondiendo a la posición real en pantalla.
                this.map.on('moveend', () => this.updateMarkerStacking());
            }

            setupEventListeners() {
                this.setupMapControls();
            }

            setupMapControls() {
                const baseMapToggle = document.getElementById('base-map-toggle');
                if (baseMapToggle) {
                    baseMapToggle.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const menu = document.getElementById('base-map-menu');
                        const isShowing = menu.classList.contains('show');
                        toggleMenu('base-map-menu', !isShowing);
                    });
                }
                
                document.querySelectorAll('#base-map-menu button').forEach(button => {
                    button.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const layerKey = button.getAttribute('data-layer');
                        this.changeBaseLayer(layerKey);
                        closeMenu('base-map-menu');
                    });
                });
                
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
                this.createCoordinateDisplayElement();
                
                this.map.on('pointermove', (evt) => {
                    if (evt.dragging) return;
                    this.updateCoordinateDisplay(evt.coordinate);
                });
            }

            createCoordinateDisplayElement() {
                const existingDisplays = document.querySelectorAll('.coordinate-display');
                existingDisplays.forEach(display => display.remove());
                
                this.coordinateDisplay = document.createElement('div');
                this.coordinateDisplay.className = 'coordinate-display';
                
                const mapContainer = this.map.getTargetElement();
                if (mapContainer) {
                    mapContainer.style.position = 'relative';
                    mapContainer.appendChild(this.coordinateDisplay);
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

            // =============================================
            // CARGA DE POLÍGONOS CON TARJETAS FLOTANTES
            // =============================================

            clearMarkerOverlays() {
                if (!this.markerOverlays || this.markerOverlays.length === 0) return;
                this.markerOverlays.forEach(o => this.map.removeOverlay(o));
                this.markerOverlays = [];
            }

createMarkerOverlay(feature) {
    const geom = feature.getGeometry();
    let interiorPoint = null;
    if (geom.getInteriorPoint) {
        interiorPoint = geom.getInteriorPoint().getCoordinates();
    } else {
        interiorPoint = geom.getClosestPoint(geom.getExtent());
    }

    const properties = feature.getProperties();
    const name = properties.name || 'Polígono sin nombre';
    const producer = properties.producer || 'Sin productor asignado';
    const hasProducer = properties.type === 'with_producer';
    
    // Obtener datos de deforestación
    const deforestations = properties.deforestations || [];
    const hasDeforestationData = deforestations && deforestations.length > 0;
    
    // Determinar el estado de deforestación
    let deforestationStatus = properties.deforestation_status || 'no_data';
    let markerClass = 'map-marker--no-data';
    let statusText = 'Sin datos';
    let statusIcon = '';
    let deforestationInfoHtml = '';
    
    // Calcular área
    const areaText = properties.area_ha ? parseFloat(properties.area_ha).toFixed(2) + ' ha' : 'Sin área';
    
    // =============================================
    // CALCULAR PROMEDIO DE DEFORESTACIÓN
    // =============================================
    
    if (deforestationStatus === 'has_deforestation') {
        markerClass = 'map-marker--has-deforestation';
        statusText = 'Con deforestación';
        statusIcon = '🔴';
        
        if (hasDeforestationData) {
            const lossData = deforestations.map(d => ({
                year: d.year,
                loss: parseFloat(d.percentage_loss) || 0
            }));
            
            lossData.sort((a, b) => a.year - b.year);
            
            const totalLoss = lossData.reduce((sum, d) => sum + d.loss, 0);
            const avgLoss = lossData.length > 0 ? totalLoss / lossData.length : 0;
            const maxLossEntry = lossData.reduce((max, current) => 
                current.loss > max.loss ? current : max, 
                lossData[0] || { year: null, loss: 0 }
            );
            
            const firstYear = lossData.length > 0 ? lossData[0].year : null;
            const lastYear = lossData.length > 0 ? lossData[lossData.length - 1].year : null;
            const yearRange = (firstYear && lastYear) 
                ? (firstYear === lastYear ? firstYear : `${firstYear} - ${lastYear}`)
                : 'N/A';
            
            const yearsWithLoss = lossData
                .filter(d => d.loss > 0)
                .map(d => d.year);
            
            deforestationInfoHtml = `
                <div class="deforestation-info has-deforestation">
                    <div class="deforestation-stats">
                        <span class="loss avg">📊 ${avgLoss.toFixed(2)}% promedio</span>
                        <span class="loss max">🔴 ${maxLossEntry.loss.toFixed(2)}% máximo</span>
                    </div>
                    <div class="deforestation-meta">
                        <span>📅 ${yearRange}</span>
                        <span>📈 ${lossData.length} años</span>
                        ${yearsWithLoss.length > 0 ? `<span>⚠️ ${yearsWithLoss.length} con pérdida</span>` : ''}
                    </div>
                </div>
            `;
        }
    } else if (deforestationStatus === 'no_deforestation') {
        markerClass = 'map-marker--no-deforestation';
        statusText = 'Sin deforestación';
        statusIcon = '🟢';
        deforestationInfoHtml = `
            <div class="deforestation-info no-deforestation">
                <span class="loss">✅ Sin pérdida registrada</span>
                ${hasDeforestationData ? `<span style="font-size:8.5px;color:#6b7280;margin-left:4px;">📅 ${deforestations.length} años analizados</span>` : ''}
            </div>
        `;
    } else if (!hasProducer) {
        markerClass = 'map-marker--no-producer';
        statusText = 'Sin productor';
        statusIcon = '🔵';
        if (hasDeforestationData) {
            const lossData = deforestations.map(d => ({
                year: d.year,
                loss: parseFloat(d.percentage_loss) || 0
            }));
            const totalLoss = lossData.reduce((sum, d) => sum + d.loss, 0);
            const avgLoss = lossData.length > 0 ? totalLoss / lossData.length : 0;
            const maxLoss = Math.max(...lossData.map(d => d.loss));
            
            deforestationInfoHtml = `
                <div class="deforestation-info has-deforestation" style="border-top-color:rgba(59,130,246,0.15);">
                    <div class="deforestation-stats">
                        <span class="loss avg" style="color:#3b82f6;">📊 ${avgLoss.toFixed(2)}% promedio</span>
                        <span class="loss max" style="color:#dc2626;">🔴 ${maxLoss.toFixed(2)}% máximo</span>
                    </div>
                    <div class="deforestation-meta">
                        <span>📈 ${lossData.length} años</span>
                    </div>
                </div>
            `;
        }
    } else {
        markerClass = 'map-marker--no-data';
        statusText = 'Sin datos';
        statusIcon = '⚪';
        deforestationInfoHtml = `
            <div class="deforestation-info no-data">
                <span class="loss">⚪ Sin análisis de deforestación</span>
            </div>
        `;
    }

    // Icono dentro del pin
    let pinIcon = '';
    if (deforestationStatus === 'has_deforestation') {
        pinIcon = `<text x="20" y="15.5" text-anchor="middle" font-size="5" font-weight="bold" fill="#ffffff">!</text>`;
    } else if (deforestationStatus === 'no_deforestation') {
        pinIcon = `<path d="M17.5 13l1.8 1.8 3.6-4" stroke="#ffffff" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>`;
    } else if (!hasProducer) {
        pinIcon = `
            <circle cx="20" cy="11.5" r="1.8" fill="#ffffff" opacity="0.9"/>
            <path d="M17.5 15.5c0-1.4 1.1-2.5 2.5-2.5s2.5 1.1 2.5 2.5" stroke="#ffffff" stroke-width="1.2" fill="none" stroke-linecap="round"/>
        `;
    } else {
        pinIcon = `<text x="20" y="15.5" text-anchor="middle" font-size="5.5" font-weight="bold" fill="#ffffff">?</text>`;
    }

    // Truncar nombres
    let displayName = name;
    if (displayName.length > 36) displayName = displayName.slice(0, 34) + '…';
    
    let displayProducer = producer;
    if (displayProducer.length > 26) displayProducer = displayProducer.slice(0, 24) + '…';

    const container = document.createElement('div');
    container.className = `map-marker ${markerClass}`;
    container.setAttribute('title', `${name} — ${statusText}`);
    
    container.innerHTML = `
        <div class="map-marker__card">
            <div class="map-marker__card-title">🌳 ${this.escapeHtml(displayName)}</div>
            <div class="map-marker__producer-area">
                ${hasProducer 
                    ? `👤 <span>${this.escapeHtml(displayProducer)}</span>` 
                    : `🔵 <span>Sin productor</span>`
                }
                <span class="area-badge">📐 ${areaText}</span>
            </div>
            ${deforestationInfoHtml}
        </div>
        <div class="map-marker__pin">
            <svg viewBox="0 0 40 40" width="40" height="40" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 2C13.37 2 8 7.37 8 14c0 5.96 8.5 16.2 10.98 18.87a1.5 1.5 0 0 0 2.04 0C23.5 30.2 32 19.96 32 14c0-6.63-5.37-12-12-12z" 
                    class="marker-fill" stroke="#ffffff" stroke-width="1.5"/>
                <circle cx="20" cy="13" r="5" fill="#ffffff" opacity="0.9"/>
                ${pinIcon}
            </svg>
        </div>
    `;

    // =============================================
    // CREAR OVERLAY — Z-INDEX SEGÚN POSICIÓN EN PANTALLA
    // =============================================
    // OJO: OpenLayers envuelve el `element` que le pasamos dentro de SU
    // PROPIO div (.ol-overlay-container), y ese wrapper lleva un
    // `transform: translate(...)` inline. Cualquier elemento con transform
    // crea su propio contexto de apilamiento en CSS, así que el z-index que
    // realmente compite entre marcadores es el del WRAPPER, no el de
    // nuestro `container` (.map-marker). Poner el z-index en `container`
    // no tenía ningún efecto entre overlays distintos — por eso algunas
    // tarjetas seguían apareciendo detrás de pines vecinos. La coordenada Y
    // en pantalla se usa como z-index base: el marcador más "bajo" (más
    // cerca del usuario) siempre queda encima del que está más "arriba".
    const overlay = new ol.Overlay({
        element: container,
        position: interiorPoint,
        positioning: 'bottom-center',
        stopEvent: false,
        offset: [0, 0]
    });

    container._hovered = false;

    // El wrapper (.ol-overlay-container) solo existe una vez que el overlay
    // se agrega al mapa, así que lo añadimos primero.
    this.map.addOverlay(overlay);
    this.markerOverlays.push(overlay);

    const overlayWrapper = container.parentElement;
    if (overlayWrapper) {
        overlayWrapper.style.zIndex = '1';
    }

    container.addEventListener('mouseenter', () => {
        container._hovered = true;
        if (container.parentElement) {
            container.parentElement.style.zIndex = '9999';
        }
    });

    container.addEventListener('mouseleave', () => {
        container._hovered = false;
        // Pequeño retraso para permitir que termine cualquier transición
        setTimeout(() => {
            if (!container._hovered && container.parentElement) {
                container.parentElement.style.zIndex = container.dataset.baseZIndex || '1';
            }
        }, 50);
    });
}

/**
 * Recalcula el z-index de todos los marcadores según su posición vertical
 * actual en pantalla (píxel Y). Se debe llamar después de crear/mover los
 * overlays y en cada 'moveend' del mapa para mantener el apilamiento
 * correcto al hacer pan/zoom.
 *
 * Importante: el z-index se aplica sobre el WRAPPER que crea OpenLayers
 * (overlay.getElement().parentElement), no sobre el elemento que nosotros
 * creamos — ver nota en createMarkerOverlay().
 */
updateMarkerStacking() {
    if (!this.markerOverlays || this.markerOverlays.length === 0) return;

    this.markerOverlays.forEach((overlay) => {
        const element = overlay.getElement();
        const wrapper = element ? element.parentElement : null;
        const position = overlay.getPosition();
        if (!element || !wrapper || !position) return;

        const pixel = this.map.getPixelFromCoordinate(position);
        if (!pixel) return;

        const baseZIndex = String(Math.round(pixel[1]));
        element.dataset.baseZIndex = baseZIndex;

        // No pisar el z-index elevado de un marcador que está en hover
        if (!element._hovered) {
            wrapper.style.zIndex = baseZIndex;
        }
    });
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

                    console.log('Features procesados:', features.length);

                    this.polygonsLayer.getSource().clear();
                    this.clearMarkerOverlays();

                    this.polygonsLayer.getSource().addFeatures(features);

                    features.forEach((f, index) => {
                        console.log(`Creando marcador ${index}:`, f.getProperties());
                        this.createMarkerOverlay(f);
                    });

                    if (features.length > 0) {
                        const extent = this.polygonsLayer.getSource().getExtent();
                        this.map.getView().fit(extent, { padding: [50, 50, 50, 50], maxZoom: 15 });
                    }

                    // Ajustar el z-index inicial de todos los marcadores según
                    // su posición en pantalla ya con la vista final aplicada
                    this.updateMarkerStacking();

                    console.log(`✅ Polígonos cargados: ${features.length}`);
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
            if (!menu) return;
            
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
            if (!sidebar) return;
            
            const sidebarObserver = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        setTimeout(() => {
                            if (window.polygonsMapInstance && window.polygonsMapInstance.map) {
                                window.polygonsMapInstance.map.updateSize();
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
                    setTimeout(() => {
                        if (window.polygonsMapInstance && window.polygonsMapInstance.map) {
                            window.polygonsMapInstance.map.updateSize();
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
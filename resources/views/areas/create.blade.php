
<x-app-layout>
    <div class="">
        <div class="max-w-7xl mx-auto">
            <div class="bg-stone-100/90 dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl shadow-soft p-4 md:p-6 lg:p-8">
                <div class="text-gray-900 dark:text-gray-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="text-2xl font-bold"><i class="fas fa-map-marker-alt mr-2"></i>Crear Área Geográfica</h2>        
                    </div>
                    
                    <form>
                        <div class="relative rounded-lg overflow-hidden mb-6 border border-gray-200 dark:border-gray-700" style="height: 500px;">
                            <div id="map" class="h-full w-full"></div>
                            <div style="-webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none;" class="coordinates-display" id="coordinates-display">
                                Lat: 0.000000 | Lng: 0.000000
                            </div>
                            <div id="message-display" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-20 px-4 py-2 rounded-lg bg-gray-900 text-white font-semibold shadow-lg hidden"></div>
                            <div class="map-overlay">
                                <button type="button" id="draw-polygon" class="bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-100 px-3 py-2 rounded-lg shadow-md hover:bg-gray-200 flex items-center">
                                    <i class="fas fa-draw-polygon mr-2"></i> Dibujar Parcela
                                </button>
                                <button type="button" id="draw-marker" class="bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-100 px-3 py-2 rounded-lg shadow-md hover:bg-gray-200 flex items-center mt-2">
                                    <i class="fas fa-map-marker-alt mr-2"></i> Colocar Marcador
                                </button>
                                <button type="button" id="clear-map" class="bg-red-500 text-white px-3 py-2 rounded-lg shadow-md hover:bg-red-600 flex items-center mt-2">
                                    <i class="fas fa-trash-alt mr-2"></i> Limpiar Mapa
                                </button>
                            </div>
                        </div>

                        <input type="hidden" id="polygon_coords" name="polygon_coords" value="[]">
                        
                        <div class="flex items-center justify-end mt-4 space-x-4">
                            <x-go-back-button />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<style>
    .coordinates-display {
        position: absolute;
        bottom: 10px;
        left: 10%;
        transform: translateX(-50%);
        z-index: 1000;
        background: rgba(255, 255, 255, 0.8);
        padding: 5px 10px;
        border-radius: 5px;
        font-family: Arial, sans-serif;
        font-size: 14px;
        color: #333;
        white-space: nowrap;
    }
    .map-overlay {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
    }
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ol@v8.2.0/ol.css">
<script src="https://cdn.jsdelivr.net/npm/ol@v8.2.0/dist/ol.js"></script>

<script>
    // ==========================================================
    // SECCIÓN 1: INICIALIZACIÓN Y CONFIGURACIÓN DEL MAPA (OPENLAYERS)
    // ==========================================================

    const { Map, View } = ol;
    const { OSM } = ol.source;
    const { Tile } = ol.layer;
    const { fromLonLat, transform } = ol.proj;
    const { Feature } = ol;
    const { Point, Polygon } = ol.geom;
    const { Style, Icon } = ol.style;
    const { Vector: VectorSource } = ol.source;
    const { Vector: VectorLayer } = ol.layer;
    const { Draw } = ol.interaction;

    const defaultCoords = fromLonLat([-63.260654, 10.640359]);
    const map = new Map({
        target: 'map',
        layers: [
            new Tile({ source: new OSM() }),
        ],
        view: new View({
            center: defaultCoords,
            zoom: 10,
        }),
    });

    // Fuente y capa para dibujar los elementos (marcadores y Parcelas)
    const vectorSource = new VectorSource({ features: [] });
    const vectorLayer = new VectorLayer({ source: vectorSource });
    map.addLayer(vectorLayer);

    let currentInteraction = null;
    let drawnCoordinates = [];

    const coordinatesDisplay = document.getElementById('coordinates-display');
    const messageDisplay = document.getElementById('message-display');

    // ==========================================================
    // SECCIÓN 2: LÓGICA DE EVENTOS Y ACTUALIZACIONES
    // ==========================================================

    /**
     * @function updateCoordinatesDisplay
     * @description Actualiza el texto del visor de coordenadas.
     * @param {Array} coords - Coordenadas en formato de OpenLayers.
     */
    function updateCoordinatesDisplay(coords) {
        const lonLat = transform(coords, 'EPSG:3857', 'EPSG:4326');
        if (coordinatesDisplay) {
            coordinatesDisplay.textContent = `Lat: ${lonLat[1].toFixed(6)} | Lng: ${lonLat[0].toFixed(6)}`;
        }
    }

    /**
     * @function updateFormInput
     * @description Serializa las coordenadas y actualiza el campo oculto.
     */
    function updateFormInput() {
        const polygonInput = document.getElementById('polygon_coords');
        polygonInput.value = JSON.stringify(drawnCoordinates);
    }

    /**
     * @function setInteraction
     * @description Configura la interacción de dibujo en el mapa.
     * @param {string} type - 'Polygon' o 'Point'.
     */
    function setInteraction(type) {
        // Remueve la interacción anterior si existe
        if (currentInteraction) {
            map.removeInteraction(currentInteraction);
        }
        // Agrega una nueva interacción
        const draw = new Draw({
            source: vectorSource,
            type: type,
        });
        map.addInteraction(draw);
        currentInteraction = draw;

        // Muestra el mensaje de instrucción
        if (type === 'Polygon') {
            messageDisplay.textContent = "Haz clic para añadir puntos. Doble clic para terminar.";
        } else {
            messageDisplay.textContent = "Haz clic en el mapa para colocar un marcador.";
        }
        messageDisplay.classList.remove('hidden');
        setTimeout(() => {
            messageDisplay.classList.add('hidden');
        }, 5000);

        // Escucha el evento de fin de dibujo
        draw.on('drawend', function(e) {
            drawnCoordinates = []; // Reinicia las coordenadas para un nuevo dibujo
            const geometry = e.feature.getGeometry();
            const coordsOl = geometry.getCoordinates();
            
            // Si es un Parcela, las coordenadas son un array de arrays
            if (type === 'Polygon') {
                coordsOl[0].forEach(c => {
                    const lonLat = transform(c, 'EPSG:3857', 'EPSG:4326');
                    drawnCoordinates.push({ lat: lonLat[1], lng: lonLat[0] });
                });
            } else { // Si es un punto
                const lonLat = transform(coordsOl, 'EPSG:3857', 'EPSG:4326');
                drawnCoordinates.push({ lat: lonLat[1], lng: lonLat[0] });
            }
            updateFormInput();
            updateCoordinatesDisplay(geometry.getCoordinates());
            removeInteraction();
        });
    }
    
    // Remueve la interacción activa
    function removeInteraction() {
        if (currentInteraction) {
            map.removeInteraction(currentInteraction);
            currentInteraction = null;
        }
    }

    // Escucha el movimiento del cursor sobre el mapa
    map.on('pointermove', function(e) {
        updateCoordinatesDisplay(e.coordinate);
    });

    // Maneja el clic en el botón 'Dibujar Parcela'
    document.getElementById('draw-polygon').addEventListener('click', function() {
        vectorSource.clear();
        setInteraction('Polygon');
    });

    // Maneja el clic en el botón 'Colocar Marcador'
    document.getElementById('draw-marker').addEventListener('click', function() {
        vectorSource.clear();
        setInteraction('Point');
    });

    // Maneja el clic en el botón 'Limpiar Mapa'
    document.getElementById('clear-map').addEventListener('click', function() {
        vectorSource.clear();
        drawnCoordinates = [];
        updateFormInput();
        removeInteraction();
    });

    // ==========================================================
    // SECCIÓN 3: SINCRONIZACIÓN DE VALORES INICIALES
    // ==========================================================
    
    const initialCoordsJson = document.getElementById('polygon_coords').value;
    if (initialCoordsJson && initialCoordsJson !== '[]') {
        try {
            const initialCoords = JSON.parse(initialCoordsJson);
            if (Array.isArray(initialCoords) && initialCoords.length > 0) {
                // Determinar si son coordenadas de Parcela o marcador
                const isPolygon = Array.isArray(initialCoords[0]);
                
                if (isPolygon) {
                    const coords = initialCoords[0].map(c => fromLonLat([c.lng, c.lat]));
                    const polygon = new Feature({ geometry: new Polygon([coords]) });
                    vectorSource.addFeature(polygon);
                    map.getView().fit(polygon.getGeometry(), { padding: [50, 50, 50, 50], duration: 1000 });
                    drawnCoordinates = initialCoords[0];
                } else {
                    const firstCoord = initialCoords[0];
                    const newCoords = fromLonLat([firstCoord.lng, firstCoord.lat]);
                    const newMarker = new Feature({ geometry: new Point(newCoords) });
                    vectorSource.addFeature(newMarker);
                    map.getView().setCenter(newCoords);
                    map.getView().setZoom(13);
                    drawnCoordinates = initialCoords;
                }
                updateFormInput();
            }
        } catch (e) {
            console.error("Error al parsear las coordenadas JSON iniciales:", e);
        }
    } else {
        updateCoordinatesDisplay(map.getView().getCenter());
    }
</script>
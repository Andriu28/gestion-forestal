/**
 * SCRIPT CARTOGRÁFICO DE EDICIÓN Y CAPTURA: polygon-map-edit.js
 * 
 * Implementación basada en Leaflet y Leaflet.Draw para el formulario de edición y creación de parcelas:
 * 1. PolygonMapManager: Gestor del visor Leaflet, digitalización vectorial y edición de vértices.
 * 2. LocationDetector: Cliente HTTP para geocodificación inversa con OSM Nominatim y sincronización con el backend.
 * 3. UTMCoordinates: Validador y conversor matemático de coordenadas UTM a geográficas WGS84 mediante Proj4js.
 * 4. UTMModalManager: Controlador de la interfaz modal para la carga manual de vértices por teclado.
 */

// =========================================================================
// 1. Configuración de Teselas y Capas Base (TileLayers)
// =========================================================================
const BaseLayers = {
    osm: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© Colaboradores de OpenStreetMap'
    }),
    satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '© Esri World Imagery'
    }),
    maptiler_satellite: L.tileLayer('https://api.maptiler.com/maps/satellite/{z}/{x}/{y}.jpg?key={key}', {
        attribution: '© MapTiler',
        key: 'your_maptiler_key'
    }),
    terrain: L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenTopoMap'
    }),
    dark: L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '© CARTO Dark Matter'
    })
};

// =========================================================================
// 2. Parámetros de la Herramienta de Trazo Vectorial (Leaflet.Draw)
// =========================================================================
const DrawConfig = {
    polygon: {
        allowIntersection: false, // Regla topológica: prohíbe auto-intersecciones de bordes
        showArea: true,           // Exhibir cálculo dinámico de área en pantalla
        shapeOptions: {
            color: '#2b6cb0',     // Color perimetral azul oscuro
            fillColor: '#2b6cb0',
            fillOpacity: 0.25,
            weight: 3
        }
    },
    // Desactivar figuras no admitidas para parcelas agrarias
    polyline: false,
    rectangle: false,
    circle: false,
    circlemarker: false,
    marker: false
};

/**
 * CLASE: PolygonMapManager
 * Controla la instancia de Leaflet, la digitalización de nuevos Parcelas
 * y la manipulación de vértices existentes.
 */
class PolygonMapManager {
    constructor(mapId, options = {}) {
        // Inicializar mapa centrado en Venezuela [8.0° N, -66.0° O] con zoom general de 6
        this.map = L.map(mapId).setView([8.0, -66.0], 6);
        this.drawnItems = new L.FeatureGroup().addTo(this.map); // Capa que aloja los Parcelas dibujados
        this.currentPolygonLayer = null;
        this.geometryInput = options.geometryInput || null;     // Input oculto donde se guarda el GeoJSON
        this.coordsDisplay = options.coordsDisplay || null;     // Elemento DOM para feedback al usuario
        this.detectBtn = options.detectBtn || null;             // Botón de detección territorial
        this.areaInput = options.areaInput || null;             // Campo de entrada de superficie en hectáreas

        // Agregar mapa base callejero inicial
        BaseLayers.osm.addTo(this.map);

        // Configurar barra de herramientas Leaflet.Draw
        this.setupDrawingControls();

        // Registrar escuchadores de eventos espaciales
        this.setupMapEvents();
    }

    /**
     * Instala los botones de dibujar Parcela y modificar vértices en el mapa.
     */
    setupDrawingControls() {
        this.drawControl = new L.Control.Draw({
            draw: DrawConfig,
            edit: {
                featureGroup: this.drawnItems,
                edit: {
                    selectedPathOptions: {
                        maintainColor: true
                    }
                }
            }
        });

        this.map.addControl(this.drawControl);
    }

    /**
     * Vincula los eventos de creación, edición y movimiento del cursor.
     */
    setupMapEvents() {
        // Disparado al cerrar el trazo de un Parcela nuevo
        this.map.on(L.Draw.Event.CREATED, (e) => this.onPolygonCreated(e));

        // Disparado al mover vértices o transformar el Parcela existente
        this.map.on(L.Draw.Event.EDITED, (e) => this.onPolygonEdited(e));

        // Disparado con el movimiento del ratón para reportar latitud y longitud
        this.map.on('mousemove', (e) => this.onMouseMove(e));
    }

    /**
     * Manejador de evento al finalizar un nuevo Parcela:
     * - Reemplaza capas previas para mantener una sola parcela en edición.
     * - Actualiza campos ocultos de geometría y cálculo de hectáreas.
     */
    onPolygonCreated(e) {
        const layer = e.layer;
        this.drawnItems.clearLayers();
        this.drawnItems.addLayer(layer);

        this.updatePolygonData(layer);
        this.currentPolygonLayer = layer;

        this.showMessage('Parcela dibujado', 'success');
    }

    /**
     * Manejador de evento al modificar o estirar vértices de la parcela:
     * Recalcula inmediatamente el área y el centroide.
     */
    onPolygonEdited(e) {
        const layers = e.layers;
        layers.eachLayer((layer) => {
            this.updatePolygonData(layer);
            this.currentPolygonLayer = layer;
        });

        this.showMessage('Parcela editado', 'success');
    }

    /**
     * Muestra las coordenadas del cursor si no hay un centroide fijado.
     */
    onMouseMove(e) {
        if (!this.geometryInput?.value) {
            this.updateCoordsDisplay(e.latlng.lat, e.latlng.lng);
        }
    }

    /**
     * Sincroniza la capa gráfica de Leaflet con los campos del formulario HTML:
     * 1. Convierte la capa a objeto GeoJSON mediante `layer.toGeoJSON()`.
     * 2. Escribe la geometría serializada en el input `#geometry`.
     * 3. Recalcula el área en hectáreas y la estampa en `#area_ha`.
     * 4. Habilita el botón de autodetección territorial.
     * 5. Calcula el centroide y actualiza el indicador visual de coordenadas.
     */
    updatePolygonData(layer) {
        const feature = layer.toGeoJSON();

        if (this.geometryInput) {
            this.geometryInput.value = JSON.stringify(feature);
        }

        this.updateArea(feature);

        if (this.detectBtn) {
            this.detectBtn.disabled = false;
        }

        const centroid = this.calculateCentroid(feature);
        if (centroid && this.coordsDisplay) {
            this.updateCoordsDisplay(centroid.lat, centroid.lng);
        }

        return feature;
    }

    /**
     * Calcula el centroide baricéntrico (punto medio geométrico) de la Parcela:
     * Promedia la sumatoria de latitudes y longitudes de todos los vértices del anillo exterior.
     * 
     * @param object feature Entidad GeoJSON conteniendo coordinates
     * @return object|null  { lat, lng }
     */
    calculateCentroid(feature) {
        try {
            const coords = feature.geometry.coordinates;
            let ring = null;

            if (feature.geometry.type === 'Polygon') ring = coords[0];
            else if (feature.geometry.type === 'MultiPolygon') ring = coords[0][0];

            if (!ring || ring.length === 0) return null;

            let latSum = 0, lngSum = 0;
            ring.forEach(pt => {
                lngSum += pt[0]; // Coordenada X (Longitud)
                latSum += pt[1]; // Coordenada Y (Latitud)
            });

            return {
                lat: latSum / ring.length,
                lng: lngSum / ring.length
            };
        } catch (e) {
            console.error('Error calculando centroide:', e);
            return null;
        }
    }

    /**
     * Algoritmo de cálculo de superficie mediante la Fórmula de Gauss / Shoelace con corrección de escala:
     * 
     * Procedimiento:
     * 1. Aplica el determinante de Gauss sobre los vértices en grados decimales:
     *    `área_grados = 0.5 * sum(xi * yj - xj * yi)`
     * 2. Calcula la latitud promedio (`avgLat`) del predio.
     * 3. Aplica los factores de conversión de grados a kilómetros considerando la convergencia de meridianos:
     *    - 1 grado de latitud = aprox. 111.32 km.
     *    - 1 grado de longitud = 111.32 * cos(latitud_promedio_en_radianes) km.
     * 4. Multiplica por los factores para obtener kilómetros cuadrados (km²).
     * 5. Convierte km² a hectáreas (1 km² = 100 hectáreas).
     * 
     * @param object feature Objeto GeoJSON
     * @return number Superficie en hectáreas redondeada a 2 decimales
     */
    calculateArea(feature) {
        if (!feature || !feature.geometry) return 0;

        const coords = feature.geometry.coordinates[0];
        if (!coords || coords.length < 3) return 0;

        let area = 0;
        const n = coords.length;

        // Sumatoria Shoelace
        for (let i = 0; i < n; i++) {
            const j = (i + 1) % n;
            const xi = coords[i][1]; // Latitud
            const yi = coords[i][0]; // Longitud
            const xj = coords[j][1];
            const yj = coords[j][0];

            area += xi * yj - xj * yi;
        }

        area = Math.abs(area) / 2;

        // Factores de corrección geodésica según la latitud media
        const avgLat = coords.reduce((sum, coord) => sum + coord[1], 0) / n;
        const kmPerDegreeLat = 111.32;
        const kmPerDegreeLon = 111.32 * Math.cos(avgLat * Math.PI / 180);

        const areaKm2 = area * kmPerDegreeLat * kmPerDegreeLon;
        const areaHa = areaKm2 * 100;

        return Math.round(areaHa * 100) / 100;
    }

    /**
     * Escribe el valor calculado de superficie en la caja de texto `#area_ha`.
     */
    updateArea(feature) {
        if (!this.areaInput) return;

        if (feature) {
            const area = this.calculateArea(feature);
            this.areaInput.value = area;
        } else {
            this.areaInput.value = '';
        }
    }

    /**
     * Actualiza la etiqueta de coordenadas del centroide o cursor en pantalla.
     */
    updateCoordsDisplay(lat, lng) {
        if (this.coordsDisplay) {
            this.coordsDisplay.textContent = `Lat: ${lat.toFixed(6)} | Lng: ${lng.toFixed(6)}`;
        }
    }

    /**
     * Muestra notificaciones temporales en el panel de coordenadas con código de color.
     */
    showMessage(text, type = 'info') {
        if (!this.coordsDisplay) return;

        const el = this.coordsDisplay;
        el.textContent = text;

        el.classList.remove('text-red-600', 'text-green-700', 'text-yellow-600');

        switch (type) {
            case 'error':
                el.classList.add('text-red-600');
                break;
            case 'success':
                el.classList.add('text-green-700');
                break;
            case 'warning':
                el.classList.add('text-yellow-600');
                break;
        }

        setTimeout(() => {
            if (!this.geometryInput?.value) {
                this.updateCoordsDisplay(0, 0);
            }
            el.classList.remove('text-red-600', 'text-green-700', 'text-yellow-600');
        }, 3000);
    }

    /**
     * Restablece el mapa eliminando capas vectoriales y borrando los campos de formulario.
     */
    clearMap() {
        this.drawnItems.clearLayers();

        if (this.geometryInput) {
            this.geometryInput.value = '';
        }

        if (this.detectBtn) {
            this.detectBtn.disabled = true;
        }

        if (this.areaInput) {
            this.areaInput.value = '';
        }

        this.updateCoordsDisplay(0, 0);
        this.currentPolygonLayer = null;

        this.showMessage('Mapa limpiado', 'info');
    }

    /**
     * Carga y dibuja en pantalla una geometría GeoJSON preexistente (utilizado en la vista de edición):
     * - Añade la capa al grupo de entidades editables (`drawnItems`).
     * - Centra la cámara automáticamente sobre los límites de la parcela (`fitBounds`).
     * - Calcula las hectáreas y el centroide inicial.
     */
    loadExistingPolygon(geoJSON) {
        if (!geoJSON) return false;

        try {
            const geoJsonLayer = L.geoJSON(geoJSON, {
                style: {
                    color: '#2b6cb0',
                    fillColor: '#2b6cb0',
                    fillOpacity: 0.25,
                    weight: 3
                }
            }).addTo(this.drawnItems);

            // Ajustar encuadre cartográfico al tamaño de la Parcela
            this.map.fitBounds(geoJsonLayer.getBounds());
            this.currentPolygonLayer = geoJsonLayer;

            this.updatePolygonData(geoJsonLayer);

            return true;
        } catch (error) {
            console.error('Error al cargar Parcela existente:', error);
            this.showMessage('Error al cargar el Parcela existente', 'error');
            return false;
        }
    }

    /**
     * Cambia la capa base activa del mapa Leaflet (satélite, OSM, etc.).
     */
    changeBaseLayer(layerKey) {
        Object.values(BaseLayers).forEach(layer => {
            if (this.map.hasLayer(layer)) {
                this.map.removeLayer(layer);
            }
        });

        if (BaseLayers[layerKey]) {
            BaseLayers[layerKey].addTo(this.map);
            return true;
        }

        return false;
    }
}

/**
 * CLASE: LocationDetector
 * Administra la geocodificación inversa mediante peticiones HTTP a OpenStreetMap Nominatim
 * y la posterior homologación con las tablas de parroquias en el servidor Laravel.
 */
class LocationDetector {
    constructor(options = {}) {
        this.csrfToken = options.csrfToken || '';
        this.findParishUrl = options.findParishUrl || '';
    }

    /**
     * Consulta al API pública de OpenStreetMap Nominatim por coordenadas geográficas:
     * Devuelve el desglose administrativo (país, estado, municipio, parroquia, localidad).
     */
    async detectLocation(lat, lng) {
        try {
            const response = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1&accept-language=es`,
                {
                    headers: {
                        'User-Agent': 'PolygonApp/1.0',
                        'Accept': 'application/json'
                    }
                }
            );

            if (!response.ok) {
                throw new Error('Error en Nominatim');
            }

            return await response.json();
        } catch (error) {
            console.error('Error detectando ubicación:', error);
            throw error;
        }
    }

    /**
     * Elimina prefijos comunes de la cadena geográfica devuelta por OSM.
     */
    cleanLocationString(str) {
        if (!str) return '';
        return str.toString()
            .replace('Parroquia ', '')
            .replace('Municipio ', '')
            .replace('Estado ', '')
            .trim();
    }

    /**
     * Envía una petición POST al endpoint de Laravel (`polygons.find-parish`)
     * para buscar o crear la parroquia en la base de datos según la jerarquía territorial.
     */
    async findAndAssignParish(parishName, municipalityName, stateName) {
        try {
            const response = await fetch(this.findParishUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({
                    parish_name: parishName ? parishName.toLowerCase().trim() : '',
                    municipality_name: municipalityName ? municipalityName.toLowerCase().trim() : '',
                    state_name: stateName ? stateName.toLowerCase().trim() : ''
                })
            });

            return await response.json();
        } catch (error) {
            console.warn('Asignación parroquia fallida:', error);
            return { success: false, error: error.message };
        }
    }
}

/**
 * CLASE: UTMCoordinates
 * Utilidades matemáticas para la validación y transformación de coordenadas métricas UTM.
 */
class UTMCoordinates {
    /**
     * Valida que los parámetros UTM cumplan con las restricciones del sistema Universal Transversa de Mercator:
     * - Zona entre 1 y 60.
     * - Hemisferio N o S.
     * - Coordenada Este (Easting) entre 0m y 1,000,000m.
     * - Coordenada Norte (Northing) entre 0m y 10,000,000m.
     */
    static validate(zone, hemisphere, easting, northing) {
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

    /**
     * Registra dinámicamente la definición PROJ4 para la zona y hemisferio indicados.
     */
    static setupProjection(zone, hemisphere) {
        const epsgCode = hemisphere === 'N' ? `EPSG:326${zone}` : `EPSG:327${zone}`;

        if (!proj4.defs(epsgCode)) {
            const proj4String = `+proj=utm +zone=${zone} +${hemisphere === 'S' ? '+south ' : ''}datum=WGS84 +units=m +no_defs`;
            proj4.defs(epsgCode, proj4String);
        }

        return epsgCode;
    }

    /**
     * Convierte una lista de vértices UTM `[Easting, Northing, Zone, Hemisphere]`
     * a pares de coordenadas geográficas WGS84 `[Longitud, Latitud]` mediante Proj4.
     */
    static convertToWGS84(utmCoords) {
        return utmCoords.map(coord => {
            const [easting, northing, zone, hemisphere] = coord;
            const epsgCode = this.setupProjection(zone, hemisphere);

            const wgs84 = proj4('WGS84');
            const utm = proj4(epsgCode);
            const point = proj4(utm, wgs84, [easting, northing]);

            return [point[0], point[1]]; // [longitud, latitud]
        });
    }
}

/**
 * CLASE: UTMModalManager
 * Controla el ciclo de apertura, cierre y captura de vértices en la ventana modal
 * de coordenadas manuales.
 */
class UTMModalManager {
    constructor(options = {}) {
        this.modalId = options.modalId || 'manual-polygon-modal';
        this.coordinatesList = [];
        this.onDrawPolygon = options.onDrawPolygon || null;

        this.init();
    }

    init() {
        this.modal = document.getElementById(this.modalId);
        this.openModalBtn = document.getElementById('manual-polygon-toggle');
        this.closeModalBtn = document.getElementById('close-modal');
        this.cancelModalBtn = document.getElementById('cancel-modal');

        if (this.openModalBtn && this.modal) {
            this.setupEventListeners();
        }
    }

    setupEventListeners() {
        this.openModalBtn.addEventListener('click', () => this.open());

        if (this.closeModalBtn) {
            this.closeModalBtn.addEventListener('click', () => this.close());
        }

        if (this.cancelModalBtn) {
            this.cancelModalBtn.addEventListener('click', () => this.close());
        }

        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) this.close();
        });
    }

    open() {
        this.modal.classList.remove('hidden');
        this.modal.classList.add('flex');
        setTimeout(() => this.modal.classList.add('opacity-100'), 10);
    }

    close() {
        this.modal.classList.remove('opacity-100');
        setTimeout(() => {
            this.modal.classList.remove('flex');
            this.modal.classList.add('hidden');
            this.coordinatesList = [];
        }, 300);
    }

    drawPolygon(utmCoordinates) {
        if (this.onDrawPolygon && typeof this.onDrawPolygon === 'function') {
            this.onDrawPolygon(utmCoordinates);
        }
    }
}

// =========================================================================
// Exportación al Ámbito Global del Navegador
// =========================================================================
window.PolygonMapManager = PolygonMapManager;
window.LocationDetector = LocationDetector;
window.UTMCoordinates = UTMCoordinates;
window.UTMModalManager = UTMModalManager;
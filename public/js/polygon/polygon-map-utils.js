// polygon-map-utils.js
/**
 * Utilidades para manejo de mapas y polígonos
 */

// Configuración de capas base
const BaseLayers = {
    osm: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }),
    satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '© Esri'
    }),
    maptiler_satellite: L.tileLayer('https://api.maptiler.com/maps/satellite/{z}/{x}/{y}.jpg?key={key}', {
        attribution: '© MapTiler',
        key: 'your_maptiler_key'
    }),
    terrain: L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenTopoMap'
    }),
    dark: L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '© CARTO'
    })
};

// Configuración de dibujo
const DrawConfig = {
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
};

/**
 * Clase para manejar mapas de polígonos
 */
class PolygonMapManager {
    constructor(mapId, options = {}) {
        this.map = L.map(mapId).setView([8.0, -66.0], 6);
        this.drawnItems = new L.FeatureGroup().addTo(this.map);
        this.currentPolygonLayer = null;
        this.geometryInput = options.geometryInput || null;
        this.coordsDisplay = options.coordsDisplay || null;
        this.detectBtn = options.detectBtn || null;
        this.areaInput = options.areaInput || null;
        
        // Inicializar capa base
        BaseLayers.osm.addTo(this.map);
        
        // Configurar controles de dibujo
        this.setupDrawingControls();
        
        // Configurar eventos del mapa
        this.setupMapEvents();
    }
    
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
    
    setupMapEvents() {
        // Evento de creación de polígono
        this.map.on(L.Draw.Event.CREATED, (e) => this.onPolygonCreated(e));
        
        // Evento de edición de polígono
        this.map.on(L.Draw.Event.EDITED, (e) => this.onPolygonEdited(e));
        
        // Evento de movimiento del mouse
        this.map.on('mousemove', (e) => this.onMouseMove(e));
    }
    
    onPolygonCreated(e) {
        const layer = e.layer;
        this.drawnItems.clearLayers();
        this.drawnItems.addLayer(layer);
        
        this.updatePolygonData(layer);
        this.currentPolygonLayer = layer;
        
        this.showMessage('Polígono dibujado', 'success');
    }
    
    onPolygonEdited(e) {
        const layers = e.layers;
        layers.eachLayer((layer) => {
            this.updatePolygonData(layer);
            this.currentPolygonLayer = layer;
        });
        
        this.showMessage('Polígono editado', 'success');
    }
    
    onMouseMove(e) {
        if (!this.geometryInput?.value) {
            this.updateCoordsDisplay(e.latlng.lat, e.latlng.lng);
        }
    }
    
    updatePolygonData(layer) {
        const feature = layer.toGeoJSON();
        
        // Actualizar campo de geometría
        if (this.geometryInput) {
            this.geometryInput.value = JSON.stringify(feature);
        }
        
        // Actualizar área
        this.updateArea(feature);
        
        // Habilitar detección
        if (this.detectBtn) {
            this.detectBtn.disabled = false;
        }
        
        // Actualizar coordenadas del centroide
        const centroid = this.calculateCentroid(feature);
        if (centroid && this.coordsDisplay) {
            this.updateCoordsDisplay(centroid.lat, centroid.lng);
        }
        
        return feature;
    }
    
    calculateCentroid(feature) {
        try {
            const coords = feature.geometry.coordinates;
            let ring = null;
            
            if (feature.geometry.type === 'Polygon') ring = coords[0];
            else if (feature.geometry.type === 'MultiPolygon') ring = coords[0][0];
            
            if (!ring || ring.length === 0) return null;
            
            let latSum = 0, lngSum = 0;
            ring.forEach(pt => {
                lngSum += pt[0];
                latSum += pt[1];
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
    
    calculateArea(feature) {
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
    
    updateArea(feature) {
        if (!this.areaInput) return;
        
        if (feature) {
            const area = this.calculateArea(feature);
            this.areaInput.value = area;
        } else {
            this.areaInput.value = '';
        }
    }
    
    updateCoordsDisplay(lat, lng) {
        if (this.coordsDisplay) {
            this.coordsDisplay.textContent = `Lat: ${lat.toFixed(6)} | Lng: ${lng.toFixed(6)}`;
        }
    }
    
    showMessage(text, type = 'info') {
        if (!this.coordsDisplay) return;
        
        const el = this.coordsDisplay;
        el.textContent = text;
        
        // Reset classes
        el.classList.remove('text-red-600', 'text-green-700', 'text-yellow-600');
        
        // Add appropriate class
        switch(type) {
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
        
        // Auto-clear message
        setTimeout(() => {
            if (!this.geometryInput?.value) {
                this.updateCoordsDisplay(0, 0);
            }
            el.classList.remove('text-red-600', 'text-green-700', 'text-yellow-600');
        }, 3000);
    }
    
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
            
            this.map.fitBounds(geoJsonLayer.getBounds());
            this.currentPolygonLayer = geoJsonLayer;
            
            const feature = geoJsonLayer.toGeoJSON();
            this.updatePolygonData(geoJsonLayer);
            
            return true;
        } catch (error) {
            console.error('Error al cargar polígono existente:', error);
            this.showMessage('Error al cargar el polígono existente', 'error');
            return false;
        }
    }
    
    changeBaseLayer(layerKey) {
        // Remover todas las capas base
        Object.values(BaseLayers).forEach(layer => {
            if (this.map.hasLayer(layer)) {
                this.map.removeLayer(layer);
            }
        });
        
        // Añadir nueva capa
        if (BaseLayers[layerKey]) {
            BaseLayers[layerKey].addTo(this.map);
            return true;
        }
        
        return false;
    }
}

/**
 * Utilidades para detección de ubicación
 */
class LocationDetector {
    constructor(options = {}) {
        this.csrfToken = options.csrfToken || '';
        this.findParishUrl = options.findParishUrl || '';
    }
    
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
    
    cleanLocationString(str) {
        if (!str) return '';
        return str.toString()
            .replace('Parroquia ', '')
            .replace('Municipio ', '')
            .replace('Estado ', '')
            .trim();
    }
    
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
 * Utilidades para coordenadas UTM
 */
class UTMCoordinates {
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
    
    static setupProjection(zone, hemisphere) {
        const epsgCode = hemisphere === 'N' ? `EPSG:326${zone}` : `EPSG:327${zone}`;
        
        if (!proj4.defs(epsgCode)) {
            const proj4String = `+proj=utm +zone=${zone} +${hemisphere === 'S' ? '+south ' : ''}datum=WGS84 +units=m +no_defs`;
            proj4.defs(epsgCode, proj4String);
        }
        
        return epsgCode;
    }
    
    static convertToWGS84(utmCoords) {
        return utmCoords.map(coord => {
            const [easting, northing, zone, hemisphere] = coord;
            const epsgCode = this.setupProjection(zone, hemisphere);
            
            const wgs84 = proj4('WGS84');
            const utm = proj4(epsgCode);
            const point = proj4(utm, wgs84, [easting, northing]);
            
            return [point[0], point[1]]; // [lng, lat]
        });
    }
}

/**
 * Clase para manejar el modal de coordenadas manuales
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

// Exportar clases y utilidades
window.PolygonMapManager = PolygonMapManager;
window.LocationDetector = LocationDetector;
window.UTMCoordinates = UTMCoordinates;
window.UTMModalManager = UTMModalManager;
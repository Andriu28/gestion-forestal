/**
 * CLASE PRINCIPAL - POLYGON MAP
 * Mapa interactivo para creación de polígonos con funcionalidades similares
 * al sistema de deforestación pero simplificado para polígonos.
 * Utiliza: OpenLayers 6, Turf.js (para cálculos), Proj4 (para transformaciones)
 */
class PolygonMap {
    constructor() {
        // =============================================
        // 1. INICIALIZACIÓN DE PROPIEDADES
        // =============================================
        this.map = null;              // Instancia principal del mapa OpenLayers
        this.draw = null;             // Interacción de dibujo actual
        this.source = null;           // Fuente de datos vectoriales
        this.polygonStyle = null;     // Estilo base para polígonos
        this.coordinateDisplay = null;// Elemento DOM para mostrar coordenadas
        this.baseLayers = {};         // Capas base disponibles
        this.currentBaseLayer = null; // Capa base activa
        this.drawingFeature = null;   // Feature en proceso de dibujo

        // Constantes de configuración
        this.INITIAL_CENTER = [-63.172905251869125, 10.555594747510682]; // Venezuela (lon, lat)
        this.INITIAL_ZOOM = 15;
        this.MINZOOM = 5;
        this.MAXZOOM = 18;

        // =============================================
        // 2. CONFIGURACIÓN INICIAL
        // =============================================
        this.defineCustomProjections(); // Definir proyecciones EPSG personalizadas
        this.initializeMap();           // Configurar mapa y capas
        this.setupEventListeners();     // Configurar listeners de eventos
        this.setupCoordinateDisplay();  // Configurar display de coordenadas

        // Verificación de dependencias críticas
        this.verifyDependencies();
    }

    /**
     * VERIFICA DEPENDENCIAS EXTERNAS
     */
    verifyDependencies() {
        console.log('=== VERIFICACIÓN DE LIBRERÍAS PARA POLÍGONOS ===');
        console.log('OpenLayers cargado:', typeof ol !== 'undefined');
        console.log('Turf.js cargado:', typeof turf !== 'undefined');
        
        if (typeof turf === 'undefined') {
            console.error('ERROR: Turf.js no está cargado');
            console.info('Agrega: <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>');
        }
    }

    /**
     * DEFINE PROYECCIONES PERSONALIZADAS
     */
    defineCustomProjections() {
        if (typeof proj4 !== 'undefined') {
            // EPSG:2203 - UTM Zone 20S (Venezuela)
            proj4.defs('EPSG:2203', 
                '+proj=utm +zone=20 +south +ellps=intl +towgs84=-288,175,-376,0,0,0,0 +units=m +no_defs'
            );
            
            // EPSG:32620 - UTM Zone 20N (WGS84)
            proj4.defs('EPSG:32620',
                '+proj=utm +zone=20 +ellps=WGS84 +datum=WGS84 +units=m +no_defs'
            );
            
            // Registrar definiciones en OpenLayers
            if (typeof ol !== 'undefined') {
                ol.proj.proj4.register(proj4);
            }
        }
    }

    /**
     * INICIALIZA TODOS LOS COMPONENTES DEL MAPA
     */
    initializeMap() {
        this.setupBaseLayers();    // Capas base (OSM, Satélite, etc.)
        this.setupVectorLayer();   // Capa vectorial para dibujos
        this.setupMapInstance();   // Crear instancia del mapa
    }

    /**
     * CONFIGURA CAPAS BASE (igual que deforestación)
     */
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

    /**
     * CONFIGURA CAPA VECTORIAL Y ESTILOS
     */
    setupVectorLayer() {
        this.source = new ol.source.Vector();
        this.setupStyles();

        this.vectorLayer = new ol.layer.Vector({
            source: this.source,
            style: (feature) => this.getFeatureStyle(feature)
        });
    }

    /**
     * CREA INSTANCIA PRINCIPAL DEL MAPA
     */
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
            })
        });

        this.currentBaseLayer = this.baseLayers.osm;
    }

    /**
     * CONFIGURA TODOS LOS ESTILOS VISUALES
     */
    setupStyles() {
        this.polygonStyle = this.getPolygonStyle('default');
    }

    /**
     * OBTIENE ESTILO DE POLÍGONO POR ESTADO
     */
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

    /**
     * OBTIENE ESTILO PARA UNA FEATURE ESPECÍFICA
     */
    getFeatureStyle(feature) {
        const geometry = feature.getGeometry();
        const styles = [];
        const areaHa = feature.get('area') || 0;
        
        // Usar estilo personalizado si existe
        const customStyle = feature.getStyle();
        if (customStyle) {
            styles.push(customStyle);
        } else {
            // Para polígonos finalizados, mostrar área
            if (geometry.getType() === 'Polygon' && areaHa > 0) {
                styles.push(this.getPolygonStyle('finished', areaHa));
            } else {
                styles.push(this.polygonStyle);
            }
        }

        return styles;
    }

    /**
     * CONFIGURA LISTENERS DE EVENTOS
     */
    setupEventListeners() {
        document.addEventListener('keydown', (e) => this.handleKeyDown(e));
    }

    /**
     * MANEJA EVENTOS DE TECLADO
     */
    handleKeyDown(event) {
        // Cancelar dibujo con Escape
        if (event.key === 'Escape' && this.draw && this.drawingFeature) {
            this.cancelDrawing();
            event.preventDefault();
        }
    }

    /**
     * CANCELA DIBUJO ACTUAL
     */
    cancelDrawing() {
        if (this.draw) {
            this.map.removeInteraction(this.draw);
            this.draw = null;
        }
        this.drawingFeature = null;
        this.updateAreaDisplay(0);
    }

    /**
     * CONFIGURA DISPLAY DE COORDENADAS UTM
     */
    setupCoordinateDisplay() {
        this.createCoordinateDisplayElement();
        
        this.map.on('pointermove', (evt) => {
            if (evt.dragging) return;
            this.updateCoordinateDisplay(evt.coordinate);
        });
    }

    /**
     * CREA ELEMENTO DOM PARA MOSTRAR COORDENADAS
     */
    createCoordinateDisplayElement() {
        const existingDisplays = document.querySelectorAll('#coordinate-display');
        existingDisplays.forEach(display => display.remove());
        
        this.coordinateDisplay = document.getElementById('coordinate-display');
        
        if (this.coordinateDisplay) {
            this.coordinateDisplay.classList.remove('hidden');
        }
    }

    /**
     * ACTUALIZA DISPLAY CON COORDENADAS UTM
     */
    updateCoordinateDisplay(coordinate) {
        if (!this.coordinateDisplay) return;
        
        const lonLat = ol.proj.toLonLat(coordinate);
        const lon = lonLat[0];
        const lat = lonLat[1];
        
        const zone = Math.floor((lon + 180) / 6) + 1;
        const hemisphere = lat >= 0 ? 'N' : 'S';
        
        try {
            const epsgCode = this.setupUTMProjection(zone, hemisphere);
            const [easting, northing] = proj4('EPSG:4326', epsgCode, [lon, lat]);
            
            if (this.isValidUTM(easting, northing, zone, hemisphere)) {
                this.coordinateDisplay.textContent = 
                    `Zona ${zone}${hemisphere} | ` +
                    `Este: ${easting.toFixed(3)} | ` +
                    `Norte: ${northing.toFixed(3)}`;
                this.coordinateDisplay.classList.remove('hidden');
            } else {
                this.coordinateDisplay.classList.add('hidden');
            }
        } catch (error) {
            console.warn('Error en conversión UTM:', error);
            this.coordinateDisplay.classList.add('hidden');
        }
    }

    /**
     * CONFIGURA PROYECCIÓN UTM DINÁMICAMENTE
     */
    setupUTMProjection(zone, hemisphere) {
        const epsgCode = hemisphere === 'N' ? `EPSG:326${zone}` : `EPSG:327${zone}`;
        
        if (!proj4.defs(epsgCode)) {
            const proj4String = `+proj=utm +zone=${zone} +${hemisphere === 'S' ? '+south ' : ''}datum=WGS84 +units=m +no_defs`;
            proj4.defs(epsgCode, proj4String);
        }
        
        return epsgCode;
    }

    /**
     * VALIDA COORDENADAS UTM
     */
    isValidUTM(easting, northing, zone, hemisphere) {
        if (easting < 0 || easting > 1000000) return false;
        
        if (hemisphere === 'N') {
            return northing >= 0 && northing <= 10000000;
        } else {
            return northing >= 1000000 && northing <= 10000000;
        }
    }

    /**
     * ACTIVA HERRAMIENTA DE DIBUJO DE POLÍGONOS
     */
    activateDrawing() {
        this.removeExistingDrawInteraction();

        this.draw = new ol.interaction.Draw({
            source: this.source,
            type: 'Polygon',
            style: this.getPolygonStyle('drawing')
        });

        this.setupDrawEvents();
        this.map.addInteraction(this.draw);
    }

    /**
     * CONFIGURA EVENTOS DE DIBUJO
     */
    setupDrawEvents() {
        this.draw.on('drawstart', (evt) => {
            this.drawingFeature = evt.feature;
            this.source.clear();
            this.updateAreaDisplay(0);
            document.getElementById('detect-location').disabled = true;
        });

        this.draw.on('drawadd', () => this.refreshArea());
        this.draw.on('drawabort', () => this.resetDrawingState());
        this.draw.on('drawend', (event) => this.finalizeDrawing(event.feature));
    }

    /**
     * FINALIZA PROCESO DE DIBUJO
     */
    finalizeDrawing(feature) {
        const areaHa = this.refreshArea(feature);
        
        feature.set('area', areaHa);
        feature.setStyle(this.getPolygonStyle('finished', areaHa));
        
        this.convertToGeoJSON(feature, areaHa);
        this.showAlert(`Polígono completado. Área: ${areaHa.toFixed(6)} ha`, 'success');
        
        document.getElementById('detect-location').disabled = false;
        
        this.map.removeInteraction(this.draw);
        this.draw = null;
        this.resetDrawingState();
    }

    /**
     * ELIMINA INTERACCIÓN DE DIBUJO EXISTENTE
     */
    removeExistingDrawInteraction() {
        if (this.draw) {
            this.map.removeInteraction(this.draw);
        }
    }

    /**
     * LIMPIA ESTADO INTERNO DE DIBUJO
     */
    resetDrawingState() {
        this.drawingFeature = null;
        this.updateAreaDisplay(0);
    }

    /**
     * CALCULA ÁREA DE UN POLÍGONO USANDO TURF.JS
     */
    calculateArea(feature) {
        if (!feature || !feature.getGeometry) {
            return 0;
        }
        
        const geometry = feature.getGeometry();
        if (!geometry) {
            return 0;
        }
        
        const geomType = geometry.getType();
        
        if (!['Polygon', 'MultiPolygon'].includes(geomType)) {
            return 0;
        }
        
        if (typeof turf === 'undefined') {
            this.showAlert('Error: Turf.js no está cargado. Los cálculos de área no están disponibles.', 'error');
            return 0;
        }
        
        try {
            const wgs84Geometry = geometry.clone().transform('EPSG:3857', 'EPSG:4326');
            const coordinates = wgs84Geometry.getCoordinates();
            
            let turfFeature;
            switch (geomType) {
                case 'Polygon':
                    turfFeature = turf.polygon(coordinates);
                    break;
                case 'MultiPolygon':
                    turfFeature = turf.multiPolygon(coordinates);
                    break;
                default:
                    return 0;
            }
            
            const areaM2 = turf.area(turfFeature);
            
            if (isNaN(areaM2) || areaM2 <= 0) {
                return 0;
            }
            
            const areaHa = areaM2 / 10000;
            
            return parseFloat(areaHa.toFixed(6));
            
        } catch (error) {
            console.error('Error en cálculo de área:', error);
            this.showAlert('Error calculando área', 'error');
            return 0;
        }
    }

    /**
     * REFRESCA VISUALIZACIÓN DEL ÁREA
     */
    refreshArea(feature = this.drawingFeature) {
        if (feature) {
            const areaHa = this.calculateArea(feature);
            this.updateAreaDisplay(areaHa);
            return areaHa;
        }
        return 0;
    }

    /**
     * ACTUALIZA DISPLAY DE ÁREA EN INTERFAZ
     */
    updateAreaDisplay(areaHa) {
        const areaInput = document.getElementById('area_ha');
        if (areaInput) {
            areaInput.value = areaHa > 0 ? areaHa.toFixed(6) : '';
        }
    }

    /**
     * CONVIERTE FEATURE A GEOJSON
     */
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

    /**
     * CAMBIA LA CAPA BASE ACTIVA
     */
    changeBaseLayer(layerKey) {
        if (!this.baseLayers[layerKey]) {
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
    }

    /**
     * LIMPIA MAPA COMPLETAMENTE
     */
    clearMap() {
        this.source.clear();
        document.getElementById('geometry').value = '';
        document.getElementById('area_ha').value = '';
        this.updateAreaDisplay(0);
        document.getElementById('detect-location').disabled = true;
        document.getElementById('location-info').classList.add('hidden');
        
        this.removeExistingDrawInteraction();
        this.drawingFeature = null;
    }

    /**
     * DIBUJA POLÍGONO DESDE COORDENADAS UTM
     */
    drawFromUTMCoordinates(utmCoordinates) {
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

    /**
     * CIERRA POLÍGONO SI NO ESTÁ CERRADO
     */
    closePolygonIfNeeded(coordinates) {
        const firstCoord = coordinates[0];
        const lastCoord = coordinates[coordinates.length - 1];
        
        if (firstCoord[0] !== lastCoord[0] || firstCoord[1] !== lastCoord[1]) {
            coordinates.push(firstCoord);
        }
    }

    /**
     * CREA POLÍGONO DESDE COORDENADAS WGS84
     */
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
        document.getElementById('detect-location').disabled = false;
        
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

    /**
     * MUESTRA ALERTA AL USUARIO
     */
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
// INICIALIZACIÓN GLOBAL
// =============================================

/**
 * INICIALIZA MAPA CUANDO EL DOCUMENTO ESTÉ LISTO
 */
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('map')) {
        window.polygonMapInstance = new PolygonMap();
    }
});
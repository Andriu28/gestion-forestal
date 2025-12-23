
class DeforestationMap {
    constructor() {
        // Instancias y configuraciones principales
        this.map = null;
        this.draw = null;
        this.source = null;
        this.polygonStyle = null;
        this.pointStyle = null;
        this.labelStyle = null;
        this.coordinateDisplay = null;
        this.baseLayers = {};
        this.currentBaseLayer = null;
        this.gfwLossLayer = null;
        this.drawingFeature = null;

        // Definir proyecciones adicionales
        this.defineCustomProjections();
        
        // Constantes
        this.STORAGE_KEY = 'gfwLossLayerState';
        this.INITIAL_CENTER = [-63.26716, 10.63673]; // Venezuela
        this.INITIAL_ZOOM = 12;
        this.GFW_LOSS_URL = 'https://tiles.globalforestwatch.org/umd_tree_cover_loss/latest/dynamic/{z}/{x}/{y}.png';

        // Inicialización
        this.initializeMap();
        this.setupEventListeners();
        this.setupCoordinateDisplay();
        this.initializeGfWLayerToggle();

        console.log('=== VERIFICACIÓN DE LIBRERÍAS ===');
        console.log('OpenLayers cargado:', typeof ol !== 'undefined');
        console.log('Turf.js cargado:', typeof turf !== 'undefined');
        
        if (typeof turf === 'undefined') {
            console.error('ERROR: Turf.js no está cargado');
            console.info('Por favor, agrega: <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>');
        } else {
            console.log('Turf.js disponible para cálculos precisos');
        }

        if (typeof proj4 !== 'undefined') {
            proj4.defs('EPSG:2203', '+proj=utm +zone=20 +south +ellps=intl +towgs84=-288,175,-376,0,0,0,0 +units=m +no_defs');
        }
    }

    // =============================================
    // 1. CONFIGURACIÓN INICIAL DEL MAPA
    // =============================================

    /**
     * Inicializa el mapa, las capas base y los estilos
     */
    initializeMap() {
        this.setupBaseLayers();
        this.setupGFWLayer();
        this.setupVectorLayer();
        this.setupMapInstance();
    }

    /**
     * Configura las capas base del mapa
     */
    setupBaseLayers() {
        this.baseLayers = {
            osm: new ol.layer.Tile({
                source: new ol.source.OSM(), 
                visible: true,
                title: 'OpenStreetMap' 
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
                    attributions: '<a href="https://www.maptiler.com/copyright/" target="_blank">&copy; MapTiler</a> <a href="https://www.openstreetmap.org/copyright" target="_blank">&copy; OpenStreetMap contributors</a>',
                    tileSize: 512,
                    maxZoom: 20
                }),
                visible: false,
                title: 'MapTiler Satélite'
            })
        };
    }

    /**
     * Configura la capa de pérdida forestal GFW
     */
    setupGFWLayer() {
        this.gfwLossLayer = new ol.layer.Tile({
            source: new ol.source.XYZ({
                url: this.GFW_LOSS_URL,
                attributions: 'Hansen/UMD/Google/USGS/NASA | GFW',
            }),
            opacity: 0.9,
            visible: false,
            title: 'Pérdida Arbórea GFW'
        });
    }

    /**
     * Configura la capa vectorial y estilos
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
     * Configura la instancia principal del mapa
     */
    setupMapInstance() {
        const baseLayerGroup = new ol.layer.Group({
            layers: Object.values(this.baseLayers)
        });

        const initialCenter = ol.proj.fromLonLat(this.INITIAL_CENTER);

        this.map = new ol.Map({
            target: 'map',
            layers: [baseLayerGroup, this.gfwLossLayer, this.vectorLayer],
            view: new ol.View({
                center: initialCenter,
                zoom: this.INITIAL_ZOOM
            })
        });

        this.currentBaseLayer = this.baseLayers.osm;
    }

    // =============================================
    // 2. CONFIGURACIÓN DE ESTILOS
    // =============================================

    /**
     * Configura todos los estilos utilizados en el mapa
     */
    setupStyles() {
        this.polygonStyle = this.getPolygonStyle('default');
        
        this.pointStyle = new ol.style.Style({
            image: new ol.style.Circle({
                radius: 7,
                fill: new ol.style.Fill({ 
                    color: '#ffffff'
                }),
                stroke: new ol.style.Stroke({ 
                    color: '#10b981',
                    width: 3 
                })
            })
        });

        this.labelStyle = new ol.style.Style({
            text: new ol.style.Text({
                text: '',
                font: 'bold 14px "Arial", sans-serif',
                fill: new ol.style.Fill({ 
                    color: '#1f2937'
                }),
                stroke: new ol.style.Stroke({ 
                    color: '#ffffff',
                    width: 2 
                }),
                offsetY: -20,
                overflow: true,
                backgroundFill: new ol.style.Fill({
                    color: 'rgba(255, 255, 255, 0.85)'
                }),
                padding: [4, 8, 4, 8],
                textBaseline: 'middle',
                textAlign: 'center'
            })
        });
    }

    /**
     * Obtiene los estilos para polígonos según el estado
     * @param {string} state - Estado del polígono: 'drawing', 'finished', 'default'
     * @param {number} areaHa - Área en hectáreas para mostrar en el texto
     * @returns {ol.style.Style} Estilo correspondiente
     */
    getPolygonStyle(state = 'default', areaHa = 0) {
        const styles = {
            drawing: new ol.style.Style({
                stroke: new ol.style.Stroke({
                    color: '#3b82f6',
                    width: 3,
                    lineDash: [5, 10],
                    lineCap: 'round'
                }),
                fill: new ol.style.Fill({
                    color: 'rgba(59, 130, 246, 0.2)'
                }),
                image: new ol.style.Circle({
                    radius: 6,
                    fill: new ol.style.Fill({ color: '#ffffff' }),
                    stroke: new ol.style.Stroke({ color: '#3b82f6', width: 2 })
                })
            }),
            
            finished: new ol.style.Style({
                stroke: new ol.style.Stroke({
                    color: '#10b981',
                    width: 3,
                    lineDash: null,
                    lineCap: 'round'
                }),
                fill: new ol.style.Fill({
                    color: 'rgba(16, 185, 129, 0.3)'
                }),
                image: new ol.style.Circle({
                    radius: 5,
                    fill: new ol.style.Fill({ color: '#10b981' }),
                    stroke: new ol.style.Stroke({ color: '#ffffff', width: 2 })
                }),
                // TEXTO DINÁMICO CON EL ÁREA
                text: new ol.style.Text({
                    text: areaHa > 0 ? `${areaHa.toFixed(6)} ha` : '',
                    font: 'bold 14px Arial, sans-serif',
                    fill: new ol.style.Fill({
                        color: '#1f2937' // Color de texto oscuro
                    }),
                    stroke: new ol.style.Stroke({
                        color: '#ffffff', // Borde blanco para mejor contraste
                        width: 3
                    }),
                    backgroundFill: new ol.style.Fill({
                        color: 'rgba(255, 255, 255, 0.7)' // Fondo semitransparente
                    }),
                    padding: [4, 8, 4, 8],
                    textBaseline: 'middle',
                    textAlign: 'center',
                    offsetY: 0
                })
            }),
            
            default: new ol.style.Style({
                stroke: new ol.style.Stroke({
                    color: '#10b981',
                    width: 3,
                    lineDash: [8, 4],
                    lineCap: 'round'
                }),
                fill: new ol.style.Fill({
                    color: 'rgba(16, 185, 129, 0.25)'
                })
            })
        };

        return styles[state] || styles.default;
    }

    /**
     * Obtiene el estilo para una feature específica
     * @param {ol.Feature} feature 
     * @returns {Array<ol.style.Style>}
     */
    getFeatureStyle(feature) {
        const geometry = feature.getGeometry();
        const styles = [];

        // Obtener el área de la feature si existe
        const areaHa = feature.get('area') || 0;
        
        // Usar estilo personalizado si existe, sino usar el por defecto
        const customStyle = feature.getStyle();
        if (customStyle) {
            styles.push(customStyle);
        } else {
            // Para polígonos finalizados, usar estilo con área
            if (geometry.getType() === 'Polygon' && areaHa > 0) {
                styles.push(this.getPolygonStyle('finished', areaHa));
            } else {
                styles.push(this.polygonStyle);
            }
        }

        // Agregar etiqueta si existe (para nombres de productores)
        this.addLabelToFeature(feature, geometry);

        return styles;
    }

    /**
     * Agrega etiqueta a una feature si tiene label
     * @param {ol.Feature} feature 
     * @param {ol.geom.Geometry} geometry 
     */
    addLabelToFeature(feature, geometry) {
        const label = feature.get('label');
        if (label && geometry.getType() !== 'Point') {
            const labelStyle = this.labelStyle.clone();
            labelStyle.getText().setText(label);

            const center = ol.extent.getCenter(geometry.getExtent());
            const pointFeature = new ol.Feature({
                geometry: new ol.geom.Point(center)
            });
            pointFeature.setStyle(labelStyle);

            this.source.addFeature(pointFeature);
        }
    }

    // =============================================
    // 3. MANEJO DE EVENTOS E INTERACCIONES
    // =============================================

    /**
     * Configura los event listeners
     */
    setupEventListeners() {
        document.getElementById('draw-polygon').addEventListener('click', () => this.activateDrawing());
        document.getElementById('clear-map').addEventListener('click', () => this.clearMap());
        document.getElementById('analysis-form').addEventListener('submit', (e) => this.handleFormSubmit(e));
        document.addEventListener('keydown', (e) => this.handleKeyDown(e));
    }

    /**
     * Configura el display de coordenadas
     */
    setupCoordinateDisplay() {
        this.createCoordinateDisplayElement();
        
        this.map.on('pointermove', (evt) => {
            if (evt.dragging) return;
            this.updateCoordinateDisplay(evt.coordinate);
        });
    }

    /**
     * Crea el elemento para mostrar coordenadas
     */
    createCoordinateDisplayElement() {
        const existingDisplays = document.querySelectorAll('.coordinate-display');
        existingDisplays.forEach(display => display.remove());
        
        this.coordinateDisplay = document.createElement('div');
        this.coordinateDisplay.className = 'coordinate-display';
        
        const mapContainer = this.map.getTargetElement();
        mapContainer.style.position = 'relative';
        mapContainer.appendChild(this.coordinateDisplay);
    }

    /**
     * Actualiza el display de coordenadas
     * @param {Array} coordinate - Coordenadas del mapa
     */
    updateCoordinateDisplay(coordinate) {
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

    // =============================================
    // 4. FUNCIONALIDADES DE DIBUJO Y GEOMETRÍA
    // =============================================

    /**
     * Activa la herramienta de dibujo de polígonos
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
     * Configura los eventos de dibujo
     */
    setupDrawEvents() {
        this.draw.on('drawstart', (evt) => {
            this.drawingFeature = evt.feature;
            this.source.clear();
            this.updateAreaDisplay(0);
        });

        // Centralizamos la actualización del área durante el dibujo
        this.draw.on('drawadd', () => this.refreshArea());

        this.draw.on('drawabort', () => this.resetDrawingState());

        this.draw.on('drawend', (event) => {
            this.finalizeDrawing(event.feature);
        });
    }

    /**
     * Calcula y actualiza el área basada en la feature actual
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
 * Finaliza el proceso de dibujo y limpia la interacción
 */
finalizeDrawing(feature) {
    // Calcular área una sola vez
    const areaHa = this.refreshArea(feature);
    
    // Configuración de la feature
    feature.set('area', areaHa);
    feature.setStyle(this.getPolygonStyle('finished', areaHa));
    
    // Pasar área ya calculada para evitar recalcular
    this.convertToGeoJSON(feature, areaHa);
    this.showAlert(`Polígono completado. Área: ${areaHa.toFixed(6)} ha`, 'success');
    
    this.map.removeInteraction(this.draw);
    this.draw = null;
    this.resetDrawingState();
}

    /**
     * Limpia el estado interno del dibujo
     */
    resetDrawingState() {
        this.drawingFeature = null;
        this.updateAreaDisplay(0);
    }

    /**
     * Elimina la interacción de dibujo existente
     */
    removeExistingDrawInteraction() {
        if (this.draw) {
            this.map.removeInteraction(this.draw);
        }
    }

    // =============================================
    // 5. CÁLCULOS Y CONVERSIONES GEOGRÁFICAS
    // =============================================

    /**
 * Calcula área con Turf.js (versión optimizada)
 * @param {ol.Feature} feature
 * @returns {number}
 */
calculateArea(feature) {
    console.log('=== CÁLCULO DE ÁREA CON TURF.JS ===');
    
    // Validar feature y geometría
    if (!feature || !feature.getGeometry) {
        console.warn('Feature no válida para cálculo de área');
        return 0;
    }
    
    const geometry = feature.getGeometry();
    if (!geometry) {
        console.warn('Feature sin geometría');
        return 0;
    }
    
    const geomType = geometry.getType();
    
    // Solo calcular área para polígonos
    if (!['Polygon', 'MultiPolygon'].includes(geomType)) {
        console.log(`Geometría ${geomType} no requiere cálculo de área`);
        return 0;
    }
    
    // Verificar disponibilidad de Turf.js
    if (typeof turf === 'undefined') {
        console.error('ERROR: Turf.js no está disponible');
        this.showTurfJSError();
        return 0;
    }
    
    if (typeof turf.area !== 'function') {
        console.error('ERROR: La función turf.area no está disponible');
        this.showTurfJSError();
        return 0;
    }
    
    try {
        // 1. Transformar a WGS84 (EPSG:4326) - requerido por Turf.js
        const wgs84Geometry = geometry.clone().transform('EPSG:3857', 'EPSG:4326');
        
        // 2. Obtener coordenadas según el tipo de geometría
        const coordinates = wgs84Geometry.getCoordinates();
        
        // 3. Crear feature de Turf.js
        let turfFeature;
        switch (geomType) {
            case 'Polygon':
                // Validar que el polígono esté cerrado
                this.validatePolygonCoordinates(coordinates);
                turfFeature = turf.polygon(coordinates);
                break;
                
            case 'MultiPolygon':
                // Validar cada polígono del multipolígono
                coordinates.forEach(polygonCoords => {
                    this.validatePolygonCoordinates(polygonCoords);
                });
                turfFeature = turf.multiPolygon(coordinates);
                break;
                
            default:
                return 0;
        }
        
        // 4. Calcular área con Turf.js (en metros cuadrados)
        const areaM2 = turf.area(turfFeature);
        
        // 5. Validar resultado
        if (isNaN(areaM2) || areaM2 <= 0) {
            console.warn('Área calculada no válida:', areaM2);
            return 0;
        }
        
        // 6. Convertir a hectáreas (1 ha = 10,000 m²)
        const areaHa = areaM2 / 10000;
        
        // 7. Log detallado
        console.log(`Cálculo completado:
          - Tipo: ${geomType}
          - Área: ${areaM2.toFixed(2)} m²
          - Área: ${areaHa.toFixed(6)} ha
          - Coordenadas: ${this.getCoordinateCount(coordinates)} puntos
        `);
        
        // 8. Devolver redondeado a 6 decimales
        return parseFloat(areaHa.toFixed(6));
        
    } catch (error) {
        console.error('Error en cálculo de área:', error);
        this.showAreaCalculationError(error);
        return 0;
    }
}

/**
 * Muestra error cuando Turf.js no está disponible
 */
showTurfJSError() {
    const errorMessage = 'Turf.js no está disponible. ' +
        'Agrega en tu HTML: <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>';
    
    console.error(errorMessage);
    
    if (this.showAlert) {
        this.showAlert(
            'Error: Turf.js no está cargado. Los cálculos de área no están disponibles.',
            'error'
        );
    } else {
        alert('Error: Turf.js no está cargado. Los cálculos de área no están disponibles.');
    }
}

/**
 * Valida las coordenadas de un polígono
 * @param {Array} coordinates - Coordenadas del polígono
 */
validatePolygonCoordinates(coordinates) {
    if (!coordinates || !Array.isArray(coordinates)) {
        throw new Error('Coordenadas del polígono no válidas');
    }
    
    if (coordinates.length === 0) {
        throw new Error('Polígono sin coordenadas');
    }
    
    // Verificar que el polígono esté cerrado (primer y último punto iguales)
    const firstRing = coordinates[0];
    if (firstRing && firstRing.length > 0) {
        const firstPoint = firstRing[0];
        const lastPoint = firstRing[firstRing.length - 1];
        
        if (firstPoint[0] !== lastPoint[0] || firstPoint[1] !== lastPoint[1]) {
            console.warn('Polígono no cerrado. Asegurándose de cerrar el polígono.');
            // No lanzamos error, solo registramos advertencia
        }
    }
}

/**
 * Obtiene el conteo de coordenadas
 * @param {Array} coordinates - Coordenadas
 * @returns {number} - Número total de puntos
 */
getCoordinateCount(coordinates) {
    if (!Array.isArray(coordinates)) return 0;
    
    let count = 0;
    const countRecursive = (arr) => {
        if (Array.isArray(arr[0]) && typeof arr[0][0] === 'number') {
            count += arr.length;
        } else {
            arr.forEach(item => countRecursive(item));
        }
    };
    
    countRecursive(coordinates);
    return count;
}

/**
 * Muestra error de cálculo de área
 * @param {Error} error - Error ocurrido
 */
showAreaCalculationError(error) {
    console.error('Error detallado en cálculo de área:', error);
    
    const userMessage = error.message.includes('Turf.js') 
        ? 'Error en la biblioteca de cálculos geográficos. Verifica la consola para más detalles.'
        : 'Error al calcular el área. Verifica que el polígono sea válido.';
    
    if (this.showAlert) {
        this.showAlert(userMessage, 'error');
    }
}

    /**
 * Convierte la geometría a GeoJSON y la guarda en el input oculto
 * @param {ol.Feature} feature
 * @param {number} existingArea - Área ya calculada (opcional)
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
        
        // Usar área existente si se proporciona, de lo contrario calcular
        const areaHa = existingArea !== null ? existingArea : feature.get('area') || this.calculateArea(feature);
        document.getElementById('area_ha').value = areaHa.toFixed(6);
        
        // Solo mostrar alerta si no hay un área ya establecida
        if (existingArea === null) {
            this.showAlert(`Polígono guardado. Área: ${areaHa.toFixed(6)} ha`);
        }
        
    } catch (error) {
        console.error('Error al convertir GeoJSON:', error);
        this.showAlert('Error al guardar el polígono: ' + error.message, 'error');
    }
}

    // =============================================
    // 6. IMPORTACIÓN Y EXPORTACIÓN DE DATOS
    // =============================================

    /**
     * Importa uno o varios polígonos desde GeoJSON con soporte para EPSG:2203
     * @param {Object|string} geojson - Objeto GeoJSON o string JSON
     */
    importGeoJSON(geojson) {
        console.log('=== INICIANDO IMPORTACIÓN GEOJSON CON EPSG:2203 ===');
        
        try {
            // 1. Validar y parsear el input
            let geojsonObj;
            if (typeof geojson === 'string') {
                try {
                    geojsonObj = JSON.parse(geojson);
                } catch (parseError) {
                    throw new Error('El texto proporcionado no es un JSON válido');
                }
            } else if (typeof geojson === 'object' && geojson !== null) {
                geojsonObj = geojson;
            } else {
                throw new Error('El parámetro debe ser un objeto GeoJSON o un string JSON');
            }

            // 2. Validar estructura GeoJSON básica
            if (!geojsonObj.type) {
                throw new Error('El objeto no tiene propiedad "type" (no es GeoJSON válido)');
            }

            // 3. Determinar si el GeoJSON tiene CRS personalizado
            const hasCustomCRS = geojsonObj.crs && 
                                geojsonObj.crs.properties && 
                                geojsonObj.crs.properties.name && 
                                geojsonObj.crs.properties.name.includes('EPSG::2203');
            
            console.log(`GeoJSON tiene CRS personalizado (EPSG:2203): ${hasCustomCRS}`);
            console.log(`Tipo de geometrías: ${geojsonObj.features?.[0]?.geometry?.type || 'desconocido'}`);

            // 4. Limpiar mapa antes de importar
            this.clearMap();
            
            // 5. Crear formato y leer features con la proyección correcta
            const format = new ol.format.GeoJSON();
            let features;
            
            try {
                if (hasCustomCRS) {
                    // Caso 1: GeoJSON con EPSG:2203 (coordenadas en metros)
                    console.log('Procesando GeoJSON con EPSG:2203...');
                    features = this.readFeaturesFromEPSG2203(geojsonObj, format);
                } else {
                    // Caso 2: GeoJSON estándar (EPSG:4326)
                    console.log('Procesando GeoJSON estándar (EPSG:4326)...');
                    features = format.readFeatures(geojsonObj, {
                        dataProjection: 'EPSG:4326',
                        featureProjection: 'EPSG:3857'
                    });
                }
            } catch (projectionError) {
                console.warn('Error en conversión de proyección:', projectionError);
                // Fallback: intentar leer sin transformación
                try {
                    features = format.readFeatures(geojsonObj);
                } catch (fallbackError) {
                    throw new Error(`No se pudo leer el GeoJSON: ${fallbackError.message}`);
                }
            }

            // 6. Validar que se hayan leído features
            if (!features || features.length === 0) {
                this.showAlert('El archivo no contiene geometrías válidas.', 'error');
                return;
            }

            console.log(`Features importadas: ${features.length}`);
            
            // 7. Procesar features
            const processedData = this.processImportedFeatures(features, 'geojson', geojsonObj);
            
            // 8. Finalizar importación
            this.finalizeImport(features, geojsonObj, processedData.totalArea, 'GeoJSON');
            
        } catch (error) {
            console.error('Error completo en importGeoJSON:', error);
            this.showAlert(`Error al importar GeoJSON: ${error.message}`, 'error');
        }
    }

    /**
     * Lee features desde GeoJSON con EPSG:2203
     * @param {Object} geojsonObj - Objeto GeoJSON
     * @param {ol.format.GeoJSON} format - Formato GeoJSON de OpenLayers
     * @returns {Array} Features procesadas
     */
    readFeaturesFromEPSG2203(geojsonObj, format) {
        const features = [];
        
        // Verificar si proj4 está disponible para la transformación
        if (typeof proj4 === 'undefined') {
            throw new Error('Se requiere la biblioteca proj4 para transformar EPSG:2203 a WGS84');
        }
        
        // Iterar sobre cada feature en el FeatureCollection
        geojsonObj.features.forEach((featureData, index) => {
            try {
                const geometry = featureData.geometry;
                const properties = featureData.properties || {};
                
                if (!geometry || !geometry.coordinates) {
                    console.warn(`Feature ${index}: Sin geometría válida`);
                    return;
                }
                
                // Convertir coordenadas de EPSG:2203 a WGS84 (EPSG:4326)
                const wgs84Coordinates = this.convertEPSG2203ToWGS84(geometry);
                
                // Crear la geometría en WGS84
                let olGeometry;
                if (geometry.type === 'MultiPolygon') {
                    olGeometry = new ol.geom.MultiPolygon(wgs84Coordinates);
                } else if (geometry.type === 'Polygon') {
                    olGeometry = new ol.geom.Polygon(wgs84Coordinates);
                } else {
                    console.warn(`Feature ${index}: Tipo de geometría no soportado: ${geometry.type}`);
                    return;
                }
                
                // Transformar a la proyección del mapa (EPSG:3857)
                olGeometry.transform('EPSG:4326', 'EPSG:3857');
                
                // Crear la feature
                const feature = new ol.Feature({
                    geometry: olGeometry
                });
                
                // Agregar propiedades
                feature.setProperties({
                    id: properties.id || index,
                    area: properties.Area_Ha || 0,
                    productor: properties.Productor || 'Desconocido',
                    label: properties.Productor || `Polígono ${index + 1}`
                });
                
                features.push(feature);
                
            } catch (error) {
                console.error(`Error procesando feature ${index}:`, error);
            }
        });
        
        return features;
    }

    /**
     * Convierte coordenadas de EPSG:2203 a WGS84
     * @param {Object} geometry - Geometría en EPSG:2203
     * @returns {Array} Coordenadas convertidas a WGS84
     */
    convertEPSG2203ToWGS84(geometry) {
        if (geometry.type === 'MultiPolygon') {
            return geometry.coordinates.map(polygon =>
                polygon.map(ring =>
                    ring.map(coord => {
                        // Transformar cada coordenada usando proj4
                        const [x, y] = coord;
                        // Transformar de EPSG:2203 a WGS84 (EPSG:4326)
                        const transformed = proj4('EPSG:2203', 'EPSG:4326', [x, y]);
                        return transformed;
                    })
                )
            );
        } else if (geometry.type === 'Polygon') {
            return geometry.coordinates.map(ring =>
                ring.map(coord => {
                    const [x, y] = coord;
                    const transformed = proj4('EPSG:2203', 'EPSG:4326', [x, y]);
                    return transformed;
                })
            );
        }
        throw new Error(`Tipo de geometría no soportado: ${geometry.type}`);
    }

    /**
     * Importa uno o varios polígonos desde KML
     * @param {string} kmlText - Texto KML
     */
    importKML(kmlText) {
        console.log('=== INICIANDO IMPORTACIÓN KML ===');
        
        try {
            // 1. Validar input
            if (typeof kmlText !== 'string' || kmlText.trim() === '') {
                throw new Error('El texto KML está vacío o no es válido');
            }

            // 2. Limpiar mapa
            this.clearMap();
            
            // 3. Crear formato y leer features
            const format = new ol.format.KML({
                extractStyles: false, // No extraer estilos del KML
                showPointNames: false // No mostrar nombres de puntos
            });
            
            let features;
            try {
                features = format.readFeatures(kmlText, {
                    dataProjection: 'EPSG:4326',      // KML usa WGS84
                    featureProjection: 'EPSG:3857'    // Mapa usa Web Mercator
                });
            } catch (readError) {
                console.warn('Error leyendo KML, intentando sin proyección:', readError);
                features = format.readFeatures(kmlText);
            }

            // 4. Validar features
            if (!features || features.length === 0) {
                this.showAlert('El archivo KML no contiene geometrías válidas.', 'error');
                return;
            }

            console.log(`Features KML importadas: ${features.length}`);
            
            // 5. Procesar features
            const processedData = this.processImportedFeatures(features, 'kml');
            
            // 6. Crear FeatureCollection para el formulario
            const featureCollection = this.createFeatureCollection(features);
            
            // 7. Finalizar importación
            this.finalizeImport(features, featureCollection, processedData.totalArea, 'KML');
            
        } catch (error) {
            console.error('Error completo en importKML:', error);
            this.showAlert(`Error al importar KML: ${error.message}`, 'error');
        }
    }

    /**
 * Procesa features importadas con validación mejorada
 * @param {Array} features - Features de OpenLayers
 * @param {string} type - Tipo de archivo ('geojson' o 'kml')
 * @param {Object} originalData - Datos originales (opcional)
 * @returns {Object} {totalArea, validFeatures, invalidFeatures}
 */
processImportedFeatures(features, type = 'geojson', originalData = null) {
    console.log(`=== PROCESANDO ${features.length} FEATURES (${type}) ===`);
    
    let totalArea = 0;
    let validFeatures = 0;
    let invalidFeatures = 0;
    
    features.forEach((feature, index) => {
        try {
            // 1. Obtener geometría y validarla
            const geometry = feature.getGeometry();
            if (!geometry) {
                console.warn(`Feature ${index}: Sin geometría`);
                invalidFeatures++;
                return;
            }
            
            const geomType = geometry.getType();
            console.log(`Feature ${index}: Tipo ${geomType}`);
            
            // 2. Extraer metadatos según tipo y estructura
            const props = feature.getProperties();
            
            // Para GeoJSON con estructura específica de Sistema Deforest
            if (type === 'geojson') {
                // Usar las propiedades ya establecidas en readFeaturesFromEPSG2203
                const productor = props.productor || props.Productor;
                const areaHa = props.area || props.Area_Ha || 0;
                
                if (productor && productor !== 'Desconocido' && productor !== 'null') {
                    feature.set('label', String(productor));
                    feature.set('productor', String(productor));
                } else {
                    feature.set('label', `Polígono ${index + 1}`);
                    feature.set('productor', 'Sin productor');
                }
                
                // Si ya tenemos área en las propiedades, usarla
                if (areaHa && areaHa > 0) {
                    feature.set('area', parseFloat(areaHa));
                    totalArea += parseFloat(areaHa);
                    validFeatures++;
                    
                    // Aplicar estilo con área
                    feature.setStyle(this.getPolygonStyle('finished', areaHa));
                } else {
                    // Solo calcular si no tenemos área
                    const calculatedArea = this.calculateArea(feature);
                    if (!isNaN(calculatedArea) && calculatedArea > 0) {
                        feature.set('area', calculatedArea);
                        totalArea += calculatedArea;
                        validFeatures++;
                        feature.setStyle(this.getPolygonStyle('finished', calculatedArea));
                    } else {
                        console.warn(`Feature ${index}: Área inválida (${calculatedArea})`);
                        invalidFeatures++;
                        feature.setStyle(this.getPolygonStyle('default'));
                    }
                }
                
            } else {
                // Para otros tipos (KML) usar extracción normal
                const productor = props.productor || props.name || props.Productor || props.Nombre;
                if (productor) {
                    feature.set('label', String(productor));
                    feature.set('productor', String(productor));
                }
                
                // Calcular área para KML (no suele venir con área precalculada)
                const calculatedArea = this.calculateArea(feature);
                if (!isNaN(calculatedArea) && calculatedArea > 0) {
                    feature.set('area', calculatedArea);
                    totalArea += calculatedArea;
                    validFeatures++;
                    feature.setStyle(this.getPolygonStyle('finished', calculatedArea));
                } else {
                    console.warn(`Feature ${index}: Área inválida (${calculatedArea})`);
                    invalidFeatures++;
                    feature.setStyle(this.getPolygonStyle('default'));
                }
            }
            
            // Agregar etiqueta con productor y área
            this.addLabelToFeature(feature, geometry);
            
            // 5. Agregar al mapa
            this.source.addFeature(feature);
            
        } catch (featureError) {
            console.error(`Error procesando feature ${index}:`, featureError);
            invalidFeatures++;
        }
    });
    
    console.log(`Procesamiento completado: ${validFeatures} válidas, ${invalidFeatures} inválidas`);
    console.log(`Área total calculada: ${totalArea.toFixed(6)} ha`);
    
    return {
        totalArea: parseFloat(totalArea.toFixed(6)),
        validFeatures,
        invalidFeatures
    };
}

    /**
     * Define proyecciones personalizadas necesarias
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
            
            // Registrar las definiciones en OpenLayers
            if (typeof ol !== 'undefined') {
                ol.proj.proj4.register(proj4);
            }
        }
    }

    /**
     * Finaliza el proceso de importación
     * @param {Array} features 
     * @param {Object} data 
     * @param {number} totalArea 
     * @param {string} type 
     */
    finalizeImport(features, data, totalArea, type) {
        const extent = this.source.getExtent();
        this.map.getView().fit(extent, { padding: [50, 50, 50, 50], duration: 1000 });

        document.getElementById('geometry').value = JSON.stringify(data);
        this.updateAreaDisplay(totalArea);
        this.processMultiPolygonInfo(features);

        this.showAlert(
            `${type === 'KML' ? features.length + ' áreas' : 'Áreas'} importadas correctamente. Área total: ${totalArea.toFixed(6)} ha`, 
            'success'
        );
    }

    // =============================================
    // 7. MANEJO DE CAPAS Y VISUALIZACIÓN
    // =============================================

    /**
     * Permite cambiar la capa base del mapa
     * @param {string} layerKey - Clave de la capa base
     */
    changeBaseLayer(layerKey) {
        Object.values(this.baseLayers).forEach(layer => layer.setVisible(false));
        this.baseLayers[layerKey].setVisible(true);
        this.currentBaseLayer = this.baseLayers[layerKey];
    }

    /**
     * Configura el toggle de la capa GFW
     */
    initializeGfWLayerToggle() {
        const toggleButton = document.getElementById('visibility-toggle-button');
        
        if (!toggleButton) return;

        let storedState = localStorage.getItem(this.STORAGE_KEY);
        let isLayerVisible = storedState === 'false' ? false : true;
        
        this.applyGfwLayerState(isLayerVisible);
        toggleButton.classList.remove('invisible');

        toggleButton.addEventListener('click', () => {
            isLayerVisible = !isLayerVisible;
            this.applyGfwLayerState(isLayerVisible);
            localStorage.setItem(this.STORAGE_KEY, isLayerVisible.toString());
        });
    }

    /**
     * Aplica el estado de visibilidad a la capa GFW
     * @param {boolean} isVisible 
     */
    applyGfwLayerState(isVisible) {
        const iconVisible = document.getElementById('icon-eye-open');
        const iconHidden = document.getElementById('icon-eye-closed');
        
        if (iconVisible && iconHidden) {
            iconVisible.style.display = isVisible ? 'inline-block' : 'none';
            iconHidden.style.display = isVisible ? 'none' : 'inline-block';
        }

        if (this.gfwLossLayer) {
            this.gfwLossLayer.setVisible(isVisible);
        }
    }

    setGFWOpacity(opacity) {
        if (this.gfwLossLayer) {
            this.gfwLossLayer.setOpacity(opacity);
        }
    }

    getGFWOpacity() {
        return this.gfwLossLayer ? this.gfwLossLayer.getOpacity() : 0.9;
    }

    toggleGFWVisibility() {
        if (this.gfwLossLayer) {
            const currentVisibility = this.gfwLossLayer.getVisible();
            this.gfwLossLayer.setVisible(!currentVisibility);
            return !currentVisibility;
        }
        return false;
    }

    // =============================================
    // 8. UTILIDADES Y HELPERS
    // =============================================

    /**
     * Valida coordenadas UTM
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
     * Configura Proj4 para UTM
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
     * Actualiza el display del área
     */
    updateAreaDisplay(areaHa) {
        const areaDisplay = document.getElementById('area-display');
        const areaValue = document.getElementById('area-value');
        
        if (areaDisplay && areaValue) {
            if (areaHa > 0) {
                areaValue.textContent = areaHa.toFixed(6);
                areaDisplay.classList.remove('hidden');
            } else {
                areaDisplay.classList.add('hidden');
            }
        }
    }

    /**
     * Limpia el mapa
     */
    clearMap() {
        this.source.clear();
        document.getElementById('geometry').value = '';
        document.getElementById('area_ha').value = '';
        this.updateAreaDisplay(0);
        
        this.removeExistingDrawInteraction();
        this.drawingFeature = null;
    }

    // =============================================
    // 9. MANEJO DE FORMULARIOS Y DATOS
    // =============================================

    /**
     * Maneja el envío del formulario
     */
    handleFormSubmit(event) {
        event.preventDefault();

        const geometryInput = document.getElementById('geometry');
        
        if (!geometryInput.value) {
            this.showAlert('Por favor, dibuja o importa un área de interés.', 'error');
            return;
        }

        this.disableFormDuringSubmission();
        event.target.submit();
    }

    /**
     * Deshabilita el formulario durante el envío
     */
    disableFormDuringSubmission() {
        const form = document.getElementById('analysis-form');
        const submitButton = form.querySelector('button[type="submit"]');
        const spinner = document.getElementById('loading-spinner');
        
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Analizando...';
        }

        if (spinner) {
            spinner.classList.remove('d-none');
        }
    }

    /**
     * Extrae datos de KML mejorado
     */
    extractKMLData(feature) {
        try {
            const kmlDescription = feature.get('description');
            if (!kmlDescription) return null;
            
            // Intentar varios métodos de parseo
            let doc;
            const parser = new DOMParser();
            
            // Método 1: Como XML
            try {
                doc = parser.parseFromString(kmlDescription, 'text/xml');
                if (doc.querySelector('parsererror')) throw new Error('XML parsing error');
            } catch (xmlError) {
                // Método 2: Como HTML
                try {
                    doc = parser.parseFromString(kmlDescription, 'text/html');
                } catch (htmlError) {
                    console.warn('No se pudo parsear KML description:', htmlError);
                    return null;
                }
            }
            
            // Buscar datos en múltiples ubicaciones
            const dataSources = [
                // ExtendedData > Data
                ...Array.from(doc.querySelectorAll('ExtendedData Data')).map(el => ({
                    name: el.getAttribute('name'),
                    value: el.querySelector('value')?.textContent
                })),
                // SimpleData
                ...Array.from(doc.querySelectorAll('SimpleData')).map(el => ({
                    name: el.getAttribute('name'),
                    value: el.textContent
                })),
                // SchemaData > SimpleData
                ...Array.from(doc.querySelectorAll('SchemaData SimpleData')).map(el => ({
                    name: el.getAttribute('name'),
                    value: el.textContent
                }))
            ];
            
            // Buscar nombres comunes
            const nameFields = ['productor', 'name', 'nombre', 'title', 'producer', 'owner'];
            
            for (const field of nameFields) {
                const match = dataSources.find(ds => 
                    ds.name && ds.value && 
                    ds.name.toLowerCase() === field.toLowerCase()
                );
                if (match) return match.value;
            }
            
            return null;
            
        } catch (error) {
            console.warn('Error extrayendo datos KML:', error);
            return null;
        }
    }

    /**
 * Procesa información de múltiples polígonos
 */
processMultiPolygonInfo(features) {
    const polygonsInfo = features
        .filter(feature => {
            const geometry = feature.getGeometry();
            return geometry && geometry.getType() === 'Polygon';
        })
        .map(feature => ({
            productor: feature.get('productor') || feature.get('name') || 'Propietario desconocido',
            localidad: feature.get('localidad') || feature.get('municipio') || 'No especificado',
            area: feature.get('area') || 0  // Usar área almacenada, no recalcular
        }));

    this.displayPolygonsInfo(polygonsInfo);
}

    /**
     * Muestra información de polígonos en tabla
     */
    displayPolygonsInfo(polygonsInfo) {
        const container = document.getElementById('producers-info');
        const list = document.getElementById('producers-list');
        
        if (polygonsInfo.length > 0) {
            list.innerHTML = polygonsInfo.map(info => `
                <tr>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">${info.productor}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">${info.localidad}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">${info.area.toFixed(6)} Ha</td>
                </tr>
            `).join('');
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }

    /**
     * Crea FeatureCollection optimizado
     */
    createFeatureCollection(features) {
        const format = new ol.format.GeoJSON();
        
        const geojsonFeatures = features.map(feature => {
            try {
                const geojsonStr = format.writeFeature(feature, {
                    dataProjection: 'EPSG:4326',
                    featureProjection: 'EPSG:3857',
                    decimals: 6 // Precisión de 6 decimales
                });
                return JSON.parse(geojsonStr);
            } catch (error) {
                console.warn('Error convirtiendo feature:', error);
                return null;
            }
        }).filter(f => f !== null);
        
        return {
            type: 'FeatureCollection',
            features: geojsonFeatures
        };
    }

    /**
     * Dibuja polígono desde coordenadas UTM
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
     * Cierra el polígono si no está cerrado
     */
    closePolygonIfNeeded(coordinates) {
        const firstCoord = coordinates[0];
        const lastCoord = coordinates[coordinates.length - 1];
        
        if (firstCoord[0] !== lastCoord[0] || firstCoord[1] !== lastCoord[1]) {
            coordinates.push(firstCoord);
        }
    }

    /**
 * Crea polígono desde coordenadas
 */
createPolygonFromCoordinates(wgs84Coordinates, utmCoordinates) {
    const feature = new ol.Feature({
        geometry: new ol.geom.Polygon([wgs84Coordinates]).transform('EPSG:4326', 'EPSG:3857')
    });
    
    this.clearMap();
    
    // Calcular área una sola vez
    const areaHa = this.calculateArea(feature);
    feature.set('area', areaHa);
    
    // Aplicar estilo con área
    feature.setStyle(this.getPolygonStyle('finished', areaHa));
    
    this.source.addFeature(feature);
    this.updateAreaDisplay(areaHa);
    
    this.map.getView().fit(
        feature.getGeometry().getExtent(),
        { padding: [50, 50, 50, 50], duration: 1000 }
    );
    
    // Pasar área ya calculada
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
     * Muestra alertas al usuario
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
                timerProgressBar: true,
                customClass: {
                    popup: 'rounded-xl shadow-lg bg-stone-100/95 dark:bg-custom-gray border border-gray-200 dark:border-gray-700',
                    title: 'text-sm font-semibold text-gray-900 dark:text-white mb-1'
                }
            });
        } else {
            alert(message);
        }
    }

    /**
     * Maneja eventos de teclado para funcionalidades globales
     * @param {KeyboardEvent} event - Evento de teclado
     */
    handleKeyDown(event) {
        // Cancelar dibujo con Escape
        if (event.key === 'Escape' && this.draw && this.drawingFeature) {
            this.cancelDrawing();
            event.preventDefault(); // Prevenir comportamiento por defecto
        }
    }

    /**
     * Cancela el proceso de dibujo actual de forma limpia
     */
    cancelDrawing() {
        if (this.draw) {
            this.map.removeInteraction(this.draw);
            this.draw = null;
        }
        
        this.drawingFeature = null;
        this.updateAreaDisplay(0);
    }



}

// Inicializar el mapa cuando el documento esté listo
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('map')) {
        window.deforestationMapInstance = new DeforestationMap();
    }
});

// Manejar carga de archivo GeoJSON
document.addEventListener('DOMContentLoaded', function() {
    const importButton = document.getElementById('import-geojson');
    const fileInput = document.getElementById('import-area');
    
    if (importButton && fileInput) {
        importButton.addEventListener('click', () => {
            fileInput.click();
        });
        
        fileInput.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = (e) => {
                try {
                    const geojson = JSON.parse(e.target.result);
                    if (window.deforestationMapInstance) {
                        window.deforestationMapInstance.importGeoJSON(geojson);
                    }
                } catch (error) {
                    alert('Error al leer el archivo GeoJSON: ' + error.message);
                }
            };
            reader.readAsText(file);
            
            // Limpiar input
            event.target.value = '';
        });
    }
});
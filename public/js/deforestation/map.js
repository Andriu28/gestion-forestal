/**
 * CLASE PRINCIPAL - DEFORESTATION MAP
 * Mapa interactivo para análisis de deforestación con funcionalidades de dibujo,
 * importación de datos, cálculo de áreas y visualización de capas.
 * Utiliza: OpenLayers 6, Turf.js (para cálculos), Proj4 (para transformaciones)
 */
class DeforestationMap {
    constructor() {
        // =============================================
        // 1. INICIALIZACIÓN DE PROPIEDADES
        // =============================================
        this.map = null;              // Instancia principal del mapa OpenLayers
        this.draw = null;             // Interacción de dibujo actual
        this.source = null;           // Fuente de datos vectoriales
        this.polygonStyle = null;     // Estilo base para polígonos
        this.pointStyle = null;       // Estilo para puntos
        this.labelStyle = null;       // Estilo para etiquetas
        this.coordinateDisplay = null;// Elemento DOM para mostrar coordenadas
        this.baseLayers = {};         // Capas base disponibles
        this.currentBaseLayer = null; // Capa base activa
        this.gfwLossLayer = null;     // Capa de pérdida forestal GFW
        this.drawingFeature = null;   // Feature en proceso de dibujo

        // Constantes de configuración
        this.STORAGE_KEY = 'gfwLossLayerState'; // Key para localStorage
        this.INITIAL_CENTER = [-63.26716, 10.63673]; // Venezuela (lon, lat)
        this.INITIAL_ZOOM = 12;
        this.GFW_LOSS_URL = 'https://tiles.globalforestwatch.org/umd_tree_cover_loss/latest/dynamic/{z}/{x}/{y}.png';

        // =============================================
        // 2. CONFIGURACIÓN INICIAL
        // =============================================
        this.defineCustomProjections(); // Definir proyecciones EPSG personalizadas
        this.initializeMap();           // Configurar mapa y capas
        this.setupEventListeners();     // Configurar listeners de eventos
        this.setupCoordinateDisplay();  // Configurar display de coordenadas
        this.initializeGfWLayerToggle(); // Configurar toggle de capa GFW

        // Verificación de dependencias críticas
        this.verifyDependencies();
    }

    /**
     * VERIFICA DEPENDENCIAS EXTERNAS
     * Comprueba que OpenLayers, Turf.js y Proj4 estén cargados
     * USADO EN: Constructor (inicialización)
     */
    verifyDependencies() {
        console.log('=== VERIFICACIÓN DE LIBRERÍAS ===');
        console.log('OpenLayers cargado:', typeof ol !== 'undefined');
        console.log('Turf.js cargado:', typeof turf !== 'undefined');
        
        if (typeof turf === 'undefined') {
            console.error('ERROR: Turf.js no está cargado');
            console.info('Agrega: <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>');
        }
        
        if (typeof proj4 !== 'undefined') {
            proj4.defs('EPSG:2203', '+proj=utm +zone=20 +south +ellps=intl +towgs84=-288,175,-376,0,0,0,0 +units=m +no_defs');
        }
    }

    // =============================================
    // 3. CONFIGURACIÓN DE PROYECCIONES
    // =============================================

    /**
     * DEFINE PROYECCIONES PERSONALIZADAS
     * Configura EPSG:2203 (UTM Zone 20S Venezuela) y EPSG:32620 (UTM Zone 20N)
     * USADO EN: Constructor, importGeoJSON (para transformaciones)
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

    // =============================================
    // 4. CONFIGURACIÓN DEL MAPA
    // =============================================

    /**
     * INICIALIZA TODOS LOS COMPONENTES DEL MAPA
     * Orden de ejecución: capas base → capa GFW → capa vectorial → instancia mapa
     * USADO EN: Constructor
     */
    initializeMap() {
        this.setupBaseLayers();    // Capas base (OSM, Satélite, etc.)
        this.setupGFWLayer();      // Capa de pérdida forestal GFW
        this.setupVectorLayer();   // Capa vectorial para dibujos
        this.setupMapInstance();   // Crear instancia del mapa
    }

    /**
     * CONFIGURA CAPAS BASE
     * Crea 5 capas base diferentes para selección del usuario
     * USADO EN: initializeMap(), setupMapInstance()
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
     * CONFIGURA CAPA DE PÉRDIDA FORESTAL GFW
     * Capa de tiles dinámica que muestra pérdida arbórea
     * USADO EN: initializeMap(), setupMapInstance()
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
     * CONFIGURA CAPA VECTORIAL Y ESTILOS
     * Capa que contiene polígonos dibujados y elementos vectoriales
     * USADO EN: initializeMap(), setupMapInstance()
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
     * Combina todas las capas y configura la vista inicial
     * USADO EN: initializeMap()
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
    // 5. CONFIGURACIÓN DE ESTILOS
    // =============================================

    /**
     * CONFIGURA TODOS LOS ESTILOS VISUALES
     * Define estilos para polígonos, puntos y etiquetas
     * USADO EN: setupVectorLayer()
     */
    setupStyles() {
        this.polygonStyle = this.getPolygonStyle('default');
        
        this.pointStyle = new ol.style.Style({
            image: new ol.style.Circle({
                radius: 7,
                fill: new ol.style.Fill({ color: '#ffffff' }),
                stroke: new ol.style.Stroke({ color: '#10b981', width: 3 })
            })
        });

        this.labelStyle = new ol.style.Style({
            text: new ol.style.Text({
                text: '',
                font: 'bold 14px "Arial", sans-serif',
                fill: new ol.style.Fill({ color: '#1f2937' }),
                stroke: new ol.style.Stroke({ color: '#ffffff', width: 2 }),
                offsetY: -20,
                overflow: true,
                backgroundFill: new ol.style.Fill({ color: 'rgba(255, 255, 255, 0.85)' }),
                padding: [4, 8, 4, 8],
                textBaseline: 'middle',
                textAlign: 'center'
            })
        });
    }

    /**
     * OBTIENE ESTILO DE POLÍGONO POR ESTADO
     * @param {string} state - 'drawing', 'finished', 'default'
     * @param {number} areaHa - Área para mostrar en etiqueta
     * @returns {ol.style.Style} Estilo correspondiente
     * USADO EN: setupStyles(), getFeatureStyle(), activateDrawing(), finalizeDrawing()
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
     * @param {ol.Feature} feature - Feature a estilizar
     * @returns {Array<ol.style.Style>} Array de estilos
     * USADO EN: setupVectorLayer() (como función de estilo)
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

        // Agregar etiqueta si existe
        this.addLabelToFeature(feature, geometry);
        return styles;
    }

    /**
     * AGREGA ETIQUETA A UNA FEATURE
     * @param {ol.Feature} feature - Feature con propiedad 'label'
     * @param {ol.geom.Geometry} geometry - Geometría para posicionar etiqueta
     * USADO EN: getFeatureStyle(), processImportedFeatures()
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
    // 6. MANEJO DE EVENTOS
    // =============================================

    /**
     * CONFIGURA LISTENERS DE EVENTOS GLOBALES
     * USADO EN: Constructor
     */
    setupEventListeners() {
        document.getElementById('draw-polygon').addEventListener('click', () => this.activateDrawing());
        document.getElementById('clear-map').addEventListener('click', () => this.clearMap());
        document.getElementById('analysis-form').addEventListener('submit', (e) => this.handleFormSubmit(e));
        document.addEventListener('keydown', (e) => this.handleKeyDown(e));
    }

    /**
     * MANEJA EVENTOS DE TECLADO
     * @param {KeyboardEvent} event - Evento de tecla presionada
     * USADO EN: setupEventListeners()
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
     * USADO EN: handleKeyDown()
     */
    cancelDrawing() {
        if (this.draw) {
            this.map.removeInteraction(this.draw);
            this.draw = null;
        }
        this.drawingFeature = null;
        this.updateAreaDisplay(0);
    }

    // =============================================
    // 7. DISPLAY DE COORDENADAS
    // =============================================

    /**
     * CONFIGURA DISPLAY DE COORDENADAS UTM
     * USADO EN: Constructor
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
     * USADO EN: setupCoordinateDisplay()
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
     * ACTUALIZA DISPLAY CON COORDENADAS UTM
     * @param {Array} coordinate - Coordenadas en proyección del mapa
     * USADO EN: setupCoordinateDisplay() (evento pointermove)
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

    /**
     * CONFIGURA PROYECCIÓN UTM DINÁMICAMENTE
     * @param {number} zone - Zona UTM
     * @param {string} hemisphere - 'N' o 'S'
     * @returns {string} Código EPSG de la proyección
     * USADO EN: updateCoordinateDisplay(), drawFromUTMCoordinates()
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
     * @param {number} easting - Coordenada este
     * @param {number} northing - Coordenada norte
     * @param {number} zone - Zona UTM
     * @param {string} hemisphere - 'N' o 'S'
     * @returns {boolean} True si las coordenadas son válidas
     * USADO EN: updateCoordinateDisplay()
     */
    isValidUTM(easting, northing, zone, hemisphere) {
        if (easting < 0 || easting > 1000000) return false;
        
        if (hemisphere === 'N') {
            return northing >= 0 && northing <= 10000000;
        } else {
            return northing >= 1000000 && northing <= 10000000;
        }
    }

    // =============================================
    // 8. FUNCIONALIDADES DE DIBUJO
    // =============================================

    /**
     * ACTIVA HERRAMIENTA DE DIBUJO DE POLÍGONOS
     * USADO EN: setupEventListeners() (botón draw-polygon)
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
     * USADO EN: activateDrawing()
     */
    setupDrawEvents() {
        this.draw.on('drawstart', (evt) => {
            this.drawingFeature = evt.feature;
            this.source.clear();
            this.updateAreaDisplay(0);
        });

        this.draw.on('drawadd', () => this.refreshArea());
        this.draw.on('drawabort', () => this.resetDrawingState());
        this.draw.on('drawend', (event) => this.finalizeDrawing(event.feature));
    }

    /**
     * FINALIZA PROCESO DE DIBUJO
     * @param {ol.Feature} feature - Polígono dibujado
     * USADO EN: setupDrawEvents() (evento drawend)
     */
    finalizeDrawing(feature) {
        const areaHa = this.refreshArea(feature);
        
        feature.set('area', areaHa);
        feature.setStyle(this.getPolygonStyle('finished', areaHa));
        
        this.convertToGeoJSON(feature, areaHa);
        this.showAlert(`Polígono completado. Área: ${areaHa.toFixed(6)} ha`, 'success');
        
        this.map.removeInteraction(this.draw);
        this.draw = null;
        this.resetDrawingState();
    }

    /**
     * ELIMINA INTERACCIÓN DE DIBUJO EXISTENTE
     * USADO EN: activateDrawing(), clearMap()
     */
    removeExistingDrawInteraction() {
        if (this.draw) {
            this.map.removeInteraction(this.draw);
        }
    }

    /**
     * LIMPIA ESTADO INTERNO DE DIBUJO
     * USADO EN: setupDrawEvents(), finalizeDrawing(), cancelDrawing()
     */
    resetDrawingState() {
        this.drawingFeature = null;
        this.updateAreaDisplay(0);
    }

    // =============================================
    // 9. CÁLCULOS DE GEOMETRÍA
    // =============================================

    /**
     * CALCULA ÁREA DE UN POLÍGONO USANDO TURF.JS
     * @param {ol.Feature} feature - Feature con geometría
     * @returns {number} Área en hectáreas
     * USADO EN: refreshArea(), convertToGeoJSON(), processImportedFeatures(), createPolygonFromCoordinates()
     */
    calculateArea(feature) {
        console.log('=== CÁLCULO DE ÁREA CON TURF.JS ===');
        
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
        
        if (!['Polygon', 'MultiPolygon'].includes(geomType)) {
            console.log(`Geometría ${geomType} no requiere cálculo de área`);
            return 0;
        }
        
        if (typeof turf === 'undefined') {
            console.error('ERROR: Turf.js no está disponible');
            this.showTurfJSError();
            return 0;
        }
        
        try {
            const wgs84Geometry = geometry.clone().transform('EPSG:3857', 'EPSG:4326');
            const coordinates = wgs84Geometry.getCoordinates();
            
            let turfFeature;
            switch (geomType) {
                case 'Polygon':
                    this.validatePolygonCoordinates(coordinates);
                    turfFeature = turf.polygon(coordinates);
                    break;
                case 'MultiPolygon':
                    coordinates.forEach(polygonCoords => {
                        this.validatePolygonCoordinates(polygonCoords);
                    });
                    turfFeature = turf.multiPolygon(coordinates);
                    break;
                default:
                    return 0;
            }
            
            const areaM2 = turf.area(turfFeature);
            
            if (isNaN(areaM2) || areaM2 <= 0) {
                console.warn('Área calculada no válida:', areaM2);
                return 0;
            }
            
            const areaHa = areaM2 / 10000;
            
            console.log(`Cálculo completado:
              - Tipo: ${geomType}
              - Área: ${areaM2.toFixed(2)} m²
              - Área: ${areaHa.toFixed(6)} ha
            `);
            
            return parseFloat(areaHa.toFixed(6));
            
        } catch (error) {
            console.error('Error en cálculo de área:', error);
            this.showAreaCalculationError(error);
            return 0;
        }
    }

    /**
     * REFRESCA VISUALIZACIÓN DEL ÁREA
     * @param {ol.Feature} feature - Feature para calcular área (opcional)
     * @returns {number} Área en hectáreas
     * USADO EN: setupDrawEvents(), finalizeDrawing()
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
     * VALIDA COORDENADAS DE POLÍGONO
     * @param {Array} coordinates - Coordenadas del polígono
     * USADO EN: calculateArea()
     */
    validatePolygonCoordinates(coordinates) {
        if (!coordinates || !Array.isArray(coordinates)) {
            throw new Error('Coordenadas del polígono no válidas');
        }
        
        if (coordinates.length === 0) {
            throw new Error('Polígono sin coordenadas');
        }
        
        const firstRing = coordinates[0];
        if (firstRing && firstRing.length > 0) {
            const firstPoint = firstRing[0];
            const lastPoint = firstRing[firstRing.length - 1];
            
            if (firstPoint[0] !== lastPoint[0] || firstPoint[1] !== lastPoint[1]) {
                console.warn('Polígono no cerrado. Asegurándose de cerrar el polígono.');
            }
        }
    }

    // =============================================
    // 10. IMPORTACIÓN DE DATOS
    // =============================================

    /**
     * IMPORTA GEOJSON CON SOPORTE PARA EPSG:2203
     * @param {Object|string} geojson - Objeto GeoJSON o string
     * USADO EN: Event listener de importación de archivos
     */
    importGeoJSON(geojson) {
        console.log('=== INICIANDO IMPORTACIÓN GEOJSON CON EPSG:2203 ===');
        
        try {
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

            if (!geojsonObj.type) {
                throw new Error('El objeto no tiene propiedad "type" (no es GeoJSON válido)');
            }

            const hasCustomCRS = geojsonObj.crs && 
                                geojsonObj.crs.properties && 
                                geojsonObj.crs.properties.name && 
                                geojsonObj.crs.properties.name.includes('EPSG::2203');
            
            console.log(`GeoJSON tiene CRS personalizado (EPSG:2203): ${hasCustomCRS}`);

            this.clearMap();
            
            const format = new ol.format.GeoJSON();
            let features;
            
            try {
                if (hasCustomCRS) {
                    console.log('Procesando GeoJSON con EPSG:2203...');
                    features = this.readFeaturesFromEPSG2203(geojsonObj, format);
                } else {
                    console.log('Procesando GeoJSON estándar (EPSG:4326)...');
                    features = format.readFeatures(geojsonObj, {
                        dataProjection: 'EPSG:4326',
                        featureProjection: 'EPSG:3857'
                    });
                }
            } catch (projectionError) {
                console.warn('Error en conversión de proyección:', projectionError);
                try {
                    features = format.readFeatures(geojsonObj);
                } catch (fallbackError) {
                    throw new Error(`No se pudo leer el GeoJSON: ${fallbackError.message}`);
                }
            }

            if (!features || features.length === 0) {
                this.showAlert('El archivo no contiene geometrías válidas.', 'error');
                return;
            }

            console.log(`Features importadas: ${features.length}`);
            
            const processedData = this.processImportedFeatures(features, 'geojson', geojsonObj);
            this.finalizeImport(features, geojsonObj, processedData.totalArea, 'GeoJSON');
            
        } catch (error) {
            console.error('Error completo en importGeoJSON:', error);
            this.showAlert(`Error al importar GeoJSON: ${error.message}`, 'error');
        }
    }

    /**
     * LEE FEATURES DESDE GEOJSON CON EPSG:2203
     * @param {Object} geojsonObj - Objeto GeoJSON
     * @param {ol.format.GeoJSON} format - Formato GeoJSON
     * @returns {Array} Features procesadas
     * USADO EN: importGeoJSON()
     */
    readFeaturesFromEPSG2203(geojsonObj, format) {
        const features = [];
        
        if (typeof proj4 === 'undefined') {
            throw new Error('Se requiere la biblioteca proj4 para transformar EPSG:2203 a WGS84');
        }
        
        geojsonObj.features.forEach((featureData, index) => {
            try {
                const geometry = featureData.geometry;
                const properties = featureData.properties || {};
                
                if (!geometry || !geometry.coordinates) {
                    console.warn(`Feature ${index}: Sin geometría válida`);
                    return;
                }
                
                const wgs84Coordinates = this.convertEPSG2203ToWGS84(geometry);
                
                let olGeometry;
                if (geometry.type === 'MultiPolygon') {
                    olGeometry = new ol.geom.MultiPolygon(wgs84Coordinates);
                } else if (geometry.type === 'Polygon') {
                    olGeometry = new ol.geom.Polygon(wgs84Coordinates);
                } else {
                    console.warn(`Feature ${index}: Tipo de geometría no soportado: ${geometry.type}`);
                    return;
                }
                
                olGeometry.transform('EPSG:4326', 'EPSG:3857');
                
                const feature = new ol.Feature({ geometry: olGeometry });
                
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
     * CONVIERTE COORDENADAS DE EPSG:2203 A WGS84
     * @param {Object} geometry - Geometría en EPSG:2203
     * @returns {Array} Coordenadas convertidas
     * USADO EN: readFeaturesFromEPSG2203()
     */
    convertEPSG2203ToWGS84(geometry) {
        if (geometry.type === 'MultiPolygon') {
            return geometry.coordinates.map(polygon =>
                polygon.map(ring =>
                    ring.map(coord => {
                        const [x, y] = coord;
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
     * IMPORTA DATOS DESDE KML
     * @param {string} kmlText - Texto KML
     * USADO EN: Potencialmente por eventos de importación KML
     */
    importKML(kmlText) {
        console.log('=== INICIANDO IMPORTACIÓN KML ===');
        
        try {
            if (typeof kmlText !== 'string' || kmlText.trim() === '') {
                throw new Error('El texto KML está vacío o no es válido');
            }

            this.clearMap();
            
            const format = new ol.format.KML({
                extractStyles: false,
                showPointNames: false
            });
            
            let features;
            try {
                features = format.readFeatures(kmlText, {
                    dataProjection: 'EPSG:4326',
                    featureProjection: 'EPSG:3857'
                });
            } catch (readError) {
                console.warn('Error leyendo KML, intentando sin proyección:', readError);
                features = format.readFeatures(kmlText);
            }

            if (!features || features.length === 0) {
                this.showAlert('El archivo KML no contiene geometrías válidas.', 'error');
                return;
            }

            console.log(`Features KML importadas: ${features.length}`);
            
            const processedData = this.processImportedFeatures(features, 'kml');
            const featureCollection = this.createFeatureCollection(features);
            this.finalizeImport(features, featureCollection, processedData.totalArea, 'KML');
            
        } catch (error) {
            console.error('Error completo en importKML:', error);
            this.showAlert(`Error al importar KML: ${error.message}`, 'error');
        }
    }

    /**
     * PROCESA FEATURES IMPORTADAS
     * @param {Array} features - Features de OpenLayers
     * @param {string} type - 'geojson' o 'kml'
     * @param {Object} originalData - Datos originales
     * @returns {Object} Datos procesados
     * USADO EN: importGeoJSON(), importKML()
     */
    processImportedFeatures(features, type = 'geojson', originalData = null) {
        console.log(`=== PROCESANDO ${features.length} FEATURES (${type}) ===`);
        
        let totalArea = 0;
        let validFeatures = 0;
        let invalidFeatures = 0;
        
        features.forEach((feature, index) => {
            try {
                const geometry = feature.getGeometry();
                if (!geometry) {
                    console.warn(`Feature ${index}: Sin geometría`);
                    invalidFeatures++;
                    return;
                }
                
                const geomType = geometry.getType();
                console.log(`Feature ${index}: Tipo ${geomType}`);
                
                const props = feature.getProperties();
                
                if (type === 'geojson') {
                    const productor = props.productor || props.Productor;
                    const areaHa = props.area || props.Area_Ha || 0;
                    
                    if (productor && productor !== 'Desconocido' && productor !== 'null') {
                        feature.set('label', String(productor));
                        feature.set('productor', String(productor));
                    } else {
                        feature.set('label', `Polígono ${index + 1}`);
                        feature.set('productor', 'Sin productor');
                    }
                    
                    if (areaHa && areaHa > 0) {
                        feature.set('area', parseFloat(areaHa));
                        totalArea += parseFloat(areaHa);
                        validFeatures++;
                        feature.setStyle(this.getPolygonStyle('finished', areaHa));
                    } else {
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
                    const productor = props.productor || props.name || props.Productor || props.Nombre;
                    if (productor) {
                        feature.set('label', String(productor));
                        feature.set('productor', String(productor));
                    }
                    
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
                
                this.addLabelToFeature(feature, geometry);
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
     * FINALIZA PROCESO DE IMPORTACIÓN
     * @param {Array} features - Features importadas
     * @param {Object} data - Datos originales
     * @param {number} totalArea - Área total calculada
     * @param {string} type - Tipo de archivo
     * USADO EN: importGeoJSON(), importKML()
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
    // 11. MANEJO DE FORMULARIOS
    // =============================================

    /**
     * MANEJA ENVÍO DE FORMULARIO
     * @param {Event} event - Evento de submit
     * USADO EN: setupEventListeners()
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
     * DESHABILITA FORMULARIO DURANTE ENVÍO
     * USADO EN: handleFormSubmit()
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
     * CONVIERTE FEATURE A GEOJSON
     * @param {ol.Feature} feature - Feature a convertir
     * @param {number} existingArea - Área pre-calculada (opcional)
     * USADO EN: finalizeDrawing(), createPolygonFromCoordinates()
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
            
            if (existingArea === null) {
                this.showAlert(`Polígono guardado. Área: ${areaHa.toFixed(6)} ha`);
            }
            
        } catch (error) {
            console.error('Error al convertir GeoJSON:', error);
            this.showAlert('Error al guardar el polígono: ' + error.message, 'error');
        }
    }

    // =============================================
    // 12. MANEJO DE CAPAS
    // =============================================

    /**
     * CONFIGURA TOGGLE DE CAPA GFW
     * USADO EN: Constructor
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
     * APLICA ESTADO DE VISIBILIDAD A CAPA GFW
     * @param {boolean} isVisible - Estado de visibilidad
     * USADO EN: initializeGfWLayerToggle()
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

    // =============================================
    // 13. UTILIDADES
    // =============================================

    /**
     * ACTUALIZA DISPLAY DE ÁREA EN INTERFAZ
     * @param {number} areaHa - Área en hectáreas
     * USADO EN: refreshArea(), resetDrawingState(), cancelDrawing(), clearMap(), finalizeImport()
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
     * LIMPIA MAPA COMPLETAMENTE
     * USADO EN: setupEventListeners() (botón clear-map), importGeoJSON(), importKML()
     */
    clearMap() {
        this.source.clear();
        document.getElementById('geometry').value = '';
        document.getElementById('area_ha').value = '';
        this.updateAreaDisplay(0);
        
        this.removeExistingDrawInteraction();
        this.drawingFeature = null;
    }

    /**
     * CREA FEATURECOLLECTION PARA FORMULARIO
     * @param {Array} features - Features a incluir
     * @returns {Object} FeatureCollection GeoJSON
     * USADO EN: importKML()
     */
    createFeatureCollection(features) {
        const format = new ol.format.GeoJSON();
        
        const geojsonFeatures = features.map(feature => {
            try {
                const geojsonStr = format.writeFeature(feature, {
                    dataProjection: 'EPSG:4326',
                    featureProjection: 'EPSG:3857',
                    decimals: 6
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
     * MUESTRA ALERTA AL USUARIO
     * @param {string} message - Mensaje a mostrar
     * @param {string} icon - Tipo de icono ('info', 'success', 'error', 'warning')
     * USADO EN: finalizeDrawing(), showTurfJSError(), showAreaCalculationError(), 
     *           convertToGeoJSON(), importGeoJSON(), importKML(), finalizeImport(),
     *           handleFormSubmit(), createPolygonFromCoordinates(), drawFromUTMCoordinates()
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

    /**
     * MUESTRA ERROR DE TURF.JS
     * USADO EN: calculateArea()
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
     * MUESTRA ERROR DE CÁLCULO DE ÁREA
     * @param {Error} error - Error ocurrido
     * USADO EN: calculateArea()
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

    // =============================================
    // 14. FUNCIONALIDADES ADICIONALES
    // =============================================

    /**
     * DIBUJA POLÍGONO DESDE COORDENADAS UTM
     * @param {Array} utmCoordinates - Array de coordenadas UTM [easting, northing, zone, hemisphere]
     * USADO EN: Potencialmente desde interfaz externa
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
     * @param {Array} coordinates - Coordenadas del polígono
     * USADO EN: drawFromUTMCoordinates()
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
     * @param {Array} wgs84Coordinates - Coordenadas en WGS84
     * @param {Array} utmCoordinates - Coordenadas UTM originales
     * USADO EN: drawFromUTMCoordinates()
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
     * PROCESA INFORMACIÓN DE MÚLTIPLES POLÍGONOS
     * @param {Array} features - Features a procesar
     * USADO EN: finalizeImport()
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
                area: feature.get('area') || 0
            }));

        this.displayPolygonsInfo(polygonsInfo);
    }

    /**
     * MUESTRA INFORMACIÓN DE POLÍGONOS EN TABLA
     * @param {Array} polygonsInfo - Información de polígonos
     * USADO EN: processMultiPolygonInfo()
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
}

// =============================================
// 15. INICIALIZACIÓN GLOBAL
// =============================================

/**
 * INICIALIZA MAPA CUANDO EL DOCUMENTO ESTÉ LISTO
 * Crea instancia global window.deforestationMapInstance
 */
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('map')) {
        window.deforestationMapInstance = new DeforestationMap();
    }
});

/**
 * MANEJA CARGA DE ARCHIVOS GEOJSON
 * Configura evento para importar archivos GeoJSON
 */
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
            
            event.target.value = '';
        });
    }
});
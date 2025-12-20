
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
        const areaHa = this.refreshArea(feature);
        
        // Configuración de la feature
        feature.set('area', areaHa);
        feature.setStyle(this.getPolygonStyle('finished', areaHa));
        
        this.convertToGeoJSON(feature);
        this.showAlert(`Polígono completado.`, 'success');
        
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
     * Calcula el área de un polígono en hectáreas
     * @param {ol.Feature} feature
     * @returns {number}
     */
    calculateArea(feature) {
        console.log('=== CÁLCULO DE ÁREA CON TURF.JS ===');
        
        try {
            const geometry = feature.getGeometry();
            if (!geometry || geometry.getType() !== 'Polygon') {
                console.warn('Geometría inválida para cálculo de área');
                return 0;
            }
            
            // 1. Transformar a WGS84
            const wgs84Geometry = geometry.clone().transform('EPSG:3857', 'EPSG:4326');
            const coordinates = wgs84Geometry.getCoordinates();
            
            if (!coordinates || coordinates.length === 0 || coordinates[0].length < 3) {
                console.warn('Coordenadas insuficientes');
                return 0;
            }
            
            console.log('Número de puntos:', coordinates[0].length);
            
            // 2. Crear Feature de Turf.js
            const turfPolygon = {
                type: 'Feature',
                geometry: {
                    type: 'Polygon',
                    coordinates: coordinates
                },
                properties: {}
            };
            
            // 3. Calcular área con Turf.js (en metros cuadrados)
            const areaM2 = turf.area(turfPolygon);
            const areaHa = areaM2 / 10000;
            
            console.log('Resultado Turf.js:');
            console.log('- Área en m²:', areaM2.toFixed(2));
            console.log('- Área en ha:', areaHa.toFixed(6));
            
            return Math.max(0, parseFloat(areaHa.toFixed(6)));
            
        } catch (error) {
            console.error('Error calculando área con Turf.js:', error);
            
            // Fallback al método original
            return this.calculateAreaOriginal(feature);
        }
    }

    /**
     * Convierte la geometría a GeoJSON y la guarda en el input oculto
     * @param {ol.Feature} feature
     */
    convertToGeoJSON(feature) {
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
            
            const areaHa = this.calculateArea(feature);
            document.getElementById('area_ha').value = areaHa.toFixed(6);
            
            this.showAlert(`Polígono guardado. Área: ${areaHa.toFixed(6)} ha`);
        } catch (error) {
            console.error('Error al convertir GeoJSON:', error);
            this.showAlert('Error al guardar el polígono: ' + error.message, 'error');
        }
    }

    // =============================================
    // 6. IMPORTACIÓN Y EXPORTACIÓN DE DATOS
    // =============================================

    /**
     * Importa uno o varios polígonos desde GeoJSON
     * @param {Object} geojson
     */
    importGeoJSON(geojson) {
        try {
            this.clearMap();
            const format = new ol.format.GeoJSON();
            const features = format.readFeatures(geojson, {
                featureProjection: 'EPSG:2203 '
            });

            if (features.length === 0) {
                this.showAlert('El archivo no contiene geometría válida.', 'error');
                return;
            }

            const totalArea = this.processImportedFeatures(features);
            this.finalizeImport(features, geojson, totalArea, 'GeoJSON');
            
        } catch (error) {
            this.showAlert('Error al importar el área: ' + error.message, 'error');
        }
    }

    /**
     * Importa uno o varios polígonos desde KML
     * @param {string} kmlText
     */
    importKML(kmlText) {
        try {
            this.clearMap();
            const format = new ol.format.KML();
            const features = format.readFeatures(kmlText, {
                featureProjection: 'EPSG:3857'
            });

            if (features.length === 0) {
                this.showAlert('El archivo KML no contiene geometría válida.', 'error');
                return;
            }

            const totalArea = this.processImportedFeatures(features, 'kml');
            const featureCollection = this.createFeatureCollection(features);
            
            this.finalizeImport(features, featureCollection, totalArea, 'KML');
            
        } catch (error) {
            this.showAlert('Error al importar el área KML: ' + error.message, 'error');
        }
    }

    /**
     * Procesa features importadas
     * @param {Array} features 
     * @param {string} type 
     * @returns {number}
     */
    processImportedFeatures(features, type = 'geojson') {
        let totalArea = 0;
        
        features.forEach(feature => {
            if (type === 'kml') {
                const productor = this.extractKMLData(feature);
                if (productor) {
                    feature.set('label', productor);
                    feature.set('productor', productor);
                }
            } else {
                const name = feature.get('name') || feature.get('Nombre') || 
                            feature.get('NOMBRE') || feature.get('title') || 
                            feature.get('Productor');
                if (name) {
                    feature.set('label', name);
                    feature.set('productor', name);
                }
            }
            
            // Calcular y guardar área para mostrar en el texto
            if (feature.getGeometry().getType() === 'Polygon') {
                const areaHa = this.calculateArea(feature);
                feature.set('area', areaHa);
                totalArea += areaHa;
                
                // Aplicar estilo con área
                feature.setStyle(this.getPolygonStyle('finished', areaHa));
            }
            
            this.source.addFeature(feature);
        });
        
        return totalArea;
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
     * Extrae datos de KML
     */
    extractKMLData(feature) {
        try {
            const kmlDescription = feature.get('description');
            if (!kmlDescription) return null;
            
            const parser = new DOMParser();
            const doc = parser.parseFromString(kmlDescription, 'text/html');
            const simpleDataElements = doc.querySelectorAll('simpledata');
            const data = {};
            
            simpleDataElements.forEach(el => {
                const name = el.getAttribute('name');
                const value = el.textContent;
                if (name && value) data[name.toLowerCase()] = value;
            });
            
            return data.productor || data.name || data.nombre || null;
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
            .filter(feature => feature.getGeometry().getType() === 'Polygon')
            .map(feature => ({
                productor: feature.get('productor') || feature.get('name') || 'Propietario desconocido',
                localidad: feature.get('localidad') || feature.get('municipio') || 'No especificado',
                area: this.calculateArea(feature)
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
     * Crea FeatureCollection desde features
     */
    createFeatureCollection(features) {
        return {
            type: 'FeatureCollection',
            features: features.map(feature => {
                const geoJSONFormat = new ol.format.GeoJSON();
                return JSON.parse(geoJSONFormat.writeFeature(feature, {
                    dataProjection: 'EPSG:4326',
                    featureProjection: 'EPSG:3857'
                }));
            })
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
        
        // Calcular área y guardarla en la feature
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
        
        this.convertToGeoJSON(feature);
        
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
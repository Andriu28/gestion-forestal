/**
 * CLASE PRINCIPAL: PolygonMap (Utilidades y Motor Cartográfico para Polígonos)
 * 
 * Controla el visor cartográfico interactivo para la digitalización, captura y edición de parcelas forestales:
 * 1. Librerías GIS utilizadas:
 *    - OpenLayers 6: Renderizado de capas raster y vectoriales en proyección Web Mercator (EPSG:3857).
 *    - Proj4js: Reproyección matemática al vuelo de coordenadas geodésicas (WGS84) a cartesianas UTM (zonas venezolanas).
 *    - Turf.js: Algoritmos espaciales en el navegador para cálculo de superficie geodésica esferoidal en hectáreas.
 * 2. Funcionalidades del cliente:
 *    - Alternancia de capas base (satélite Esri, MapTiler, OpenStreetMap, relieve topográfico, modo oscuro).
 *    - Dibujo vectorial interactivo de polígonos con detección de vértices.
 *    - Visualizador en tiempo real de coordenadas UTM (Huso, Hemisferio, Coordenada Este, Coordenada Norte).
 *    - Carga e interpretación de vértices introducidos manualmente en coordenadas UTM.
 *    - Exportación y sincronización automática del trazo en el campo oculto GeoJSON del formulario web.
 */
class PolygonMap {
    constructor() {
        // =========================================================================
        // 1. Inicialización de Propiedades Internas
        // =========================================================================
        this.map = null;               // Instancia principal del visor OpenLayers
        this.draw = null;              // Controlador de interacción de digitalización activa (Draw Interaction)
        this.source = null;            // Fuente de datos en memoria para las geometrías dibujadas (Vector Source)
        this.polygonStyle = null;      // Estilo gráfico predeterminado para el polígono
        this.coordinateDisplay = null; // Elemento del DOM donde se imprimen las coordenadas UTM
        this.baseLayers = {};          // Catálogo de proveedores de teselas base
        this.currentBaseLayer = null;  // Capa base seleccionada actualmente
        this.drawingFeature = null;    // Elemento espacial mientras el usuario agrega vértices

        // Coordenadas geográficas iniciales (centrado en territorio venezolano)
        this.INITIAL_CENTER = [-63.172905251869125, 10.555594747510682]; // Longitud, Latitud (WGS84)
        this.INITIAL_ZOOM = 15;        // Nivel de aumento predeterminado
        this.MINZOOM = 5;              // Zoom mínimo (visión de país)
        this.MAXZOOM = 18;             // Zoom máximo (visión de detalle a nivel de parcela)

        // =========================================================================
        // 2. Secuencia de Arranque del Entorno Cartográfico
        // =========================================================================
        this.defineCustomProjections(); // Registrar parámetros geodésicos en Proj4
        this.initializeMap();           // Construir capas y montar OpenLayers en el DOM
        this.setupEventListeners();     // Registrar teclas rápidas (Escape para cancelar dibujo)
        this.setupCoordinateDisplay();  // Activar visor de coordenadas con el movimiento del ratón

        this.verifyDependencies();      // Diagnóstico en consola de librerías externas
    }

    /**
     * Comprueba que las librerías OpenLayers y Turf.js se encuentren disponibles globalmente en window.
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
     * Registra proyecciones cartográficas específicas para la cartografía en Venezuela:
     * - EPSG:2203: UTM Zona 20 Sur (Sistema La Canoa / Internacional 1924).
     * - EPSG:32620: UTM Zona 20 Norte (Elipsoide WGS84, estándar en el oriente de Venezuela).
     * 
     * Registra las cadenas PROJ.4 en el motor de proyecciones de OpenLayers.
     */
    defineCustomProjections() {
        if (typeof proj4 !== 'undefined') {
            // EPSG:2203 - UTM Zone 20S (Venezuela histórico)
            proj4.defs('EPSG:2203', 
                '+proj=utm +zone=20 +south +ellps=intl +towgs84=-288,175,-376,0,0,0,0 +units=m +no_defs'
            );
            
            // EPSG:32620 - UTM Zone 20N (WGS84 estándar)
            proj4.defs('EPSG:32620',
                '+proj=utm +zone=20 +ellps=WGS84 +datum=WGS84 +units=m +no_defs'
            );
            
            if (typeof ol !== 'undefined') {
                ol.proj.proj4.register(proj4);
            }
        }
    }

    /**
     * Orquesta la inicialización de las capas ráster, el lienzo vectorial y la cámara de visualización.
     */
    initializeMap() {
        this.setupBaseLayers();    // Capas satelitales y de mapa callejero
        this.setupVectorLayer();   // Capa dinámica para el trazo de parcelas
        this.setupMapInstance();   // Creación del canvas de OpenLayers
    }

    /**
     * Configura el conjunto de mapas base en formato XYZ Tiles:
     * 1. OSM Voyager (CARTO): Mapa vectorial claro para ubicación de vías y asentamientos.
     * 2. Satélite Esri World Imagery: Ortoimágenes satelitales de alta resolución para distinguir la vegetación.
     * 3. Relieve Esri Shaded Relief: Modelo digital de sombras topográficas.
     * 4. CartoDB Dark Matter: Mapa nocturno/oscuro de alto contraste.
     * 5. MapTiler Satellite: Proveedor satelital secundario.
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
     * Instancia la capa vectorial de dibujo y asigna la función de renderizado de estilos dinámicos.
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
     * Construye la instancia de `ol.Map`:
     * - Agrupa las capas base.
     * - Configura la vista (View) transformando el centro desde longitud/latitud (EPSG:4326) a Web Mercator (EPSG:3857).
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
     * Define los estilos gráficos iniciales.
     */
    setupStyles() {
        this.polygonStyle = this.getPolygonStyle('default');
    }

    /**
     * Fábrica de estilos visuales según el ciclo del polígono:
     * - 'drawing': Borde azul discontinuo (dash) con vértices marcados en blanco mientras se dibuja.
     * - 'finished': Borde verde sólido con relleno esmeralda semitransparente y etiqueta centrada
     *   que exhibe la superficie total calculada en hectáreas (ha).
     * - 'default': Estilo pasivo verde esmeralda.
     *
     * @param string state  Estado del polígono ('drawing', 'finished', 'default')
     * @param number areaHa Superficie calculada en hectáreas para mostrar en la etiqueta central
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
     * Aplica el estilo correspondiente a cada objeto espacial de la capa vectorial.
     */
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

    /**
     * Registra eventos globales del teclado (ej. tecla Escape para cancelar trazo).
     */
    setupEventListeners() {
        document.addEventListener('keydown', (e) => this.handleKeyDown(e));
    }

    /**
     * Atajo de teclado: si se presiona 'Escape' durante un trazo, aborta el dibujo actual.
     */
    handleKeyDown(event) {
        if (event.key === 'Escape' && this.draw && this.drawingFeature) {
            this.cancelDrawing();
            event.preventDefault();
        }
    }

    /**
     * Aborta el dibujo en curso, retira el interactuador de OpenLayers y restablece contadores.
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
     * Escucha el evento `pointermove` del ratón sobre el mapa para calcular y proyectar
     * las coordenadas UTM en el panel inferior en tiempo real.
     */
    setupCoordinateDisplay() {
        this.createCoordinateDisplayElement();
        
        this.map.on('pointermove', (evt) => {
            if (evt.dragging) return;
            this.updateCoordinateDisplay(evt.coordinate);
        });
    }

    /**
     * Conecta o rescata el contenedor HTML donde se muestran las coordenadas.
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
     * Algoritmo de cálculo dinámico de coordenadas UTM a partir del cursor:
     * 1. Convierte la posición de Web Mercator (EPSG:3857) a WGS84 Geográfico (longitud, latitud).
     * 2. Calcula automáticamente el huso o Zona UTM según la fórmula matemática:
     *    `Zona = floor((longitud + 180) / 6) + 1`
     * 3. Determina el hemisferio (Norte si latitud >= 0, Sur si latitud < 0).
     * 4. Registra dinámicamente la proyección correspondiente (ej. EPSG:32619 o EPSG:32620).
     * 5. Ejecuta Proj4 para obtener las coordenadas métricas cartesianas Este (X) y Norte (Y).
     * 6. Imprime los valores formateados con 3 decimales en la interfaz.
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
     * Genera al vuelo la definición PROJ4 para cualquier huso UTM solicitado:
     * Código EPSG 326XX para el hemisferio Norte y 327XX para el hemisferio Sur.
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
     * Valida que las coordenadas UTM se encuentren dentro de los límites matemáticos del plano cartesiano:
     * - Coordenada Este (Easting): Típicamente entre 100,000m y 900,000m (falso este central de 500,000m).
     * - Coordenada Norte (Northing): Entre 0m y 10,000,000m.
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
     * Activa el modo de digitalización interactiva de polígonos:
     * Monta una interacción `ol.interaction.Draw` sobre la fuente vectorial.
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
     * Suscribe las funciones controladoras a los eventos de dibujo:
     * - `drawstart`: Limpia dibujos previos y bloquea botones de detección geográfica.
     * - `drawadd`: Recalcula el área conforme el usuario añade cada nuevo vértice.
     * - `drawabort`: Cancela el proceso si el trazo es interrumpido.
     * - `drawend`: Concluye el polígono, calcula el área final y convierte a GeoJSON.
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
     * Concluye la digitalización del predio:
     * 1. Calcula la superficie total en hectáreas con precisión geodésica.
     * 2. Aplica el estilo visual de polígono finalizado con etiqueta de área.
     * 3. Exporta la geometría en formato GeoJSON al campo `#geometry` del formulario.
     * 4. Habilita el botón de autodetección territorial (`#detect-location`).
     * 5. Desactiva la interacción de dibujo para evitar trazos involuntarios.
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
     * Remueve cualquier interacción de dibujo activa del mapa.
     */
    removeExistingDrawInteraction() {
        if (this.draw) {
            this.map.removeInteraction(this.draw);
        }
    }

    /**
     * Restaura los estados de control de dibujo.
     */
    resetDrawingState() {
        this.drawingFeature = null;
        this.updateAreaDisplay(0);
    }

    /**
     * Cálculo geodésico de superficie mediante Turf.js:
     * 
     * Procedimiento matemático:
     * 1. Clona la geometría de OpenLayers y la proyecta de Web Mercator (EPSG:3857) a WGS84 Geográfico (EPSG:4326).
     * 2. Construye un Feature GeoJSON compatible con Turf.js (`turf.polygon`).
     * 3. Ejecuta `turf.area(turfFeature)`, que utiliza el algoritmo de Karney sobre el esferoide terrestre WGS84
     *    para calcular el área real en metros cuadrados (m²).
     * 4. Convierte metros cuadrados a hectáreas dividiendo entre 10,000 (`areaM2 / 10000`).
     * 5. Retorna el valor en punto flotante con 6 decimales de precisión.
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
            // Proyección al elipsoide geodésico WGS84
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
            
            // Área geodésica en metros cuadrados
            const areaM2 = turf.area(turfFeature);
            
            if (isNaN(areaM2) || areaM2 <= 0) {
                return 0;
            }
            
            // Conversión m² a hectáreas
            const areaHa = areaM2 / 10000;
            
            return parseFloat(areaHa.toFixed(6));
            
        } catch (error) {
            console.error('Error en cálculo de área:', error);
            this.showAlert('Error calculando área', 'error');
            return 0;
        }
    }

    /**
     * Actualiza el cálculo del área sobre la geometría que está siendo trazada en ese instante.
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
     * Escribe la cifra calculada de hectáreas en la caja de texto del formulario web (`#area_ha`).
     */
    updateAreaDisplay(areaHa) {
        const areaInput = document.getElementById('area_ha');
        if (areaInput) {
            areaInput.value = areaHa > 0 ? areaHa.toFixed(6) : '';
        }
    }

    /**
     * Serializa la geometría a formato GeoJSON estándar en coordenadas WGS84 (EPSG:4326):
     * Asigna la cadena JSON resultante en el input oculto `#geometry` para ser enviada en el POST HTTP al servidor.
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
     * Alterna la capa base visible (OSM, satélite, relieve, etc.) ocultando las restantes.
     */
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
    }

    /**
     * Limpia completamente el mapa: borra geometrías, vacía los campos de texto del formulario
     * y oculta los paneles de información geográfica.
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
     * Construye y dibuja un polígono a partir de una lista de coordenadas UTM ingresadas por teclado:
     * 1. Convierte cada vértice [Este, Norte, Zona, Hemisferio] a coordenadas WGS84 (EPSG:4326) con Proj4.
     * 2. Verifica la validez de rango geográfico de las coordenadas resultantes.
     * 3. Garantiza que el polígono sea cerrado (primer vértice idéntico al último).
     * 4. Genera el Feature en el mapa y enfoca la vista (Fit View) con animación suave.
     *
     * @param array utmCoordinates Matriz de vértices [[Easting, Northing, Zone, Hemisphere], ...]
     */
    drawFromUTMCoordinates(utmCoordinates) {
        try {
            const wgs84Coordinates = utmCoordinates.map(coord => {
                const [easting, northing, zone, hemisphere] = coord;
                const sourceEpsg = this.setupUTMProjection(zone, hemisphere);
                return proj4(sourceEpsg, 'EPSG:4326', [easting, northing]);
            });

            // Validar que las coordenadas resultantes pertenezcan a la esfera terrestre (-180 a 180, -90 a 90)
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
     * Regla topológica cartográfica: asegura que el anillo exterior del polígono sea un circuito cerrado.
     * Si el último vértice no coincide exactamente con el primero, lo duplica al final del arreglo.
     */
    closePolygonIfNeeded(coordinates) {
        const firstCoord = coordinates[0];
        const lastCoord = coordinates[coordinates.length - 1];
        
        if (firstCoord[0] !== lastCoord[0] || firstCoord[1] !== lastCoord[1]) {
            coordinates.push(firstCoord);
        }
    }

    /**
     * Dibuja en la capa vectorial el polígono generado por coordenadas WGS84:
     * - Transforma las coordenadas de EPSG:4326 a EPSG:3857 para representación visual.
     * - Calcula su superficie con Turf.js.
     * - Ajusta el encuadre del mapa para centrar el predio con un margen (padding) de 50px.
     * - Notifica al usuario las zonas UTM involucradas.
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
        
        // Enfocar la cámara sobre el polígono con animación de 1 segundo
        this.map.getView().fit(
            feature.getGeometry().getExtent(),
            { padding: [50, 50, 50, 50], duration: 1000 }
        );
        
        this.convertToGeoJSON(feature, areaHa);
        
        // Identificar zonas UTM utilizadas para reporte en el mensaje
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
     * Despliega mensajes toast al usuario mediante SweetAlert2 o alert nativo como fallback.
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

// =========================================================================
// Inicialización del Motor Cartográfico al Cargar el DOM
// =========================================================================

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('map')) {
        window.polygonMapInstance = new PolygonMap();
    }
});
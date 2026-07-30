<div>
    <!-- ============================================ -->
    <!-- MAPA: dentro de wire:ignore (no se re-renderiza) -->
    <!-- ============================================ -->
    <div wire:ignore>
        <div id="{{ $mapId }}" style="height: 60vh; border: 1px solid #dededeff; border-radius: 0.5rem; margin-top: 0.5rem;"></div>
    </div>

    <!-- ============================================ -->
    <!-- CAMPOS DEL FORMULARIO: FUERA de wire:ignore  -->
    <!-- ============================================ -->

    @if($showCoordinates)
        <div class="grid grid-cols-2 gap-4 mt-3">
            <div>
                <x-input-label for="latitude_{{ $mapId }}" :value="__('Latitud')" />
                <x-text-input id="latitude_{{ $mapId }}" type="text" wire:model="latitude" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700" disabled />
            </div>
            <div>
                <x-input-label for="longitude_{{ $mapId }}" :value="__('Longitud')" />
                <x-text-input id="longitude_{{ $mapId }}" type="text" wire:model="longitude" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700" disabled />
            </div>
        </div>
    @endif

    @if($showAddress)
        <div class="mt-3">
            <x-input-label for="address_{{ $mapId }}" :value="__('Dirección')" />
            <div class="relative">
                <x-text-input id="address_{{ $mapId }}" type="text" wire:model="address" class="block mt-1 w-full" placeholder="{{ $placeholder }}" />
                <!-- Indicador de carga -->
                <div wire:loading wire:target="setLocation" class="absolute right-2 top-1/2 transform -translate-y-1/2">
                    <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </div>
            </div>
        </div>
    @endif

    @if($showLocateButton)
        <button type="button" id="locate-user-{{ $mapId }}"
            class="mt-3 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
            Usar mi ubicación
        </button>
    @endif

    <!-- ============================================ -->
    <!-- ESTILOS Y SCRIPTS (DENTRO DEL ELEMENTO RAÍZ) -->
    <!-- ============================================ -->
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/css/ol.css">
    @endpush

    @push('scripts')
        @once
            <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.8.0/proj4.js"></script>
            <script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/build/ol.js"></script>

            <script>
                // ============================================
                // FUNCIÓN GLOBAL PARA INICIALIZAR EL MAPA
                // ============================================
                window.initLocationPicker = function(config) {
                    console.log(' initLocationPicker llamado con config:', config);

                    const {
                        mapId,
                        initialLat,
                        initialLng,
                        initialZoom,
                        defaultCenterLon,
                        defaultCenterLat,
                        componentId,
                        showAddress,
                        showCoordinates,
                        showLocateButton,
                        placeholder
                    } = config;

                    const mapElement = document.getElementById(mapId);
                    console.log(' Elemento del mapa encontrado:', mapElement);

                    if (!mapElement) {
                        console.error(' No se encontró el elemento con id:', mapId);
                        return;
                    }

                    if (mapElement._mapInitialized) {
                        console.log(' El mapa ya estaba inicializado');
                        return;
                    }
                    mapElement._mapInitialized = true;

                    const lon = (initialLng !== null && initialLng !== undefined) ? initialLng : defaultCenterLon;
                    const lat = (initialLat !== null && initialLat !== undefined) ? initialLat : defaultCenterLat;

                    console.log(' Centro inicial:', { lon, lat });

                    // Verificar que ol esté definido
                    if (typeof ol === 'undefined') {
                        console.error(' OpenLayers no está cargado');
                        return;
                    }

                    const map = new ol.Map({
                        target: mapId,
                        layers: [
                            new ol.layer.Tile({
                                source: new ol.source.OSM()
                            })
                        ],
                        view: new ol.View({
                            center: ol.proj.fromLonLat([lon, lat]),
                            zoom: initialZoom || 13
                        })
                    });

                    console.log(' Mapa creado correctamente');

                    // Capa de marcador
                    const markerLayer = new ol.layer.Vector({
                        source: new ol.source.Vector(),
                        style: new ol.style.Style({
                            image: new ol.style.Circle({
                                radius: 8,
                                fill: new ol.style.Fill({ color: '#dc2626' }),
                                stroke: new ol.style.Stroke({ color: '#ffffff', width: 2 })
                            })
                        })
                    });
                    map.addLayer(markerLayer);

                    function placeMarker(coordinate) {
                        markerLayer.getSource().clear();
                        const feature = new ol.Feature({
                            geometry: new ol.geom.Point(coordinate)
                        });
                        markerLayer.getSource().addFeature(feature);
                        console.log(' Marcador colocado en:', coordinate);
                    }

                    // ============================================
                    // GEOCODIFICACIÓN INVERSA (SIN PAÍS)
                    // ============================================
                    function reverseGeocode(lat, lng, callback) {
                        const url =
                            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1&accept-language=es`;

                        console.log(' Consultando Nominatim:', url);

                        fetch(url)
                            .then(response => response.json())
                            .then(data => {
                                if (data && data.address) {
                                    const addr = data.address;

                                    let parroquia = addr.municipality || '';
                                    let municipio = addr.county || addr.state_district || addr.region || '';
                                    let estado = addr.state || addr.region || '';

                                    const cleanParroquia = removePrefixes(parroquia, ['Parroquia', 'Sector', 'Zona']);
                                    const cleanMunicipio = removePrefixes(municipio, ['Municipio', 'Distrito',
                                        'County'
                                    ]);
                                    const cleanEstado = removePrefixes(estado, ['Estado', 'State', 'Departamento']);

                                    const components = {
                                        parroquia: cleanParroquia,
                                        municipio: cleanMunicipio,
                                        estado: cleanEstado,
                                    };

                                    const parts = [];
                                    if (components.parroquia) parts.push(components.parroquia);
                                    if (components.municipio) parts.push(components.municipio);
                                    if (components.estado) parts.push(components.estado);
                                    const concise = parts.join(', ') || 'Dirección no encontrada';

                                    console.log(' Componentes extraídos:', components);
                                    console.log(' Dirección construida:', concise);

                                    callback(concise, components);
                                } else {
                                    callback('Dirección no encontrada', {
                                        parroquia: '',
                                        municipio: '',
                                        estado: '',
                                    });
                                }
                            })
                            .catch((error) => {
                                console.error('Error en geocodificación:', error);
                                callback('Error al obtener dirección', {
                                    parroquia: '',
                                    municipio: '',
                                    estado: '',
                                });
                            });
                    }

                    // ============================================
                    // FUNCIÓN AUXILIAR PARA LIMPIAR PREFIJOS
                    // ============================================
                    function removePrefixes(str, prefixes) {
                        if (!str) return '';

                        let result = str.trim();

                        prefixes.forEach(prefix => {
                            const regex = new RegExp(`^${prefix}\\s+`, 'i');
                            if (regex.test(result)) {
                                const match = result.match(regex);
                                if (match) {
                                    result = result.substring(match[0].length);
                                }
                            }
                        });

                        result = result.replace(/\s+/g, ' ').trim();
                        return result;
                    }

                    // ============================================
                    // EVENTOS DEL MAPA
                    // ============================================

                    // Click en el mapa
                    map.on('click', function(evt) {
                        const coord = evt.coordinate;
                        const lonLat = ol.proj.toLonLat(coord);
                        const lat = lonLat[1];
                        const lng = lonLat[0];

                        console.log(' Click en mapa:', { lat, lng });

                        placeMarker(coord);

                        reverseGeocode(lat, lng, function(address, components) {
                            if (window.Livewire) {
                                Livewire.find(componentId).call('setLocation', lat, lng, address,
                                components);
                            }
                        });
                    });

                    // Botón "Usar mi ubicación"
                    if (showLocateButton) {
                        const locateBtn = document.getElementById(`locate-user-${mapId}`);
                        if (locateBtn) {
                            locateBtn.addEventListener('click', function() {
                                if (navigator.geolocation) {
                                    navigator.geolocation.getCurrentPosition(function(position) {
                                        const lat = position.coords.latitude;
                                        const lng = position.coords.longitude;
                                        const coord = ol.proj.fromLonLat([lng, lat]);

                                        console.log(' Ubicación del navegador:', { lat, lng });

                                        placeMarker(coord);
                                        map.getView().setCenter(coord);
                                        map.getView().setZoom(15);

                                        if (showAddress) {
                                            const addressInput = document.getElementById(
                                                `address_${mapId}`);
                                            if (addressInput) addressInput.value =
                                                'Buscando dirección...';
                                        }

                                        reverseGeocode(lat, lng, function(address, components) {
                                            if (window.Livewire) {
                                                Livewire.find(componentId).call('setLocation',
                                                    lat, lng, address, components);
                                            }
                                        });
                                    }, function(error) {
                                        alert('No se pudo obtener tu ubicación: ' + error
                                        .message);
                                    });
                                } else {
                                    alert('Tu navegador no soporta geolocalización.');
                                }
                            });
                        }
                    }

                    // Cargar ubicación inicial si existe
                    if (initialLat !== null && initialLat !== undefined && initialLng !== null && initialLng !==
                        undefined) {
                        const coord = ol.proj.fromLonLat([parseFloat(initialLng), parseFloat(initialLat)]);
                        console.log(' Cargando ubicación inicial:', { initialLat, initialLng });
                        placeMarker(coord);
                        map.getView().setCenter(coord);
                        map.getView().setZoom(15);
                    }

                    mapElement._map = map;
                    console.log(' Mapa completamente inicializado');
                };

                if (!window._locationPickerConfigs) {
                    window._locationPickerConfigs = [];
                }
            </script>
        @endonce

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                console.log('📄 DOMContentLoaded - Inicializando LocationPicker');

                const config = {
                    mapId: '{{ $mapId }}',
                    initialLat: {{ $latitude ?? 'null' }},
                    initialLng: {{ $longitude ?? 'null' }},
                    initialZoom: {{ $initialZoom }},
                    defaultCenterLon: {{ $initialCenterLon }},
                    defaultCenterLat: {{ $initialCenterLat }},
                    componentId: '{{ $this->getId() }}',
                    showAddress: {{ $showAddress ? 'true' : 'false' }},
                    showCoordinates: {{ $showCoordinates ? 'true' : 'false' }},
                    showLocateButton: {{ $showLocateButton ? 'true' : 'false' }},
                    placeholder: '{{ $placeholder }}'
                };

                console.log(' Configuración:', config);

                window._locationPickerConfigs.push(config);

                if (window.initLocationPicker) {
                    setTimeout(() => {
                        console.log(' Ejecutando initLocationPicker...');
                        window.initLocationPicker(config);
                    }, 100);
                } else {
                    console.error(' initLocationPicker no está definida');
                }
            });
        </script>
    @endpush
</div>
<div wire:ignore>
    <div id="{{ $mapId }}" style="height: 60vh; border: 1px solid #dededeff; border-radius: 0.5rem; margin-top: 0.5rem;"></div>

    @if($showCoordinates)
        <div class="grid grid-cols-2 gap-4 mt-3">
            <div>
                <x-input-label for="latitude_{{ $mapId }}" :value="__('Latitud')" />
                <x-text-input id="latitude_{{ $mapId }}" type="text" wire:model="latitude" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700 cursor-not-allowed" disabled />
            </div>
            <div>
                <x-input-label for="longitude_{{ $mapId }}" :value="__('Longitud')" />
                <x-text-input id="longitude_{{ $mapId }}" type="text" wire:model="longitude" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700 cursor-not-allowed" disabled />
            </div>
        </div>
    @endif

    @if($showAddress)
        <div class="mt-3">
            <x-input-label for="address_{{ $mapId }}" :value="__('Dirección')" />
            <x-text-input id="address_{{ $mapId }}" type="text" wire:model="address" class="block mt-1 w-full" placeholder="{{ $placeholder }}" />
        </div>
    @endif

    @if($showLocateButton)
        <button type="button" id="locate-user-{{ $mapId }}"
            class="mt-3 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
            Usar mi ubicación
        </button>
    @endif
</div>

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/css/ol.css">
@endpush

@push('scripts')
    @once
        <!-- Dependencias: proj4js primero, luego OpenLayers -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.8.0/proj4.js"></script>
        <script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/build/ol.js"></script>

        <script>
            // Función global para inicializar mapas (se define una sola vez)
            window.initLocationPicker = function(config) {
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
                if (!mapElement) return;
                if (mapElement._mapInitialized) return;
                mapElement._mapInitialized = true;

                // Centro inicial (Carúpano por defecto)
                const lon = (initialLng !== null && initialLng !== undefined) ? initialLng : defaultCenterLon;
                const lat = (initialLat !== null && initialLat !== undefined) ? initialLat : defaultCenterLat;

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
                }

                // Geocodificación inversa (dirección concisa)
                function reverseGeocode(lat, lng, callback) {
                    const url =
                        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            if (data && data.address) {
                                const addr = data.address;
                                const parts = [];
                                if (addr.road) parts.push(addr.road);
                                if (addr.city || addr.town || addr.village) {
                                    parts.push(addr.city || addr.town || addr.village);
                                }
                                if (addr.state) parts.push(addr.state);
                                if (addr.country) parts.push(addr.country);
                                callback(parts.join(', ') || 'Dirección no encontrada');
                            } else {
                                callback('Dirección no encontrada');
                            }
                        })
                        .catch(() => {
                            callback('Error al obtener dirección');
                        });
                }

                // Click en el mapa
                map.on('click', function(evt) {
                    const coord = evt.coordinate;
                    const lonLat = ol.proj.toLonLat(coord);
                    const lat = lonLat[1];
                    const lng = lonLat[0];

                    placeMarker(coord);

                    if (showAddress) {
                        const addressInput = document.getElementById(`address_${mapId}`);
                        if (addressInput) addressInput.value = 'Buscando dirección...';
                    }

                    reverseGeocode(lat, lng, function(address) {
                        if (window.Livewire) {
                            Livewire.find(componentId).call('setLocation', lat, lng, address);
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

                                    placeMarker(coord);
                                    map.getView().setCenter(coord);
                                    map.getView().setZoom(15);

                                    if (showAddress) {
                                        const addressInput = document.getElementById(
                                            `address_${mapId}`);
                                        if (addressInput) addressInput.value = 'Buscando dirección...';
                                    }

                                    reverseGeocode(lat, lng, function(address) {
                                        if (window.Livewire) {
                                            Livewire.find(componentId).call('setLocation', lat,
                                                lng, address);
                                        }
                                    });
                                }, function(error) {
                                    alert('No se pudo obtener tu ubicación: ' + error.message);
                                });
                            } else {
                                alert('Tu navegador no soporta geolocalización.');
                            }
                        });
                    }
                }

                // Cargar ubicación inicial si existe (sin marcador por defecto)
                if (initialLat !== null && initialLat !== undefined && initialLng !== null && initialLng !==
                    undefined) {
                    const coord = ol.proj.fromLonLat([parseFloat(initialLng), parseFloat(initialLat)]);
                    placeMarker(coord);
                    map.getView().setCenter(coord);
                    map.getView().setZoom(15);
                }

                // Guardar referencia al mapa
                mapElement._map = map;
            };

            // Registrar configuraciones para inicializar al cargar la página
            if (!window._locationPickerConfigs) {
                window._locationPickerConfigs = [];
            }
        </script>
    @endonce

    <!-- Inicialización específica para esta instancia -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

            window._locationPickerConfigs.push(config);

            // Si la función ya está definida, inicializar inmediatamente
            if (window.initLocationPicker) {
                setTimeout(() => {
                    window.initLocationPicker(config);
                }, 100);
            }
        });
    </script>
@endpush
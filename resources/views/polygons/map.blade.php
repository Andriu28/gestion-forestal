{{-- [file name]: map.blade.php --}}
<x-app-layout>
    <div class="">
        <div class="max-w-7xl mx-auto">
            <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden shadow-sm sm:rounded-2xl shadow-soft p-4 md:p-6 lg:p-8">
                <div class="text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="font-semibold text-xl leading-tight">
                            {{ __('Mapa de Polígonos') }}
                        </h2>
                        <a href="{{ route('polygons.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                            </svg>
                            <span>{{ __('Ver Lista') }}</span>
                        </a>
                    </div>

                    <!-- Mapa -->
                    <div id="map" style="height: 70vh; border: 1px solid #dededeff; border-radius: 0.5rem; position: relative;"></div>

                    <!-- Leyenda -->
                    <div class="mt-4 flex flex-wrap gap-4 items-center">
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-blue-500 border-2 border-blue-700 mr-2"></div>
                            <span class="text-sm text-gray-700 dark:text-gray-300">Polígonos con productor</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-green-500 border-2 border-green-700 mr-2"></div>
                            <span class="text-sm text-gray-700 dark:text-gray-300">Polígonos sin productor</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Incluir OpenLayers -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/css/ol.css">
    <script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/build/ol.js"></script>

    <script>
        let map;
        let polygonsLayer;

        function initMap() {
            // Capa base OSM
            const osmLayer = new ol.layer.Tile({
                source: new ol.source.OSM()
            });

            // Capa para los polígonos
            polygonsLayer = new ol.layer.Vector({
                source: new ol.source.Vector(),
                style: function(feature) {
                    const type = feature.get('type');
                    return new ol.style.Style({
                        fill: new ol.style.Fill({
                            color: type === 'with_producer' ? 'rgba(59, 130, 246, 0.3)' : 'rgba(34, 197, 94, 0.3)'
                        }),
                        stroke: new ol.style.Stroke({
                            color: type === 'with_producer' ? '#1d4ed8' : '#15803d',
                            width: 2
                        })
                    });
                }
            });

            // Mapa
            map = new ol.Map({
                target: 'map',
                layers: [osmLayer, polygonsLayer],
                view: new ol.View({
                    center: ol.proj.fromLonLat([-66.9036, 10.4806]), // Centro de Venezuela
                    zoom: 6
                })
            });

            // Cargar polígonos
            loadPolygons();

            // Popup para mostrar información
            const popup = new ol.Overlay({
                element: document.getElementById('popup'),
                positioning: 'bottom-center',
                stopEvent: false
            });
            map.addOverlay(popup);

            map.on('click', function(evt) {
                const feature = map.forEachFeatureAtPixel(evt.pixel, function(feature) {
                    return feature;
                });

                if (feature) {
                    const properties = feature.getProperties();
                    const coordinate = evt.coordinate;

                    const popupContent = `
                        <div class="p-3 bg-white dark:bg-gray-800 rounded-lg shadow-lg max-w-xs">
                            <h3 class="font-bold text-lg mb-2 text-gray-900 dark:text-white">${properties.name}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">
                                <strong>Productor:</strong> ${properties.producer}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">
                                <strong>Área:</strong> ${properties.area_ha ? properties.area_ha.toFixed(2) + ' Ha' : 'N/A'}
                            </p>
                            ${properties.description ? `<p class="text-sm text-gray-600 dark:text-gray-300">${properties.description}</p>` : ''}
                            <div class="mt-2">
                                <a href="/polygons/${properties.id}" class="text-blue-600 hover:text-blue-800 text-sm">Ver detalles</a>
                            </div>
                        </div>
                    `;

                    popup.getElement().innerHTML = popupContent;
                    popup.setPosition(coordinate);
                } else {
                    popup.setPosition(undefined);
                }
            });
        }

        function loadPolygons() {
            fetch('{{ route("polygons.geojson") }}')
                .then(response => response.json())
                .then(data => {
                    const features = new ol.format.GeoJSON().readFeatures(data, {
                        featureProjection: 'EPSG:3857'
                    });

                    polygonsLayer.getSource().clear();
                    polygonsLayer.getSource().addFeatures(features);

                    // Ajustar vista para mostrar todos los polígonos
                    if (features.length > 0) {
                        const extent = polygonsLayer.getSource().getExtent();
                        map.getView().fit(extent, { padding: [50, 50, 50, 50], maxZoom: 15 });
                    }
                })
                .catch(error => {
                    console.error('Error loading polygons:', error);
                });
        }

        // Inicializar mapa cuando el documento esté listo
        document.addEventListener('DOMContentLoaded', initMap);
    </script>

    <!-- Elemento popup -->
    <div id="popup" class="ol-popup absolute bg-white rounded-lg shadow-lg z-10 hidden"></div>

    <style>
        .ol-popup {
            bottom: 12px;
            left: -50px;
            min-width: 280px;
        }
    </style>
</x-app-layout>
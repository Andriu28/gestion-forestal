{{-- [file name]: map.blade.php --}}
<x-app-layout>
    <div class="">
        <div class="mx-auto">
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
        let ownerOverlays = []; // overlays para las burbujas de propietario

        function initMap() {
            const osmLayer = new ol.layer.Tile({
                source: new ol.source.OSM()
            });

            // Capa para los polígonos (sin texto en la geometría)
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

            map = new ol.Map({
                target: 'map',
                layers: [osmLayer, polygonsLayer],
                view: new ol.View({
                    center: ol.proj.fromLonLat([-66.9036, 10.4806]),
                    zoom: 6
                })
            });

            loadPolygons();

            // Popup de click (mantener)
            const popup = new ol.Overlay({
                element: document.getElementById('popup'),
                positioning: 'bottom-center',
                stopEvent: false,
                offset: [0, -12]
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
                        <div class="p-3">
                            <h3 class="font-bold text-lg mb-2 text-gray-900 dark:text-white">${properties.name}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">
                                <strong>Productor:</strong> ${properties.producer || 'N/A'}
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

                    const el = popup.getElement();
                    const contentEl = el.querySelector('.popup-content');
                    if (contentEl) contentEl.innerHTML = popupContent;
                    popup.setPosition(coordinate);
                } else {
                    popup.setPosition(undefined);
                }
            });
        }

        function clearOwnerOverlays() {
            if (!ownerOverlays || ownerOverlays.length === 0) return;
            ownerOverlays.forEach(o => map.removeOverlay(o));
            ownerOverlays = [];
        }

        function createOwnerBubbleOverlay(feature) {
            // calcular punto interior (en EPSG:3857)
            const geom = feature.getGeometry();
            let interiorPoint = null;
            if (geom.getInteriorPoint) {
                interiorPoint = geom.getInteriorPoint().getCoordinates();
            } else {
                interiorPoint = geom.getClosestPoint(geom.getExtent());
            }

            // propiedades para mostrar
            const producer = feature.get('producer') || '';
            const name = feature.get('name') || '';
            let label = producer || name;
            if (label.length > 26) label = label.slice(0, 23) + '...';

            // crear elemento DOM para la burbuja
            const container = document.createElement('div');
            container.className = 'owner-bubble-wrapper';
            container.innerHTML = `
                <div class="owner-bubble">
                    <div class="owner-text">${escapeHtml(label)}</div>
                </div>
                <div class="owner-pin" aria-hidden="true"></div>
            `;

            // overlay con offset para que la flecha/pin encaje sobre el punto
            const overlay = new ol.Overlay({
                element: container,
                position: interiorPoint,
                positioning: 'bottom-center',
                stopEvent: false,
                offset: [0, -10]
            });

            map.addOverlay(overlay);
            ownerOverlays.push(overlay);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(text));
            return div.innerHTML;
        }

        function loadPolygons() {
            fetch('{{ route("polygons.geojson") }}')
                .then(response => response.json())
                .then(data => {
                    const features = new ol.format.GeoJSON().readFeatures(data, {
                        dataProjection: 'EPSG:4326',
                        featureProjection: 'EPSG:3857'
                    });

                    // limpiar capa y overlays previos
                    polygonsLayer.getSource().clear();
                    clearOwnerOverlays();

                    polygonsLayer.getSource().addFeatures(features);

                    // crear overlays (burbujas) para cada feature
                    features.forEach(f => {
                        // solo crear burbuja si tiene nombre o productor
                        if (f.get('producer') || f.get('name')) {
                            createOwnerBubbleOverlay(f);
                        }
                    });

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

    <!-- Elemento popup con contenedor y flecha -->
    <div id="popup" class="ol-popup absolute z-10">
        <div class="popup-content bg-gray-50 dark:bg-gray-800 rounded-lg shadow-lg"></div>
        <div class="popup-arrow" aria-hidden="true"></div>
    </div>

   <!-- Reemplaza el CSS existente desde la línea que comienza con "/* Estilos para la burbuja tipo Google Maps */" -->
<style>
    /* Popup base (mantener existente) */
    .ol-popup {
        position: absolute;
        transform: translateX(-50%);
        min-width: 260px;
        max-width: 320px;
        left: 50%;
        bottom: 12px;
        pointer-events: auto;
        z-index: 1000;
    }

    /* Contenido: fondo redondeado y sombra */
    .ol-popup .popup-content {
        border-radius: 12px;
        padding: 12px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.12);
        border: 1px solid rgba(0,0,0,0.06);
        color: #111827;
        background-clip: padding-box;
    }

    /* Flecha: cuadro rotado 45deg para simular triángulo */
    .ol-popup .popup-arrow {
        width: 16px;
        height: 16px;
        background: #ffffff;
        border-left: 1px solid rgba(0,0,0,0.06);
        border-top: 1px solid rgba(0,0,0,0.06);
        transform: translateX(-50%) rotate(45deg);
        position: absolute;
        left: 50%;
        bottom: -8px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.06);
    }

    /* NUEVOS ESTILOS MEJORADOS PARA INDICADORES */
    .owner-bubble-wrapper {
        display: flex;
        align-items: center;
        flex-direction: column;
        pointer-events: auto;
        transform: translateY(-4px);
        filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.15));
    }

    .owner-bubble {
        background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);
        border-radius: 10px;
        padding: 6px 10px;
        border: 1px solid #e5e7eb;
        display: inline-block;
        max-width: 200px;
        text-align: center;
        position: relative;
        z-index: 2;
        transition: all 0.2s ease;
    }

    /* Efecto hover sutil */
    .owner-bubble-wrapper:hover .owner-bubble {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        border-color: #d1d5db;
    }

    .owner-bubble .owner-text {
        color: #111827;
        font-weight: 600;
        font-size: 11px;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        letter-spacing: 0.01em;
    }

    /* Pin mejorado - diseño más limpio */
    .owner-pin {
        width: 18px;
        height: 18px;
        background: #3b82f6; /* Azul más profesional */
        transform: rotate(45deg);
        margin-top: -9px;
        border-radius: 2px;
        border: 2px solid white;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        position: relative;
        z-index: 1;
    }

    /* Círculo central del pin */
    .owner-pin::after {
        content: '';
        display: block;
        width: 6px;
        height: 6px;
        background: white;
        border-radius: 50%;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
    }

    /* Indicador para polígonos sin productor (verde) */
    .owner-bubble-wrapper.no-producer .owner-bubble {
        background: linear-gradient(180deg, #f0fdf4 0%, #ecfdf5 100%);
        border-color: #bbf7d0;
    }
    
    .owner-bubble-wrapper.no-producer .owner-pin {
        background: #10b981; /* Verde para sin productor */
    }

    /* Indicador para polígonos con productor (azul) */
    .owner-bubble-wrapper.with-producer .owner-bubble {
        background: linear-gradient(180deg, #eff6ff 0%, #dbeafe 100%);
        border-color: #bfdbfe;
    }
    
    .owner-bubble-wrapper.with-producer .owner-pin {
        background: #3b82f6; /* Azul para con productor */
    }

    /* Modo oscuro mejorado */
    @media (prefers-color-scheme: dark) {
        .ol-popup .popup-content {
            background: #1f2937;
            color: #e5e7eb;
            border: 1px solid rgba(255,255,255,0.04);
        }
        
        .ol-popup .popup-arrow {
            background: #1f2937;
            border-left: 1px solid rgba(255,255,255,0.04);
            border-top: 1px solid rgba(255,255,255,0.04);
        }

        .owner-bubble {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            border: 1px solid #334155;
            color: #f8fafc;
        }

        .owner-bubble .owner-text {
            color: #f1f5f9;
        }

        /* Modo oscuro - sin productor */
        .owner-bubble-wrapper.no-producer .owner-bubble {
            background: linear-gradient(180deg, #064e3b 0%, #022c22 100%);
            border-color: #065f46;
        }
        
        .owner-bubble-wrapper.no-producer .owner-pin {
            background: #10b981;
            border-color: #1e293b;
        }

        /* Modo oscuro - con productor */
        .owner-bubble-wrapper.with-producer .owner-bubble {
            background: linear-gradient(180deg, #464f69ff 0%, #323542ff 100%);
            border-color: #888888ff;
        }
        
        .owner-bubble-wrapper.with-producer .owner-pin {
            background: #3b82f6;
            border-color: #1e293b;
        }

        .owner-pin {
            border-color: #1e293b;
        }
    }

    /* Responsive: textos más pequeños en pantallas medianas */
    @media (max-width: 1024px) {
        .owner-bubble .owner-text {
            font-size: 10px;
            padding: 6px 10px;
        }
        
        .owner-bubble {
            max-width: 160px;
        }
    }

    /* Agrega esto al final de tu sección <style> */
    .ol-overlay-container .owner-bubble-wrapper {
        transform: translate(0%, 24%) !important;
    }

    /* Animación sutil al cargar */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .owner-bubble-wrapper {
        animation: fadeInUp 0.3s ease-out;
    }
</style>

<!-- También actualiza la función JavaScript para aplicar las clases correctas -->
<script>
    function createOwnerBubbleOverlay(feature) {
        // calcular punto interior (en EPSG:3857)
        const geom = feature.getGeometry();
        let interiorPoint = null;
        if (geom.getInteriorPoint) {
            interiorPoint = geom.getInteriorPoint().getCoordinates();
        } else {
            interiorPoint = geom.getClosestPoint(geom.getExtent());
        }

        // propiedades para mostrar
        const producer = feature.get('producer') || '';
        const name = feature.get('name') || '';
        const type = feature.get('type') || 'with_producer';
        let label = producer || name;
        if (label.length > 22) label = label.slice(0, 20) + '...';

        // Determinar clase CSS según el tipo
        const typeClass = type === 'with_producer' ? 'with-producer' : 'no-producer';

        // crear elemento DOM para la burbuja
        const container = document.createElement('div');
        container.className = `owner-bubble-wrapper ${typeClass}`;
        container.innerHTML = `
            <div class="owner-bubble">
                <div class="owner-text">${escapeHtml(label)}</div>
            </div>
            <div class="owner-pin" aria-hidden="true"></div>
        `;

        // overlay con offset para que la flecha/pin encaje sobre el punto
        const overlay = new ol.Overlay({
            element: container,
            position: interiorPoint,
            positioning: 'bottom-center',
            stopEvent: false,
            offset: [0, -10]
        });

        map.addOverlay(overlay);
        ownerOverlays.push(overlay);
    }
</script>
</x-app-layout>
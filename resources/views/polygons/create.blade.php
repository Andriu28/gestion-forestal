{{-- [file name]: create.blade.php --}}
<x-app-layout>
    <div class="max-w-7xl mx-auto p-4">
        {{-- Añadir panel de debug (temporal) justo arriba del formulario para ver datos enviados / respuesta del servidor --}}
        @if(session('debug_info') || session('debug_error'))
            <div class="max-w-7xl mx-auto p-4">
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <p class="font-semibold">DEBUG servidor</p>
                    @if(session('debug_info'))
                        <pre class="text-sm">{{ json_encode(session('debug_info')) }}</pre>
                    @endif
                    @if(session('debug_error'))
                        <pre class="text-sm text-red-600">{{ session('debug_error') }}</pre>
                    @endif
                </div>
            </div>
        @endif

        <div class="bg-stone-100/90 dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl p-6">
            <div class="text-gray-900 dark:text-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold"><i class="fas fa-draw-polygon mr-2"></i> Crear Nuevo Polígono</h2>
                </div>

                <form action="{{ route('polygons.store') }}" method="POST" id="polygon-form" novalidate>
                    @csrf

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Formulario -->
                        <div class="space-y-6">
                            <div>
                                <x-input-label for="name" :value="__('Nombre del Polígono *')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                    :value="old('name')" required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            </div>

                            <div>
                                <x-input-label for="description" :value="__('Descripción')" />
                                <textarea id="description" name="description" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    placeholder="Descripción del polígono...">{{ old('description') }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('description')" />
                            </div>

                            <div>
                                <x-input-label for="producer_id" :value="__('Productor (Opcional)')" />
                                <select id="producer_id" name="producer_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">Seleccione un productor</option>
                                    @foreach($producers as $producer)
                                        <option value="{{ $producer->id }}" {{ old('producer_id') == $producer->id ? 'selected' : '' }}>
                                            {{ $producer->name }} {{ $producer->lastname }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('producer_id')" />
                            </div>

                            <div>
                                <x-input-label for="parish_id" :value="__('Parroquia (Opcional)')" />
                                <select id="parish_id" name="parish_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">Seleccione una parroquia</option>
                                    @foreach($parishes as $parish)
                                        <option value="{{ $parish->id }}" {{ old('parish_id') == $parish->id ? 'selected' : '' }}>
                                            {{ $parish->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('parish_id')" />
                            </div>

                            <div>
                                <x-input-label for="area_ha" :value="__('Área en Hectáreas')" />
                                <x-text-input id="area_ha" name="area_ha" type="number" step="0.01"
                                    class="mt-1 block w-full" :value="old('area_ha')" placeholder="Se calculará automáticamente si se deja vacío" />
                                <x-input-error class="mt-2" :messages="$errors->get('area_ha')" />
                            </div>

                            <!-- Campos ocultos para la detección y el envío -->
                            <input type="hidden" id="geometry" name="geometry" value="{{ old('geometry', '') }}" required>
                            <input type="hidden" id="detected_parish" name="detected_parish" value="{{ old('detected_parish') }}">
                            <input type="hidden" id="detected_municipality" name="detected_municipality" value="{{ old('detected_municipality') }}">
                            <input type="hidden" id="detected_state" name="detected_state" value="{{ old('detected_state') }}">
                            <input type="hidden" id="centroid_lat" name="centroid_lat" value="{{ old('centroid_lat') }}">
                            <input type="hidden" id="centroid_lng" name="centroid_lng" value="{{ old('centroid_lng') }}">
                            <input type="hidden" id="location_data" name="location_data" value="{{ old('location_data') }}">

                            <div id="location-info" class="p-4 bg-blue-50 dark:bg-blue-900 rounded-lg hidden">
                                <h3 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">📍 Ubicación detectada</h3>
                                <div class="text-sm space-y-1">
                                    <div><strong>Parroquia:</strong> <span id="detected-parish-text">-</span></div>
                                    <div><strong>Municipio:</strong> <span id="detected-municipality-text">-</span></div>
                                    <div><strong>Estado:</strong> <span id="detected-state-text">-</span></div>
                                    <div><strong>Coordenadas:</strong> <span id="detected-coords-text">-</span></div>
                                </div>
                            </div>
                        </div>

                        <!-- Mapa -->
                        <div>
                            <x-input-label for="map" :value="__('Dibujar Polígono en el Mapa *')" />
                            <div class="relative rounded-lg overflow-hidden mb-6 border border-gray-200 dark:border-gray-700 mt-1" style="height:420px;">
                                <div id="map" class="h-full w-full"></div>

                                <div class="absolute top-4 right-4 z-50 flex flex-col space-y-2">
                                    <button type="button" id="draw-polygon" class="bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-100 px-3 py-2 rounded-lg shadow-md hover:bg-gray-200">
                                        Dibujar
                                    </button>
                                    <button type="button" id="detect-location" class="bg-green-500 text-white px-3 py-2 rounded-lg shadow-md hover:bg-green-600" disabled>
                                        Detectar Ubicación
                                    </button>
                                    <button type="button" id="clear-map" class="bg-red-500 text-white px-3 py-2 rounded-lg shadow-md hover:bg-red-600">
                                        Limpiar
                                    </button>
                                </div>

                                <div class="absolute left-1/2 bottom-4 transform -translate-x-1/2 z-40 bg-white/90 dark:bg-gray-800 p-2 rounded text-sm shadow">
                                    <span id="coordinates-display">Lat: 0.000000 | Lng: 0.000000</span>
                                </div>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('geometry')" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-6 space-x-4">
                        <x-go-back-button />
                        <x-primary-button type="submit" id="submit-btn">Crear Polígono</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<!-- Estilos y librerías -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>

<script>
/* DEBUG: Añadidos puntos de alert/console para trazar flujo de creación y envío del polígono */
document.addEventListener('DOMContentLoaded', () => {
    alert('DEBUG: create.blade.js cargado'); console.log('DEBUG: script cargado');

    let map = L.map('map').setView([8.0, -66.0], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap contributors' }).addTo(map);

    const drawnItems = new L.FeatureGroup().addTo(map);
    const drawControl = new L.Control.Draw({
        draw: {
            polygon: { allowIntersection: false, showArea: true, shapeOptions: { color: '#2b6cb0', fillColor: '#2b6cb0', fillOpacity: 0.25, weight: 3 } },
            polyline: false, rectangle: false, circle: false, circlemarker: false, marker: false
        },
        edit: { featureGroup: drawnItems }
    });

    map.addControl(drawControl);

    const drawBtn = document.getElementById('draw-polygon');
    const detectBtn = document.getElementById('detect-location');
    const clearBtn = document.getElementById('clear-map');
    const geometryInput = document.getElementById('geometry');
    const coordsDisplay = document.getElementById('coordinates-display');

    function showMessage(text, type = 'info') {
        const el = coordsDisplay;
        el.textContent = text;
        el.classList.add(type === 'error' ? 'text-red-600' : 'text-green-700');
        setTimeout(() => {
            if (!geometryInput.value) el.textContent = 'Lat: 0.000000 | Lng: 0.000000';
        }, 3000);
    }

    function setFeatureToInput(feature) {
        if (!feature) {
            geometryInput.value = '';
            return;
        }
        try {
            geometryInput.value = JSON.stringify(feature);
            alert('DEBUG: GeoJSON guardado en input geometry (length: ' + geometryInput.value.length + ')');
            console.log('DEBUG: geometry input value:', geometryInput.value);
        } catch (e) {
            geometryInput.value = '';
            console.error('Error serializing feature', e);
            alert('DEBUG: Error serializing feature: ' + e.message);
        }
    }

    function calcCentroidFromFeature(feature) {
        try {
            const coords = feature.geometry.coordinates;
            let ring = null;
            if (feature.geometry.type === 'Polygon') ring = coords[0];
            else if (feature.geometry.type === 'MultiPolygon') ring = coords[0][0];
            if (!ring || ring.length === 0) return null;
            let latSum = 0, lngSum = 0, count = 0;
            ring.forEach(pt => { lngSum += pt[0]; latSum += pt[1]; count++; });
            return { lat: latSum / count, lng: lngSum / count };
        } catch (e) {
            return null;
        }
    }

    drawBtn.addEventListener('click', () => {
        alert('DEBUG: click Dibujar - iniciando L.Draw.Polygon');
        new L.Draw.Polygon(map, drawControl.options.draw.polygon).enable();
    });

    clearBtn.addEventListener('click', () => {
        drawnItems.clearLayers();
        geometryInput.value = '';
        detectBtn.disabled = true;
        document.getElementById('location-info').classList.add('hidden');
        coordsDisplay.textContent = 'Lat: 0.000000 | Lng: 0.000000';
        showMessage('Mapa limpio', 'info');
        alert('DEBUG: Mapa limpiado y campo geometry vaciado');
        console.log('DEBUG: cleared geometry input');
    });

    map.on(L.Draw.Event.CREATED, function (e) {
        const layer = e.layer;
        drawnItems.clearLayers();
        drawnItems.addLayer(layer);

        const feature = layer.toGeoJSON(); // Feature
        setFeatureToInput(feature);

        detectBtn.disabled = false;
        const centr = calcCentroidFromFeature(feature);
        if (centr) coordsDisplay.textContent = `Lat: ${centr.lat.toFixed(6)} | Lng: ${centr.lng.toFixed(6)}`;

        // DEBUG: mostrar primer punto y tipo
        try {
            const coords0 = feature.geometry.coordinates[0] && feature.geometry.coordinates[0][0] ? feature.geometry.coordinates[0][0] : null;
            alert('DEBUG: Polígono creado. type=' + feature.geometry.type + ' firstPoint=' + JSON.stringify(coords0));
            console.log('DEBUG: feature creado', feature);
        } catch (err) {
            console.error('DEBUG: error al leer feature', err);
        }
    });

    map.on('mousemove', (e) => {
        coordsDisplay.textContent = `Lat: ${e.latlng.lat.toFixed(6)} | Lng: ${e.latlng.lng.toFixed(6)}`;
    });

    detectBtn.addEventListener('click', async () => {
        alert('DEBUG: Detectar Ubicación clickeado');
        const val = geometryInput.value;
        if (!val) { showMessage('Dibuja un polígono primero', 'error'); alert('DEBUG: detect abortado - geometry vacío'); return; }

        let feature;
        try { feature = JSON.parse(val); } catch (e) { showMessage('GeoJSON inválido', 'error'); alert('DEBUG: JSON.parse geometry fallo: ' + e.message); return; }

        const centroid = calcCentroidFromFeature(feature);
        if (!centroid) { showMessage('No se pudo calcular centroid', 'error'); alert('DEBUG: centroid cálculo fallo'); return; }

        document.getElementById('centroid_lat').value = centroid.lat;
        document.getElementById('centroid_lng').value = centroid.lng;

        detectBtn.disabled = true;
        const originalText = detectBtn.textContent;
        detectBtn.textContent = 'Detectando...';

        try {
            alert('DEBUG: Llamando a Nominatim reverse con lat=' + centroid.lat + ' lng=' + centroid.lng);
            const resp = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${centroid.lat}&lon=${centroid.lng}&zoom=18&addressdetails=1&accept-language=es`, {
                headers: { 'User-Agent': 'DeforestationApp/1.0' }
            });
            alert('DEBUG: Nominatim responded status=' + resp.status);
            if (!resp.ok) throw new Error('Error en Nominatim: ' + resp.status);
            const data = await resp.json();
            alert('DEBUG: Nominatim JSON recibido (length: ' + JSON.stringify(data).length + ')');
            document.getElementById('location_data').value = JSON.stringify(data);

            const address = data.address || {};
            const parish = address.county || address.suburb || address.village || address.town || address.city || '';
            const municipality = address.municipality || address.county || address.city || '';
            const state = address.state || address.region || '';

            document.getElementById('detected_parish').value = parish;
            document.getElementById('detected_municipality').value = municipality;
            document.getElementById('detected_state').value = state;

            document.getElementById('detected-parish-text').textContent = parish || 'No detectado';
            document.getElementById('detected-municipality-text').textContent = municipality || 'No detectado';
            document.getElementById('detected-state-text').textContent = state || 'No detectado';
            document.getElementById('detected-coords-text').textContent = `${centroid.lat.toFixed(6)}, ${centroid.lng.toFixed(6)}`;

            document.getElementById('location-info').classList.remove('hidden');

            try {
                alert('DEBUG: Llamando API backend para asignar parroquia');
                const assignResp = await fetch('{{ route("polygons.find-parish-api") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        parish_name: parish ? parish.toString().toLowerCase().trim() : '',
                        municipality_name: municipality ? municipality.toString().toLowerCase().trim() : '',
                        state_name: state ? state.toString().toLowerCase().trim() : ''
                    })
                });
                alert('DEBUG: backend find-parish-api status=' + assignResp.status);
                const assignJson = await assignResp.json();
                console.log('DEBUG: assignJson', assignJson);
                if (assignJson.success && assignJson.parish) {
                    document.getElementById('parish_id').value = assignJson.parish.id;
                    showMessage('Parroquia encontrada y asignada', 'info');
                    alert('DEBUG: Parroquia asignada id=' + assignJson.parish.id);
                } else {
                    showMessage('No se encontró parroquia exacta. Selecciona manualmente.', 'info');
                    alert('DEBUG: No se encontró parroquia en backend');
                }
            } catch (e) {
                console.warn('Asignación parroquia fallida', e);
                alert('DEBUG: Error en assign parish API: ' + (e.message || e));
            }
        } catch (err) {
            console.error(err);
            showMessage('Error detectando ubicación', 'error');
            alert('DEBUG: error detect location: ' + (err.message || err));
        } finally {
            detectBtn.disabled = false;
            detectBtn.textContent = originalText;
        }
    });

    document.getElementById('polygon-form').addEventListener('submit', function (e) {
        alert('DEBUG: Intentando enviar formulario - validando geometry');
        const val = geometryInput.value;
        if (!val) {
            e.preventDefault();
            showMessage('❌ Debes dibujar un polígono en el mapa', 'error');
            alert('DEBUG: submit cancelado - geometry vacío');
            return false;
        }
        try {
            const parsed = JSON.parse(val);
            const feature = (parsed.type && parsed.type === 'Feature') ? parsed : { type: 'Feature', geometry: parsed };
            const geom = feature.geometry;
            if (!geom || !geom.type || !['Polygon', 'MultiPolygon'].includes(geom.type)) {
                e.preventDefault();
                showMessage('❌ La geometría debe ser Polygon o MultiPolygon', 'error');
                alert('DEBUG: submit cancelado - geometría no válida: type=' + (geom?geom.type:'null'));
                return false;
            }

            // DEBUG: mostrar payload antes de submit (no bloquear envío)
            const debugPayload = {
                name: document.getElementById('name').value,
                geometry: feature,
                parish_id: document.getElementById('parish_id') ? document.getElementById('parish_id').value : null,
                producer_id: document.getElementById('producer_id') ? document.getElementById('producer_id').value : null,
            };
            console.log('DEBUG: payload antes submit', debugPayload);
            alert('DEBUG: formulario validado, enviando. geometry length=' + JSON.stringify(feature).length);
            return true;
        } catch (err) {
            e.preventDefault();
            showMessage('❌ Geometría inválida (JSON)', 'error');
            alert('DEBUG: submit cancelado - JSON.parse error: ' + err.message);
            return false;
        }
    });
});
</script>
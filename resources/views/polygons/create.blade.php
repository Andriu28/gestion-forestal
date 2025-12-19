{{-- [file name]: create.blade.php --}}
<x-app-layout>
    <div class="mx-auto">
        

        <div class="bg-stone-100/90 dark:bg-gray-800 overflow-hidden shadow-sm rounded-2xl p-6">
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
document.addEventListener('DOMContentLoaded', () => {
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
        el.classList.toggle('text-red-600', type === 'error');
        el.classList.toggle('text-green-700', type !== 'error');
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
        } catch (e) {
            geometryInput.value = '';
            console.error('Error serializing feature', e);
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
        new L.Draw.Polygon(map, drawControl.options.draw.polygon).enable();
    });

    clearBtn.addEventListener('click', () => {
        drawnItems.clearLayers();
        geometryInput.value = '';
        detectBtn.disabled = true;
        document.getElementById('location-info').classList.add('hidden');
        coordsDisplay.textContent = 'Lat: 0.000000 | Lng: 0.000000';
        showMessage('Mapa limpio', 'info');
    });

    map.on(L.Draw.Event.CREATED, function (e) {
        const layer = e.layer;
        drawnItems.clearLayers();
        drawnItems.addLayer(layer);

        const feature = layer.toGeoJSON();
        setFeatureToInput(feature);

        detectBtn.disabled = false;
        const centr = calcCentroidFromFeature(feature);
        if (centr) coordsDisplay.textContent = `Lat: ${centr.lat.toFixed(6)} | Lng: ${centr.lng.toFixed(6)}`;
    });

    map.on('mousemove', (e) => {
        coordsDisplay.textContent = `Lat: ${e.latlng.lat.toFixed(6)} | Lng: ${e.latlng.lng.toFixed(6)}`;
    });

    detectBtn.addEventListener('click', async () => {
        const val = geometryInput.value;
        if (!val) { showMessage('Dibuja un polígono primero', 'error'); return; }

        let feature;
        try { feature = JSON.parse(val); } catch (e) { showMessage('GeoJSON inválido', 'error'); return; }

        const centroid = calcCentroidFromFeature(feature);
        if (!centroid) { showMessage('No se pudo calcular centroid', 'error'); return; }

        document.getElementById('centroid_lat').value = centroid.lat;
        document.getElementById('centroid_lng').value = centroid.lng;

        detectBtn.disabled = true;
        const originalText = detectBtn.textContent;
        detectBtn.textContent = 'Detectando...';

        try {
            const resp = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${centroid.lat}&lon=${centroid.lng}&zoom=18&addressdetails=1&accept-language=es`, {
                headers: { 'User-Agent': 'DeforestationApp/1.0' }
            });
            if (!resp.ok) throw new Error('Error en Nominatim');
            const data = await resp.json();
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
                const assignJson = await assignResp.json();
                if (assignJson.success && assignJson.parish) {
                    document.getElementById('parish_id').value = assignJson.parish.id;
                    showMessage('Parroquia encontrada y asignada', 'info');
                } else {
                    showMessage('No se encontró parroquia exacta. Selecciona manualmente.', 'info');
                }
            } catch (e) {
                console.warn('Asignación parroquia fallida', e);
            }
        } catch (err) {
            console.error(err);
            showMessage('Error detectando ubicación', 'error');
        } finally {
            detectBtn.disabled = false;
            detectBtn.textContent = originalText;
        }
    });

    document.getElementById('polygon-form').addEventListener('submit', function (e) {
        const val = geometryInput.value;
        if (!val) {
            e.preventDefault();
            showMessage('❌ Debes dibujar un polígono en el mapa', 'error');
            return false;
        }
        try {
            const parsed = JSON.parse(val);
            const feature = (parsed.type && parsed.type === 'Feature') ? parsed : { type: 'Feature', geometry: parsed };
            const geom = feature.geometry;
            if (!geom || !geom.type || !['Polygon', 'MultiPolygon'].includes(geom.type)) {
                e.preventDefault();
                showMessage('❌ La geometría debe ser Polygon o MultiPolygon', 'error');
                return false;
            }
            return true;
        } catch (err) {
            e.preventDefault();
            showMessage('❌ Geometría inválida (JSON)', 'error');
            return false;
        }
    });
});
</script>
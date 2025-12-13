{{-- [file name]: create.blade.php --}}
<x-app-layout>
    <div class="">
        <div class="max-w-7xl mx-auto">
            <div class="bg-stone-100/90 dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl shadow-soft p-4 md:p-6 lg:p-8">
                <div class="text-gray-900 dark:text-gray-100">
                    <div class="d-flex justify-content-between align-items-center mb-6">
                        <h2 class="text-2xl font-bold"><i class="fas fa-draw-polygon mr-2"></i>Crear Nuevo Polígono</h2>        
                    </div>
                    
                    <form action="{{ route('polygons.store') }}" method="POST" id="polygon-form">
                        @csrf
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Columna del formulario -->
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
                                             class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                                             placeholder="Descripción del polígono...">{{ old('description') }}</textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                                </div>

                                <div>
                                    <x-input-label for="producer_id" :value="__('Productor (Opcional)')" />
                                    <select id="producer_id" name="producer_id" 
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
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
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
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
                                                 class="mt-1 block w-full" :value="old('area_ha')" 
                                                 placeholder="Se calculará automáticamente si se deja vacío" />
                                    <x-input-error class="mt-2" :messages="$errors->get('area_ha')" />
                                </div>

                                <!-- Campos ocultos para la ubicación detectada -->
                                <input type="hidden" id="detected_parish" name="detected_parish">
                                <input type="hidden" id="detected_municipality" name="detected_municipality">
                                <input type="hidden" id="detected_state" name="detected_state">
                                <input type="hidden" id="centroid_lat" name="centroid_lat">
                                <input type="hidden" id="centroid_lng" name="centroid_lng">
                                <input type="hidden" id="location_data" name="location_data">

                               <!-- Información de ubicación detectada -->
                                <div id="location-info" class="p-4 bg-blue-50 dark:bg-blue-900 rounded-lg hidden">
                                    <h3 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">
                                        📍 Ubicación Detectada Automáticamente
                                    </h3>
                                    <div class="space-y-1 text-sm">
                                        <div><strong>Parroquia:</strong> <span id="detected-parish-text">-</span></div>
                                        <div><strong>Municipio:</strong> <span id="detected-municipality-text">-</span></div>
                                        <div><strong>Estado:</strong> <span id="detected-state-text">-</span></div>
                                        <div><strong>Coordenadas:</strong> <span id="detected-coords-text">-</span></div>
                                        <div id="parish-match-info" class="mt-2 p-2 bg-green-100 dark:bg-green-800 rounded hidden">
                                            <span class="text-green-700 dark:text-green-300 text-xs">
                                                ✅ Se encontró una parroquia coincidente en la base de datos
                                            </span>
                                        </div>
                                        <div id="parish-no-match-info" class="mt-2 p-2 bg-yellow-100 dark:bg-yellow-800 rounded hidden">
                                            <span class="text-yellow-700 dark:text-yellow-300 text-xs">
                                                ⚠️ No se encontró parroquia coincidente. Selecciona una manualmente.
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Columna del mapa -->
                            <div>
                                <x-input-label for="map" :value="__('Dibujar Polígono en el Mapa *')" />
                                <div class="relative rounded-lg overflow-hidden mb-6 border border-gray-200 dark:border-gray-700 mt-1" style="height: 400px;">
                                    <div id="map" class="h-full w-full"></div>
                                    <div class="coordinates-display" id="coordinates-display">
                                        Lat: 0.000000 | Lng: 0.000000
                                    </div>
                                    <div id="message-display" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-20 px-4 py-2 rounded-lg bg-gray-900 text-white font-semibold shadow-lg hidden"></div>
                                    <div class="map-overlay">
                                        <button type="button" id="draw-polygon" class="bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-100 px-3 py-2 rounded-lg shadow-md hover:bg-gray-200 flex items-center mb-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-2">
                                                <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
                                            </svg>
                                            Dibujar Polígono
                                        </button>
                                        <button type="button" id="detect-location" class="bg-green-500 text-white px-3 py-2 rounded-lg shadow-md hover:bg-green-600 flex items-center mb-2" disabled>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-2">
                                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                                            </svg>
                                            Detectar Ubicación
                                        </button>
                                        <button type="button" id="clear-map" class="bg-red-500 text-white px-3 py-2 rounded-lg shadow-md hover:bg-red-600 flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-2">
                                                <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                            </svg>
                                            Limpiar Mapa
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" id="geometry" name="geometry" value="{{ old('geometry', '[]') }}" required>
                                <x-input-error class="mt-2" :messages="$errors->get('geometry')" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 space-x-4">
                            <x-go-back-button />
                            <x-primary-button type="submit" id="submit-btn">
                                {{ __('Crear Polígono') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<style>
    .coordinates-display {
        position: absolute;
        bottom: 10px;
        left: 10%;
        transform: translateX(-50%);
        z-index: 1000;
        background: rgba(255, 255, 255, 0.8);
        padding: 5px 10px;
        border-radius: 5px;
        font-family: Arial, sans-serif;
        font-size: 14px;
        color: #333;
        white-space: nowrap;
    }
    .map-overlay {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
    }
    .loading-spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid #ffffff;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 1s ease-in-out infinite;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>

<script>
    // Variables globales
    let map;
    let drawnItems;
    let drawControl;
    let currentPolygon = null;

    // Inicializar mapa
    function initMap() {
        // Centro en Venezuela
        map = L.map('map').setView([8.0, -66.0], 6);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Capa para dibujar
        drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        // Configurar controles de dibujo
        drawControl = new L.Control.Draw({
            draw: {
                polygon: {
                    allowIntersection: false,
                    showArea: true,
                    shapeOptions: {
                        color: '#3498db',
                        fillColor: '#3498db',
                        fillOpacity: 0.3,
                        weight: 3
                    }
                },
                polyline: false,
                rectangle: false,
                circle: false,
                circlemarker: false,
                marker: false
            },
            edit: {
                featureGroup: drawnItems
            }
        });

        // Evento cuando se crea un polígono
        map.on(L.Draw.Event.CREATED, function (e) {
            const layer = e.layer;
            drawnItems.clearLayers();
            drawnItems.addLayer(layer);
            currentPolygon = layer;
            
            document.getElementById('detect-location').disabled = false;
            document.getElementById('message-display').textContent = '✅ Polígono dibujado. Haz clic en "Detectar Ubicación"';
            document.getElementById('message-display').classList.remove('hidden');
            
            setTimeout(() => {
                document.getElementById('message-display').classList.add('hidden');
            }, 3000);
            
            // Guardar siempre el objeto "geometry" del GeoJSON (no todo el Feature ni solo coordinates)
            const geojson = layer.toGeoJSON();
            if (geojson && geojson.geometry) {
                document.getElementById('geometry').value = JSON.stringify(geojson.geometry);
            } else {
                document.getElementById('geometry').value = '';
            }
        });

        // Mostrar coordenadas al mover el mouse
        map.on('mousemove', function(e) {
            const coords = e.latlng;
            document.getElementById('coordinates-display').textContent = 
                `Lat: ${coords.lat.toFixed(6)} | Lng: ${coords.lng.toFixed(6)}`;
        });

        map.addControl(drawControl);
    }

    // Calcular centroide del polígono
    function calculateCentroid(polygon) {
        const latlngs = polygon.getLatLngs()[0];
        let latSum = 0;
        let lngSum = 0;
        
        latlngs.forEach(latlng => {
            latSum += latlng.lat;
            lngSum += latlng.lng;
        });
        
        return {
            lat: latSum / latlngs.length,
            lng: lngSum / latlngs.length
        };
    }

    // Detectar ubicación usando OpenStreetMap
    async function detectLocation() {
        if (!currentPolygon) {
            showMessage('❌ Primero debes dibujar un polígono', 'error');
            return;
        }

        const detectBtn = document.getElementById('detect-location');
        const originalText = detectBtn.innerHTML;
        detectBtn.innerHTML = '<span class="loading-spinner"></span> Detectando...';
        detectBtn.disabled = true;

        try {
            const centroid = calculateCentroid(currentPolygon);
            
            // Consultar Nominatim (OpenStreetMap)
            const response = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${centroid.lat}&lon=${centroid.lng}&zoom=18&addressdetails=1&accept-language=es`,
                { 
                    headers: {
                        'User-Agent': 'DeforestationApp/1.0'
                    }
                }
            );
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Procesar y mostrar la ubicación detectada
            processDetectedLocation(data, centroid);
            showMessage('✅ Ubicación detectada correctamente', 'success');
            
        } catch (error) {
            showMessage(`❌ Error: ${error.message}`, 'error');
            console.error('Error:', error);
        } finally {
            detectBtn.innerHTML = originalText;
            detectBtn.disabled = false;
        }
    }

    // En create.blade.php - actualizar la función processDetectedLocation
    async function processDetectedLocation(data, centroid) {
        const address = data.address;
        
        // Extraer datos de OpenStreetMap (MEJORADO)
        const detectedParish = extractParishName(address);
        const detectedMunicipality = extractMunicipalityName(address);
        const detectedState = extractStateName(address);
        
        // Actualizar campos hidden
        document.getElementById('detected_parish').value = detectedParish.original;
        document.getElementById('detected_municipality').value = detectedMunicipality.original;
        document.getElementById('detected_state').value = detectedState.original;
        document.getElementById('centroid_lat').value = centroid.lat;
        document.getElementById('centroid_lng').value = centroid.lng;
        document.getElementById('location_data').value = JSON.stringify(data);
        
        // Mostrar información al usuario (con/sin prefijos según lo original)
        document.getElementById('detected-parish-text').textContent = detectedParish.original;
        document.getElementById('detected-municipality-text').textContent = detectedMunicipality.original;
        document.getElementById('detected-state-text').textContent = detectedState.original;
        document.getElementById('detected-coords-text').textContent = `${centroid.lat.toFixed(6)}, ${centroid.lng.toFixed(6)}`;
        
        // Buscar parroquia en la base de datos (enviando ambas versiones)
        await findAndAssignParish(
            detectedParish.normalized, // Versión normalizada para búsqueda
            detectedParish.original,   // Versión original para mostrar
            detectedMunicipality.normalized,
            detectedMunicipality.original,
            detectedState.normalized,
            detectedState.original
        );

        // Validar que el polígono existe antes de buscar ubicación
        if (!currentPolygon) {
            showMessage('❌ No hay polígono dibujado', 'error');
            return;
        }
        
        // Guardar geometría actualizada en el campo hidden (OBJETO geometry)
        if (currentPolygon) {
            const geojson = currentPolygon.toGeoJSON();
            if (geojson && geojson.geometry) {
                document.getElementById('geometry').value = JSON.stringify(geojson.geometry);
            }
        }
        
        // Mostrar sección de ubicación detectada
        document.getElementById('location-info').classList.remove('hidden');
    }

    // Funciones auxiliares para extraer nombres
    function extractParishName(address) {
        const fields = ['county', 'municipality', 'city_district', 'district', 'suburb', 'village'];
        
        for (const field of fields) {
            if (address[field]) {
                return {
                    original: address[field],
                    normalized: normalizeLocationName(address[field])
                };
            }
        }
        
        return {
            original: 'No detectado',
            normalized: ''
        };
    }

    function extractMunicipalityName(address) {
        const fields = ['municipality', 'county', 'state_district', 'city', 'town'];
        
        for (const field of fields) {
            if (address[field]) {
                return {
                    original: address[field],
                    normalized: normalizeLocationName(address[field])
                };
            }
        }
        
        return {
            original: 'No detectado',
            normalized: ''
        };
    }

    function extractStateName(address) {
        if (address.state) {
            return {
                original: address.state,
                normalized: normalizeLocationName(address.state)
            };
        }
        
        if (address.region) {
            return {
                original: address.region,
                normalized: normalizeLocationName(address.region)
            };
        }
        
        return {
            original: 'No detectado',
            normalized: ''
        };
    }

    // Normalizar nombres en el frontend (similar al backend)
    function normalizeLocationName(name) {
        if (!name) return '';
        
        // Convertir a minúsculas
        let normalized = name.toLowerCase().trim();
        
        // Eliminar prefijos comunes
        const prefixes = ['parroquia', 'municipio', 'estado', 'municipal'];
        prefixes.forEach(prefix => {
            const regex = new RegExp(`^${prefix}\\s+|\\s+${prefix}\\s+`, 'gi');
            normalized = normalized.replace(regex, ' ');
        });
        
        // Eliminar espacios múltiples
        normalized = normalized.replace(/\s+/g, ' ').trim();
        
        return normalized;
    }

    // Nueva función para buscar parroquia en la base de datos
    async function findAndAssignParish(
        parishNameNormalized, 
        parishNameOriginal,
        municipalityNameNormalized,
        municipalityNameOriginal,
        stateNameNormalized,
        stateNameOriginal
    ) {
        try {
            const response = await fetch('{{ route("polygons.find-parish-api") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    parish_name: parishNameNormalized,
                    parish_name_original: parishNameOriginal, // Enviar original también
                    municipality_name: municipalityNameNormalized,
                    municipality_name_original: municipalityNameOriginal,
                    state_name: stateNameNormalized,
                    state_name_original: stateNameOriginal
                })
            });
            
            const result = await response.json();
            
            if (result.success && result.parish) {
                // Asignar automáticamente la parroquia encontrada
                document.getElementById('parish_id').value = result.parish.id;
                
                // Mostrar información de coincidencia
                document.getElementById('parish-match-info').classList.remove('hidden');
                document.getElementById('parish-no-match-info').classList.add('hidden');
                
                // Actualizar texto con la información confirmada
                document.getElementById('detected-parish-text').innerHTML = 
                    `<span class="text-green-600">${result.parish.name} ✅</span>`;
                document.getElementById('detected-municipality-text').innerHTML = 
                    `<span class="text-green-600">${result.parish.municipality} ✅</span>`;
                document.getElementById('detected-state-text').innerHTML = 
                    `<span class="text-green-600">${result.parish.state} ✅</span>`;
                    
                showMessage('✅ Parroquia encontrada y asignada automáticamente', 'success');
            } else {
                // No se encontró coincidencia
                document.getElementById('parish-match-info').classList.add('hidden');
                document.getElementById('parish-no-match-info').classList.remove('hidden');
                
                // Mostrar sugerencias si hay opciones similares
                if (result.suggestions && result.suggestions.length > 0) {
                    showSuggestions(result.suggestions);
                }
                
                showMessage('⚠️ No se encontró parroquia coincidente. Selecciona una manualmente.', 'warning');
            }
        } catch (error) {
            console.error('Error buscando parroquia:', error);
            document.getElementById('parish-match-info').classList.add('hidden');
            document.getElementById('parish-no-match-info').classList.remove('hidden');
            
            showMessage('❌ Error al buscar parroquia en la base de datos', 'error');
        }
    }

    // Mostrar mensajes temporales
    function showMessage(message, type) {
        const messageDiv = document.getElementById('message-display');
        messageDiv.textContent = message;
        messageDiv.className = `absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-20 px-4 py-2 rounded-lg font-semibold shadow-lg ${type === 'error' ? 'bg-red-500 text-white' : 'bg-green-500 text-white'}`;
        messageDiv.classList.remove('hidden');
        
        setTimeout(() => {
            messageDiv.classList.add('hidden');
        }, 4000);
    }

    // Activar dibujo de polígonos
    function activateDrawing() {
        drawnItems.clearLayers();
        currentPolygon = null;
        document.getElementById('detect-location').disabled = true;
        document.getElementById('location-info').classList.add('hidden');
        
        // Limpiar campos de ubicación
        document.getElementById('detected_parish').value = '';
        document.getElementById('detected_municipality').value = '';
        document.getElementById('detected_state').value = '';
        document.getElementById('centroid_lat').value = '';
        document.getElementById('centroid_lng').value = '';
        document.getElementById('location_data').value = '';

        // Limpiar también el campo de geometría (usar cadena vacía para mayor consistencia)
        document.getElementById('geometry').value = '';
        
        new L.Draw.Polygon(map, drawControl.options.draw.polygon).enable();
    }

    // Limpiar mapa
    function clearMap() {
        drawnItems.clearLayers();
        currentPolygon = null;
        document.getElementById('detect-location').disabled = true;
        document.getElementById('geometry').value = '';
        document.getElementById('location-info').classList.add('hidden');
        
        // Limpiar campos de ubicación
        document.getElementById('detected_parish').value = '';
        document.getElementById('detected_municipality').value = '';
        document.getElementById('detected_state').value = '';
        document.getElementById('centroid_lat').value = '';
        document.getElementById('centroid_lng').value = '';
        document.getElementById('location_data').value = '';
        
        showMessage('🗑️ Mapa limpiado. Dibuja un nuevo polígono.', 'info');
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        initMap();
        
        document.getElementById('draw-polygon').addEventListener('click', activateDrawing);
        document.getElementById('detect-location').addEventListener('click', detectLocation);
        document.getElementById('clear-map').addEventListener('click', clearMap);
        
        // Validar antes de enviar el formulario
        document.getElementById('polygon-form').addEventListener('submit', function(e) {
            const geometry = document.getElementById('geometry').value;
            // Aceptar vacío o '[]' como no válido; también proteger contra 'null'
            if (!geometry || geometry === '[]' || geometry === 'null') {
                e.preventDefault();
                showMessage('❌ Debes dibujar un polígono en el mapa', 'error');
                return false;
            }
        });
    });
</script>
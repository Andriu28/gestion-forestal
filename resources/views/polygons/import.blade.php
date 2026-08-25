{{-- resources/views/polygons/import.blade.php --}}
<x-app-layout>
    <div class="max-w-7xl mx-auto py-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                Importar Polígonos desde GeoJSON
            </h2>

            <form id="import-form" action="{{ route('polygons.import.process') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- Campos del formulario original --}}
                <div>
                    <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Archivo GeoJSON</label>
                    <input type="file" name="file" id="file" accept=".json,.geojson" required
                           class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-md file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-blue-50 file:text-blue-700
                                  dark:file:bg-blue-900 dark:file:text-blue-300
                                  hover:file:bg-blue-100 dark:hover:file:bg-blue-800
                                  cursor-pointer border border-gray-300 dark:border-gray-600 rounded-md">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Selecciona un archivo GeoJSON (.json o .geojson).</p>
                </div>

                {{-- SRID (automático, pero editable) --}}
                <div>
                    <label for="srid" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        SRID de entrada (sistema de coordenadas del archivo)
                    </label>
                    <input type="number" name="srid" id="srid" value="{{ old('srid', 2203) }}" min="0"
                           class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Si el archivo tiene un CRS definido, se usará automáticamente. Puedes modificarlo si es necesario.
                    </p>
                </div>

                {{-- Opciones globales --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="parish_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Parroquia predeterminada (se aplicará a todos los polígonos sin asignación manual)
                        </label>
                        <select name="parish_id" id="parish_id"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="">-- Sin asignar --</option>
                            @foreach($parishes as $parish)
                                <option value="{{ $parish->id }}">{{ $parish->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="default_producer_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Productor predeterminado (se aplicará a los polígonos sin productor)
                        </label>
                        <select name="default_producer_id" id="default_producer_id"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="">-- Ninguno --</option>
                            @foreach($producers as $producer)
                                <option value="{{ $producer->id }}">{{ $producer->name }} {{ $producer->lastname }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="producer_field" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nombre del campo en properties que contiene el productor
                    </label>
                    <input type="text" name="producer_field" id="producer_field" value="Productor"
                           class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Ejemplo: "Productor", "propietario", "owner".</p>
                </div>

                <div class="flex space-x-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="create_missing_producers" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Crear productores que no existan</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="skip_existing" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Omitir polígonos con 'id' ya existente</span>
                    </label>
                </div>

                {{-- Contenedor para la tabla de previsualización (inicialmente oculto) --}}
                <div id="preview-container" class="hidden">
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden shadow-sm">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-2 flex justify-between items-center">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Previsualización de features</h3>
                            <span id="feature-count" class="text-xs text-gray-500 dark:text-gray-400"></span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="preview-table">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">ID</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nombre</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Área (Ha)</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Productor</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Parroquia</th>
                                    </tr>
                                </thead>
                                <tbody id="preview-body" class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                    <!-- Las filas se llenarán dinámicamente con JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="submit" id="import-btn" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md shadow transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            Importar Ahora
                        </button>
                    </div>
                </div>

                {{-- Botón para cancelar siempre visible --}}
                <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('polygons.index') }}"
                       class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('file');
            const previewContainer = document.getElementById('preview-container');
            const previewBody = document.getElementById('preview-body');
            const featureCount = document.getElementById('feature-count');
            const importBtn = document.getElementById('import-btn');
            const form = document.getElementById('import-form');

            // Función para leer y procesar el archivo
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function(event) {
                    try {
                        const geojson = JSON.parse(event.target.result);

                        // Validación básica
                        if (!geojson.type || geojson.type !== 'FeatureCollection') {
                            alert('El archivo no es un FeatureCollection GeoJSON válido.');
                            return;
                        }

                        const features = geojson.features || [];
                        if (!features.length) {
                            alert('El archivo no contiene features.');
                            return;
                        }

                        // Detectar SRID (opcional)
                        let detectedSrid = 4326;
                        if (geojson.crs && geojson.crs.properties && geojson.crs.properties.name) {
                            const match = geojson.crs.properties.name.match(/EPSG::(\d+)/);
                            if (match) {
                                detectedSrid = parseInt(match[1]);
                                document.getElementById('srid').value = detectedSrid;
                            }
                        }

                        // Limpiar tabla
                        previewBody.innerHTML = '';

                        // Llenar tabla con los features
                        features.forEach((feature, index) => {
                            const props = feature.properties || {};
                            const geometry = JSON.stringify(feature.geometry);

                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td class="px-4 py-2">
                                    <input type="text" name="features[${index}][id]" value="${props.id || ''}" class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="text" name="features[${index}][name]" value="${props.name || props.Productor || 'Polígono'}" class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" step="0.01" name="features[${index}][area_ha]" value="${props.Area_Ha || ''}" class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                                </td>
                                <td class="px-4 py-2">
                                    <select name="features[${index}][producer_id]" class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                                        <option value="">Sin asignar</option>
                                        @foreach($producers as $producer)
                                            <option value="{{ $producer->id }}" ${props.producer_id == {{ $producer->id }} ? 'selected' : ''}>
                                                {{ $producer->name }} {{ $producer->lastname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-2">
                                    <select name="features[${index}][parish_id]" class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                                        <option value="">Sin asignar</option>
                                        @foreach($parishes as $parish)
                                            <option value="{{ $parish->id }}" ${props.parish_id == {{ $parish->id }} ? 'selected' : ''}>
                                                {{ $parish->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            `;
                            previewBody.appendChild(row);

                            // Agregar campo oculto para la geometría
                            const hiddenGeo = document.createElement('input');
                            hiddenGeo.type = 'hidden';
                            hiddenGeo.name = `features[${index}][geometry]`;
                            hiddenGeo.value = geometry;
                            row.appendChild(hiddenGeo);

                            // Campo opcional para nombre del productor (para creación)
                            const hiddenProducerName = document.createElement('input');
                            hiddenProducerName.type = 'hidden';
                            hiddenProducerName.name = `features[${index}][producer_name]`;
                            hiddenProducerName.value = props.Productor || '';
                            row.appendChild(hiddenProducerName);
                        });

                        // Mostrar contenedor y habilitar botón
                        previewContainer.classList.remove('hidden');
                        featureCount.textContent = `${features.length} features`;
                        importBtn.disabled = false;

                        // Cambiar el texto del botón de importar a "Confirmar Importación"
                        importBtn.textContent = 'Confirmar Importación';

                    } catch (error) {
                        alert('Error al leer el archivo: ' + error.message);
                        console.error(error);
                    }
                };
                reader.readAsText(file);
            });

            // Prevenir el envío si no se ha cargado un archivo válido
            form.addEventListener('submit', function(e) {
                if (importBtn.disabled) {
                    e.preventDefault();
                    alert('Por favor, carga un archivo GeoJSON válido primero.');
                }
            });
        });
    </script>
    
</x-app-layout>
{{-- resources/views/polygons/import.blade.php --}}
<x-app-layout>
    <div class="mx-auto">
        <div class="bg-stone-100/90 dark:bg-custom-gray shadow-sm rounded-2xl shadow-soft p-4 md:p-6 lg:p-6 mb-6 animate-on-load">
            <div class="text-gray-900 dark:text-gray-100">
                <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-200 mb-4 md:mb-4">
                    Importar Parcelas desde GeoJSON
                </h2>

                <form id="import-form" action="{{ route('polygons.import.process') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    {{-- Campos del formulario original --}}
                    <div>
                        <x-input-label for="file" :value="__('Archivo GeoJSON *')" />
                        <input type="file" name="file" id="file" accept=".json,.geojson" required
                               class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400
                                      file:mr-4 file:py-2.5 file:px-4
                                      file:rounded-lg file:border-0
                                      file:text-sm file:font-semibold
                                      file:bg-blue-50 file:text-blue-700
                                      dark:file:bg-blue-900/30 dark:file:text-blue-300
                                      hover:file:bg-blue-100 dark:hover:file:bg-blue-900/50
                                      cursor-pointer border border-stone-400/80 dark:border-gray-600 rounded-lg
                                      bg-stone-50 dark:bg-gray-800/50
                                      focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Selecciona un archivo GeoJSON (.json o .geojson).</p>
                    </div>

                    {{-- SRID (automático, pero editable) --}}
                    <div>
                        <x-input-label for="srid" :value="__('SRID de entrada (sistema de coordenadas del archivo)')" />
                        <input type="number" name="srid" id="srid" value="{{ old('srid', 2203) }}" min="0"
                               class="mt-1 block w-full border border-stone-400/80 dark:border-gray-600 rounded-lg shadow-sm
                                      bg-stone-50 dark:bg-gray-800/50 text-gray-900 dark:text-white px-3 py-2
                                      focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">
                            Si el archivo tiene un CRS definido, se usará automáticamente. Puedes modificarlo si es necesario.
                        </p>
                    </div>

                    {{-- Opciones globales --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="parish_id" :value="__('Parroquia predeterminada')" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1.5">Se aplicará a todos los Parcelas sin asignación manual</p>
                            <select name="parish_id" id="parish_id"
                                    class="mt-1 block w-full border border-stone-400/80 dark:border-gray-600 rounded-lg shadow-sm
                                           bg-stone-50 dark:bg-gray-800/50 text-gray-900 dark:text-white px-3 py-2
                                           focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70">
                                <option value="">-- Sin asignar --</option>
                                @foreach($parishes as $parish)
                                    <option value="{{ $parish->id }}">{{ $parish->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="default_producer_id" :value="__('Productor predeterminado')" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1.5">Se aplicará a los Parcelas sin productor</p>
                            <select name="default_producer_id" id="default_producer_id"
                                    class="mt-1 block w-full border border-stone-400/80 dark:border-gray-600 rounded-lg shadow-sm
                                           bg-stone-50 dark:bg-gray-800/50 text-gray-900 dark:text-white px-3 py-2
                                           focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70">
                                <option value="">-- Ninguno --</option>
                                @foreach($producers as $producer)
                                    <option value="{{ $producer->id }}">{{ $producer->name }} {{ $producer->lastname }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Campo productor --}}
                    <div>
                        <x-input-label for="producer_field" :value="__('Nombre del campo en properties que contiene el productor')" />
                        <input type="text" name="producer_field" id="producer_field" value="Productor"
                               class="mt-1 block w-full border border-stone-400/80 dark:border-gray-600 rounded-lg shadow-sm
                                      bg-stone-50 dark:bg-gray-800/50 text-gray-900 dark:text-white px-3 py-2
                                      focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Ejemplo: "Productor", "propietario", "owner".</p>
                    </div>

                    {{-- Checkboxes --}}
                    <div class="flex flex-wrap gap-4">
                        <label class="inline-flex items-center cursor-pointer group">
                            <input type="checkbox" name="create_missing_producers" value="1"
                                   class="h-5 w-5 text-green-600 focus:ring-green-500 border-gray-300 rounded
                                        dark:bg-gray-700 dark:border-gray-600 dark:checked:bg-green-500
                                        dark:focus:ring-green-400 dark:focus:ring-offset-gray-800">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-950 dark:group-hover:text-gray-50 transition-colors duration-200">Crear productores que no existan</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer group">
                            <input type="checkbox" name="skip_existing" value="1"
                                   class="h-5 w-5 text-green-600 focus:ring-green-500 border-gray-300 rounded
                                        dark:bg-gray-700 dark:border-gray-600 dark:checked:bg-green-500
                                        dark:focus:ring-green-400 dark:focus:ring-offset-gray-800">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-950 dark:group-hover:text-gray-50 transition-colors duration-200">Omitir Parcelas con 'id' ya existente</span>
                        </label>
                    </div>

                    <div class="flex flex-wrap gap-4">
                        {{-- Análisis de deforestación automático --}}
                        <label for="analyze_deforestation" class="flex items-start gap-3 cursor-pointer group">
                            <input
                                type="checkbox"
                                name="analyze_deforestation"
                                id="analyze_deforestation"
                                value="1"
                                {{ old('analyze_deforestation') ? 'checked' : '' }}
                                class="mt-0.5 h-5 w-5 text-green-600 focus:ring-green-500 border-gray-300 rounded
                                       dark:bg-gray-700 dark:border-gray-600 dark:checked:bg-green-500
                                       dark:focus:ring-green-400 dark:focus:ring-offset-gray-800"
                            >
                            <span class="text-sm">
                                <span class="block font-medium text-gray-700 dark:text-gray-300 group-hover:text-gray-950 dark:group-hover:text-gray-50 transition-colors duration-200">
                                    Realizar análisis de deforestación al importar
                                </span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Consulta Global Forest Watch para cada Parcela (2020–{{ now()->year }}).
                                    Puede tardar varios minutos si importas muchos Parcelas.
                                </span>
                            </span>
                        </label>
                    </div>

                    {{-- Contenedor para la tabla de previsualización (inicialmente oculto) --}}
                    <div id="preview-container" class="hidden">
                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm bg-white dark:bg-gray-800/50">
                            <div class="bg-gray-50 dark:bg-gray-800/80 px-4 py-3 flex justify-between items-center border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Previsualización de features</h3>
                                <span id="feature-count" class="text-xs text-gray-500 dark:text-gray-400 bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded-full"></span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="table-auto divide-y divide-gray-200 dark:divide-gray-700" id="preview-table">
                                    <thead class="bg-stone-100/90 dark:bg-custom-gray">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nombre</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Área (Ha)</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Productor</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Parroquia</th>
                                        </tr>
                                    </thead>
                                    <tbody id="preview-body" class="bg-gray-200/60 dark:bg-gray-700/30 divide-y divide-gray-200 dark:divide-gray-700">
                                        <!-- Las filas se llenarán dinámicamente con JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <button type="submit" id="import-btn"
                                    class="px-6 py-2.5
                                           bg-gradient-to-br from-[#6B4226] to-[#8F5A34]
                                           dark:from-[#4E301B] dark:to-[#6B4226]
                                           hover:from-[#4E301B] hover:to-[#6B4226]
                                           dark:hover:from-[#3A2314] dark:hover:to-[#4E301B]
                                           active:from-[#3A2314] active:to-[#4E301B]
                                           text-white rounded-lg font-medium
                                           shadow-sm hover:shadow-md
                                           transition-all duration-200
                                           focus:outline-none focus:ring-2 focus:ring-[#6B4226]/70
                                           focus:ring-offset-2 dark:focus:ring-offset-gray-800
                                           disabled:opacity-50 disabled:cursor-not-allowed"
                                    disabled>
                                Importar Ahora
                            </button>
                        </div>
                    </div>

                    {{-- Botón para cancelar siempre visible --}}
                    <div class="flex items-center justify-end pt-4 pb-3 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('polygons.index') }}"
                               class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 font-medium transition-colors duration-200">
                                Cancelar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Animación de entrada (solo visual) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.animate-on-load');
            if (container) {
                container.style.opacity = '0';
                container.style.transform = 'translateY(30px)';
                container.style.transition = 'opacity 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)';
                void container.offsetWidth;
                setTimeout(() => {
                    container.style.opacity = '1';
                    container.style.transform = 'translateY(0)';
                }, 100);
            }
        });
    </script>

    {{-- Lógica de importación (sin cambios) --}}
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
                                <td class="px-2 py-1 whitespace-nowrap">
                                    <input type="text" name="features[${index}][id]" value="${props.id || ''}"
                                           class="w-auto min-w-[8rem] px-2 py-1 text-sm rounded-md border border-gray-300 dark:border-gray-600
                                                  bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                                                  focus:outline-none focus:ring-2 focus:ring-custom-gold-dark/60">
                                </td>
                                <td class="px-2 py-1 whitespace-nowrap">
                                    <input type="text" name="features[${index}][name]" value="${props.name || props.Productor || 'Parcela'}"
                                           class="w-auto min-w-[12rem] px-2 py-1 text-sm rounded-md border border-gray-300 dark:border-gray-600
                                                  bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                                                  focus:outline-none focus:ring-2 focus:ring-custom-gold-dark/60">
                                </td>
                                <td class="px-2 py-1 whitespace-nowrap">
                                    <input type="number" step="0.01" name="features[${index}][area_ha]" value="${props.Area_Ha || ''}"
                                           class="w-auto min-w-[9rem] px-2 py-1 text-sm rounded-md border border-gray-300 dark:border-gray-600
                                                  bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                                                  focus:outline-none focus:ring-2 focus:ring-custom-gold-dark/60">
                                </td>
                                <td class="px-2 py-1 whitespace-nowrap">
                                    <select name="features[${index}][producer_id]"
                                            class="w-max min-w-[14rem] px-2 py-1 text-sm rounded-md border border-gray-300 dark:border-gray-600
                                                   bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                                                   focus:outline-none focus:ring-2 focus:ring-custom-gold-dark/60">
                                        <option value="">Sin asignar</option>
                                        @foreach($producers as $producer)
                                            <option value="{{ $producer->id }}" ${props.producer_id == {{ $producer->id }} ? 'selected' : ''}>
                                                {{ $producer->name }} {{ $producer->lastname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-2 py-1 whitespace-nowrap">
                                    <select name="features[${index}][parish_id]"
                                            class="w-max min-w-[14rem] px-2 py-1 text-sm rounded-md border border-gray-300 dark:border-gray-600
                                                   bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                                                   focus:outline-none focus:ring-2 focus:ring-custom-gold-dark/60">
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
// ============================================================
// INTERCEPTAR SUBMIT DE IMPORTACIÓN
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form[action*="polygons/import"]')
              || document.getElementById('import-form');

    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const importId = (crypto.randomUUID?.() || Date.now().toString(36))
            + '-' + Math.random().toString(36).slice(2, 8);

        let hiddenId = form.querySelector('input[name="import_id"]');
        if (!hiddenId) {
            hiddenId = document.createElement('input');
            hiddenId.type = 'hidden';
            hiddenId.name = 'import_id';
            form.appendChild(hiddenId);
        }
        hiddenId.value = importId;

        // Arrancar el widget ANTES del POST.
        // El polling puede dar 404 durante ~1s hasta que el controller
        // cree el cache entry. El widget tiene 6.4s de gracia.
        window.BackgroundProgress.start(importId, {
            title: 'Importando Parcelas',
            progressUrl: '/polygons/import/progress/__ID__',
        });

        try {
            const formData = new FormData(form);
            const res = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            // 202 → job encolado, seguimos con el polling
            if (res.status === 202) {
                return;
            }

            // 422 → errores de validación; recargamos la página
            if (res.status === 422) {
                const html = await res.text();
                document.open();
                document.write(html);
                document.close();
                return;
            }

            // Cualquier otro estado: fallback
            window.BackgroundProgress.stop();
            form.submit();
        } catch (err) {
            console.error('Error enviando importación:', err);
            window.BackgroundProgress.stop();
            form.submit();
        }
    });
});
    </script>
</x-app-layout>
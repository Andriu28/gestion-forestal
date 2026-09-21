{{-- resources/views/polygons/import.blade.php --}}
<x-app-layout>
    <div class="mx-auto">
        <div class="bg-stone-100/90 dark:bg-custom-gray shadow-sm rounded-2xl shadow-soft p-4 md:p-6 lg:p-6 mb-6 animate-on-load">
            <div class="text-gray-900 dark:text-gray-100">
                <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-200 mb-4 md:mb-4">
                    Importar Polígonos desde GeoJSON
                </h2>

                <form id="import-form" action="{{ route('polygons.import.process') }}" method="POST" class="space-y-6">
                    @csrf

                    {{-- Archivo + botón Previsualizar en la misma fila --}}
                    <div>
                        <x-input-label for="file" :value="__('Archivo GeoJSON *')" />

                        <div class="mt-1 flex items-stretch gap-2">
                            {{-- Input file --}}
                            <input type="file" name="_dummy_file" id="file" accept=".json,.geojson" required
                                   class="flex-1 block w-full text-sm text-gray-500 dark:text-gray-400
                                          file:mr-4 file:py-2.5 file:px-4
                                          file:rounded-lg file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-blue-50 file:text-blue-700
                                          dark:file:bg-blue-900/30 dark:file:text-blue-300
                                          hover:file:bg-blue-100 dark:hover:file:bg-blue-900/50
                                          cursor-pointer border border-stone-400/80 dark:border-gray-600 rounded-lg
                                          bg-stone-50 dark:bg-gray-800/50
                                          focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70">

                            {{-- Botón Previsualizar --}}
                            <button type="button" id="preview-btn"
                                    class="px-4 py-2
                                           bg-gray-200 hover:bg-gray-300
                                           dark:bg-gray-700 dark:hover:bg-gray-600
                                           text-gray-800 dark:text-gray-200
                                           rounded-lg
                                           font-medium
                                           border border-transparent
                                           dark:border-gray-600
                                           transition-colors duration-200
                                           disabled:opacity-50 disabled:cursor-not-allowed
                                           disabled:hover:bg-gray-200 dark:disabled:hover:bg-gray-700
                                           whitespace-nowrap flex items-center gap-2"
                                    disabled>
                                Previsualizar
                            </button>
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Selecciona un archivo GeoJSON (.json o .geojson).</p>
                        <x-input-error class="mt-2" :messages="$errors->get('file')" />
                    </div>

                    {{-- SRID --}}
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
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1.5">Se aplicará a todos los polígonos sin asignación manual</p>
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
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1.5">Se aplicará a los polígonos sin productor</p>
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
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-950 dark:group-hover:text-gray-50 transition-colors duration-200">Omitir polígonos con 'id' ya existente</span>
                        </label>
                    </div>

                    {{-- Contenedor para la tabla de previsualización (inicialmente oculto) --}}
                    <div id="preview-container" class="hidden">
                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm bg-white dark:bg-gray-800/50">
                            <div class="bg-gray-50 dark:bg-gray-800/80 px-4 py-3 flex justify-between items-center border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Previsualización de features
                                    <span class="text-xs text-gray-400 font-normal ml-2">(puedes editar los valores, excepto el ID)</span>
                                </h3>
                                <span id="feature-count" class="text-xs text-gray-500 dark:text-gray-400 bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded-full"></span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-max table-auto divide-y divide-gray-200 dark:divide-gray-700" id="preview-table">
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
                                        <!-- Filas dinámicas -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <button type="button" id="confirm-preview-btn"
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
                                           focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                Confirmar
                            </button>
                        </div>
                    </div>

                    {{-- Botones de acción --}}
                     <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            <span id="file-status" class="font-medium text-gray-700 dark:text-gray-300">Ningún archivo seleccionado</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('polygons.index') }}"
                               class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 font-medium transition-colors duration-200">
                                Cancelar
                            </a>
                            <button type="submit" form="import-form" id="preview-import-btn"
                                    disabled
                                    class="px-5 py-2.5
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
                                           disabled:opacity-50 disabled:cursor-not-allowed">
                                Importar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // =============================================
            // ANIMACIÓN DE ENTRADA
            // =============================================
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

            // =============================================
            // ELEMENTOS DEL DOM
            // =============================================
            const fileInput = document.getElementById('file');
            const previewBtn = document.getElementById('preview-btn');
            const fileStatus = document.getElementById('file-status');
            const form = document.getElementById('import-form');
            const previewImportBtn = document.getElementById('preview-import-btn');
            const previewContainer = document.getElementById('preview-container');
            const previewBody = document.getElementById('preview-body');
            const featureCount = document.getElementById('feature-count');
            const confirmPreviewBtn = document.getElementById('confirm-preview-btn');

            let currentFeatures = [];

            // =============================================
            // LECTURA DEL ARCHIVO
            // =============================================
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];

                if (!file) {
                    fileStatus.textContent = 'Ningún archivo seleccionado';
                    previewBtn.disabled = true;
                    previewImportBtn.disabled = true;
                    previewContainer.classList.add('hidden');
                    currentFeatures = [];
                    return;
                }

                fileStatus.textContent = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
                previewBtn.disabled = false;
                previewImportBtn.disabled = false;
                currentFeatures = [];

                previewContainer.classList.add('hidden');
                previewBody.innerHTML = '';

                const reader = new FileReader();
                reader.onload = function(event) {
                    try {
                        const geojson = JSON.parse(event.target.result);

                        if (!geojson.type || geojson.type !== 'FeatureCollection') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'El archivo no es un FeatureCollection GeoJSON válido.',
                                confirmButtonColor: '#63afc2'
                            });
                            previewBtn.disabled = true;
                            previewImportBtn.disabled = true;
                            return;
                        }

                        const features = geojson.features || [];
                        if (!features.length) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Advertencia',
                                text: 'El archivo no contiene features.',
                                confirmButtonColor: '#6b9ab6'
                            });
                            previewBtn.disabled = true;
                            previewImportBtn.disabled = true;
                            return;
                        }

                        currentFeatures = features;

                        if (geojson.crs && geojson.crs.properties && geojson.crs.properties.name) {
                            const match = geojson.crs.properties.name.match(/EPSG::(\d+)/);
                            if (match) {
                                document.getElementById('srid').value = parseInt(match[1]);
                            }
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Archivo cargado',
                            text: `Se encontraron ${features.length} features. Presiona "Previsualizar" para verlos.`,
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });

                        previewBtn.disabled = false;
                        previewImportBtn.disabled = false;

                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al leer el archivo',
                            text: error.message,
                            confirmButtonColor: '#c67a2e'
                        });
                        console.error(error);
                        previewBtn.disabled = true;
                        previewImportBtn.disabled = true;
                    }
                };
                reader.readAsText(file);
            });

            // =============================================
            // PREVISUALIZAR → construye la tabla EDITABLE
            // =============================================
            previewBtn.addEventListener('click', function() {
                if (currentFeatures.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin datos',
                        text: 'Por favor, carga un archivo GeoJSON válido primero.',
                        confirmButtonColor: '#c67a2e'
                    });
                    return;
                }

                previewBody.innerHTML = '';

                const producersOptions = `
                    <option value="">Sin asignar</option>
                    @foreach($producers as $producer)
                        <option value="{{ $producer->id }}">{{ $producer->name }} {{ $producer->lastname }}</option>
                    @endforeach
                `;

                const parishesOptions = `
                    <option value="">Sin asignar</option>
                    @foreach($parishes as $parish)
                        <option value="{{ $parish->id }}">{{ $parish->name }}</option>
                    @endforeach
                `;

                currentFeatures.forEach((feature, index) => {
                    const props = feature.properties || {};
                    const row = document.createElement('tr');

                    const id = props.id || props.ID || '';
                    const name = props.name || props.Nombre || props.Productor || 'Polígono';
                    const area = props.area_ha || props.Area_Ha || props.area || '';
                    const producerName = props.Productor || props.producer || props.propietario || '';
                    const producerId = props.producer_id || '';
                    const parishId = props.parish_id || '';

                    row.innerHTML = `
                           <td class="px-2 py-1 whitespace-nowrap">
                            <input type="text" data-field="id" value="${id}" readonly
                                size="${Math.max(String(id).length, 8)}"
                                class="w-auto min-w-[8rem] px-2 py-1 text-sm rounded-md border border-gray-300 dark:border-gray-600 
                                          bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 cursor-not-allowed
                                          focus:outline-none">
                        </td>
                           <td class="px-2 py-1 whitespace-nowrap">
                            <input type="text" data-field="name" value="${name}"
                                size="${Math.max(String(name).length, 16)}"
                                class="w-auto min-w-[12rem] px-2 py-1 text-sm rounded-md border border-gray-300 dark:border-gray-600 
                                          bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                                          focus:outline-none focus:ring-2 focus:ring-custom-gold-dark/60">
                        </td>
                           <td class="px-2 py-1 whitespace-nowrap">
                            <input type="number" step="0.01" data-field="area_ha" value="${area}"
                                size="${Math.max(String(area).length, 10)}"
                                class="w-auto min-w-[9rem] px-2 py-1 text-sm rounded-md border border-gray-300 dark:border-gray-600 
                                          bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                                          focus:outline-none focus:ring-2 focus:ring-custom-gold-dark/60">
                        </td>
                           <td class="px-2 py-1 whitespace-nowrap">
                            <select data-field="producer_id"
                                 class="w-max min-w-[14rem] px-2 py-1 text-sm rounded-md border border-gray-300 dark:border-gray-600 
                                           bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                                           focus:outline-none focus:ring-2 focus:ring-custom-gold-dark/60">
                                ${producersOptions}
                            </select>
                            <input type="hidden" data-field="producer_name" value="${producerName}">
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap">
                            <select data-field="parish_id"
                                    class="w-max min-w-[14rem] px-2 py-1 text-sm rounded-md border border-gray-300 dark:border-gray-600 
                                           bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                                           focus:outline-none focus:ring-2 focus:ring-custom-gold-dark/60">
                                ${parishesOptions}
                            </select>
                        </td>
                    `;

                    previewBody.appendChild(row);

                    const producerSelect = row.querySelector('[data-field="producer_id"]');
                    const parishSelect = row.querySelector('[data-field="parish_id"]');

                    if (producerId) producerSelect.value = producerId;
                    if (parishId) parishSelect.value = parishId;
                });

                featureCount.textContent = `${currentFeatures.length} features`;
                previewContainer.classList.remove('hidden');
                previewContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });

            // =============================================
            // CONFIRMAR Y CERRAR → solo oculta la sección
            // =============================================
            confirmPreviewBtn?.addEventListener('click', function () {
                previewContainer.classList.add('hidden');
                document.querySelector('#import-form')?.scrollIntoView({ behavior: 'smooth', block: 'start' });

                Swal.fire({
                    icon: 'success',
                    title: 'Cambios confirmados',
                    text: 'Puedes proceder a importar cuando estés listo.',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            });

            // =============================================
            // SUBMIT: leer los valores EDITADOS y enviarlos
            // =============================================
            form.addEventListener('submit', function(e) {
                if (currentFeatures.length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin datos para importar',
                        text: 'Por favor, carga un archivo GeoJSON válido primero.',
                        confirmButtonColor: '#c67a2e'
                    });
                    return;
                }

                form.querySelectorAll('.dynamic-feature-input').forEach(el => el.remove());

                const rows = previewBody.querySelectorAll('tr');

                if (rows.length === 0) {
                    const defaultProducerId = document.getElementById('default_producer_id').value;
                    const defaultParishId = document.getElementById('parish_id').value;
                    const producerField = document.getElementById('producer_field').value || 'Productor';

                    currentFeatures.forEach((feature, index) => {
                        const props = feature.properties || {};
                        const fields = {
                            id: props.id || props.ID || '',
                            name: props.name || props.Nombre || 'Polígono importado',
                            area_ha: props.area_ha || props.Area_Ha || props.area || '',
                            producer_id: props.producer_id || defaultProducerId || '',
                            parish_id: props.parish_id || defaultParishId || '',
                            producer_name: props[producerField] || props.Productor || '',
                            geometry: JSON.stringify(feature.geometry),
                        };

                        Object.entries(fields).forEach(([key, value]) => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = `features[${index}][${key}]`;
                            input.value = value;
                            input.classList.add('dynamic-feature-input');
                            form.appendChild(input);
                        });
                    });
                } else {
                    rows.forEach((row, index) => {
                        const feature = currentFeatures[index];
                        if (!feature) return;

                        const getValue = (field) => {
                            const el = row.querySelector(`[data-field="${field}"]`);
                            return el ? el.value : '';
                        };

                        const fields = {
                            id: getValue('id'),
                            name: getValue('name'),
                            area_ha: getValue('area_ha'),
                            producer_id: getValue('producer_id'),
                            parish_id: getValue('parish_id'),
                            producer_name: getValue('producer_name'),
                            geometry: JSON.stringify(feature.geometry),
                        };

                        Object.entries(fields).forEach(([key, value]) => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = `features[${index}][${key}]`;
                            input.value = value;
                            input.classList.add('dynamic-feature-input');
                            form.appendChild(input);
                        });
                    });
                }

                Swal.fire({
                    title: 'Importando...',
                    text: 'Por favor espera mientras se procesan los datos',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => { Swal.showLoading(); }
                });
            });
        });
    </script>

</x-app-layout>
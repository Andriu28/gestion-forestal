{{-- resources/views/polygons/import.blade.php --}}
<x-app-layout>
    <div class="mx-auto">
        {{-- Contenedor principal con animación de entrada --}}
        <div class="bg-stone-100/90 dark:bg-custom-gray shadow-sm sm:rounded-2xl shadow-soft p-4 md:p-6 lg:p-6 mb-6 animate-on-load">
            <div class="text-gray-900 dark:text-gray-100">
                <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-200 mb-4 md:mb-4">
                    Importar Polígonos desde GeoJSON
                </h2>

                <form id="import-form" action="{{ route('polygons.import.process') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    {{-- Archivo --}}
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
                                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Previsualización de features</h3>
                                <span id="feature-count" class="text-xs text-gray-500 dark:text-gray-400 bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded-full"></span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="preview-table">
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
                                    class="px-6 py-2.5 bg-custom-gold-dark hover:bg-custom-gold-darker text-white rounded-lg shadow transition disabled:opacity-50 disabled:cursor-not-allowed font-medium" 
                                    disabled>
                                <span id="import-btn-text">Confirmar Importación</span>
                            </button>
                        </div>
                    </div>

                    {{-- Botones de acción --}}
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            <span id="file-status" class="font-medium text-gray-700 dark:text-gray-300">Ningún archivo seleccionado</span>
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('polygons.index') }}"
                               class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 font-medium transition-colors duration-200">
                                Cancelar
                            </a>
                            <button type="button" id="preview-btn" 
                                    class="px-5 py-2.5 bg-custom-gold-dark hover:bg-custom-gold-darker text-white rounded-lg transition font-medium disabled:opacity-50 disabled:cursor-not-allowed" 
                                    disabled>
                                Previsualizar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal de previsualización --}}
    <div id="preview-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50 transition-opacity" id="preview-modal-overlay"></div>
            
            <!-- Modal -->
            <div class="relative bg-gray-100 dark:bg-custom-gray rounded-xl shadow-2xl w-full max-w-5xl transition-all duration-300 scale-95 opacity-0 pointer-events-none" id="preview-modal-content">
                <!-- Header -->
                <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-600">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Previsualización de Features</h3>
                    <button id="close-preview-modal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Body -->
                <div class="p-6 max-h-[70vh] overflow-y-auto">
                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm bg-white dark:bg-gray-800/50">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="preview-modal-table">
                                <thead class="bg-gray-200 dark:bg-gray-600/30 ">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nombre</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Área (Ha)</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Productor</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Parroquia</th>
                                    </tr>
                                </thead>
                                <tbody id="preview-modal-body" class="bg-gray-50 dark:bg-custom-gray/30 divide-y divide-gray-200 dark:divide-gray-700">
                                    <!-- Las filas se llenarán dinámicamente con JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="flex justify-between items-center p-6 border-t border-gray-200 dark:border-gray-600">
                    <span id="preview-modal-count" class="text-sm text-gray-500 dark:text-gray-400"></span>
                    <div class="flex space-x-3">
                        <button id="close-preview-modal-btn" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 font-medium transition-colors duration-200">
                            Cerrar
                        </button>
                        <button type="submit" form="import-form" id="preview-import-btn" 
                                class="px-6 py-2.5 bg-custom-gold-dark hover:bg-custom-gold-darker text-white rounded-lg shadow transition font-medium">
                            Importar Ahora
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // =============================================
            // ANIMACIÓN DE ENTRADA - EFECTO "APARECER DESDE ABAJO"
            // =============================================
            
            // Aplicar la animación al contenedor principal
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
            const importBtn = document.getElementById('import-btn');
            const fileStatus = document.getElementById('file-status');
            const form = document.getElementById('import-form');
            
            // Modal elements
            const previewModal = document.getElementById('preview-modal');
            const previewModalContent = document.getElementById('preview-modal-content');
            const previewModalBody = document.getElementById('preview-modal-body');
            const previewModalCount = document.getElementById('preview-modal-count');
            const closePreviewModal = document.getElementById('close-preview-modal');
            const closePreviewModalBtn = document.getElementById('close-preview-modal-btn');
            const previewImportBtn = document.getElementById('preview-import-btn');
            const previewModalOverlay = document.getElementById('preview-modal-overlay');

            let currentFeatures = [];

            // =============================================
            // FUNCIONES DEL MODAL
            // =============================================

            function openModal() {
                previewModal.classList.remove('hidden');
                void previewModalContent.offsetWidth;
                previewModalContent.classList.remove('scale-95', 'opacity-0', 'pointer-events-none');
                previewModalContent.classList.add('scale-100', 'opacity-100', 'pointer-events-auto');
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                previewModalContent.classList.remove('scale-100', 'opacity-100', 'pointer-events-auto');
                previewModalContent.classList.add('scale-95', 'opacity-0', 'pointer-events-none');
                setTimeout(() => {
                    previewModal.classList.add('hidden');
                    document.body.style.overflow = '';
                }, 300);
            }

            closePreviewModal?.addEventListener('click', closeModal);
            closePreviewModalBtn?.addEventListener('click', closeModal);
            previewModalOverlay?.addEventListener('click', closeModal);
            
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !previewModal.classList.contains('hidden')) {
                    closeModal();
                }
            });

            // =============================================
            // FUNCIONES DEL ARCHIVO
            // =============================================

            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) {
                    fileStatus.textContent = 'Ningún archivo seleccionado';
                    previewBtn.disabled = true;
                    importBtn.disabled = true;
                    return;
                }

                fileStatus.textContent = `📄 ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
                previewBtn.disabled = false;
                importBtn.disabled = true;
                currentFeatures = [];

                const reader = new FileReader();
                reader.onload = function(event) {
                    try {
                        const geojson = JSON.parse(event.target.result);

                        if (!geojson.type || geojson.type !== 'FeatureCollection') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'El archivo no es un FeatureCollection GeoJSON válido.',
                                confirmButtonColor: '#c67a2e'
                            });
                            return;
                        }

                        const features = geojson.features || [];
                        if (!features.length) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Advertencia',
                                text: 'El archivo no contiene features.',
                                confirmButtonColor: '#c67a2e'
                            });
                            return;
                        }

                        currentFeatures = features;

                        if (geojson.crs && geojson.crs.properties && geojson.crs.properties.name) {
                            const match = geojson.crs.properties.name.match(/EPSG::(\d+)/);
                            if (match) {
                                const detectedSrid = parseInt(match[1]);
                                document.getElementById('srid').value = detectedSrid;
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

                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al leer el archivo',
                            text: error.message,
                            confirmButtonColor: '#c67a2e'
                        });
                        console.error(error);
                        previewBtn.disabled = true;
                    }
                };
                reader.readAsText(file);
            });

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

                previewModalBody.innerHTML = '';
                
                currentFeatures.forEach((feature, index) => {
                    const props = feature.properties || {};
                    const row = document.createElement('tr');
                    
                    const id = props.id || props.ID || `feature-${index}`;
                    const name = props.name || props.Nombre || props.Productor || 'Polígono';
                    const area = props.area_ha || props.Area_Ha || props.area || '';
                    const producer = props.producer || props.Productor || props.propietario || '';
                    const parish = props.parish || props.Parroquia || props.parroquia || '';
                    
                    row.innerHTML = `
                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">${index + 1}</td>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-200 font-mono">${id}</td>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-200">${name}</td>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-200">${area ? parseFloat(area).toFixed(2) : '-'}</td>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-200">${producer || '-'}</td>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-200">${parish || '-'}</td>
                    `;
                    previewModalBody.appendChild(row);
                });

                previewModalCount.textContent = `${currentFeatures.length} features cargados`;
                openModal();
            });

            previewImportBtn?.addEventListener('click', function() {
                form.dispatchEvent(new Event('submit'));
            });

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
                
                Swal.fire({
                    title: 'Importando...',
                    text: 'Por favor espera mientras se procesan los datos',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            });

            console.log('Import page initialized');
        });
    </script>

</x-app-layout>
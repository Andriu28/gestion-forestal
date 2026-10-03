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

                <div class="flex space-x-4">
                    {{-- Análisis de deforestación automático --}}
                    <label for="analyze_deforestation" class="flex items-start gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            name="analyze_deforestation"
                            id="analyze_deforestation"
                            value="1"
                            {{ old('analyze_deforestation') ? 'checked' : '' }}
                            class="mt-1 h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500 dark:bg-gray-700 dark:border-gray-600"
                        >
                        <span class="text-sm">
                            <span class="block font-medium text-gray-800 dark:text-gray-200">
                                Realizar análisis de deforestación al importar
                            </span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Consulta Global Forest Watch para cada polígono (2020–{{ now()->year }}).
                                Puede tardar varios minutos si importas muchos polígonos.
                            </span>
                        </span>
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
        // ============================================================
// PROGRESO DE IMPORTACIÓN EN VIVO
// ============================================================

const ImportProgressWidget = (() => {
    let pollInterval = null;
    let importId = null;

    const el = {
        widget:    () => document.getElementById('import-progress-widget'),
        bar:       () => document.getElementById('import-progress-bar'),
        text:      () => document.getElementById('import-progress-text'),
        pct:       () => document.getElementById('import-progress-pct'),
        list:      () => document.getElementById('import-progress-list'),
        spinner:   () => document.getElementById('import-progress-spinner'),
        footer:    () => document.getElementById('import-progress-footer'),
        summary:   () => document.getElementById('import-progress-summary'),
        close:     () => document.getElementById('import-progress-close'),
        minimize:  () => document.getElementById('import-progress-minimize'),
    };

    function show() {
        const w = el.widget();
        if (!w) return;
        w.classList.remove('hidden');
        w.classList.add('animate-in', 'fade-in', 'slide-in-from-bottom-4');

        // Resetear UI
        el.bar().style.width = '0%';
        el.text().textContent = 'Iniciando…';
        el.pct().textContent = '0%';
        el.list().innerHTML = '';
        el.footer().classList.add('hidden');
        el.spinner()?.classList.remove('hidden');
    }

    function hide() {
        el.widget()?.classList.add('hidden');
    }

    function renderList(features) {
        const container = el.list();
        if (!container) return;

        container.innerHTML = '';

        Object.entries(features).forEach(([idx, f]) => {
            const row = document.createElement('div');
            row.className = 'flex items-center gap-2 py-0.5';

            const icon = statusIcon(f.status);
            const color = statusColor(f.status);

            row.innerHTML = `
                <span class="flex-shrink-0">${icon}</span>
                <span class="flex-1 truncate text-gray-700 dark:text-gray-300">
                    ${escapeHtml(f.name || `Feature #${idx}`)}
                </span>
                ${f.deforested_ha !== null && f.deforested_ha !== undefined ? `
                    <span class="text-xs font-medium ${color}">
                        ${Number(f.deforested_ha).toFixed(2)} ha
                    </span>` : ''}
            `;
            container.appendChild(row);
        });

        // Auto-scroll al final
        container.scrollTop = container.scrollHeight;
    }

    function statusIcon(status) {
        switch (status) {
            case 'analyzed':    return '🔍';
            case 'success':     return '✅';
            case 'skipped':     return '⏭️';
            case 'duplicated':  return '⚠️';
            case 'error':       return '❌';
            default:            return '⏳';
        }
    }

    function statusColor(status) {
        switch (status) {
            case 'analyzed':    return 'text-purple-600 dark:text-purple-400';
            case 'success':     return 'text-emerald-600 dark:text-emerald-400';
            case 'duplicated':  return 'text-yellow-600 dark:text-yellow-400';
            case 'error':       return 'text-red-600 dark:text-red-400';
            default:            return 'text-gray-500';
        }
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function update(state) {
        const total   = state.total || 0;
        const current = state.current || 0;
        const pct     = total > 0 ? Math.round((current / total) * 100) : 0;

        el.bar().style.width = `${pct}%`;
        el.pct().textContent = `${pct}%`;
        el.text().textContent = `${current} de ${total} polígonos`;

        renderList(state.feature_statuses || {});

        if (state.status === 'done') {
            finish(state);
        }
    }

    function finish(state) {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }

        el.spinner()?.classList.add('hidden');
        el.bar().style.width = '100%';
        el.pct().textContent = '100%';
        el.text().textContent = 'Completado';

        const s = state.summary || {};
        const parts = [];
        if (s.imported)               parts.push(`✅ ${s.imported} importado(s)`);
        if (s.analyzed)               parts.push(`🔍 ${s.analyzed} analizado(s)`);
        if (s.skipped)                parts.push(`⏭️ ${s.skipped} omitido(s)`);
        if (s.duplicated?.length)     parts.push(`⚠️ ${s.duplicated.length} duplicado(s)`);
        if (s.errors?.length)         parts.push(`❌ ${s.errors.length} error(es)`);

        el.summary().innerHTML = parts.length ? parts.join(' · ') : 'Sin cambios.';
        el.footer().classList.remove('hidden');

        // Auto-cerrar a los 8s si no hay errores
        if (!s.errors?.length) {
            setTimeout(() => hide(), 8000);
        }
    }

    async function start(id) {
        importId = id;
        show();

        // Polling cada 800ms
        pollInterval = setInterval(async () => {
            try {
                const res = await fetch(`/polygons/import/progress/${importId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!res.ok) {
                    // 404 → probablemente ya expiró; detenemos el polling
                    if (res.status === 404) stop();
                    return;
                }

                const data = await res.json();
                if (data.success) update(data.state);
            } catch (e) {
                console.warn('Polling error:', e);
            }
        }, 800);
    }

    function stop() {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }

    // Listeners de UI
    document.addEventListener('DOMContentLoaded', () => {
        el.close()?.addEventListener('click', hide);
        el.minimize()?.addEventListener('click', hide);
    });

    return { start, stop, hide };
})();

// ============================================================
// INTERCEPTAR SUBMIT DEL FORM DE IMPORTACIÓN
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form[action*="polygons/import"]')
              || document.getElementById('import-form');

    if (!form) {
        console.warn('No se encontró el formulario de importación');
        return;
    }

    form.addEventListener('submit', async (e) => {
        const analyzeChk = document.getElementById('analyze_deforestation');
        const wantsAnalysis = analyzeChk?.checked;

        // Si no se pidió análisis, dejamos el submit normal (rápido)
        if (!wantsAnalysis) {
            return; // comportamiento por defecto
        }

        e.preventDefault();

        // Generar un ID único para esta importación
        const importId = (crypto.randomUUID?.() || Date.now().toString(36))
            + '-' + Math.random().toString(36).slice(2, 8);

        // Inyectar hidden input con import_id
        let hiddenId = form.querySelector('input[name="import_id"]');
        if (!hiddenId) {
            hiddenId = document.createElement('input');
            hiddenId.type = 'hidden';
            hiddenId.name = 'import_id';
            form.appendChild(hiddenId);
        }
        hiddenId.value = importId;

        // Arrancar el widget
        ImportProgressWidget.start(importId);

        // Enviar el formulario vía fetch para no bloquear la página
        try {
            const formData = new FormData(form);
            const res = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                redirect: 'manual', // manejamos el redirect nosotros
            });

            // Laravel devuelve 302 en redirects; fetch con redirect:manual lo captura
            if (res.type === 'opaqueredirect' || res.status === 302) {
                // La importación terminó. Damos tiempo al polling a recibir el estado 'done'
                setTimeout(() => {
                    window.location.href = '/polygons';
                }, 1500);
                return;
            }

            // Si llega 200 con HTML (por ejemplo errores de validación), recargamos
            if (res.status === 200) {
                const html = await res.text();
                document.open();
                document.write(html);
                document.close();
                return;
            }

            // Cualquier otro estado
            console.warn('Respuesta inesperada:', res.status);
            ImportProgressWidget.stop();
        } catch (err) {
            console.error('Error enviando importación:', err);
            ImportProgressWidget.stop();
            // Fallback: submit normal
            form.submit();
        }
    });
});
    </script>
    {{-- Widget de progreso de importación (esquina inferior derecha) --}}
<div id="import-progress-widget"
     class="fixed bottom-4 right-4 z-50 hidden w-80 rounded-xl shadow-2xl
            bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
            overflow-hidden transition-all duration-300">

    {{-- Header --}}
    <div class="flex items-center justify-between px-4 py-3
                bg-gradient-to-r from-emerald-600 to-emerald-500
                dark:from-emerald-700 dark:to-emerald-600">
        <div class="flex items-center gap-2">
            <svg id="import-progress-spinner"
                 class="w-5 h-5 text-white animate-spin"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span class="text-sm font-semibold text-white">Importando polígonos</span>
        </div>
        <button type="button" id="import-progress-minimize"
                class="text-white/80 hover:text-white transition-colors p-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M20 12H4"/>
            </svg>
        </button>
    </div>

    {{-- Barra de progreso --}}
    <div class="px-4 pt-3">
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
            <div id="import-progress-bar"
                 class="bg-emerald-500 h-2 rounded-full transition-all duration-500 ease-out"
                 style="width: 0%;"></div>
        </div>
        <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mt-1.5">
            <span id="import-progress-text">Iniciando…</span>
            <span id="import-progress-pct">0%</span>
        </div>
    </div>

    {{-- Lista de features --}}
    <div id="import-progress-list"
         class="max-h-52 overflow-y-auto px-4 py-3 space-y-1.5 text-xs">
        {{-- se llena dinámicamente --}}
    </div>

    {{-- Footer con resumen --}}
    <div id="import-progress-footer"
         class="hidden px-4 py-3 border-t border-gray-200 dark:border-gray-700
                bg-gray-50 dark:bg-gray-900/40">
        <div class="text-xs text-gray-700 dark:text-gray-300" id="import-progress-summary"></div>
        <button type="button" id="import-progress-close"
                class="mt-2 w-full py-1.5 text-xs font-medium rounded-md
                       bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600
                       text-gray-700 dark:text-gray-200 transition-colors">
            Cerrar
        </button>
    </div>
</div>
</x-app-layout>
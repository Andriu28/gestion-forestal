<x-app-layout>
    <div class="mx-auto">
        <div class="p-4 overflow-hidden shadow-sm bg-stone-100/90 dark:bg-custom-gray sm:rounded-2xl shadow-soft md:p-6 lg:p-8">

            @if(session('save_success'))
                <div class="save-message success">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        {{ session('save_success') }}
                    </div>
                </div>
            @endif

            <!-- Encabezado -->
            <div class="flex flex-col gap-3 mb-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-3xl font-semibold leading-tight text-gray-900 dark:text-gray-100">
                        Resultados del Análisis Múltiple
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Comparación de {{ count($multiResults) }} {{ count($multiResults) === 1 ? 'polígono analizado' : 'polígonos analizados' }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('deforestation.create') }}"
                       class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 transition-colors whitespace-nowrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/>
                        </svg>
                        Nuevo Análisis
                    </a>
                     <!-- Botón para crear nuevo análisis -->
                            <a href="{{ route('deforestation.create') }}"
                            title="Crear nuevo análisis" 
                            class="group px-2.5 py-1.5 bg-stone-200/80 hover:bg-green-600/70 dark:hover:bg-green-500/60 text-stone-700 hover:text-white border border-stone-300/70 hover:border-transparent dark:bg-gray-700/40 dark:text-gray-300 dark:hover:text-white dark:border-gray-600/50 rounded-md flex items-center hover:shadow-md hover:-translate-y-0.5 overflow-hidden">
                            
                                <!-- Contenedor del ícono - se contrae en hover -->
                                <span class="flex items-center justify-center w-6 h-6 transition-all duration-300 group-hover:w-6 group-hover:h-6 flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2002/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-emerald-700/70 group-hover:text-white dark:text-emerald-500/70">
                                        <circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/>
                                    </svg>
                                </span>
                                
                                <!-- Texto - oculto en estado normal, visible en hover -->
                                <span class="text-base font-medium transition-all duration-300 w-0 opacity-0 group-hover:w-10 group-hover:opacity-100 group-hover:ml-1 whitespace-nowrap overflow-hidden text-inherit">
                                    Crear
                                </span>
                            </a>
                    @if(count($multiResults) > 0)
                        <button id="btn-download-pdf" type="button" onclick="downloadCurrentPDF()"
                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-red-600 rounded-lg shadow-sm hover:bg-red-700 transition-colors whitespace-nowrap">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 15V3"/><path d="m7 10 5 5 5-5"/><path d="M20 21H4"/>
                            </svg>
                            Descargar PDF
                        </button>
                    @endif
                </div>
            </div>

            @if(count($multiResults) > 0)
                @php
                    $totalPolygons = count($multiResults);
                    $sumTotalArea = 0;
                    $sumDeforested = 0;
                    foreach ($multiResults as $p) {
                        $sumTotalArea += $p['polygon_area_ha'] ?? 0;
                        $sumDeforested += $p['total_loss']['totalDeforestedArea'] ?? 0;
                    }
                    $avgPercentage = $sumTotalArea > 0 ? ($sumDeforested / $sumTotalArea) * 100 : 0;
                @endphp

                <!-- Ribbon de indicadores generales -->
                <div class="flex flex-wrap items-center gap-x-8 gap-y-3 px-5 py-4 mb-6 bg-white border border-gray-200 dark:bg-gray-800/30 dark:border-gray-700 rounded-xl">
                    <div>
                        <p class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Polígonos</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $totalPolygons }}</p>
                    </div>
                    <div class="hidden w-px h-10 bg-gray-200 dark:bg-gray-700 sm:block"></div>
                    <div>
                        <p class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Área Total</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($sumTotalArea, 4, ',', '.') }} <span class="text-sm font-normal text-gray-500 dark:text-gray-400">ha</span></p>
                    </div>
                    <div class="hidden w-px h-10 bg-gray-200 dark:bg-gray-700 sm:block"></div>
                    <div>
                        <p class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Área Deforestada</p>
                        <p class="text-xl font-bold text-red-600 dark:text-red-400">{{ number_format($sumDeforested, 4, ',', '.') }} <span class="text-sm font-normal text-gray-500 dark:text-gray-400">ha</span></p>
                    </div>
                    <div class="hidden w-px h-10 bg-gray-200 dark:bg-gray-700 sm:block"></div>
                    <div>
                        <p class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Deforestación Promedio</p>
                        <p class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($avgPercentage, 2) }}%</p>
                    </div>
                </div>

                <!-- Layout maestro-detalle: lista a la izquierda, detalle siempre visible a la derecha -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-[340px_1fr] lg:items-start">

                    <!-- Columna izquierda: lista de polígonos -->
                    <div class="lg:sticky lg:top-6">
                        <div class="mb-3 space-y-2">
                            <div class="relative">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     class="absolute -translate-y-1/2 pointer-events-none left-3 top-1/2 text-gray-400">
                                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                                </svg>
                                <input id="polygon-search" type="text" autocomplete="off"
                                       placeholder="Buscar por nombre o productor..."
                                       class="w-full py-2 pl-9 pr-3 text-sm bg-white border border-gray-300 rounded-lg dark:bg-gray-800/40 dark:border-gray-600 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <select id="sort-select"
                                        class="w-full py-1.5 pl-2 pr-8 text-xs bg-white border border-gray-300 rounded-lg dark:bg-gray-800/40 dark:border-gray-600 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="name-asc">Ordenar: Nombre (A-Z)</option>
                                    <option value="pct-desc">Ordenar: % Deforestación (mayor a menor)</option>
                                    <option value="pct-asc">Ordenar: % Deforestación (menor a mayor)</option>
                                    <option value="area-desc">Ordenar: Área total (mayor a menor)</option>
                                </select>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Mostrando <span id="visible-count" class="font-semibold text-gray-700 dark:text-gray-300">{{ $totalPolygons }}</span> de {{ $totalPolygons }} polígonos
                            </p>
                        </div>

                        <div id="polygon-list" class="space-y-2 overflow-y-auto lg:max-h-[65vh] pr-1 -mr-1">
                            @foreach($multiResults as $index => $polygonData)
                                @php
                                    $totalArea = $polygonData['polygon_area_ha'] ?? 0;
                                    $deforestedArea = $polygonData['total_loss']['totalDeforestedArea'] ?? 0;
                                    $percentage = $totalArea > 0 ? ($deforestedArea / $totalArea) * 100 : 0;
                                    $productorName = $polygonData['productor_name'] ?? 'Sin productor';

                                    if ($percentage >= 20) {
                                        $dotClass = 'bg-red-500';
                                        $badgeClasses = 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300';
                                    } elseif ($percentage >= 5) {
                                        $dotClass = 'bg-yellow-500';
                                        $badgeClasses = 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300';
                                    } else {
                                        $dotClass = 'bg-green-500';
                                        $badgeClasses = 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300';
                                    }
                                @endphp
                                <button type="button"
                                        id="card-{{ $index }}"
                                        onclick="selectPolygon({{ $index }})"
                                        data-search="{{ Str::lower($polygonData['polygon_name'] . ' ' . $productorName) }}"
                                        data-name="{{ Str::lower($polygonData['polygon_name']) }}"
                                        data-pct="{{ $percentage }}"
                                        data-area="{{ $totalArea }}"
                                        class="sidebar-card w-full text-left p-3 bg-white dark:bg-gray-800/30 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-blue-300 dark:hover:border-blue-700 transition-colors">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate dark:text-gray-100">
                                                <span class="inline-block w-2 h-2 mr-1.5 rounded-full {{ $dotClass }}"></span>
                                                {{ $polygonData['polygon_name'] }}
                                            </p>
                                            <p class="mt-0.5 text-xs text-gray-500 truncate dark:text-gray-400">{{ $productorName }}</p>
                                        </div>
                                        <span class="flex-shrink-0 px-2 py-0.5 text-xs font-semibold rounded-full {{ $badgeClasses }}">
                                            {{ number_format($percentage, 1) }}%
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3 mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        <span>Total: {{ number_format($totalArea, 2, ',', '.') }} ha</span>
                                        <span class="text-red-600 dark:text-red-400">Deforestado: {{ number_format($deforestedArea, 2, ',', '.') }} ha</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                        <p id="no-results-msg" class="hidden py-6 text-xs text-center text-gray-500 dark:text-gray-400">
                            Sin resultados para ese criterio.
                        </p>
                    </div>

                    <!-- Columna derecha: detalle del polígono seleccionado -->
                    <div class="min-w-0 p-4 bg-white border border-gray-200 dark:bg-gray-800/20 dark:border-gray-700 rounded-xl md:p-6">
                        <div id="detail-content"></div>
                    </div>
                </div>
            @else
                <!-- Estado vacío -->
                <div class="py-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-gray-600 dark:text-gray-400">No hay resultados para mostrar.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Incluir librerías necesarias -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/css/ol.css">
    <script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/build/ol.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Datos completos de todos los polígonos (convertidos a JSON seguro)
        const polygonsData = @json($multiResults);
        let currentMap = null;
        let currentGfwLayer = null;
        let currentEvolutionChart = null;
        let currentDistributionChart = null;
        let selectedIndex = null;

        function selectPolygon(index) {
            const data = polygonsData[index];
            if (!data) return;

            selectedIndex = index;
            highlightSelectedCard(index);
            renderDetailContent(data);
            updateDownloadButton(data);

            // En pantallas angostas la lista queda arriba del detalle: llevamos la vista hacia el panel
            if (window.innerWidth < 1024) {
                document.getElementById('detail-content').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        function updateDownloadButton(data) {
            const btn = document.getElementById('btn-download-pdf');
            if (btn) {
                btn.title = 'Descargar PDF de ' + data.polygon_name;
            }
        }

        function downloadCurrentPDF() {
            if (selectedIndex === null || !polygonsData[selectedIndex]) return;
            generateSinglePDF(polygonsData[selectedIndex]);
        }

        function highlightSelectedCard(index) {
            document.querySelectorAll('.sidebar-card').forEach(card => {
                card.classList.remove('sidebar-card-active');
            });
            const card = document.getElementById('card-' + index);
            if (card) card.classList.add('sidebar-card-active');
        }

        function formatNumber(value) {
            return value.toLocaleString('es-ES', { minimumFractionDigits: 4, maximumFractionDigits: 4 });
        }

        function renderDetailContent(data) {
            const container = document.getElementById('detail-content');
            if (!container) return;

            // Usamos HTML plano, sin componentes Blade
            const totalArea = data.polygon_area_ha;
            const deforestedArea = data.total_loss.totalDeforestedArea;
            const conservedArea = totalArea - deforestedArea;
            const percentageLoss = totalArea > 0 ? (deforestedArea / totalArea) * 100 : 0;

            container.innerHTML = `
                <div class="mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">${data.polygon_name}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Período analizado: ${data.start_year} - ${data.end_year}</p>
                </div>

                <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-4">
                    <div class="p-4 text-center rounded-lg bg-green-50 dark:bg-green-900/20">
                        <p class="text-sm font-bold text-green-600 uppercase dark:text-green-400">Área Total del Polígono</p>
                        <p class="text-2xl font-bold text-green-700 dark:text-green-300">${formatNumber(totalArea)} <span class="text-sm">ha</span></p>
                    </div>
                    <div class="p-4 text-center rounded-lg bg-red-50 dark:bg-red-900/20">
                        <p class="text-sm font-bold text-red-600 uppercase dark:text-red-400">Área Deforestada</p>
                        <p class="text-2xl font-bold text-red-700 dark:text-red-300">${formatNumber(deforestedArea)} <span class="text-sm">ha</span></p>
                    </div>
                    <div class="p-4 text-center rounded-lg bg-purple-50 dark:bg-purple-900/20">
                        <p class="text-sm font-bold text-purple-600 uppercase dark:text-purple-400">Pérdida Acumulada</p>
                        <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">${percentageLoss.toFixed(2)}%</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">del área total</p>
                    </div>
                    <div class="p-4 text-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                        <p class="text-sm font-bold text-blue-600 uppercase dark:text-blue-400">Área Conservada</p>
                        <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">${formatNumber(conservedArea)} <span class="text-sm">ha</span></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <div>
                        <h3 class="mb-3 text-xl font-semibold text-gray-900 dark:text-gray-100">Área de Interés</h3>
                        <div id="detail-map" style="height: 430px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></div>
                    </div>
                    <div>
                        <h3 class="mb-3 text-xl font-semibold text-gray-900 dark:text-gray-100">Distribución del Área</h3>
                        <div class="p-4 bg-gray-100 rounded-lg shadow-inner dark:bg-gray-800/40" style="height: 430px;">
                            <canvas id="detail-distribution-chart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="mt-8">
                    <h3 class="mb-3 text-xl font-semibold text-gray-900 dark:text-gray-100">Evolución de la Deforestación (${data.start_year}-${data.end_year})</h3>
                    <div class="p-4 bg-gray-100 rounded-lg shadow-inner dark:bg-gray-800/40" style="height: 430px;">
                        <canvas id="detail-evolution-chart"></canvas>
                    </div>
                </div>
            `;

            initDetailMap(data.original_geojson);
            initDetailCharts(data, totalArea, deforestedArea, conservedArea);
        }

        function initDetailMap(geojsonString) {
            const target = 'detail-map';
            if (currentMap) currentMap.setTarget(null);

            currentMap = new ol.Map({
                target: target,
                layers: [
                    new ol.layer.Tile({
                        source: new ol.source.XYZ({
                            url: 'https://{a-c}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
                            attributions: '© OpenStreetMap contributors',
                            maxZoom: 20
                        })
                    })
                ],
                view: new ol.View({
                    center: ol.proj.fromLonLat([-63.176998, 10.562177]),
                    zoom: 6
                })
            });

            const GFW_URL = 'https://tiles.globalforestwatch.org/umd_tree_cover_loss/latest/dynamic/{z}/{x}/{y}.png';
            currentGfwLayer = new ol.layer.Tile({
                source: new ol.source.XYZ({ url: GFW_URL, attributions: 'GFW' }),
                opacity: 0.75,
                visible: true
            });
            currentMap.addLayer(currentGfwLayer);

            const format = new ol.format.GeoJSON();
            let features = format.readFeatures(geojsonString, {
                dataProjection: 'EPSG:4326',
                featureProjection: 'EPSG:3857'
            });
            if (features.length === 0) {
                features = format.readFeatures(geojsonString, {
                    dataProjection: 'EPSG:3857',
                    featureProjection: 'EPSG:3857'
                });
            }
            if (features.length > 0) {
                const vectorLayer = new ol.layer.Vector({
                    source: new ol.source.Vector({ features: features }),
                    style: new ol.style.Style({
                        stroke: new ol.style.Stroke({ color: 'rgba(59, 130, 246, 0.8)', width: 3 }),
                        fill: new ol.style.Fill({ color: 'rgba(59, 130, 246, 0.2)' })
                    })
                });
                currentMap.addLayer(vectorLayer);
                currentMap.getView().fit(vectorLayer.getSource().getExtent(), { padding: [50, 50, 50, 50], duration: 1000 });
            }
        }

        function initDetailCharts(data, totalArea, deforestedArea, conservedArea) {
            // Gráfico de distribución
            const ctxDist = document.getElementById('detail-distribution-chart').getContext('2d');
            if (currentDistributionChart) currentDistributionChart.destroy();
            currentDistributionChart = new Chart(ctxDist, {
                type: 'doughnut',
                data: {
                    labels: ['Área Conservada', 'Área Deforestada'],
                    datasets: [{
                        data: [conservedArea, deforestedArea],
                        backgroundColor: ['rgba(75, 192, 192, 0.8)', 'rgba(255, 99, 132, 0.8)'],
                        borderColor: ['rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed;
                                    const percentage = ((value / totalArea) * 100).toFixed(2);
                                    return `${context.label}: ${value.toFixed(4)} ha (${percentage}%)`;
                                }
                            }
                        },
                        title: { display: true, text: 'Estado Actual del Predio', font: { size: 18 } }
                    }
                }
            });

            // Gráfico de evolución
            const yearlyResults = data.yearly_results;
            const startYear = data.start_year;
            const endYear = data.end_year;
            const labels = [];
            const evolutionData = [];
            for (let year = startYear; year <= endYear; year++) {
                labels.push(year.toString());
                const area = (yearlyResults[year] && yearlyResults[year].area__ha) ? yearlyResults[year].area__ha : 0;
                evolutionData.push(area);
            }

            const ctxEvol = document.getElementById('detail-evolution-chart').getContext('2d');
            if (currentEvolutionChart) currentEvolutionChart.destroy();
            currentEvolutionChart = new Chart(ctxEvol, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Área Deforestada (ha)',
                        data: evolutionData,
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        pointBackgroundColor: evolutionData.map(v => v > 0 ? 'rgba(34, 197, 94, 0.8)' : 'rgba(156, 163, 175, 0.5)')
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: { display: true, text: `Evolución de la Deforestación (${startYear}-${endYear})` },
                        tooltip: { callbacks: { label: (ctx) => `${ctx.parsed.y.toFixed(6)} ha` } }
                    },
                    scales: {
                        y: { beginAtZero: true, title: { display: true, text: 'Hectáreas' } },
                        x: { title: { display: true, text: 'Años' } }
                    }
                }
            });
        }

        function generateSinglePDF(data) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("deforestation.report") }}';
            form.target = '_blank';
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            form.appendChild(csrf);
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'report_data';
            input.value = JSON.stringify({ dataToPass: data });
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);f400
        }

        // Buscador de la lista de polígonos
        const searchInput = document.getElementById('polygon-search');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const term = this.value.trim().toLowerCase();
                const cards = document.querySelectorAll('.sidebar-card');
                const visibleCountEl = document.getElementById('visible-count');
                const noResultsMsg = document.getElementById('no-results-msg');
                let visibleCount = 0;

                cards.forEach(card => {
                    const matches = card.dataset.search.includes(term);
                    card.classList.toggle('hidden', !matches);
                    if (matches) visibleCount++;
                });

                if (visibleCountEl) visibleCountEl.textContent = visibleCount;
                if (noResultsMsg) noResultsMsg.classList.toggle('hidden', visibleCount !== 0);
            });
        }

        // Orden de la lista de polígonos
        const sortSelect = document.getElementById('sort-select');
        if (sortSelect) {
            sortSelect.addEventListener('change', function() {
                const list = document.getElementById('polygon-list');
                if (!list) return;
                const cards = Array.from(list.querySelectorAll('.sidebar-card'));

                cards.sort((a, b) => {
                    switch (this.value) {
                        case 'pct-desc':
                            return parseFloat(b.dataset.pct) - parseFloat(a.dataset.pct);
                        case 'pct-asc':
                            return parseFloat(a.dataset.pct) - parseFloat(b.dataset.pct);
                        case 'area-desc':
                            return parseFloat(b.dataset.area) - parseFloat(a.dataset.area);
                        case 'name-asc':
                        default:
                            return a.dataset.name.localeCompare(b.dataset.name);
                    }
                });

                cards.forEach(card => list.appendChild(card));
            });
        }

        // Seleccionar automáticamente el primer polígono al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            if (polygonsData.length > 0) {
                selectPolygon(0);
            }
        });
    </script>

    <style>
        .save-message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .save-message.success {
            background-color: #d1fae5;
            border: 1px solid #10b981;
            color: #065f46;
        }
        .sidebar-card-active {
            border-color: rgb(37, 99, 235) !important;
            background-color: rgba(59, 130, 246, 0.06);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }
        .dark .sidebar-card-active {
            background-color: rgba(59, 130, 246, 0.1);
        }
    </style>
</x-app-layout>
<x-app-layout>
    <div class="mx-auto">
        <div class="p-4 overflow-hidden shadow-sm bg-stone-100/90 dark:bg-custom-gray sm:rounded-2xl shadow-soft md:p-6 lg:p-8">
            @if(session('save_success'))
                <div class="mb-4 save-message success">
                    {{ session('save_success') }}
                </div>
            @endif

            <h2 class="mb-6 text-3xl font-semibold leading-tight text-gray-900 dark:text-gray-100">
                Resultados del Análisis Múltiple
            </h2>

            <!-- Tabla resumen de polígonos -->
            <div class="mb-8 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">#</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Nombre</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Productor</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Área Total (ha)</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Área Deforestada (ha)</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">% Deforestación</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800/20 dark:divide-gray-700">
                        @foreach($multiResults as $index => $polygonData)
                            @php
                                $totalArea = $polygonData['polygon_area_ha'] ?? 0;
                                $deforestedArea = $polygonData['total_loss']['totalDeforestedArea'] ?? 0;
                                $percentage = $totalArea > 0 ? ($deforestedArea / $totalArea) * 100 : 0;
                                $productorName = $polygonData['productor_name'] ?? 'Sin productor';
                            @endphp
                            <tr class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50" onclick="selectPolygon({{ $index }})">
                                <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap dark:text-gray-100">{{ $polygonData['polygon_name'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">{{ $productorName }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">{{ number_format($totalArea, 4, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm text-red-600 whitespace-nowrap dark:text-red-400">{{ number_format($deforestedArea, 4, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">{{ number_format($percentage, 2) }}%</td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    <button onclick="event.stopPropagation(); selectPolygon({{ $index }})" class="font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                        Ver detalles
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Panel de detalle del polígono seleccionado -->
            <div id="detail-panel" class="hidden">
                <div class="pt-6 mt-4 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="mb-4 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                        Detalles del polígono: <span id="detail-name"></span>
                    </h3>
                    <div id="detail-content"></div>
                </div>
            </div>
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

        function selectPolygon(index) {
            const data = polygonsData[index];
            if (!data) return;

            const panel = document.getElementById('detail-panel');
            panel.classList.remove('hidden');
            document.getElementById('detail-name').innerText = data.polygon_name;
            renderDetailContent(data);
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
                <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-4">
                    <div class="p-4 text-center rounded-lg bg-green-50 dark:bg-green-900/20">
                        <p class="text-sm font-bold text-green-600 uppercase dark:text-green-400">Área Total del Polígono</p>
                        <p class="text-2xl font-bold text-green-700 dark:text-green-300">${formatNumber(totalArea)} <span class="text-sm">ha</span></p>
                    </div>
                    <div class="p-4 text-center rounded-lg bg-red-50 dark:bg-red-900/20">
                        <p class="text-sm font-bold text-red-600 uppercase dark:text-red-400">Área Deforestada ${data.start_year} - ${data.end_year}</p>
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
                        <div id="detail-map" style="height: 400px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></div>
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
                    <div class="p-4 bg-gray-100 rounded-lg shadow-inner dark:bg-gray-800/40" style="height: 400px;">
                        <canvas id="detail-evolution-chart"></canvas>
                    </div>
                </div>

                <div class="pt-4 mt-8 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('deforestation.create') }}" class="inline-flex items-center px-6 py-3 text-base font-medium text-white bg-blue-600 border border-transparent rounded-lg shadow-sm hover:bg-blue-700">
                            Nuevo Análisis
                        </a>
                        <button onclick="generateSinglePDF(${JSON.stringify(data).replace(/"/g, '&quot;')})" class="inline-flex items-center px-6 py-3 text-base font-medium text-white bg-red-600 border border-transparent rounded-lg shadow-sm hover:bg-red-700">
                            Descargar PDF (este polígono)
                        </button>
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
            document.body.removeChild(form);
        }
    </script>

    <style>
        .save-message.success {
            background-color: #d1fae5;
            border: 1px solid #10b981;
            color: #065f46;
            padding: 12px;
            border-radius: 8px;
        }
        .cursor-pointer {
            cursor: pointer;
        }
    </style>
</x-app-layout>
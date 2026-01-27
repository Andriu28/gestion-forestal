<x-app-layout>
    @if(session('save_success'))
    <div class="save-message success">
        <div class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            {{ session('save_success') }}
        </div>
    </div>
@endif

@if(isset($dataToPass['save_message']))
    <div class="save-message info">
        {{ $dataToPass['save_message'] }}
    </div>
@endif

@if(isset($dataToPass['save_error']))
    <div class="save-message error">
        {{ $dataToPass['save_error'] }}
    </div>
@endif
    <div class="mx-auto ">
        <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden shadow-sm sm:rounded-2xl shadow-soft p-4 md:p-6 lg:p-8 ">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">¡Éxito!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class=" overflow-hidden ">
                <h2 class="font-semibold text-3xl text-gray-900 dark:text-gray-100 leading-tight mb-6">
                    Resultados del Análisis de Deforestación
                </h2>

                <!-- Información del Área de Estudio -->
                <div class="mb-8 p-4 bg-grey-300 dark:bg-gray-600/10 rounded-lg">
                    <h3 class="font-semibold text-xl text-grey-800 dark:text-grey-100 mb-2">Nombre del Polígono:
                        {{ $dataToPass['polygon_name'] }}
                    </h3>
                    @if($dataToPass['description'])
                        <p class="text-grey-800 dark:text-grey-100">Descripción del Polígono: {{ $dataToPass['description'] }}</p>
                    @endif
                </div>

                <!-- seccion que permite editar rango de fecha -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    
                    <x-forms.year-range-editor 
                        :start-year="$dataToPass['start_year']" 
                        :end-year="$dataToPass['end_year']" 
                    />

                    <x-cards.stats-card 
                        title="Área Total del Polígono" 
                        color="green" 
                        value="{{ number_format($dataToPass['polygon_area_ha'], 4, ',', '.') }}" 
                        unit="ha" 
                    />

                    @php
                        $areaHa = $dataToPass['total_loss']['totalDeforestedArea'] ?? 0;
                        $polygonArea = $dataToPass['polygon_area_ha'] ?? 1;
                        $currentYearPercentage = $polygonArea > 0 ? ($areaHa / $polygonArea) * 100 : 0;
                    @endphp

                    <x-cards.stats-card 
                        title="Área Deforestada {{ $dataToPass['start_year'] }} - {{ $dataToPass['end_year'] }}" 
                        color="red" 
                        value="{{ number_format($areaHa, 4, ',', '.') }}" 
                        unit="ha" 
                    >
                    </x-cards.stats-card>

                    <x-cards.total-loss-card :data-to-pass="$dataToPass" />
                </div>
                

                <!-- Resumen Estadístico -->
                <div class="mb-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 dark:bg-gray-800/40 p-4 rounded-lg">
                        <h4 class="font-semibold text-lg text-gray-900 dark:text-gray-100 mb-3">Resumen del Área</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Área total del poligono:</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($dataToPass['polygon_area_ha'], 4, ',', '.') }} ha</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Area deforestada:</span>
                                <span class="font-medium text-red-600 dark:text-red-400">{{ number_format($dataToPass['total_loss']['totalDeforestedArea'], 4, ',', '.') }} ha</span>
                            </div>
                            <div class="flex justify-between border-t pt-2">
                                <span class="text-gray-600 dark:text-gray-300">Área conservada:</span>
                                @php
                                    $conservedArea = $dataToPass['polygon_area_ha'] - $dataToPass['total_loss']['totalDeforestedArea'];
                                @endphp
                                <span class="font-medium text-green-600 dark:text-green-400">{{ number_format($conservedArea, 4, ',', '.') }} ha</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-800/40 p-4 rounded-lg">
                        <h4 class="font-semibold text-lg text-gray-900 dark:text-gray-100 mb-3">Estado del Servicio</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Estado:</span>
                                <span class="font-medium @if($dataToPass['status'] === 'success') text-green-600 @else text-red-600 @endif">
                                    {{ $dataToPass['status'] }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Tipo de geometría:</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $dataToPass['type'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-semibold text-xl text-gray-900 dark:text-gray-100">
                                Área de Interés
                            </h3>
                            <!-- Controles del mapa -->
                            <div class="flex space-x-2">
                                <!-- Botón para mostrar/ocultar capa de deforestación -->
                                <button id="toggle-gfw-layer" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg flex items-center shadow-lg" title="Ocultar Deforestación">
                                    <span id="gfw-eye-open">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </span>
                                    <span id="gfw-eye-closed" class="hidden">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                                            <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                                            <line x1="2" x2="22" y1="2" y2="22"/>
                                        </svg>
                                    </span>
                                </button>

                                <!-- Control de opacidad -->
                                <div class="relative">
                                    <button id="result-opacity-control" title="Ajustar Opacidad" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg flex items-center shadow-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/>
                                            <path d="m22 17.46-8.58-3.91a2 2 0 0 0-1.66 0L3 17.46"/>
                                            <path d="m22 12.46-8.58-3.91a2 2 0 0 0-1.66 0L3 12.46"/>
                                        </svg>
                                    </button>
                                    
                                    <!-- Panel de control de opacidad -->
                                    <div id="result-opacity-panel" 
                                        class="absolute mt-2 w-48 rounded-xl shadow-lg bg-gray-50 dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-10 right-0
                                                transition-all duration-400 ease-out scale-95 opacity-0 pointer-events-none">
                                        <div class="absolute -top-2 right-6 w-4 h-2 z-100 pointer-events-none">
                                            <svg viewBox="0 0 16 8" class="w-4 h-2 text-white dark:text-gray-800">
                                                <polygon points="8,0 16,8 0,8" fill="currentColor"/>
                                            </svg>
                                        </div>
                                        
                                        <!-- Contenido del panel -->
                                        <div class="p-4 z-100">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Opacidad GFW</span>
                                                <span id="result-opacity-value" class="text-xs font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">75%</span>
                                            </div>
                                            
                                            <!-- Slider de opacidad -->
                                            <input type="range" 
                                                id="result-opacity-slider" 
                                                min="0" 
                                                max="100" 
                                                value="75"
                                                class="w-full h-2 bg-gray-200 dark:bg-gray-600 rounded-lg appearance-none cursor-pointer slider-thumb">
                                            
                                            <!-- Botones predefinidos -->
                                            <div class="flex space-x-2 mt-3">
                                                <button type="button" data-opacity="25" class="flex-1 py-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-xs">
                                                    25%
                                                </button>
                                                <button type="button" data-opacity="50" class="flex-1 py-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-xs">
                                                    50%
                                                </button>
                                                <button type="button" data-opacity="75" class="flex-1 py-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-xs">
                                                    75%
                                                </button>
                                                <button type="button" data-opacity="100" class="flex-1 py-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-xs">
                                                    100%
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="result-map" style="height: 400px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); position: relative;">
                        </div>
                    </div>

                    <div>
                        <h3 class="font-semibold text-xl text-gray-900 dark:text-gray-100 mb-3">
                            Distribución del Área
                        </h3>
                        <div class="bg-gray-100 dark:bg-gray-800/40 p-4 rounded-lg shadow-inner" style="height: 430px;">
                            <canvas id="area-distribution-chart"></canvas>
                        </div>
                        <!-- Gráfica de Evolución -->
                    </div>
                    <div class="mt-8">
                        <h3 class="font-semibold text-xl text-gray-900 dark:text-gray-100 mb-3">
                            Evolución de la Deforestación ({{ $dataToPass['start_year'] }}-{{ $dataToPass['end_year'] }})
                        </h3>
                        
                        <div class="flex flex-col md:flex-row gap-4 w-full items-stretch">
                            
                            <div class="w-full md:flex-[2] bg-gray-100 dark:bg-gray-800/40 p-4 rounded-lg shadow-inner" style="height: 400px;">
                                <canvas id="deforestation-evolution-chart"></canvas>
                            </div>

                            
                        </div>
                    </div>
                    <div id="panel-detalle" 
                        class="invisible opacity-0 translate-x-4 w-full md:flex-1 bg-white dark:bg-gray-900 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-lg flex flex-col transition-all duration-500 ease-out" 
                        style="height: 400px;">
                        
                        <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">
                            <h4 class="font-bold text-lg text-gray-800 dark:text-gray-100">
                                Reporte Anual: <span id="detalle-year" class="text-blue-600 dark:text-blue-400"></span>
                            </h4>
                            <button onclick="cerrarPanel()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div id="detalle-contenido" class="overflow-y-auto h-full pr-2 custom-scrollbar">
                            </div>
                    </div>
                </div>

    </div>
                </div>
                </div>

                <div class="mt-8 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('deforestation.create') }}" 
                        class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-blue-600 dark:bg-blue-800 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                            Nuevo Análisis
                        </a>
                        
                       <div class="">
                            <form action="{{ route('deforestation.report') }}" method="POST" target="_blank">
                                @csrf
                                {{-- Pasamos todos los datos actuales para que el PDF se genere con lo que el usuario ve --}}
                                <input type="hidden" name="report_data" value="{{ json_encode($dataToPass) }}">
                                
                                <button type="submit" 
                                    class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-150 ease-in-out">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Descargar PDF
                                </button>
                            </form>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/build/ol.js"></script>

<script>

// Datos para el gráfico de distribución
const polygonArea = {{ $dataToPass['polygon_area_ha'] ?? 0 }};
const deforestedArea = {{ $dataToPass['total_loss']['totalDeforestedArea'] ?? 0 }};
const conservedArea = polygonArea - deforestedArea;

// Gráfico de distribución del área
const ctx = document.getElementById('area-distribution-chart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Área Conservada', 'Área Deforestada'],
        datasets: [{
            data: [conservedArea, deforestedArea],
            backgroundColor: [
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 99, 132, 0.8)'
            ],
            borderColor: [
                'rgba(75, 192, 192, 1)',
                'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const value = context.parsed;
                        const percentage = ((value / polygonArea) * 100).toFixed(2);
                        return `${context.label}: ${value.toFixed(4)} ha (${percentage}%)`;
                    }
                }
            },
            title: {
                display: true,
                text: 'Estado Actual del Predio',
                font: { size: 18 }
            }
        }
    }
});

// Mapa de resultados (OpenLayers)
let resultMap = null;
let gfwLossLayer = null;

function initResultMap() {
    const polygonGeojson = @json($dataToPass['original_geojson'] ?? '{}');
    
    resultMap = new ol.Map({
        target: 'result-map',
        layers: [
            new ol.layer.Tile({ 
                source: new ol.source.OSM() 
            })
        ],
        controls: ol.control.defaults({
            zoom: false,
            rotate: false,
            attribution: false
        }),
        view: new ol.View({
            center: ol.proj.fromLonLat([-63.176998053868616, 10.56217792404226]),
            zoom: 6
        })
    });

    // Añadir capa GFW (INICIALMENTE VISIBLE)
    const GFW_LOSS_URL = 'https://tiles.globalforestwatch.org/umd_tree_cover_loss/latest/dynamic/{z}/{x}/{y}.png';
    
    gfwLossLayer = new ol.layer.Tile({
        source: new ol.source.XYZ({
            url: GFW_LOSS_URL,
            attributions: 'Hansen/UMD/Google/USGS/NASA | GFW',
        }),
        opacity: 0.75,
        visible: true // Visible por defecto
    });
    
    resultMap.addLayer(gfwLossLayer);
    
    // Añadir el polígono al mapa
    const format = new ol.format.GeoJSON();
    
    let features = format.readFeatures(polygonGeojson, {
        dataProjection: 'EPSG:4326', 
        featureProjection: 'EPSG:3857'
    });
    
    if (features.length === 0) {
        features = format.readFeatures(polygonGeojson, {
            dataProjection: 'EPSG:3857',
            featureProjection: 'EPSG:3857'
        });
    }
    
    if (features.length > 0) {
        const vectorLayer = new ol.layer.Vector({
            source: new ol.source.Vector({ features: features }),
            style: new ol.style.Style({
                stroke: new ol.style.Stroke({ 
                    color: 'rgba(59, 130, 246, 0.8)', 
                    width: 3 
                }),
                fill: new ol.style.Fill({ 
                    color: 'rgba(59, 130, 246, 0.2)' 
                })
            })
        });
        
        resultMap.addLayer(vectorLayer);
        
        // Ajustar zoom al polígono
        resultMap.getView().fit(vectorLayer.getSource().getExtent(), {
            padding: [50, 50, 50, 50],
            duration: 1000
        });
    }

}

// Funciones para controles del mapa
function toggleOpacityPanel(show) {
    const panel = document.getElementById('result-opacity-panel');
    
    if (show) {
        panel.classList.remove('scale-95', 'opacity-0', 'pointer-events-none');
        panel.classList.add('scale-100', 'opacity-100', 'pointer-events-auto', 'show');
    } else {
        panel.classList.remove('scale-100', 'opacity-100', 'pointer-events-auto', 'show');
        panel.classList.add('scale-95', 'opacity-0', 'pointer-events-none');
    }
}

function updateOpacity(value) {
    const opacity = value / 100;
    
    if (gfwLossLayer) {
        gfwLossLayer.setOpacity(opacity);
    }
    
    // Actualizar la interfaz
    document.getElementById('result-opacity-value').textContent = `${value}%`;
    document.getElementById('result-opacity-slider').value = value;
    
    // Actualizar botones predefinidos
    document.querySelectorAll('#result-opacity-panel [data-opacity]').forEach(btn => {
        const btnOpacity = parseInt(btn.getAttribute('data-opacity'));
        if (btnOpacity === value) {
            btn.classList.add('bg-blue-600', 'text-white');
            btn.classList.remove('bg-gray-200', 'text-gray-700', 'dark:bg-gray-700', 'dark:text-gray-300');
        } else {
            btn.classList.remove('bg-blue-600', 'text-white');
            btn.classList.add('bg-gray-200', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
        }
    });
    
    // Actualizar el track del slider visualmente
    const slider = document.getElementById('result-opacity-slider');
    const progress = (value / slider.max) * 100;
    slider.style.background = `linear-gradient(to right, #4f46e5 ${progress}%, #e5e7eb ${progress}%)`;
}

// Event listeners para controles del mapa
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar mapa
    if (document.getElementById('result-map')) {
        initResultMap();
    }

    // Toggle capa GFW
    document.getElementById('toggle-gfw-layer').addEventListener('click', function() {
        if (gfwLossLayer) {
            const isVisible = !gfwLossLayer.getVisible();
            gfwLossLayer.setVisible(isVisible);
            
            // Alternar iconos y título
            const iconOpen = document.getElementById('gfw-eye-open');
            const iconClosed = document.getElementById('gfw-eye-closed');
            
            if (isVisible) {
                iconOpen.classList.remove('hidden');
                iconClosed.classList.add('hidden');
                this.setAttribute('title', 'Ocultar Deforestación');
            } else {
                iconOpen.classList.add('hidden');
                iconClosed.classList.remove('hidden');
                this.setAttribute('title', 'Mostrar Deforestación');
            }
        }
    });

    // Control de opacidad
    document.getElementById('result-opacity-control').addEventListener('click', function(e) {
        e.stopPropagation();
        const panel = document.getElementById('result-opacity-panel');
        const isShowing = panel.classList.contains('show');
        toggleOpacityPanel(!isShowing);
    });

    // Slider de opacidad
    document.getElementById('result-opacity-slider').addEventListener('input', function(e) {
        updateOpacity(parseInt(e.target.value));
    });

    // Botones predefinidos de opacidad
    document.querySelectorAll('#result-opacity-panel [data-opacity]').forEach(button => {
        button.addEventListener('click', function() {
            const opacityValue = parseInt(this.getAttribute('data-opacity'));
            updateOpacity(opacityValue);
        });
    });

    // Cerrar panel de opacidad al hacer clic fuera
    document.addEventListener('click', function(e) {
        const opacityButton = document.getElementById('result-opacity-control');
        const opacityPanel = document.getElementById('result-opacity-panel');
        
        if (!opacityButton.contains(e.target) && !opacityPanel.contains(e.target)) {
            toggleOpacityPanel(false);
        }
    });

    // Inicializar opacidad
    setTimeout(() => {
        if (gfwLossLayer) {
            const currentOpacity = gfwLossLayer.getOpacity() * 100;
            updateOpacity(currentOpacity || 75);
        }
    }, 500);
});

/// Gráfica de evolución de la deforestación - VERSIÓN CON RANGO DINÁMICO
let evolutionChart = null;
let yearlyData = @json($dataToPass['yearly_results'] ?? []);
let startYear = {{ $dataToPass['start_year'] ?? 2020 }};
let endYear = {{ $dataToPass['end_year'] ?? 2024 }};
const totalYears = endYear - startYear + 1;

function initEvolutionChart() {
    const ctx = document.getElementById('deforestation-evolution-chart').getContext('2d');
    
    const chartData = getChartData();
    
    evolutionChart = new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Evolución de la Deforestación por Año ({{ $dataToPass['start_year'] }}-{{ $dataToPass['end_year'] }})'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed.y;
                            const year = context.label;
                            const yearData = yearlyData[year];
                            const status = yearData?.status || 'unknown';
                            
                            let tooltipText = `${context.dataset.label}: ${value.toFixed(6)} ha`;
                            if (status === 'error') {
                                tooltipText += ' (Error en consulta)';
                            }
                            return tooltipText;
                        }
                    }
                },
                legend: {
                    display: true,
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Área Deforestada (hectáreas)'
                    },
                    ticks: {
                        callback: function(value) {
                            if (value === 0) return '0 ha';
                            if (value < 0.01) return value.toFixed(6) + ' ha';
                            if (value < 1) return value.toFixed(4) + ' ha';
                            return value.toFixed(4) + ' ha';
                        }
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Años'
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeInOutQuart'
            },
            onClick: (event, elements, chart) => {
                // 1. Verificar si el usuario hizo clic en un punto (elemento)
                if (elements.length > 0) {
                    const index = elements[0].index; // Índice del punto clickeado
                    const year = chart.data.labels[index]; // El año
                    const value = chart.data.datasets[0].data[index]; // Las hectáreas
                    
                    // 2. Obtener datos extra que ya tienes en tu objeto yearlyData
                    const extraInfo = yearlyData[year]; 
                    
                    // 3. Llamar a una función para mostrar el detalle
                    mostrarDetalleAnual(year, value, extraInfo);
                }
            },
            //opcion para que el cursor cambie a una mano
            onHover: (event, elements) => {
                event.native.target.style.cursor = elements.length > 0 ? 'pointer' : 'default';
            }
        }
    });

    
    // Actualizar barra de progreso
    updateProgress(Object.keys(yearlyData).length);
}

// FUNCIÓN CORREGIDA - Maneja correctamente la estructura de datos
function getChartData() {
    const allYears = [];
    for (let year = startYear; year <= endYear; year++) {
        allYears.push(year);
    }
    
    const labels = [];
    const data = [];
    const backgroundColors = [];
    const borderColors = [];
    
    allYears.forEach(year => {
        labels.push(year.toString());
        
        if (yearlyData[year] && yearlyData[year].area__ha !== undefined) {
            const areaValue = parseFloat(yearlyData[year].area__ha) || 0;
            data.push(areaValue);
            
            if (yearlyData[year].status === 'success') {
                backgroundColors.push('rgba(34, 197, 94, 0.8)');
                borderColors.push('rgba(34, 197, 94, 1)');
            } else {
                backgroundColors.push('rgba(239, 68, 68, 0.8)');
                borderColors.push('rgba(239, 68, 68, 1)');
            }
        } else {
            data.push(0);
            backgroundColors.push('rgba(156, 163, 175, 0.5)');
            borderColors.push('rgba(156, 163, 175, 0.5)');
        }
    });

return {
        labels: labels,
        datasets: [{
            label: 'Área Deforestada',
            data: data,
            borderColor: 'rgb(239, 68, 68)',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: backgroundColors,
            pointBorderColor: borderColors,
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8
        }]
    };
}

function updateProgress(loadedCount) {
    const progress = (loadedCount / totalYears) * 100;
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    
    if (progressBar) {
        progressBar.style.width = `${progress}%`;
    }
    
    if (progressText) {
        if (progress >= 100) {
            progressText.textContent = 'Completado ✓';
            progressText.classList.remove('text-blue-600');
            progressText.classList.add('text-green-600');
        } else {
            progressText.textContent = `${loadedCount}/${totalYears} años cargados`;
        }
    }
}

function mostrarDetalleAnual(year, area, info) {
    const panel = document.getElementById('panel-detalle');
    const txtYear = document.getElementById('detalle-year');
    const txtContenido = document.getElementById('detalle-contenido');

    panel.classList.remove('hidden');
    txtYear.innerText = year;

    // Generar las filas de datos técnicos dinámicamente
    let filasTecnicas = '';
    if (info) {
        Object.entries(info).forEach(([key, value]) => {
            // Saltamos valores que ya mostramos arriba o que son muy largos
            if (key === 'year' || key === 'area') return;

            filasTecnicas += `
                <div class="flex justify-between items-center p-2 rounded-lg bg-gray-50 dark:bg-gray-800/50 mb-2 border border-transparent hover:border-gray-200 dark:hover:border-gray-600 transition-all">
                    <span class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">${key.replace('_', ' ')}</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">${value}</span>
                </div>
            `;
        });
    }

    txtContenido.innerHTML = `
        <div class="flex flex-col gap-4 animate-fade-in-up">
            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-100 dark:border-blue-800">
                <p class="text-xs text-blue-600 dark:text-blue-400 uppercase font-bold tracking-wider">Superficie Perdida</p>
                <p class="text-2xl font-black text-blue-900 dark:text-blue-100">
                    ${area.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 4 })} 
                    <span class="text-sm font-normal text-blue-500">ha</span>
                </p>
            </div>

            <div>
                <p class="text-xs font-bold text-gray-400 uppercase mb-3 px-1">Atributos del registro</p>
                ${filasTecnicas || '<p class="text-gray-500 italic text-xs">No hay datos adicionales</p>'}
            </div>
            
            <div class="mt-2 p-3 rounded-lg bg-yellow-50 dark:bg-yellow-900/10 border-l-4 border-yellow-400">
                <p class="text-[11px] text-yellow-700 dark:text-yellow-500 leading-tight">
                    * Estos datos corresponden al análisis satelital consolidado del periodo seleccionado.
                </p>
            </div>
        </div>
    `;

    // 2. Activar la animación
    // Quitamos los estados ocultos y activamos los visibles
    panel.classList.remove('invisible', 'opacity-0', 'translate-x-4');
    panel.classList.add('visible', 'opacity-100', 'translate-x-0');
}

// Función para cerrar con animación de salida
function cerrarPanel() {
    const panel = document.getElementById('panel-detalle');
    panel.classList.remove('visible', 'opacity-100', 'translate-x-0');
    panel.classList.add('invisible', 'opacity-0', 'translate-x-4');
}

/* comienzo del script de edicion de año */

// Variables para controlar la edición de años
let originalStartYear = {{ $dataToPass['start_year'] }};
let originalEndYear = {{ $dataToPass['end_year'] }};
let isEditing = false;

// Función para habilitar la edición de un año
function enableYearEdit(type) {
    if (isEditing) return;
    
    isEditing = true;
    document.getElementById('year-range-display').classList.add('hidden');
    document.getElementById('year-range-edit').classList.remove('hidden');
    
    if (type === 'start') {
        const input = document.getElementById('start-year-input');
        input.focus();
        input.select();
    } else {
        const input = document.getElementById('end-year-input');
        input.focus();
        input.select();
    }
}

// Función para guardar los cambios
function saveYearEdit() {
    const newStartYear = parseInt(document.getElementById('start-year-input').value);
    const newEndYear = parseInt(document.getElementById('end-year-input').value);
    
    // Validaciones
    if (isNaN(newStartYear) || isNaN(newEndYear)) {
        alert('Los años deben ser números válidos');
        return;
    }
    
    const currentYear = new Date().getFullYear();
    if (newStartYear < 2000 || newStartYear > currentYear || 
        newEndYear < 2000 || newEndYear > currentYear) {
        alert(`Los años deben estar entre 2000 y ${currentYear}`);
        return;
    }
    
    if (newStartYear > newEndYear) {
        alert('El año de inicio no puede ser mayor al año de fin');
        return;
    }
    
    // Actualizar la visualización
    document.getElementById('start-year-display').textContent = newStartYear;
    document.getElementById('end-year-display').textContent = newEndYear;
    
    // Verificar si hubo cambios
    const hasChanged = (newStartYear !== originalStartYear) || (newEndYear !== originalEndYear);
    
    // Cerrar modo edición
    cancelYearEdit();
    
    // Mostrar botón de reanálisis si hubo cambios
    if (hasChanged) {
        document.getElementById('reanalyze-button-container').classList.remove('hidden');
    }
}

// Función para cancelar la edición
function cancelYearEdit() {
    isEditing = false;
    document.getElementById('year-range-display').classList.remove('hidden');
    document.getElementById('year-range-edit').classList.add('hidden');
    
    // Restaurar valores originales en los inputs
    document.getElementById('start-year-input').value = originalStartYear;
    document.getElementById('end-year-input').value = originalEndYear;
}

// Función para reanalizar con el nuevo rango
function reanalyzeWithNewRange() {
    const newStartYear = parseInt(document.getElementById('start-year-display').textContent);
    const newEndYear = parseInt(document.getElementById('end-year-display').textContent);
    
    // Validar que los años sean diferentes a los originales
    if (newStartYear === originalStartYear && newEndYear === originalEndYear) {
        alert('No hay cambios en el rango de años');
        return;
    }
    
    // Mostrar spinner
    const button = document.getElementById('reanalyze-button');
    const buttonText = document.getElementById('reanalyze-button-text');
    const spinner = document.getElementById('reanalyze-button-spinner');
    
    button.disabled = true;
    buttonText.textContent = 'Analizando...';
    spinner.classList.remove('hidden');
    
    // Obtener los datos necesarios del formulario original
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('name', '{{ $dataToPass["polygon_name"] }}');
    formData.append('description', '{{ $dataToPass["description"] }}');
    formData.append('geometry', '{{ $dataToPass["original_geojson"] }}');
    formData.append('area_ha', {{ $dataToPass['polygon_area_ha'] }});
    formData.append('start_year', newStartYear);
    formData.append('end_year', newEndYear);
    
    // Mostrar loader mejorado
    showEnhancedLoader();
    
    // Enviar la solicitud
    fetch('{{ route("deforestation.analyze") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (response.ok) {
            return response.text();
        }
        throw new Error('Error en la red');
    })
    .then(html => {
        // Ocultar loader
        hideEnhancedLoader();
        
        // Redirigir a la nueva página de resultados
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newContent = doc.querySelector('.bg-stone-100').innerHTML;
        
        // Reemplazar el contenido actual
        document.querySelector('.bg-stone-100').innerHTML = newContent;
        
        // Actualizar el estado del historial del navegador
        window.history.pushState({}, '', window.location.href);
        
        // Re-inicializar los scripts necesarios
        setTimeout(() => {
            if (typeof initResultMap === 'function') {
                initResultMap();
            }
            if (typeof initEvolutionChart === 'function') {
                initEvolutionChart();
            }
        }, 100);
    })
    .catch(error => {
        hideEnhancedLoader();
        
        // Restaurar el botón
        button.disabled = false;
        buttonText.textContent = 'Reanalizar con nuevo rango';
        spinner.classList.add('hidden');
        
        alert('Error al reanalizar: ' + error.message);
    });
}

// Manejar teclas durante la edición
document.addEventListener('keydown', function(e) {
    if (!isEditing) return;
    
    if (e.key === 'Enter') {
        e.preventDefault();
        saveYearEdit();
    } else if (e.key === 'Escape') {
        e.preventDefault();
        cancelYearEdit();
    }
});

// Manejar clic fuera del área de edición para cancelar
document.addEventListener('click', function(e) {
    if (!isEditing) return;
    
    const editContainer = document.getElementById('year-range-edit');
    const displayContainer = document.getElementById('year-range-display');
    
    if (!editContainer.contains(e.target) && !displayContainer.contains(e.target)) {
        cancelYearEdit();
    }
});

// Función para mostrar loader (ya debería existir)
function showEnhancedLoader() {
    const loaderOverlay = document.getElementById('loader-overlay');
    if (loaderOverlay) {
        loaderOverlay.classList.remove('hidden');
    }
}

// Función para ocultar loader (ya debería existir)
function hideEnhancedLoader() {
    const loaderOverlay = document.getElementById('loader-overlay');
    if (loaderOverlay) {
        loaderOverlay.classList.add('hidden');
    }
}

/* fin del script para la edicion de año */

// Inicializar gráfica de evolución cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('deforestation-evolution-chart')) {
        console.log('Inicializando gráfico de evolución...');
        
        // Pequeño delay para asegurar que el canvas esté listo
        setTimeout(() => {
            initEvolutionChart();
            
            // Forzar redibujado después de un breve momento
            setTimeout(() => {
                if (evolutionChart) {
                    evolutionChart.update('active');
                }
            }, 500);
        }, 100);
    }
});
</script>

<style>
/* Estilos para el slider de opacidad */
.slider-thumb::-webkit-slider-thumb {
    appearance: none;
    height: 16px;
    width: 16px;
    border-radius: 50%;
    background: #4f46e5;
    cursor: pointer;
    border: 2px solid #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

.slider-thumb::-moz-range-thumb {
    height: 16px;
    width: 16px;
    border-radius: 50%;
    background: #4f46e5;
    cursor: pointer;
    border: 2px solid #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

/* Animaciones para la gráfica */
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.loading-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Transiciones suaves */
.progress-transition {
    transition: all 0.5s ease-in-out;
}

/* Mejoras para la gráfica */
.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}

/* Estilos para la edición de años */
#year-range-edit input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

#reanalyze-button {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

#reanalyze-button:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
}

#reanalyze-button:active {
    transform: translateY(0);
}

/* Animación para el cambio de estado */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

#reanalyze-button-container {
    animation: fadeIn 0.3s ease-out;
}

/* Efecto de pulso para indicar que se puede editar */
@keyframes subtlePulse {
    0%, 100% { background-color: transparent; }
    50% { background-color: rgba(59, 130, 246, 0.05); }
}

#start-year-display:hover, #end-year-display:hover {
    animation: subtlePulse 2s infinite;
}

/* Oculta los controles spinner en Chrome, Safari y Opera */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0; /* Elimina el margen que a veces permanece */
}

/* Oculta los controles spinner en Firefox */
input[type="number"] {
  -moz-appearance: textfield;
}

/* Estilos para botones de acción */
.action-buttons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.action-button {
    display: inline-flex;
    align-items: center;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.action-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.action-button:active {
    transform: translateY(0);
}

/* Estilos para mensajes de estado */
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

.save-message.error {
    background-color: #fee2e2;
    border: 1px solid #ef4444;
    color: #991b1b;
}

.save-message.info {
    background-color: #dbeafe;
    border: 1px solid #3b82f6;
    color: #1e40af;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.4s ease-out forwards;
}
/* fin de los estilos para la edicion de años */
</style>
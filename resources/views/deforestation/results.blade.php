<x-app-layout>
    @if(session('save_success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showCustomAlert('success', 'Éxito', '{{ session('save_success') }}');
            });
        </script>
    @endif

    @if(isset($dataToPass['save_error']))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showCustomAlert('error', 'Error', '{{ $dataToPass['save_error'] }}');
            });
        </script>
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
        <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden shadow-sm sm:rounded-2xl shadow-soft p-3 sm:p-4 md:p-6 lg:p-8 ">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">¡Éxito!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class=" overflow-hidden ">
                <div class="flex flex-wrap justify-between items-start gap-4 mb-6 pt-1">
                    <h2 class="font-semibold text-2xl sm:text-3xl text-gray-900 dark:text-gray-100 leading-tight">
                        Resultados del Análisis de Deforestación
                    </h2>
                    
                    <div class="flex space-x-3 mb-0.5">
                        <!-- Botón para nuevo análisis -->
                        <a href="{{ route('deforestation.create') }}" 
                        title="Nuevo análisis" 
                        class="group px-2.5 py-1.5 bg-stone-200/80 hover:bg-blue-700/70 dark:hover:bg-blue-500/60 text-stone-700 hover:text-white border border-stone-300/70 hover:border-transparent dark:bg-gray-700/40 dark:text-gray-300 dark:hover:text-white dark:border-gray-600/50 rounded-md flex items-center hover:shadow-md hover:-translate-y-0.5 overflow-hidden">
                        
                            <span class="flex items-center justify-center w-6 h-6 transition-all duration-300 group-hover:w-6 group-hover:h-6 flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2002/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-blue-700/70 group-hover:text-white dark:text-blue-400/70">
                                    <path d="m11 19-1.106-.552a2 2 0 0 0-1.788 0l-3.659 1.83A1 1 0 0 1 3 19.381V6.618a1 1 0 0 1 .553-.894l4.553-2.277a2 2 0 0 1 1.788 0l4.212 2.106a2 2 0 0 0 1.788 0l3.659-1.83A1 1 0 0 1 21 4.619V12"/><path d="M15 5.764V12"/><path d="M18 15v6"/><path d="M21 18h-6"/><path d="M9 3.236v15"/>
                                </svg>
                            </span>
                            
                            <span class="text-base font-medium transition-all duration-300 w-0 opacity-0 group-hover:w-12 group-hover:opacity-100 group-hover:ml-1 whitespace-nowrap overflow-hidden text-inherit">
                                Nuevo
                            </span>
                        </a>

                        <!-- Botón para generar PDF -->
                        <form action="{{ route('deforestation.report') }}" method="POST" target="_blank" class="inline">
                            @csrf
                            <input type="hidden" name="report_data" value="{{ json_encode($dataToPass) }}">
                            
                            <button type="submit" 
                                title="Descargar PDF" 
                                class="group px-2.5 py-1.5 bg-stone-200/80 hover:bg-red-600/80 dark:hover:bg-red-500/70 text-stone-700 hover:text-white border border-stone-300/70 hover:border-transparent dark:bg-gray-700/40 dark:text-gray-300 dark:hover:text-white dark:border-gray-600/50 rounded-md flex items-center hover:shadow-md hover:-translate-y-0.5 overflow-hidden">
                                
                                <span class="flex items-center justify-center w-6 h-6 transition-all duration-300 group-hover:w-6 group-hover:h-6 flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2002/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-red-700/70 group-hover:text-white dark:text-red-400/70">
                                        <path d="M4 4C4 3.44772 4.44772 3 5 3H14H14.5858C14.851 3 15.1054 3.10536 15.2929 3.29289L19.7071 7.70711C19.8946 7.89464 20 8.149 20 8.41421V20C20 20.5523 19.5523 21 19 21H5C4.44772 21 4 20.5523 4 20V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        <path d="M20 8H15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M11.5 13H11V17H11.5C12.6046 17 13.5 16.1046 13.5 15C13.5 13.8954 12.6046 13 11.5 13Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M15.5 17V13L17.5 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M16 15H17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M7 17L7 15.5M7 15.5L7 13L7.75 13C8.44036 13 9 13.5596 9 14.25V14.25C9 14.9404 8.44036 15.5 7.75 15.5H7Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                
                                <span class="text-base font-medium transition-all duration-300 w-0 opacity-0 group-hover:w-8 group-hover:opacity-100 group-hover:ml-1 whitespace-nowrap overflow-hidden text-inherit">
                                    PDF
                                </span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Información del Área de Estudio -->
                <div class="mb-8 p-4 bg-gray-50 dark:bg-gray-600/10 rounded-lg">
                    <h3 class="font-semibold text-xl text-grey-800 dark:text-grey-100 mb-2">Nombre del Polígono:
                        {{ $dataToPass['polygon_name'] }}
                    </h3>
                    @if($dataToPass['description'])
                        <p class="text-grey-800 dark:text-grey-100">Descripción del Polígono: {{ $dataToPass['description'] }}</p>
                    @endif
                </div>

                <!-- seccion que permite editar rango de fecha -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
                    
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
                <div class="mb-8 grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <div class="bg-gray-50 dark:bg-gray-600/10 p-4 rounded-lg">
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

                    <div class="bg-gray-50 dark:bg-gray-600/10 p-4 rounded-lg">
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

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-semibold text-xl text-gray-900 dark:text-gray-100">
                                Área de Interés
                            </h3>
                        </div>
                        <div id="result-map" style="height: 400px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); position: relative;">
                        </div>
                    </div>

                    <div>
                        <h3 class="font-semibold text-xl text-gray-900 dark:text-gray-100 mb-3">
                            Distribución del Área
                        </h3>
                        <div class="bg-gray-50 dark:bg-gray-600/10 p-4 rounded-lg shadow-inner" style="height: 430px;">
                            <canvas id="area-distribution-chart"></canvas>
                        </div>
                    </div>
                    
                    <div class="lg:col-span-2 mt-6 sm:mt-8">
                        <h3 class="font-semibold text-xl text-gray-900 dark:text-gray-100 mb-3">
                            Evolución de la Deforestación ({{ $dataToPass['start_year'] }}-{{ $dataToPass['end_year'] }})
                        </h3>
                        
                        <div class="w-full bg-gray-50 dark:bg-gray-600/10 p-4 rounded-lg shadow-inner" style="height: 400px;">
                            <canvas id="deforestation-evolution-chart"></canvas>
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
                source: new ol.source.XYZ({
                    url: 'https://api.maptiler.com/maps/satellite/{z}/{x}/{y}.jpg?key=scUozK4fig7bE6jg7TPi',
                    attributions: '© MapTiler & OpenStreetMap',
                    tileSize: 512,
                    maxZoom: 20
                })
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

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('result-map')) {
        initResultMap();
    }
});

/// Gráfica de evolución de la deforestación - CON TOOLTIP MEJORADO
let evolutionChart = null;
let themeObserver = null;
let yearlyData = @json($dataToPass['yearly_results'] ?? []);
let startYear = {{ $dataToPass['start_year'] ?? 2020 }};
let endYear = {{ $dataToPass['end_year'] ?? 2024 }};
const totalYears = endYear - startYear + 1;

function getTooltipThemeColors() {
    const isDarkMode = document.documentElement.classList.contains('dark');

    return {
        backgroundColor: isDarkMode ? '#272a30' : '#ffffff',
        titleColor: isDarkMode ? '#f8fafc' : '#0f172a',
        bodyColor: isDarkMode ? '#e2e8f0' : '#334155',
        borderColor: isDarkMode ? '#1f1f1f' : '#cfcfcf'
    };
}

function initEvolutionChart() {
    const ctx = document.getElementById('deforestation-evolution-chart').getContext('2d');
    const chartData = getChartData();
    const tooltipTheme = getTooltipThemeColors();
    
    evolutionChart = new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Evolución de la Deforestación por Año ({{ $dataToPass['start_year'] }}-{{ $dataToPass['end_year'] }})',
                    font: { size: 14, weight: 'bold' },
                    color: '#374151'
                },
                tooltip: {
                    backgroundColor: tooltipTheme.backgroundColor,
                    titleColor: tooltipTheme.titleColor,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyColor: tooltipTheme.bodyColor,
                    bodyFont: {
                        size: 12.5
                    },
                    borderColor: tooltipTheme.borderColor,
                    borderWidth: 1.5,
                    cornerRadius: 10,
                    padding: 12,
                    displayColors: false,
                    boxPadding: 6,
                    callbacks: {
                        title: function(items) {
                            const year = items[0].label;
                            return `Año ${year}`;
                        },
                        label: function(context) {
                            const value = context.parsed.y;
                            const year = context.label;
                            const yearData = yearlyData[year];
                            const formattedValue = value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 4
                            });

                            const tooltipLines = [`Área Deforestada: ${formattedValue} ha`];

                            if (yearData && yearData.status) {
                                const statusText = yearData.status === 'success' ? 'Éxito' : 'Error';
                                tooltipLines.push(`Estado: ${statusText}`);
                            }

                            if (yearData && yearData.type) {
                                tooltipLines.push(`Tipo: ${yearData.type}`);
                            }

                            if (yearData && yearData.message && yearData.status !== 'success') {
                                tooltipLines.push(yearData.message);
                            }

                            return tooltipLines;
                        }
                    }
                },
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: {
                            size: 12,
                            weight: '500'
                        },
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    title: {
                        display: true,
                        text: 'Área Deforestada (hectáreas)',
                        font: {
                            size: 12,
                            weight: '500'
                        }
                    },
                    ticks: {
                        callback: function(value) {
                            if (value === 0) return '0 ha';
                            if (value < 0.01) return value.toFixed(6) + ' ha';
                            if (value < 1) return value.toFixed(4) + ' ha';
                            return value.toFixed(2) + ' ha';
                        },
                        font: {
                            size: 11
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Años',
                        font: {
                            size: 12,
                            weight: '500'
                        }
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                intersect: false
            },
            animation: {
                duration: 1000,
                easing: 'easeInOutQuart'
            },
            // Evento HOVER con tooltip mejorado
            onHover: function(event, elements) {
                // Cambiar el cursor a pointer cuando está sobre un punto
                event.native.target.style.cursor = elements.length > 0 ? 'pointer' : 'default';
                
                // Si hay elementos bajo el mouse, el tooltip ya se muestra automáticamente
                // gracias a la configuración de tooltip
                if (elements.length > 0) {
                    // Podemos agregar efectos adicionales aquí si queremos
                    const element = elements[0];
                    const index = element.index;
                    const year = this.data.labels[index];
                    
                    // Actualizar el título del gráfico para mostrar el año seleccionado
                    // (efecto visual adicional)
                    this.options.plugins.title.text = 
                        `Evolución de la Deforestación - Año ${year} ({{ $dataToPass['start_year'] }}-{{ $dataToPass['end_year'] }})`;
                    this.update('none');
                } else {
                    // Restaurar el título original cuando el mouse sale
                    this.options.plugins.title.text = 
                        `Evolución de la Deforestación por Año ({{ $dataToPass['start_year'] }}-{{ $dataToPass['end_year'] }})`;
                    this.update('none');
                }
            }
        }
    });

    updateProgress(Object.keys(yearlyData).length);

    if (themeObserver) {
        themeObserver.disconnect();
    }

    themeObserver = new MutationObserver(function() {
        if (!evolutionChart) return;

        const nextTheme = getTooltipThemeColors();
        evolutionChart.options.plugins.tooltip.backgroundColor = nextTheme.backgroundColor;
        evolutionChart.options.plugins.tooltip.titleColor = nextTheme.titleColor;
        evolutionChart.options.plugins.tooltip.bodyColor = nextTheme.bodyColor;
        evolutionChart.options.plugins.tooltip.borderColor = nextTheme.borderColor;
        evolutionChart.update();
    });

    themeObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });
}

function getChartData() {
    const allYears = [];
    for (let year = startYear; year <= endYear; year++) {
        allYears.push(year);
    }
    
    const labels = [];
    const data = [];
    const backgroundColors = [];
    const borderColors = [];
    const pointStyles = [];
    
    allYears.forEach(year => {
        labels.push(year.toString());
        
        if (yearlyData[year] && yearlyData[year].area__ha !== undefined) {
            const areaValue = parseFloat(yearlyData[year].area__ha) || 0;
            data.push(areaValue);
            
            if (yearlyData[year].status === 'success') {
                backgroundColors.push('rgba(34, 197, 94, 0.8)');
                borderColors.push('rgba(34, 197, 94, 1)');
                pointStyles.push('circle');
            } else {
                backgroundColors.push('rgba(239, 68, 68, 0.8)');
                borderColors.push('rgba(239, 68, 68, 1)');
                pointStyles.push('rect');
            }
        } else {
            data.push(0);
            backgroundColors.push('rgba(156, 163, 175, 0.5)');
            borderColors.push('rgba(156, 163, 175, 0.5)');
            pointStyles.push('circle');
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
            pointRadius: 7,
            pointHoverRadius: 11,
            pointHoverBorderWidth: 3,
            pointStyle: pointStyles
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

/* comienzo del script de edicion de año */

let originalStartYear = {{ $dataToPass['start_year'] }};
let originalEndYear = {{ $dataToPass['end_year'] }};
let isEditing = false;

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

function saveYearEdit() {
    const newStartYear = parseInt(document.getElementById('start-year-input').value);
    const newEndYear = parseInt(document.getElementById('end-year-input').value);
    
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
    
    document.getElementById('start-year-display').textContent = newStartYear;
    document.getElementById('end-year-display').textContent = newEndYear;
    
    const hasChanged = (newStartYear !== originalStartYear) || (newEndYear !== originalEndYear);
    
    cancelYearEdit();
    
    if (hasChanged) {
        document.getElementById('reanalyze-button-container').classList.remove('hidden');
    }
}

function cancelYearEdit() {
    isEditing = false;
    document.getElementById('year-range-display').classList.remove('hidden');
    document.getElementById('year-range-edit').classList.add('hidden');
    
    document.getElementById('start-year-input').value = originalStartYear;
    document.getElementById('end-year-input').value = originalEndYear;
}

function reanalyzeWithNewRange() {
    const newStartYear = parseInt(document.getElementById('start-year-display').textContent);
    const newEndYear = parseInt(document.getElementById('end-year-display').textContent);
    
    if (newStartYear === originalStartYear && newEndYear === originalEndYear) {
        alert('No hay cambios en el rango de años');
        return;
    }
    
    const button = document.getElementById('reanalyze-button');
    const buttonText = document.getElementById('reanalyze-button-text');
    const spinner = document.getElementById('reanalyze-button-spinner');
    
    button.disabled = true;
    buttonText.textContent = 'Analizando...';
    spinner.classList.remove('hidden');
    
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('name', '{{ $dataToPass["polygon_name"] }}');
    formData.append('description', '{{ $dataToPass["description"] }}');
    formData.append('geometry', '{{ $dataToPass["original_geojson"] }}');
    formData.append('area_ha', {{ $dataToPass['polygon_area_ha'] }});
    formData.append('start_year', newStartYear);
    formData.append('end_year', newEndYear);
    
    showEnhancedLoader();
    
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
        hideEnhancedLoader();
        
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newContent = doc.querySelector('.bg-stone-100').innerHTML;
        
        document.querySelector('.bg-stone-100').innerHTML = newContent;
        window.history.pushState({}, '', window.location.href);
        
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
        button.disabled = false;
        buttonText.textContent = 'Reanalizar con nuevo rango';
        spinner.classList.add('hidden');
        alert('Error al reanalizar: ' + error.message);
    });
}

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

document.addEventListener('click', function(e) {
    if (!isEditing) return;
    
    const editContainer = document.getElementById('year-range-edit');
    const displayContainer = document.getElementById('year-range-display');
    
    if (!editContainer.contains(e.target) && !displayContainer.contains(e.target)) {
        cancelYearEdit();
    }
});

function showEnhancedLoader() {
    const loaderOverlay = document.getElementById('loader-overlay');
    if (loaderOverlay) {
        loaderOverlay.classList.remove('hidden');
    }
}

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
        setTimeout(() => {
            initEvolutionChart();
            
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

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.loading-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.progress-transition {
    transition: all 0.5s ease-in-out;
}

.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}

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

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

#reanalyze-button-container {
    animation: fadeIn 0.3s ease-out;
}

@keyframes subtlePulse {
    0%, 100% { background-color: transparent; }
    50% { background-color: rgba(59, 130, 246, 0.05); }
}

#start-year-display:hover, #end-year-display:hover {
    animation: subtlePulse 2s infinite;
}

input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type="number"] {
  -moz-appearance: textfield;
}

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

/* Estilos mejorados para el tooltip de Chart.js */
.chartjs-tooltip {
    background: rgba(255, 255, 255, 0.95) !important;
    border-radius: 12px !important;
    padding: 16px !important;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
    border: 2px solid rgba(59, 130, 246, 0.3) !important;
}

/* Animación para el gráfico */
#deforestation-evolution-chart canvas {
    transition: all 0.3s ease;
}

#deforestation-evolution-chart canvas:hover {
    filter: brightness(1.02);
}

/* Estilos para tooltip en modo oscuro */
.dark .chartjs-tooltip {
    background: rgba(31, 41, 55, 0.95) !important;
    border-color: rgba(59, 130, 246, 0.5) !important;
}

/* Mejoras para el tooltip en móvil */
@media (max-width: 640px) {
    .chartjs-tooltip {
        max-width: 280px !important;
        font-size: 12px !important;
        padding: 12px !important;
    }
}
</style>
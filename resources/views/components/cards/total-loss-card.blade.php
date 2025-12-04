@props([
    'dataToPass', // Se pasa el array completo, ya que se necesita gran parte de él
])

@php
    // --- LÓGICA DE CÁLCULO ENCAPSULADA ---
    $yearlyResults = $dataToPass['yearly_results'] ?? [];
    $polygonAreaHa = $dataToPass['polygon_area_ha'] ?? 0;
    $startYear = $dataToPass['start_year'] ?? 2020;
    $endYear = $dataToPass['end_year'] ?? 2024;
    
    $totalDeforestedArea = 0;
    $validYears = 0;
    
    foreach ($yearlyResults as $year => $yearData) {
        if (isset($yearData['area__ha']) && $yearData['status'] === 'success') {
            $totalDeforestedArea += $yearData['area__ha'];
            $validYears++;
        }
    }
    
    $totalPercentage = $polygonAreaHa > 0 ? ($totalDeforestedArea / $polygonAreaHa) * 100 : 0;
    $totalYearsInRange = $endYear - $startYear + 1;
    // --- FIN LÓGICA DE CÁLCULO ---
@endphp

<div class="bg-yellow-100 dark:bg-yellow-800/60 p-4 rounded-lg shadow-md border-l-4 border-yellow-500">
    <p class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Pérdida Total ({{ $startYear }}-{{ $endYear }})</p>
    
    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">
        {{ number_format($totalPercentage, 2, ',', '.') }}%
    </p>
    
    @if($validYears > 0)
    <div class="text-xs text-yellow-700 dark:text-yellow-300 mt-2 space-y-1">
        <div>{{ number_format($totalDeforestedArea, 6, ',', '.') }} ha acumuladas</div>
        <div>{{ $validYears }}/{{ $totalYearsInRange }} años analizados</div>
    </div>
    @else
    <p class="text-xs text-yellow-700 dark:text-yellow-300 mt-1">
        Sin datos suficientes
    </p>
    @endif
</div>
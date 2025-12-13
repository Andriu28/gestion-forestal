@props([
    'dataToPass', // Se pasa el array completo, ya que se necesita gran parte de él
])

@php

    $yearlyResults = $dataToPass['yearly_results'] ?? [];
    $polygonAreaHa = $dataToPass['polygon_area_ha'] ?? 0;
    $startYear = $dataToPass['start_year'] ?? 2020;
    $endYear = $dataToPass['end_year'] ?? 2024;
    $validYears = $dataToPass['total_loss']['validYears'] ? $dataToPass['total_loss']['validYears'] : 0;
    $totalPercentage = $dataToPass['total_loss']['totalPercentage'] ? $dataToPass['total_loss']['totalPercentage'] : 0;
    $totalYearsInRange = $dataToPass['total_loss']['totalYearsInRange'] ? $dataToPass['total_loss']['totalYearsInRange'] : 0;

@endphp

<div class="bg-yellow-100 dark:bg-yellow-800/60 p-4 rounded-lg shadow-md border-l-4 border-yellow-500">
    <p class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Pérdida Total ({{ $startYear }}-{{ $endYear }})</p>
    
    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">
        {{ number_format($totalPercentage, 2, ',', '.') }}%
    </p>
    
    @if($validYears > 0)
    <div class="text-xs text-yellow-700 dark:text-yellow-300 mt-2 space-y-1">
        <div>{{ $validYears }}/{{ $totalYearsInRange }} años analizados</div>
    </div>
    @else
    <p class="text-xs text-yellow-700 dark:text-yellow-300 mt-1">
        Sin datos suficientes
    </p>
    @endif
</div>
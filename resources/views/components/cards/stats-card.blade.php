@props([
    'title', 
    'value', 
    'color' => 'blue', // Default color, puede ser green, red, yellow, etc.
    'unit' => '',
    'footer' => null // Contenido opcional para el pie de la tarjeta (porcentaje, subtítulo, etc.)
])

@php
    // Definición de colores base (para bg, border, y text)
    $colorMap = [
        'green' => 'bg-green-100 dark:bg-green-900/50 border-green-500 text-green-600 dark:text-green-400',
        'red' => 'bg-red-100 dark:bg-red-900/60 border-red-500 text-red-600 dark:text-red-400',
        'yellow' => 'bg-yellow-100 dark:bg-yellow-800/60 border-yellow-500 text-yellow-600 dark:text-yellow-400',
        'blue' => 'bg-blue-100 dark:bg-blue-900/50 border-blue-500 text-blue-600 dark:text-blue-400',
        // los colores se añaden aqui
    ];

    $classes = $colorMap[$color] ?? $colorMap['blue'];
@endphp

<div class="{{ $classes }} p-4 rounded-lg shadow-md border-l-4 relative">
    <p class="text-sm font-medium {{ $classes }}">{{ $title }}</p>
    
    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">
        {{ $value }} {{ $unit }}
    </p>

    @if($footer)
        {{ $footer }}
    @endif
</div>
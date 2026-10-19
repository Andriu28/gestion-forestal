@props([
    'options' => [],      // array asociativo [value => label]
    'selected' => null,   // valor seleccionado (para edición)
    'placeholder' => null,// texto del placeholder (opción vacía)
    'name' => null,
    'id' => null,
    'disabled' => false,
    'required' => false,
    'class' => '',
    'label' => null,      // etiqueta opcional (para usar con x-input-label)
])

@php
    // Si no se proporciona id, usar name
    $id = $id ?? $name;
@endphp

@if($label)
    <x-input-label :for="$id" :value="$label" />
@endif

<select
    name="{{ $name }}"
    id="{{ $id }}"
    @disabled($disabled)
    @required($required)
    {{ $attributes->merge(['class' => "w-full px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm border border-stone-400/80 dark:border-gray-600 !bg-stone-50 dark:!bg-gray-800/50 text-custom-gray dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70 $class"]) }}
>
    @if($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif

    @foreach($options as $value => $label)
        <option value="{{ $value }}" @selected($selected == $value)>
            {{ $label }}
        </option>
    @endforeach
</select>
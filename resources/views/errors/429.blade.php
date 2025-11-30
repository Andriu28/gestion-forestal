@extends('errors.minimal')

@section('title', '429 - Demasiadas Solicitudes')
@section('code', '429')
@section('message', 'Demasiadas Solicitudes')

@section('description')
Has realizado demasiadas solicitudes en un corto período. Por favor, espera unos minutos antes de intentar nuevamente.
@endsection

@section('buttons')
    <button onclick="window.location.reload()" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-800 text-white border-2 border-purple-800 rounded-lg font-semibold cursor-pointer transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg">
        Reintentar
    </button>

    <a href="{{ route('dashboard') }}" class="px-6 py-3 bg-neutral-200/90 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-2 border-gray-400 dark:border-gray-600 rounded-lg font-semibold cursor-pointer transition-all duration-300 hover:bg-gray-300 dark:hover:bg-gray-700 hover:-translate-y-0.5">
        Volver al Inicio
    </a>
@endsection
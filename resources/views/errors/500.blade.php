@extends('errors.minimal')

@section('title', '500 - Error del Servidor')
@section('code', '500')
@section('message', 'Error del Servidor')

@section('description')
Algo salió mal en nuestro servidor. Nuestro equipo técnico ha sido notificado y está trabajando para solucionarlo.
@endsection

@section('buttons')
    <button onclick="window.location.reload()" class="px-6 py-3 bg-gradient-to-r from-red-600 to-red-800 text-white border-2 border-red-800 rounded-lg font-semibold cursor-pointer transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg">
        Reintentar
    </button>

    <a href="{{ route('dashboard') }}" class="px-6 py-3 bg-neutral-200/90 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-2 border-gray-400 dark:border-gray-600 rounded-lg font-semibold cursor-pointer transition-all duration-300 hover:bg-gray-300 dark:hover:bg-gray-700 hover:-translate-y-0.5">
        Volver al Inicio
    </a>

@endsection
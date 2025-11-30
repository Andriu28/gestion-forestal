@extends('errors.minimal')

@section('title', '404 - Página No Encontrada')
@section('code', '404')
@section('message', 'Página No Encontrada')

@section('description')
La página que buscas no existe o ha sido movida. Esto puede deberse a un enlace desactualizado o un error al escribir la dirección.
@endsection

@section('buttons')
    <a href="{{ route('dashboard') }}" class="px-6 py-3 bg-gradient-to-r from-amber-600 to-amber-800 text-white border-2 border-amber-800 rounded-lg font-semibold cursor-pointer transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg">
        Volver al Inicio
    </a>

    <button onclick="history.back()" class="px-6 py-3 bg-neutral-200/90 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-2 border-gray-400 dark:border-gray-600 rounded-lg font-semibold cursor-pointer transition-all duration-300 hover:bg-gray-300 dark:hover:bg-gray-700 hover:-translate-y-0.5">
        Ir Atrás
    </button>
@endsection
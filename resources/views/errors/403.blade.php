@extends('errors.minimal')

@section('title', '403 - Acceso Prohibido')
@section('code', '403')
@section('message', 'Acceso Prohibido')

@section('description')
No tienes permisos para acceder a esta página. Contacta al administrador si necesitas acceso.
@endsection

@section('buttons')
    <a href="{{ route('dashboard') }}" class="px-6 py-3 bg-gradient-to-r from-red-600 to-red-800 text-white border-2 border-red-800 rounded-lg font-semibold cursor-pointer transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg">
        Volver al Inicio
    </a>

    <button onclick="history.back()" class="px-6 py-3 bg-neutral-200/90 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-2 border-gray-400 dark:border-gray-600 rounded-lg font-semibold cursor-pointer transition-all duration-300 hover:bg-gray-300 dark:hover:bg-gray-700 hover:-translate-y-0.5">
        Ir Atrás
    </button>

    <a href="mailto:admin@empresa.com" class="px-6 py-3 bg-amber-500 text-amber-900 border-2 border-amber-700 rounded-lg font-semibold cursor-pointer transition-all duration-300 hover:-translate-y-0.5">
        Contactar Admin
    </a>
@endsection
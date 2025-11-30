@extends('errors.minimal')

@section('title', '401 - No Autorizado')
@section('code', '401')
@section('message', 'No Autorizado')

@section('description')
No estás autorizado para acceder a esta página. Por favor, inicia sesión con las credenciales correctas.
@endsection

@section('buttons')
    <a href="{{ route('login') }}" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-800 text-white border-2 border-blue-800 rounded-lg font-semibold cursor-pointer transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg">
        Iniciar Sesión
    </a>

    <a href="{{ route('dashboard') }}" class="px-6 py-3 bg-neutral-200/90 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-2 border-gray-400 dark:border-gray-600 rounded-lg font-semibold cursor-pointer transition-all duration-300 hover:bg-gray-300 dark:hover:bg-gray-700 hover:-translate-y-0.5">
        Volver al Inicio
    </a>
@endsection
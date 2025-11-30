@extends('errors.minimal')

@section('title', '419 - Página Expirada')
@section('code', '419')
@section('message', 'Página Expirada')

@section('description')
Tu sesión ha expirado por inactividad. Por seguridad, debes volver a iniciar sesión para continuar.
@endsection

@section('buttons')

    <button onclick="window.location.reload()" class="px-6 py-3 bg-amber-500 text-amber-900 border-2 border-amber-700 rounded-lg font-semibold cursor-pointer transition-all duration-300 hover:-translate-y-0.5">
        Recargar Página
    </button>

@endsection
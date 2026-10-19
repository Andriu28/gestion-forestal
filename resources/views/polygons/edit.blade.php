{{-- [file name]: resources/views/polygons/edit.blade.php --}}
@php
    // El método ya devuelve un string GeoJSON.
    $geometryJson = old('geometry', $polygon->getGeometryGeoJson());
@endphp

<x-app-layout>
    <div class="mx-auto">
        <div class="bg-stone-100/90 dark:bg-custom-gray shadow-sm sm:rounded-2xl shadow-soft p-4 md:p-6 lg:p-6 mb-6">
            <div class="text-gray-900 dark:text-gray-100">
                <div class="flex justify-between items-center mb-2">
                    <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-200">
                        Editar Parcela: {{ $polygon->name }}
                    </h2>
                </div>

                <form action="{{ route('polygons.update', $polygon) }}" method="POST" id="polygon-form" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                        {{-- ===================== MAPA ===================== --}}
                        <div class="lg:col-span-3">
                            <div id="map-container" class="relative rounded-lg overflow-hidden mb-6 border border-gray-200 dark:border-gray-700 mt-1"
                                 style="height: 77vh; position: relative;">
                                <div id="map" class="h-full w-full"></div>

                                {{-- Controles del mapa --}}
                                <div id="map-controls" style="position:absolute; top:10px; right:10px; z-index:1000;">
                                    <div class="flex flex-col items-end space-y-2">
                                        <div class="flex space-x-2">
                                            <button type="button" id="edit-polygon" title="Editar vértices"
                                                class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg hidden">
                                            </button>

                                            <div class="relative">
                                                <button type="button" id="base-map-toggle" title="Cambiar mapa"
                                                    class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                                    </svg>
                                                    Satélite Esri
                                                </button>

                                                <div id="base-map-menu"
                                                    class="absolute mt-3 w-40 right-0 rounded-xl shadow-lg bg-gray-100 dark:bg-custom-gray ring-1 ring-black ring-opacity-5 z-10
                                                           transition-all duration-400 ease-out scale-95 opacity-0 pointer-events-none hidden">
                                                    <div class="absolute -top-2 right-6 w-8 h-2 pointer-events-none">
                                                        <svg viewBox="0 0 16 8" class="w-4 h-2 text-gray-100 dark:text-custom-gray">
                                                            <polygon points="8,0 16,8 0,8" fill="currentColor"/>
                                                        </svg>
                                                    </div>
                                                    <div class="py-2" role="menu" aria-orientation="vertical">
                                                        <button data-layer="osm" type="button" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">OpenStreetMap</button>
                                                        <button data-layer="satellite" type="button" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">Satélite Esri</button>
                                                        <button data-layer="maptiler_satellite" type="button" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">MapTiler Satélite</button>
                                                        <button data-layer="terrain" type="button" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">Relieve</button>
                                                        <button data-layer="dark" type="button" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" role="menuitem">Oscuro</button>
                                                    </div>
                                                </div>
                                            </div>

                                            <button type="button" id="fullscreen-toggle" title="Pantalla completa"
                                                class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5"></path>
                                                </svg>
                                            </button>
                                        </div>

                                        <button type="button" id="manual-polygon-toggle" title="Escribir coordenadas UTM"
                                            class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                                                <path d="M13 21h8"/><path d="m15 5 4 4"/>
                                                <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>
                                            </svg>
                                        </button>

                                        <button type="button" id="draw-polygon" title="Dibujar nueva geometría"
                                            class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                                                <path d="M12 6v12M6 12h12"/>
                                            </svg>
                                        </button>

                                        <button type="button" id="clear-map" title="Limpiar mapa"
                                            class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded-lg flex items-center shadow-lg">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                                                <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- Coordenadas bajo el cursor --}}
                                <div class="absolute bottom-3 left-3" style="z-index:1000;">
                                    <div id="coordinate-display"
                                         class="bg-white/95 dark:bg-gray-800/95 text-gray-800 dark:text-gray-200 px-3 py-2 rounded-lg shadow-lg text-xs font-mono hidden">
                                    </div>
                                </div>

                                {{-- Indicador de modo --}}
                                <div id="mode-badge" class="absolute right-3 hidden px-3 py-1 rounded-full text-xs font-semibold text-white shadow-lg"
                                     style="bottom:36px; z-index:1000;"></div>

                                {{-- Punto seleccionado --}}
                                <div id="point-info" class="absolute top-3 bg-white dark:bg-gray-800 p-3 rounded-lg shadow-lg max-w-xs hidden"
                                     style="left:56px; z-index:1000;">
                                    <h4 class="font-bold text-gray-900 dark:text-white mb-2">Punto seleccionado</h4>
                                    <div class="space-y-1 text-sm">
                                        <div><span class="font-semibold">Lat:</span> <span id="selected-lat">0.000000</span></div>
                                        <div><span class="font-semibold">Lng:</span> <span id="selected-lng">0.000000</span></div>
                                        <div><span class="font-semibold">Índice:</span> <span id="selected-index">-</span></div>
                                    </div>
                                    <div class="mt-3 space-y-2">
                                        <button type="button" id="delete-point" class="w-full bg-red-600 hover:bg-red-700 text-white py-1 px-3 rounded text-sm">Eliminar punto</button>
                                        <button type="button" id="update-point" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-1 px-3 rounded text-sm">Actualizar coordenadas</button>
                                    </div>
                                </div>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('geometry')" />
                        </div>

                        {{-- ===================== PANEL DE PUNTOS ===================== --}}
                        <div class="lg:col-span-1">
                            <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden sm:rounded-2xl h-full">
                                <div class="text-gray-900 dark:text-gray-100 h-full flex flex-col">
                                    <h2 class="text-lg font-semibold mb-4 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                        </svg>
                                        Puntos de la Parcela
                                        <span id="points-count" class="ml-2 px-2 py-1 text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full">0</span>
                                    </h2>

                                    <div class="flex-1 overflow-hidden">
                                        <div id="points-container" class="space-y-3 overflow-y-auto max-h-[40vh] pr-2">
                                            <div id="no-points-message" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                                </svg>
                                                <p>No hay puntos para mostrar</p>
                                                <p id="no-points-hint" class="text-sm mt-1">Carga una parcela o dibuja una nueva</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="polygon-summary" class="mt-4 p-3 bg-blue-50 dark:bg-blue-900 rounded-lg hidden">
                                        <h3 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">Resumen</h3>
                                        <div class="text-sm space-y-1">
                                            <div><strong>Área:</strong> <span id="summary-area">0.0000</span> Ha</div>
                                            <div><strong>Perímetro:</strong> <span id="summary-perimeter">0.0000</span> km</div>
                                            <div><strong>Número de puntos:</strong> <span id="summary-points">0</span></div>
                                        </div>
                                    </div>

                                    <div id="edit-controls" class="mt-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg hidden">
                                        <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Controles de edición</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                            Arrastra un vértice para moverlo. Arrastra el borde para crear uno nuevo. Alt + clic sobre un vértice lo elimina.
                                        </p>
                                        <div class="space-y-2">
                                            <button type="button" id="add-point" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-3 rounded text-sm flex items-center justify-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                                Agregar punto
                                            </button>
                                            <button type="button" id="finish-edit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-3 rounded text-sm">Finalizar edición</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ===================== FORMULARIO ===================== --}}
                        <div class="lg:col-span-4">
                            <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden sm:rounded-2xl p-4 md:p-6 lg:p-8">
                                <div class="text-gray-900 dark:text-gray-100">
                                    <h2 class="text-lg font-semibold mb-4">Datos de la Parcela</h2>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-6">
                                            <div>
                                                <x-input-label for="name" :value="__('Nombre de la Parcela *')" />
                                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" maxlength="40"
                                                    value="{{ old('name', $polygon->name) }}" required placeholder="Ej: Finca La Esperanza" />
                                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                                            </div>

                                            <div>
                                                <x-input-label for="description" :value="__('Descripción')" />
                                                <textarea id="description" name="description" rows="3"
                                                    class="w-full px-2.5 sm:px-3 py-1.5 mt-1 sm:py-2 text-xs sm:text-sm border border-stone-400/80 dark:border-gray-600 !bg-stone-50 dark:!bg-gray-800/80 text-custom-gray dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70"
                                                    placeholder="Descripción de la Parcela...">{{ old('description', $polygon->description) }}</textarea>
                                                <x-input-error class="mt-2" :messages="$errors->get('description')" />
                                            </div>

                                            <div>
                                                <x-input-label for="producer_id" :value="__('Productor')" />
                                                <select id="producer_id" name="producer_id"
                                                    class="w-full px-2.5 sm:px-3 py-1.5 mt-1 sm:py-2 text-xs sm:text-sm border border-stone-400/80 dark:border-gray-600 !bg-stone-50 dark:!bg-gray-800 text-custom-gray dark:text-gray-100 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70">
                                                    <option value="">Seleccione un productor</option>
                                                    @foreach($producers as $producer)
                                                        <option value="{{ $producer->id }}" {{ old('producer_id', $polygon->producer_id) == $producer->id ? 'selected' : '' }}>
                                                            {{ $producer->name }} {{ $producer->lastname }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <x-input-error class="mt-2" :messages="$errors->get('producer_id')" />
                                            </div>
                                        </div>

                                        <div class="space-y-6">
                                            <div>
                                                <x-input-label for="parish_id" :value="__('Parroquia (Opcional)')" />
                                                <select id="parish_id" name="parish_id"
                                                    class="w-full px-2.5 sm:px-3 py-1.5 mt-1 sm:py-2 text-xs sm:text-sm border border-stone-400/80 dark:border-gray-600 !bg-stone-50 dark:!bg-gray-800 text-custom-gray dark:text-gray-100 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70">
                                                    <option value="">Seleccione una parroquia</option>
                                                    @foreach($parishes as $parish)
                                                        <option value="{{ $parish->id }}" data-name="{{ $parish->name }}"
                                                                data-municipality="{{ $parish->municipality?->name }}"
                                                                {{ old('parish_id', $polygon->parish_id) == $parish->id ? 'selected' : '' }}>
                                                            {{ $parish->name }}
                                                            @if($parish->municipality && $parish->municipality->state)
                                                                ({{ $parish->municipality->name }}, {{ $parish->municipality->state->name }})
                                                            @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <x-input-error class="mt-2" :messages="$errors->get('parish_id')" />
                                            </div>

                                            <div>
                                                <x-input-label for="area_ha" :value="__('Área en Hectáreas')" />
                                                <x-text-input id="area_ha" name="area_ha" type="number" step="0.000001" readonly
                                                    class="mt-1 w-full opacity-80 cursor-not-allowed" value="{{ old('area_ha', $polygon->area_ha) }}" />
                                                <x-input-error class="mt-2" :messages="$errors->get('area_ha')" />
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Se calcula automáticamente a partir de la geometría del mapa.</p>
                                            </div>

                                            <div>
                                                <x-input-label for="is_active" :value="__('Estado')" />
                                                <div class="flex items-center space-x-4 mt-2">
                                                    <label class="inline-flex items-center">
                                                        <input type="radio" name="is_active" value="1" {{ old('is_active', $polygon->is_active) ? 'checked' : '' }}
                                                            class="text-indigo-600 border-gray-300 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                                        <span class="ml-2 text-gray-700 dark:text-gray-300">Activo</span>
                                                    </label>
                                                    <label class="inline-flex items-center">
                                                        <input type="radio" name="is_active" value="0" {{ !old('is_active', $polygon->is_active) ? 'checked' : '' }}
                                                            class="text-indigo-600 border-gray-300 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                                        <span class="ml-2 text-gray-700 dark:text-gray-300">Inactivo</span>
                                                    </label>
                                                </div>
                                                <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Campos ocultos --}}
                                    <input type="hidden" id="geometry" name="geometry" value="{{ $geometryJson }}" required>
                                    <input type="hidden" id="location_data" name="location_data" value="{{ old('location_data') }}">
                                    <input type="hidden" id="detected_parish" name="detected_parish" value="{{ old('detected_parish', $polygon->detected_parish) }}">
                                    <input type="hidden" id="detected_municipality" name="detected_municipality" value="{{ old('detected_municipality', $polygon->detected_municipality) }}">
                                    <input type="hidden" id="detected_state" name="detected_state" value="{{ old('detected_state', $polygon->detected_state) }}">
                                    <input type="hidden" id="centroid_lat" name="centroid_lat" value="{{ old('centroid_lat', $polygon->centroid_lat) }}">
                                    <input type="hidden" id="centroid_lng" name="centroid_lng" value="{{ old('centroid_lng', $polygon->centroid_lng) }}">

                                    <div id="location-info" class="p-4 bg-blue-50 dark:bg-blue-900 rounded-lg mt-6 {{ !$polygon->detected_parish && !$polygon->detected_municipality && !$polygon->detected_state ? 'hidden' : '' }}">
                                        <h3 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">📍 Ubicación detectada</h3>
                                        <div class="text-sm space-y-1">
                                            <div><strong>Parroquia:</strong> <span id="detected-parish-text">{{ $polygon->detected_parish ?? '-' }}</span></div>
                                            <div><strong>Municipio:</strong> <span id="detected-municipality-text">{{ $polygon->detected_municipality ?? '-' }}</span></div>
                                            <div><strong>Estado:</strong> <span id="detected-state-text">{{ $polygon->detected_state ?? '-' }}</span></div>
                                            <div><strong>Coordenadas:</strong>
                                                <span id="detected-coords-text">{{ $polygon->centroid_lat && $polygon->centroid_lng ? number_format($polygon->centroid_lat, 6) . ', ' . number_format($polygon->centroid_lng, 6) : '-' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-6">
                                        <button type="button" id="detect-location" disabled
                                            class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                            </svg>
                                            <span id="detect-button-text">Detectar Ubicación</span>
                                        </button>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Detecta la parroquia, municipio y estado a partir del centro de la parcela.</p>
                                    </div>

                                    <div class="flex items-center justify-end space-x-4 pt-6">
                                        <a href="{{ route('polygons.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md">Cancelar</a>
                                        <x-primary-button type="submit" id="submit-btn">Actualizar Parcela</x-primary-button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL: COORDENADAS UTM ===================== --}}
    <div id="manual-polygon-modal" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4" style="z-index:2000;">
        <div class="bg-white dark:bg-custom-gray rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Ingresar Coordenadas UTM (WGS84)</h3>
                <button id="close-modal" type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="manual-polygon-form" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Método de entrada:</label>
                    <div class="flex space-x-2">
                        <button type="button" id="method-single" class="flex-1 py-2 px-3 bg-blue-600 text-white rounded-lg text-sm font-medium">Una por una</button>
                        <button type="button" id="method-bulk" class="flex-1 py-2 px-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium">Lote</button>
                    </div>
                </div>

                <div id="single-input" class="space-y-3">
                    <div class="grid grid-cols-4 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Zona</label>
                            <input type="number" id="single-zone" class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm p-2" min="1" max="60" value="20">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Hemisferio</label>
                            <select id="single-hemisphere" class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm">
                                <option value="N">Norte (N)</option>
                                <option value="S">Sur (S)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Este</label>
                            <input type="text" inputmode="decimal" id="single-easting" class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm p-2" placeholder="476097.904">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Norte</label>
                            <input type="text" inputmode="decimal" id="single-northing" class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm p-2" placeholder="1157477.299">
                        </div>
                    </div>
                    <button type="button" id="add-coord" class="w-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 py-2 rounded-lg text-sm">+ Agregar coordenada</button>
                </div>

                <div id="bulk-input" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Coordenadas (una por línea):</label>
                    <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg mb-2">
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            <strong>Formato:</strong> Zona,Hemisferio,Este,Norte (separadores: coma, punto y coma o tabulación)<br>
                            <code class="text-green-600">20,N,476097.904,1157477.299</code>
                        </p>
                    </div>
                    <textarea id="bulk-coords" rows="6" class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-xs p-2 font-mono"
                        placeholder="20,N,476097.904,1157477.299&#10;20,N,476181.804,1157432.362&#10;20,N,475211.522,1157534.959"></textarea>
                </div>

                <div id="coords-list" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Coordenadas agregadas:</label>
                    <div class="max-h-32 overflow-y-auto border border-gray-200 dark:border-gray-500 rounded-md p-2 bg-gray-50 dark:bg-gray-800/80">
                        <div id="coords-container" class="space-y-1"></div>
                    </div>
                    <button type="button" id="clear-list" class="text-red-600 hover:text-red-700 text-xs mt-1">Limpiar lista</button>
                </div>

                <div class="flex space-x-3 pt-2">
                    <button type="button" id="cancel-modal" class="flex-1 py-2 px-4 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium">Cancelar</button>
                    <button type="submit" class="flex-1 py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">Dibujar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== MODAL: EDITAR PUNTO ===================== --}}
    <div id="edit-point-modal" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4" style="z-index:2000;">
        <div class="bg-white dark:bg-custom-gray rounded-xl shadow-2xl w-full max-w-md">
            <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white"><span id="edit-point-title">Editar Punto</span></h3>
                <button id="close-edit-modal" type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="edit-point-form" class="p-6 space-y-4">
                <input type="hidden" id="edit-point-index" value="">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Latitud</label>
                        <input type="number" id="edit-point-lat" step="any" class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Longitud</label>
                        <input type="number" id="edit-point-lng" step="any" class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm p-2">
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Zona</label>
                        <input type="number" id="edit-point-zone" min="1" max="60" class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm p-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Hem.</label>
                        <select id="edit-point-hemisphere" class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm">
                            <option value="N">N</option>
                            <option value="S">S</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Este</label>
                        <input type="number" id="edit-point-easting" step="any" class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm p-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Norte</label>
                        <input type="number" id="edit-point-northing" step="any" class="w-full rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-800/80 dark:text-gray-100 text-sm p-2">
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Puedes editar en grados o en UTM; ambos campos se sincronizan.</p>

                <div class="flex space-x-3 pt-2">
                    <button type="button" id="delete-point-btn" class="flex-1 py-2 px-4 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium">Eliminar</button>
                    <button type="button" id="cancel-edit-point" class="py-2 px-4 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium">Cancelar</button>
                    <button type="submit" class="py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== LIBRERÍAS ===================== --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/css/ol.css">
    <script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/build/ol.js"></script>
    <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.8.0/proj4.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .ol-viewport { border-radius: 0.5rem; }
        .ol-control { background-color: rgba(255,255,255,0.8); border-radius: 4px; padding: 2px; }
        .ol-control:hover { background-color: rgba(255,255,255,0.9); }

        #map {
            width: 100% !important;
            height: 100% !important;
            position: absolute !important;
            top: 0;
            left: 0;
            min-height: 400px;
        }

        #map-controls { pointer-events: auto; }
        #edit-controls { transition: all 0.3s ease; }

        #points-container::-webkit-scrollbar { width: 6px; }
        #points-container::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 3px; }
        #points-container::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
        .dark #points-container::-webkit-scrollbar-track { background: #374151; }
        .dark #points-container::-webkit-scrollbar-thumb { background: #4b5563; }

        #map-container:fullscreen { background: #111827; border-radius: 0; }
    </style>

    <script>
    (function () {
        'use strict';

        // =====================================================================
        // UTILIDADES GENERALES
        // =====================================================================
        const $ = (id) => document.getElementById(id);

        const BASE_TITLES = {
            osm: 'OpenStreetMap',
            satellite: 'Satélite Esri',
            maptiler_satellite: 'MapTiler Satélite',
            terrain: 'Relieve',
            dark: 'Oscuro'
        };
        const DEFAULT_BASE = 'satellite'; // Satélite Esri por defecto

        function toast(message, icon = 'info') {
            if (window.Swal) {
                Swal.fire({
                    toast: true, position: 'top-end', icon: icon, title: message,
                    showConfirmButton: false, timer: 3000, timerProgressBar: true
                });
            } else {
                alert(message);
            }
        }

        // ---- UTM <-> lon/lat (WGS84). Se usa la definición directa, sin registrar códigos EPSG ----
        function utmDef(zone, hemisphere) {
            return '+proj=utm +zone=' + zone + (hemisphere === 'S' ? ' +south' : '') + ' +datum=WGS84 +units=m +no_defs';
        }

        function lonLatToUtm(lon, lat) {
            const zone = Math.min(60, Math.max(1, Math.floor((lon + 180) / 6) + 1));
            const hemisphere = lat >= 0 ? 'N' : 'S';
            const xy = proj4('EPSG:4326', utmDef(zone, hemisphere), [lon, lat]);
            return { zone: zone, hemisphere: hemisphere, easting: xy[0], northing: xy[1] };
        }

        function utmToLonLat(zone, hemisphere, easting, northing) {
            return proj4(utmDef(zone, hemisphere), 'EPSG:4326', [easting, northing]);
        }

        function closestOnSegment(p, a, b) {
            const dx = b[0] - a[0], dy = b[1] - a[1];
            const len2 = dx * dx + dy * dy;
            let t = len2 ? ((p[0] - a[0]) * dx + (p[1] - a[1]) * dy) / len2 : 0;
            t = Math.max(0, Math.min(1, t));
            return [a[0] + t * dx, a[1] + t * dy];
        }

        function normalizeText(str) {
            return (str || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
        }

        function debounce(fn, wait) {
            let t;
            return function (...args) { clearTimeout(t); t = setTimeout(() => fn.apply(this, args), wait); };
        }

        // ---- Menús (mismo comportamiento que map.blade.php) ----
        function toggleMenu(menuId, show) {
            const menu = $(menuId);
            if (!menu) return;
            if (show) {
                menu.classList.remove('hidden');
                void menu.offsetWidth;
                menu.classList.remove('scale-95', 'opacity-0', 'pointer-events-none');
                menu.classList.add('scale-100', 'opacity-100', 'pointer-events-auto', 'show');
            } else {
                closeMenu(menuId);
            }
        }

        function closeMenu(menuId) {
            const menu = $(menuId);
            if (!menu) return;
            menu.classList.remove('scale-100', 'opacity-100', 'pointer-events-auto', 'show');
            menu.classList.add('scale-95', 'opacity-0', 'pointer-events-none');
            setTimeout(() => {
                if (menu.classList.contains('scale-95')) menu.classList.add('hidden');
            }, 400);
        }

        function openModal(id) {
            if (document.fullscreenElement && document.exitFullscreen) document.exitFullscreen();
            $(id).classList.remove('hidden');
        }

        function closeModal(id) {
            $(id).classList.add('hidden');
        }

        const ICON_EDIT = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';
        const ICON_CHECK = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><path d="M20 6 9 17l-5-5"/></svg>';
        const ICON_MAP = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>';

        // =====================================================================
        // EDITOR DE PARCELA
        // =====================================================================
        class PolygonEditor {
            constructor(initialGeometry) {
                this.map = null;
                this.source = null;
                this.vectorLayer = null;
                this.modify = null;
                this.draw = null;
                this.baseLayers = {};
                this.currentBaseKey = DEFAULT_BASE;
                this.currentFeature = null;
                this.isEditMode = false;
                this.addingPoint = false;
                this.polygonPoints = [];
                this.selectedPointIndex = -1;
                this.dirty = false;
                this.initialGeometry = initialGeometry;
                this.geoFormat = new ol.format.GeoJSON();

                this.INITIAL_CENTER = [-63.1729, 10.5556];
                this.INITIAL_ZOOM = 6;
                this.MINZOOM = 3;
                this.MAXZOOM = 19;

                this.init();
            }

            // -------------------------------------------------------------
            // Inicialización
            // -------------------------------------------------------------
            init() {
                if (!$('map')) { console.error('No se encontró #map'); return; }

                try {
                    this.setupBaseLayers();
                    this.setupVectorLayer();
                    this.setupMapInstance();
                } catch (error) {
                    console.error('Error al inicializar el mapa:', error);
                    toast('Error al cargar el mapa: ' + error.message, 'error');
                    return;
                }

                this.setupMapEvents();
                this.setupResizeObserver();
                this.updateBaseMapUI(DEFAULT_BASE);
                this.updateToolbarState();

                if (this.initialGeometry) {
                    this.loadGeometry(this.initialGeometry, { fit: true, silent: true });
                }
                this.dirty = false;

                setTimeout(() => this.map.updateSize(), 300);
            }

            setupResizeObserver() {
                if (!('ResizeObserver' in window)) return;
                const container = $('map-container');
                if (!container) return;
                new ResizeObserver(() => this.map && this.map.updateSize()).observe(container);
            }

            setupBaseLayers() {
                this.baseLayers = {
                    osm: new ol.layer.Tile({
                        visible: false,
                        source: new ol.source.XYZ({
                            url: 'https://{a-c}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png',
                            attributions: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                            maxZoom: 19
                        })
                    }),
                    satellite: new ol.layer.Tile({
                        visible: true,
                        source: new ol.source.XYZ({
                            url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                            attributions: 'Tiles © Esri',
                            maxZoom: 19
                        })
                    }),
                    maptiler_satellite: new ol.layer.Tile({
                        visible: false,
                        source: new ol.source.XYZ({
                            url: 'https://api.maptiler.com/maps/satellite/{z}/{x}/{y}.jpg?key=scUozK4fig7bE6jg7TPi',
                            attributions: '© MapTiler & OpenStreetMap',
                            tileSize: 512,
                            maxZoom: 20
                        })
                    }),
                    terrain: new ol.layer.Tile({
                        visible: false,
                        source: new ol.source.XYZ({
                            url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Shaded_Relief/MapServer/tile/{z}/{y}/{x}',
                            attributions: 'Tiles © Esri',
                            maxZoom: 13
                        })
                    }),
                    dark: new ol.layer.Tile({
                        visible: false,
                        source: new ol.source.XYZ({
                            url: 'https://{a-c}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}.png',
                            attributions: '© CartoDB',
                            maxZoom: 19
                        })
                    })
                };
            }

            setupVectorLayer() {
                this.source = new ol.source.Vector();
                this.vectorLayer = new ol.layer.Vector({
                    source: this.source,
                    style: (feature) => this.getFeatureStyle(feature)
                });
            }

            setupMapInstance() {
                this.map = new ol.Map({
                    target: 'map',
                    layers: [
                        new ol.layer.Group({ layers: Object.values(this.baseLayers) }),
                        this.vectorLayer
                    ],
                    view: new ol.View({
                        center: ol.proj.fromLonLat(this.INITIAL_CENTER),
                        zoom: this.INITIAL_ZOOM,
                        minZoom: this.MINZOOM,
                        maxZoom: this.MAXZOOM,
                        smoothResolutionConstraint: true
                    }),
                    controls: ol.control.defaults({ attributionOptions: { collapsible: true } })
                });
            }

            setupMapEvents() {
                // Selección de vértices / agregar punto
                this.map.on('singleclick', (evt) => {
                    if (this.addingPoint) {
                        this.addPointAt(evt.coordinate, evt.pixel);
                        return;
                    }
                    if (!this.isEditMode || !this.isPolygonType()) return;

                    const idx = this.findVertexNear(evt.pixel, 14);
                    if (idx >= 0) this.selectPoint(idx);
                    else this.deselectPoint();
                });

                // Coordenadas bajo el cursor
                this.map.on('pointermove', (evt) => {
                    if (evt.dragging) return;
                    this.updateCoordinateDisplay(evt.coordinate);
                });
                this.map.getViewport().addEventListener('mouseleave', () => {
                    const el = $('coordinate-display');
                    if (el) el.classList.add('hidden');
                });
            }

            // -------------------------------------------------------------
            // Estilos
            // -------------------------------------------------------------
            getFeatureStyle(feature) {
                const styles = [];
                const areaHa = feature.get('area') || 0;
                const editing = this.isEditMode;

                styles.push(new ol.style.Style({
                    stroke: new ol.style.Stroke({
                        color: editing ? '#8b5cf6' : '#10b981',
                        width: editing ? 4 : 3,
                        lineDash: editing ? [10, 5] : undefined,
                        lineCap: 'round'
                    }),
                    fill: new ol.style.Fill({ color: editing ? 'rgba(139,92,246,0.2)' : 'rgba(16,185,129,0.3)' })
                }));

                if (areaHa > 0) {
                    styles.push(new ol.style.Style({
                        text: new ol.style.Text({
                            text: areaHa.toFixed(4) + ' ha',
                            font: 'bold 14px Arial, sans-serif',
                            fill: new ol.style.Fill({ color: '#1f2937' }),
                            stroke: new ol.style.Stroke({ color: '#ffffff', width: 3 }),
                            backgroundFill: new ol.style.Fill({ color: 'rgba(255,255,255,0.7)' }),
                            padding: [4, 8, 4, 8],
                            textBaseline: 'middle',
                            textAlign: 'center'
                        })
                    }));
                }

                if (editing && feature === this.currentFeature && this.isPolygonType()) {
                    const ring = this.getRing();
                    ring.forEach((coord, index) => {
                        if (index >= ring.length - 1) return;
                        styles.push(new ol.style.Style({
                            geometry: new ol.geom.Point(coord),
                            image: new ol.style.Circle({
                                radius: 9,
                                fill: new ol.style.Fill({ color: index === this.selectedPointIndex ? '#ef4444' : '#3b82f6' }),
                                stroke: new ol.style.Stroke({ color: '#ffffff', width: 2 })
                            }),
                            text: new ol.style.Text({
                                text: String(index + 1),
                                font: 'bold 11px Arial, sans-serif',
                                fill: new ol.style.Fill({ color: '#ffffff' })
                            })
                        }));
                    });
                }

                return styles;
            }

            // -------------------------------------------------------------
            // Geometría actual
            // -------------------------------------------------------------
            isPolygonType() {
                return !!(this.currentFeature && this.currentFeature.getGeometry().getType() === 'Polygon');
            }

            getRing() {
                return this.currentFeature.getGeometry().getCoordinates()[0];
            }

            setRing(ring) {
                ring[ring.length - 1] = ring[0].slice();
                this.currentFeature.getGeometry().setCoordinates([ring]);
            }

            calculateArea(feature) {
                try {
                    const geomObj = this.geoFormat.writeGeometryObject(feature.getGeometry(), {
                        dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'
                    });
                    const m2 = turf.area({ type: 'Feature', geometry: geomObj, properties: {} });
                    return isNaN(m2) || m2 <= 0 ? 0 : parseFloat((m2 / 10000).toFixed(6));
                } catch (e) {
                    console.error('Error calculando área:', e);
                    return 0;
                }
            }

            getGeometryObject() {
                if (!this.currentFeature) return null;
                return this.geoFormat.writeGeometryObject(this.currentFeature.getGeometry(), {
                    dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857', decimals: 8
                });
            }

            updateGeoJSONInput() {
                const geom = this.getGeometryObject();
                $('geometry').value = geom ? JSON.stringify(geom) : '';
            }

            // Carga una geometría GeoJSON (EPSG:4326)
            loadGeometry(geoJson, opts = {}) {
                try {
                    let geom = this.geoFormat.readGeometry(geoJson, {
                        dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'
                    });

                    if (!['Polygon', 'MultiPolygon'].includes(geom.getType())) {
                        throw new Error('La geometría debe ser Polygon o MultiPolygon');
                    }
                    if (geom.getType() === 'MultiPolygon' && geom.getPolygons().length === 1) {
                        geom = geom.getPolygons()[0];
                    }

                    this.setFeature(new ol.Feature(geom), { fit: opts.fit !== false, markDirty: false });

                    if (!this.isPolygonType() && !opts.silent) {
                        toast('Parcela con varias partes: arrastra sus vértices directamente en el mapa.', 'info');
                    }
                } catch (error) {
                    console.error('Error cargando geometría:', error);
                    toast('No se pudo cargar la geometría existente: ' + error.message, 'error');
                }
            }

            setFeature(feature, { fit = true, markDirty = true } = {}) {
                this.source.clear();
                this.currentFeature = feature;
                this.source.addFeature(feature);
                this.refreshFeatureState({ markDirty: markDirty });

                if (fit) {
                    this.map.updateSize();
                    this.map.getView().fit(feature.getGeometry().getExtent(), {
                        padding: [60, 60, 60, 60], maxZoom: 17, duration: 600
                    });
                }
                this.updateToolbarState();
            }

            // Recalcula área, input GeoJSON, lista de puntos y resumen
            refreshFeatureState({ markDirty = true } = {}) {
                if (!this.currentFeature) return;
                const areaHa = this.calculateArea(this.currentFeature);
                this.currentFeature.set('area', areaHa);
                this.updateAreaInput(areaHa);
                this.updateGeoJSONInput();
                this.updatePolygonPoints();
                this.updateSummary(areaHa);
                this.vectorLayer.changed();
                if (markDirty) this.dirty = true;
            }

            updateAreaInput(areaHa) {
                const input = $('area_ha');
                if (input) input.value = areaHa > 0 ? areaHa.toFixed(6) : '';
            }

            updateToolbarState() {
                const has = !!this.currentFeature;
                const detectBtn = $('detect-location');
                const editBtn = $('edit-polygon');
                if (detectBtn) detectBtn.disabled = !has;
                if (editBtn) {
                    editBtn.classList.toggle('hidden', !has);
                    this.setEditButtonState(this.isEditMode);
                }
            }

            setEditButtonState(active) {
                const btn = $('edit-polygon');
                if (!btn) return;
                btn.innerHTML = active ? ICON_CHECK : ICON_EDIT;
                btn.title = active ? 'Finalizar edición' : 'Editar vértices';
                btn.classList.toggle('bg-green-600', active);
                btn.classList.toggle('hover:bg-green-700', active);
                btn.classList.toggle('bg-gray-600', !active);
                btn.classList.toggle('hover:bg-gray-700', !active);
            }

            setModeBadge(text, colorClass) {
                const badge = $('mode-badge');
                if (!badge) return;
                badge.className = 'absolute right-3 px-3 py-1 rounded-full text-xs font-semibold text-white shadow-lg ' + (text ? colorClass : 'hidden');
                badge.style.bottom = '36px';
                badge.style.zIndex = '1000';
                badge.textContent = text || '';
            }

            // -------------------------------------------------------------
            // Dibujo
            // -------------------------------------------------------------
            activateDrawing() {
                this.deactivateDrawing();
                this.exitEditMode(true);

                this.draw = new ol.interaction.Draw({
                    type: 'Polygon',
                    style: new ol.style.Style({
                        stroke: new ol.style.Stroke({ color: '#3b82f6', width: 3, lineDash: [5, 10], lineCap: 'round' }),
                        fill: new ol.style.Fill({ color: 'rgba(59,130,246,0.2)' }),
                        image: new ol.style.Circle({
                            radius: 6,
                            fill: new ol.style.Fill({ color: '#ffffff' }),
                            stroke: new ol.style.Stroke({ color: '#3b82f6', width: 2 })
                        })
                    })
                });

                // Evitar que el doble clic (cierra el polígono) haga zoom
                this.map.getInteractions().forEach((i) => {
                    if (i instanceof ol.interaction.DoubleClickZoom) i.setActive(false);
                });

                this.draw.on('drawend', (event) => {
                    const feature = event.feature;
                    // Se reemplaza la geometría anterior sólo cuando el nuevo trazo termina
                    setTimeout(() => {
                        this.deactivateDrawing();
                        this.setFeature(feature, { fit: false });
                        const li = $('location-info');
                        if (li) li.classList.add('hidden');
                        toast('Parcela dibujada. Área: ' + (feature.get('area') || 0).toFixed(4) + ' ha', 'success');
                    }, 0);
                });

                this.map.addInteraction(this.draw);
                this.setModeBadge('Modo dibujo (Esc para cancelar)', 'bg-blue-600');
                toast('Haz clic en el mapa para dibujar. Doble clic para terminar.', 'info');
            }

            deactivateDrawing() {
                if (this.draw) {
                    this.map.removeInteraction(this.draw);
                    this.draw = null;
                }
                this.map.getInteractions().forEach((i) => {
                    if (i instanceof ol.interaction.DoubleClickZoom) i.setActive(true);
                });
                if (!this.isEditMode) this.setModeBadge('', '');
            }

            // Crea la parcela a partir de una lista de [lon, lat]
            setPolygonFromLonLat(lonLats) {
                const ring = lonLats.map((c) => ol.proj.fromLonLat(c));
                ring.push(ring[0].slice());
                const feature = new ol.Feature(new ol.geom.Polygon([ring]));

                this.deactivateDrawing();
                this.exitEditMode(true);
                this.setFeature(feature, { fit: true });

                const li = $('location-info');
                if (li) li.classList.add('hidden');
                toast('Parcela dibujada desde coordenadas UTM', 'success');
            }

            // -------------------------------------------------------------
            // Modo edición
            // -------------------------------------------------------------
            activateEditMode(silent = false) {
                if (!this.currentFeature) {
                    toast('Primero debes cargar o dibujar una parcela', 'warning');
                    return;
                }
                this.deactivateDrawing();
                this.isEditMode = true;

                $('edit-controls').classList.remove('hidden');
                this.setEditButtonState(true);
                this.setModeBadge('Modo edición', 'bg-purple-600');

                this.activateModify();
                this.updatePolygonPoints();
                this.vectorLayer.changed();

                if (!silent) toast('Edición activada: arrastra vértices o selecciónalos para ver sus datos.', 'info');
            }

            exitEditMode(silent = false) {
                if (!this.isEditMode) {
                    this.cancelAddPoint();
                    return;
                }
                this.isEditMode = false;
                this.selectedPointIndex = -1;
                this.cancelAddPoint();

                $('point-info').classList.add('hidden');
                $('edit-controls').classList.add('hidden');
                this.setEditButtonState(false);
                this.setModeBadge('', '');

                this.deactivateModify();
                this.updatePointsList();
                this.vectorLayer.changed();

                if (!silent) toast('Edición finalizada', 'info');
            }

            activateModify() {
                this.deactivateModify();
                if (!this.currentFeature) return;

                this.modify = new ol.interaction.Modify({
                    source: this.source,
                    pixelTolerance: 12,
                    style: new ol.style.Style({
                        image: new ol.style.Circle({
                            radius: 8,
                            fill: new ol.style.Fill({ color: '#f59e0b' }),
                            stroke: new ol.style.Stroke({ color: '#ffffff', width: 3 })
                        })
                    })
                });

                this.modify.on('modifyend', () => {
                    this.selectedPointIndex = -1;
                    $('point-info').classList.add('hidden');
                    this.refreshFeatureState();
                });

                this.map.addInteraction(this.modify);
            }

            deactivateModify() {
                if (this.modify) {
                    this.map.removeInteraction(this.modify);
                    this.modify = null;
                }
            }

            // -------------------------------------------------------------
            // Selección / edición de puntos
            // -------------------------------------------------------------
            findVertexNear(pixel, tolerance) {
                const ring = this.getRing();
                let best = -1, bestDist = Infinity;
                for (let i = 0; i < ring.length - 1; i++) {
                    const p = this.map.getPixelFromCoordinate(ring[i]);
                    if (!p) continue;
                    const d = Math.hypot(p[0] - pixel[0], p[1] - pixel[1]);
                    if (d < bestDist && d <= tolerance) { bestDist = d; best = i; }
                }
                return best;
            }

            selectPoint(index, { center = false } = {}) {
                if (!this.isPolygonType()) return;
                const ring = this.getRing();
                if (index < 0 || index >= ring.length - 1) return;

                this.selectedPointIndex = index;
                const ll = ol.proj.toLonLat(ring[index]);
                $('selected-lat').textContent = ll[1].toFixed(6);
                $('selected-lng').textContent = ll[0].toFixed(6);
                $('selected-index').textContent = String(index + 1);
                $('point-info').classList.remove('hidden');

                if (center) this.map.getView().animate({ center: ring[index], duration: 300 });

                this.vectorLayer.changed();
                this.updatePointsList();
            }

            deselectPoint() {
                if (this.selectedPointIndex === -1) return;
                this.selectedPointIndex = -1;
                $('point-info').classList.add('hidden');
                this.vectorLayer.changed();
                this.updatePointsList();
            }

            deletePoint(index) {
                if (index < 0 || !this.isPolygonType()) return;
                const ring = this.getRing();

                if (ring.length <= 4) { // 3 vértices + cierre
                    toast('La parcela debe tener al menos 3 puntos', 'warning');
                    return;
                }
                if (index >= ring.length - 1) return;

                ring.splice(index, 1);
                this.setRing(ring);

                this.selectedPointIndex = -1;
                $('point-info').classList.add('hidden');
                this.refreshFeatureState();
                toast('Punto ' + (index + 1) + ' eliminado', 'success');
            }

            movePoint(index, lon, lat) {
                if (!this.isPolygonType()) return;
                const ring = this.getRing();
                if (index < 0 || index >= ring.length - 1) return;

                ring[index] = ol.proj.fromLonLat([lon, lat]);
                this.setRing(ring);
                this.refreshFeatureState();
                this.selectPoint(index);
            }

            startAddPoint() {
                if (!this.isEditMode || !this.isPolygonType()) return;
                this.addingPoint = true;
                $('map').style.cursor = 'crosshair';
                toast('Haz clic cerca del borde donde quieres insertar el nuevo punto', 'info');
            }

            cancelAddPoint() {
                this.addingPoint = false;
                const m = $('map');
                if (m) m.style.cursor = '';
            }

            addPointAt(coordinate, pixel) {
                if (!this.isPolygonType()) { this.cancelAddPoint(); return; }
                const ring = this.getRing();

                let bestIdx = -1, bestPoint = null, bestDist = Infinity;
                for (let i = 0; i < ring.length - 1; i++) {
                    const snapped = closestOnSegment(coordinate, ring[i], ring[i + 1]);
                    const sp = this.map.getPixelFromCoordinate(snapped);
                    if (!sp) continue;
                    const d = Math.hypot(sp[0] - pixel[0], sp[1] - pixel[1]);
                    if (d < bestDist) { bestDist = d; bestIdx = i; bestPoint = snapped; }
                }

                if (bestIdx === -1 || bestDist > 25) {
                    toast('Haz clic más cerca de un borde de la parcela', 'warning');
                    return;
                }

                ring.splice(bestIdx + 1, 0, bestPoint);
                this.setRing(ring);
                this.cancelAddPoint();
                this.refreshFeatureState();
                this.selectPoint(bestIdx + 1);
                toast('Nuevo punto agregado', 'success');
            }

            // -------------------------------------------------------------
            // Panel lateral
            // -------------------------------------------------------------
            updatePolygonPoints() {
                this.polygonPoints = [];
                if (this.currentFeature && this.isPolygonType()) {
                    const ring = this.getRing();
                    for (let i = 0; i < ring.length - 1; i++) {
                        const ll = ol.proj.toLonLat(ring[i]);
                        this.polygonPoints.push({ lat: ll[1], lng: ll[0], index: i });
                    }
                }
                this.updatePointsList();
            }

            updatePointsList() {
                const container = $('points-container');
                const noMsg = $('no-points-message');
                const hint = $('no-points-hint');
                if (!container) return;

                $('points-count').textContent = this.polygonPoints.length;
                $('summary-points').textContent = this.polygonPoints.length;

                if (noMsg) {
                    noMsg.classList.toggle('hidden', this.polygonPoints.length > 0);
                    if (hint) {
                        const multi = this.currentFeature && !this.isPolygonType();
                        hint.textContent = multi
                            ? 'Parcela con varias partes: edita sus vértices directamente en el mapa'
                            : 'Carga una parcela o dibuja una nueva';
                    }
                }

                container.querySelectorAll('[data-point-item]').forEach((el) => el.remove());
                this.polygonPoints.forEach((point, index) => {
                    container.appendChild(this.createPointElement(point, index));
                });
            }

            createPointElement(point, index) {
                const selected = index === this.selectedPointIndex;
                const el = document.createElement('div');
                el.setAttribute('data-point-item', '1');
                el.className = 'bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm border cursor-pointer hover:border-blue-400 ' +
                    (selected ? 'border-blue-500 ring-2 ring-blue-500' : 'border-gray-200 dark:border-gray-700');

                let utmHtml = '';
                try {
                    const u = lonLatToUtm(point.lng, point.lat);
                    utmHtml =
                        '<div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Zona: ' + u.zone + ' ' + u.hemisphere + '</div>' +
                        '<div class="text-sm"><span class="text-gray-600 dark:text-gray-400">Este:</span> <span class="font-mono text-blue-600 dark:text-blue-400">' + u.easting.toFixed(3) + '</span></div>' +
                        '<div class="text-sm"><span class="text-gray-600 dark:text-gray-400">Norte:</span> <span class="font-mono text-blue-600 dark:text-blue-400">' + u.northing.toFixed(3) + '</span></div>';
                } catch (e) { utmHtml = ''; }

                el.innerHTML =
                    '<div class="flex justify-between items-start mb-2">' +
                        '<div class="flex items-center">' +
                            '<div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center mr-2">' +
                                '<span class="text-blue-600 dark:text-blue-300 font-semibold text-sm">' + (index + 1) + '</span>' +
                            '</div>' +
                            '<div><h4 class="font-medium text-gray-900 dark:text-white">Punto ' + (index + 1) + '</h4>' +
                            '<p class="text-xs text-gray-500 dark:text-gray-400">Clic para ubicar</p></div>' +
                        '</div>' +
                        '<button type="button" class="edit-point-btn text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700" title="Editar punto">' +
                            '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" /></svg>' +
                        '</button>' +
                    '</div>' + utmHtml;

                el.addEventListener('click', (e) => {
                    if (e.target.closest('button')) return;
                    if (!this.isEditMode) this.activateEditMode(true);
                    this.selectPoint(index, { center: true });
                });

                el.querySelector('.edit-point-btn').addEventListener('click', (e) => {
                    e.stopPropagation();
                    openEditPointModal(index);
                });

                return el;
            }

            updateSummary(areaHa) {
                const summary = $('polygon-summary');
                if (!summary) return;
                summary.classList.toggle('hidden', !this.currentFeature);
                $('summary-area').textContent = (areaHa || 0).toFixed(4);

                let perimeter = 0;
                try {
                    const geomObj = this.getGeometryObject();
                    const line = turf.polygonToLine({ type: 'Feature', geometry: geomObj, properties: {} });
                    perimeter = turf.length(line, { units: 'kilometers' });
                } catch (e) { perimeter = 0; }
                $('summary-perimeter').textContent = perimeter.toFixed(4);
            }

            // -------------------------------------------------------------
            // Limpiar
            // -------------------------------------------------------------
            clearMap() {
                this.deactivateDrawing();
                this.exitEditMode(true);
                this.source.clear();
                this.currentFeature = null;
                this.selectedPointIndex = -1;
                this.polygonPoints = [];

                $('geometry').value = '';
                $('area_ha').value = '';
                $('point-info').classList.add('hidden');
                const li = $('location-info');
                if (li) li.classList.add('hidden');

                this.updatePointsList();
                this.updateSummary(0);
                this.updateToolbarState();
                this.dirty = true;
                toast('Mapa limpiado. Dibuja una nueva geometría antes de guardar.', 'info');
            }

            confirmClear() {
                if (!this.currentFeature) return;
                if (window.Swal) {
                    Swal.fire({
                        title: '¿Limpiar el mapa?',
                        text: 'Se quitará la geometría actual. No se guardará hasta que presiones Actualizar.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, limpiar',
                        cancelButtonText: 'Cancelar'
                    }).then((r) => { if (r.isConfirmed) this.clearMap(); });
                } else if (confirm('¿Limpiar el mapa?')) {
                    this.clearMap();
                }
            }

            // -------------------------------------------------------------
            // Capas base y coordenadas
            // -------------------------------------------------------------
            changeBaseLayer(key) {
                if (!this.baseLayers[key]) { toast('Capa base no encontrada: ' + key, 'error'); return; }
                Object.values(this.baseLayers).forEach((l) => l.setVisible(false));
                this.baseLayers[key].setVisible(true);
                this.currentBaseKey = key;
                this.updateBaseMapUI(key);
            }

            updateBaseMapUI(key) {
                const btn = $('base-map-toggle');
                if (btn) btn.innerHTML = ICON_MAP + BASE_TITLES[key];

                document.querySelectorAll('#base-map-menu button').forEach((b) => {
                    const active = b.getAttribute('data-layer') === key;
                    b.classList.toggle('font-semibold', active);
                    b.classList.toggle('bg-gray-200', active);
                    b.classList.toggle('dark:bg-gray-700', active);
                });
            }

            updateCoordinateDisplay(coordinate) {
                const el = $('coordinate-display');
                if (!el) return;
                try {
                    const ll = ol.proj.toLonLat(coordinate);
                    if (Math.abs(ll[1]) > 84) { el.classList.add('hidden'); return; }
                    const u = lonLatToUtm(ll[0], ll[1]);
                    el.textContent = 'Zona ' + u.zone + u.hemisphere + ' | Este: ' + u.easting.toFixed(2) +
                        ' | Norte: ' + u.northing.toFixed(2);
                    el.classList.remove('hidden');
                } catch (e) {
                    el.classList.add('hidden');
                }
            }

            toggleFullscreen() {
                const container = $('map-container');
                if (!document.fullscreenElement) {
                    (container.requestFullscreen || container.webkitRequestFullscreen || container.msRequestFullscreen || function () {}).call(container);
                } else {
                    (document.exitFullscreen || document.webkitExitFullscreen || document.msExitFullscreen || function () {}).call(document);
                }
            }

            // -------------------------------------------------------------
            // Detección de ubicación
            // -------------------------------------------------------------
            getCentroid() {
                const geom = this.getGeometryObject();
                if (!geom) return null;
                try {
                    const c = turf.centerOfMass({ type: 'Feature', geometry: geom, properties: {} });
                    return { lng: c.geometry.coordinates[0], lat: c.geometry.coordinates[1] };
                } catch (e) {
                    const ext = this.currentFeature.getGeometry().getExtent();
                    const ll = ol.proj.toLonLat(ol.extent.getCenter(ext));
                    return { lng: ll[0], lat: ll[1] };
                }
            }

            async detectLocation() {
                if (!this.currentFeature) { toast('Debes tener una parcela en el mapa', 'error'); return; }

                const centroid = this.getCentroid();
                if (!centroid) { toast('No se pudo calcular el centro de la parcela', 'error'); return; }

                $('centroid_lat').value = centroid.lat;
                $('centroid_lng').value = centroid.lng;

                const btn = $('detect-location');
                const text = $('detect-button-text');
                const original = text.textContent;
                text.textContent = 'Detectando...';
                btn.disabled = true;

                try {
                    const url = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' + centroid.lat +
                        '&lon=' + centroid.lng + '&zoom=10&addressdetails=1&accept-language=es';
                    // Sin cabeceras personalizadas: evitan el preflight CORS
                    const response = await fetch(url);
                    if (!response.ok) throw new Error('Error HTTP: ' + response.status);
                    this.processLocationData(await response.json(), centroid);
                } catch (error) {
                    console.error('Error en detección de ubicación:', error);
                    toast('Error detectando ubicación: ' + error.message, 'error');
                } finally {
                    btn.disabled = !this.currentFeature;
                    text.textContent = original;
                }
            }

            removePrefixes(str, prefixes) {
                if (!str) return '';
                let result = str.trim();
                prefixes.forEach((prefix) => {
                    result = result.replace(new RegExp('^' + prefix + '\\s+', 'i'), '');
                });
                return result.replace(/\s+/g, ' ').trim();
            }

            processLocationData(data, centroid) {
                const a = data.address || {};

                const parish = a.municipality || a.suburb || a.city_district || a.village || a.town || '';
                const municipality = a.county || a.state_district || a.region || '';
                const state = a.state || a.region || '';

                const cleanParish = this.removePrefixes(parish, ['Parroquia', 'Sector', 'Zona']);
                const cleanMunicipality = this.removePrefixes(municipality, ['Municipio', 'Distrito', 'County']);
                const cleanState = this.removePrefixes(state, ['Estado', 'State', 'Departamento']);

                $('detected_parish').value = cleanParish;
                $('detected_municipality').value = cleanMunicipality;
                $('detected_state').value = cleanState;
                $('location_data').value = JSON.stringify(data);

                $('detected-parish-text').textContent = cleanParish || 'No detectado';
                $('detected-municipality-text').textContent = cleanMunicipality || 'No detectado';
                $('detected-state-text').textContent = cleanState || 'No detectado';
                $('detected-coords-text').textContent = centroid.lat.toFixed(6) + ', ' + centroid.lng.toFixed(6);
                $('location-info').classList.remove('hidden');

                this.updateParishSelect(cleanParish, cleanMunicipality);
                this.dirty = true;
            }

            updateParishSelect(parishName, municipalityName) {
                const select = $('parish_id');
                if (!select) return;

                const target = normalizeText(parishName);
                if (!target) { toast('No se detectó la parroquia. Selecciónala manualmente.', 'info'); return; }
                const muni = normalizeText(municipalityName);

                const candidates = [];
                for (const option of select.options) {
                    if (!option.value) continue;
                    const name = normalizeText(option.dataset.name || option.text);
                    if (name === target || name.includes(target) || target.includes(name)) {
                        candidates.push({
                            option: option,
                            exact: name === target,
                            sameMuni: muni && normalizeText(option.dataset.municipality) === muni
                        });
                    }
                }

                candidates.sort((x, y) =>
                    (Number(y.sameMuni) - Number(x.sameMuni)) || (Number(y.exact) - Number(x.exact)));

                if (candidates.length) {
                    select.value = candidates[0].option.value;
                    toast('Parroquia "' + candidates[0].option.text.trim() + '" asignada automáticamente', 'success');
                } else {
                    toast('Ubicación detectada, pero no hay parroquia coincidente. Selecciónala manualmente.', 'info');
                }
            }
        }

        // =====================================================================
        // MODAL: EDITAR PUNTO
        // =====================================================================
        let editor = null;
        let syncing = false;

        function fillUtmFromLatLng() {
            if (syncing) return;
            const lat = parseFloat($('edit-point-lat').value);
            const lng = parseFloat($('edit-point-lng').value);
            if (isNaN(lat) || isNaN(lng) || Math.abs(lat) > 84) return;
            syncing = true;
            try {
                const u = lonLatToUtm(lng, lat);
                $('edit-point-zone').value = u.zone;
                $('edit-point-hemisphere').value = u.hemisphere;
                $('edit-point-easting').value = u.easting.toFixed(3);
                $('edit-point-northing').value = u.northing.toFixed(3);
            } catch (e) { /* ignorar */ }
            syncing = false;
        }

        function fillLatLngFromUtm() {
            if (syncing) return;
            const zone = parseInt($('edit-point-zone').value, 10);
            const hemi = $('edit-point-hemisphere').value;
            const e = parseFloat($('edit-point-easting').value);
            const n = parseFloat($('edit-point-northing').value);
            if (isNaN(zone) || zone < 1 || zone > 60 || isNaN(e) || isNaN(n)) return;
            syncing = true;
            try {
                const ll = utmToLonLat(zone, hemi, e, n);
                $('edit-point-lng').value = ll[0].toFixed(8);
                $('edit-point-lat').value = ll[1].toFixed(8);
            } catch (err) { /* ignorar */ }
            syncing = false;
        }

        function openEditPointModal(index) {
            if (!editor) return;
            const point = editor.polygonPoints[index];
            if (!point) return;

            $('edit-point-title').textContent = 'Editar Punto ' + (index + 1);
            $('edit-point-index').value = index;
            $('edit-point-lat').value = point.lat.toFixed(8);
            $('edit-point-lng').value = point.lng.toFixed(8);
            fillUtmFromLatLng();

            openModal('edit-point-modal');
            setTimeout(() => { $('edit-point-lat').focus(); $('edit-point-lat').select(); }, 100);
        }

        function setupEditPointModal() {
            ['edit-point-lat', 'edit-point-lng'].forEach((id) => $(id).addEventListener('input', fillUtmFromLatLng));
            ['edit-point-zone', 'edit-point-hemisphere', 'edit-point-easting', 'edit-point-northing']
                .forEach((id) => $(id).addEventListener('input', fillLatLngFromUtm));

            ['close-edit-modal', 'cancel-edit-point'].forEach((id) =>
                $(id).addEventListener('click', () => closeModal('edit-point-modal')));

            $('delete-point-btn').addEventListener('click', () => {
                const idx = parseInt($('edit-point-index').value, 10);
                if (!isNaN(idx) && editor) editor.deletePoint(idx);
                closeModal('edit-point-modal');
            });

            $('edit-point-form').addEventListener('submit', (e) => {
                e.preventDefault();
                const idx = parseInt($('edit-point-index').value, 10);
                const lat = parseFloat($('edit-point-lat').value);
                const lng = parseFloat($('edit-point-lng').value);

                if (isNaN(idx) || !editor || !editor.currentFeature) return;
                if (isNaN(lat) || isNaN(lng)) { toast('Ingresa coordenadas válidas', 'error'); return; }
                if (lat < -90 || lat > 90) { toast('La latitud debe estar entre -90 y 90', 'error'); return; }
                if (lng < -180 || lng > 180) { toast('La longitud debe estar entre -180 y 180', 'error'); return; }

                editor.movePoint(idx, lng, lat);
                toast('Punto ' + (idx + 1) + ' actualizado', 'success');
                closeModal('edit-point-modal');
            });

            $('edit-point-modal').addEventListener('click', (e) => {
                if (e.target === $('edit-point-modal')) closeModal('edit-point-modal');
            });
        }

        // =====================================================================
        // MODAL: COORDENADAS UTM MANUALES
        // =====================================================================
        const manual = { method: 'single', coords: [] };

        function setInputMethod(method) {
            manual.method = method;
            const active = 'flex-1 py-2 px-3 bg-blue-600 text-white rounded-lg text-sm font-medium';
            const inactive = 'flex-1 py-2 px-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium';
            $('method-single').className = method === 'single' ? active : inactive;
            $('method-bulk').className = method === 'bulk' ? active : inactive;
            $('single-input').classList.toggle('hidden', method !== 'single');
            $('bulk-input').classList.toggle('hidden', method !== 'bulk');
            renderCoordList();
        }

        function validateUtm(zone, hemisphere, easting, northing) {
            if (!Number.isInteger(zone) || zone < 1 || zone > 60) return 'La zona debe estar entre 1 y 60';
            if (hemisphere !== 'N' && hemisphere !== 'S') return 'El hemisferio debe ser N o S';
            if (!isFinite(easting) || easting < 100000 || easting > 900000) return 'El valor Este debe estar entre 100000 y 900000';
            if (!isFinite(northing) || northing < 0 || northing > 10000000) return 'El valor Norte debe estar entre 0 y 10000000';
            return null;
        }

        function renderCoordList() {
            const wrap = $('coords-list');
            const container = $('coords-container');
            wrap.classList.toggle('hidden', manual.method !== 'single' || manual.coords.length === 0);
            container.innerHTML = '';
            manual.coords.forEach((c, i) => {
                const row = document.createElement('div');
                row.className = 'flex justify-between items-center text-xs font-mono text-gray-700 dark:text-gray-300';
                row.innerHTML = '<span>' + (i + 1) + '. ' + c.zone + c.hemisphere + ' | ' + c.easting + ' | ' + c.northing + '</span>' +
                    '<button type="button" data-remove="' + i + '" class="text-red-600 hover:text-red-700 px-2">✕</button>';
                container.appendChild(row);
            });
        }

        function parseBulk(text) {
            const rows = [];
            const lines = text.split(/\r?\n/);
            lines.forEach((raw, i) => {
                const line = raw.trim();
                if (!line) return;
                const parts = line.split(/[,;\t]+/).map((p) => p.trim()).filter((p) => p !== '');
                // Encabezado (la primera columna no es numérica): se omite
                if (parts.length && isNaN(Number(parts[0]))) return;
                if (parts.length !== 4) throw new Error('Línea ' + (i + 1) + ': se esperan 4 valores (Zona,Hemisferio,Este,Norte)');

                const zone = parseInt(parts[0], 10);
                const hemisphere = parts[1].toUpperCase();
                const easting = parseFloat(parts[2]);
                const northing = parseFloat(parts[3]);
                const err = validateUtm(zone, hemisphere, easting, northing);
                if (err) throw new Error('Línea ' + (i + 1) + ': ' + err);
                rows.push({ zone: zone, hemisphere: hemisphere, easting: easting, northing: northing });
            });
            return rows;
        }

        function resetManualModal() {
            manual.coords = [];
            $('bulk-coords').value = '';
            $('single-easting').value = '';
            $('single-northing').value = '';
            setInputMethod('single');
        }

        function setupManualModal() {
            $('manual-polygon-toggle').addEventListener('click', () => {
                resetManualModal();
                openModal('manual-polygon-modal');
            });

            ['close-modal', 'cancel-modal'].forEach((id) =>
                $(id).addEventListener('click', () => closeModal('manual-polygon-modal')));

            $('manual-polygon-modal').addEventListener('click', (e) => {
                if (e.target === $('manual-polygon-modal')) closeModal('manual-polygon-modal');
            });

            $('method-single').addEventListener('click', () => setInputMethod('single'));
            $('method-bulk').addEventListener('click', () => setInputMethod('bulk'));

            $('add-coord').addEventListener('click', () => {
                const zone = parseInt($('single-zone').value, 10);
                const hemisphere = $('single-hemisphere').value;
                const easting = parseFloat($('single-easting').value.replace(',', '.'));
                const northing = parseFloat($('single-northing').value.replace(',', '.'));
                const err = validateUtm(zone, hemisphere, easting, northing);
                if (err) { toast(err, 'error'); return; }

                manual.coords.push({ zone: zone, hemisphere: hemisphere, easting: easting, northing: northing });
                $('single-easting').value = '';
                $('single-northing').value = '';
                $('single-easting').focus();
                renderCoordList();
            });

            $('coords-container').addEventListener('click', (e) => {
                const btn = e.target.closest('[data-remove]');
                if (!btn) return;
                manual.coords.splice(parseInt(btn.dataset.remove, 10), 1);
                renderCoordList();
            });

            $('clear-list').addEventListener('click', () => { manual.coords = []; renderCoordList(); });

            $('manual-polygon-form').addEventListener('submit', (e) => {
                e.preventDefault();
                let coords;
                try {
                    coords = manual.method === 'bulk' ? parseBulk($('bulk-coords').value) : manual.coords.slice();
                } catch (err) {
                    toast(err.message, 'error');
                    return;
                }

                if (coords.length < 3) { toast('Se necesitan al menos 3 coordenadas', 'warning'); return; }

                let lonLats;
                try {
                    lonLats = coords.map((c) => utmToLonLat(c.zone, c.hemisphere, c.easting, c.northing));
                } catch (err) {
                    toast('No se pudieron convertir las coordenadas: ' + err.message, 'error');
                    return;
                }

                if (lonLats.some((p) => !isFinite(p[0]) || !isFinite(p[1]))) {
                    toast('Alguna coordenada UTM no es válida', 'error');
                    return;
                }

                // Quitar el punto de cierre si el usuario lo repitió
                const first = lonLats[0], last = lonLats[lonLats.length - 1];
                if (lonLats.length > 3 && Math.abs(first[0] - last[0]) < 1e-9 && Math.abs(first[1] - last[1]) < 1e-9) {
                    lonLats.pop();
                }

                editor.setPolygonFromLonLat(lonLats);
                closeModal('manual-polygon-modal');
            });
        }

        // =====================================================================
        // CONTROLES DEL MAPA, FORMULARIO Y ATAJOS
        // =====================================================================
        function setupControls() {
            $('draw-polygon').addEventListener('click', () => editor.activateDrawing());

            $('edit-polygon').addEventListener('click', () => {
                if (editor.isEditMode) editor.exitEditMode();
                else editor.activateEditMode();
            });

            $('clear-map').addEventListener('click', () => editor.confirmClear());
            $('add-point').addEventListener('click', () => editor.startAddPoint());
            $('finish-edit').addEventListener('click', () => editor.exitEditMode());

            $('delete-point').addEventListener('click', () => editor.deletePoint(editor.selectedPointIndex));
            $('update-point').addEventListener('click', () => {
                if (editor.selectedPointIndex !== -1) openEditPointModal(editor.selectedPointIndex);
            });

            $('detect-location').addEventListener('click', () => editor.detectLocation());
            $('fullscreen-toggle').addEventListener('click', () => editor.toggleFullscreen());

            document.addEventListener('fullscreenchange', () => {
                setTimeout(() => editor.map.updateSize(), 150);
            });

            // Menú de capas base
            $('base-map-toggle').addEventListener('click', (e) => {
                e.stopPropagation();
                toggleMenu('base-map-menu', !$('base-map-menu').classList.contains('show'));
            });

            document.querySelectorAll('#base-map-menu button').forEach((button) => {
                button.addEventListener('click', (e) => {
                    e.stopPropagation();
                    editor.changeBaseLayer(button.getAttribute('data-layer'));
                    closeMenu('base-map-menu');
                });
            });

            document.addEventListener('click', (e) => {
                if (!$('base-map-toggle').contains(e.target) && !$('base-map-menu').contains(e.target)) {
                    closeMenu('base-map-menu');
                }
            });

            // Atajos
            document.addEventListener('keydown', (e) => {
                if (e.key !== 'Escape') return;
                if (!$('manual-polygon-modal').classList.contains('hidden')) { closeModal('manual-polygon-modal'); return; }
                if (!$('edit-point-modal').classList.contains('hidden')) { closeModal('edit-point-modal'); return; }
                if (editor.addingPoint) { editor.cancelAddPoint(); return; }
                if (editor.draw) { editor.deactivateDrawing(); return; }
                if (editor.isEditMode) editor.exitEditMode();
            });

            window.addEventListener('resize', debounce(() => editor.map.updateSize(), 250));
        }

        function setupFormGuard() {
            const form = $('polygon-form');

            form.addEventListener('submit', (e) => {
                const geometry = $('geometry').value.trim();
                const name = $('name').value.trim();

                if (!name) {
                    e.preventDefault();
                    toast('El nombre de la parcela es obligatorio', 'error');
                    $('name').focus();
                    return;
                }

                if (!geometry) {
                    e.preventDefault();
                    toast('La parcela debe tener una geometría. Dibújala o ingresa coordenadas.', 'error');
                    return;
                }

                try {
                    const g = JSON.parse(geometry);
                    if (!g.coordinates || !['Polygon', 'MultiPolygon'].includes(g.type)) throw new Error('tipo');
                    if (g.type === 'Polygon' && g.coordinates[0].length < 4) {
                        e.preventDefault();
                        toast('La parcela debe tener al menos 3 puntos', 'error');
                        return;
                    }
                } catch (err) {
                    e.preventDefault();
                    toast('La geometría no es válida', 'error');
                    return;
                }

                // Envío válido: evitar doble clic y el aviso de salida
                editor.dirty = false;
                const btn = $('submit-btn');
                if (btn) {
                    btn.disabled = true;
                    btn.style.opacity = '0.6';
                }
            });

            window.addEventListener('beforeunload', (e) => {
                if (editor && editor.dirty) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });

            ['name', 'description', 'producer_id', 'parish_id'].forEach((id) => {
                const el = $(id);
                if (el) el.addEventListener('change', () => { editor.dirty = true; });
            });
        }

        // =====================================================================
        // ARRANQUE
        // =====================================================================
        function readInitialGeometry() {
            const input = $('geometry');
            if (!input || !input.value) return null;
            try {
                let parsed = JSON.parse(input.value);
                // Tolerar valores doblemente codificados (string dentro de string)
                if (typeof parsed === 'string') parsed = JSON.parse(parsed);
                return parsed;
            } catch (error) {
                console.error('Error parseando la geometría existente:', error);
                toast('No se pudo leer la geometría guardada', 'error');
                return null;
            }
        }

        function boot() {
            editor = new PolygonEditor(readInitialGeometry());
            window.polygonEditor = editor;
            setupControls();
            setupEditPointModal();
            setupManualModal();
            setupFormGuard();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', boot);
        } else {
            boot();
        }
    })();
    </script>
</x-app-layout>
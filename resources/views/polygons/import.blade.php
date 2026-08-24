{{-- resources/views/polygons/import.blade.php --}}
<x-app-layout>
    <div class="max-w-4xl mx-auto py-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                Importar Polígonos desde GeoJSON
            </h2>

            <form action="{{ route('polygons.import') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- Archivo --}}
                <div>
                    <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Archivo GeoJSON</label>
                    <input type="file" name="file" id="file" accept=".json,.geojson" required
                           class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-md file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-blue-50 file:text-blue-700
                                  dark:file:bg-blue-900 dark:file:text-blue-300
                                  hover:file:bg-blue-100 dark:hover:file:bg-blue-800
                                  cursor-pointer border border-gray-300 dark:border-gray-600 rounded-md">
                    @error('file')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- SRID --}}
                <div>
                    <label for="srid" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        SRID de entrada (sistema de coordenadas del archivo)
                    </label>
                    <input type="number" name="srid" id="srid" value="{{ old('srid', 2203) }}" min="0"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Si el archivo tiene un CRS definido, se usará automáticamente. Si no, introduce el código EPSG (ej. 2203 para Venezuela). Por defecto 4326 (WGS84).
                    </p>
                </div>

                {{-- Asignación de parroquia --}}
                <div>
                    <label for="parish_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Parroquia predeterminada (opcional)
                    </label>
                    <select name="parish_id" id="parish_id"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">-- Detectar automáticamente o dejar sin asignar --</option>
                        @foreach($parishes as $parish)
                            <option value="{{ $parish->id }}">{{ $parish->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Si no se selecciona, se intentará detectar por ubicación (requiere geometría en parroquias).</p>
                </div>

                {{-- Productor --}}
                <div>
                    <label for="default_producer_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Productor predeterminado (si no se encuentra en el archivo)
                    </label>
                    <select name="default_producer_id" id="default_producer_id"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">-- Ninguno --</option>
                        @foreach($producers as $producer)
                            <option value="{{ $producer->id }}">{{ $producer->name }} {{ $producer->lastname }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Campo de productor en properties --}}
                <div>
                    <label for="producer_field" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nombre del campo en properties que contiene el productor
                    </label>
                    <input type="text" name="producer_field" id="producer_field" value="Productor"
                           class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Ejemplo: "Productor", "propietario", "owner".</p>
                </div>

                {{-- Opciones --}}
                <div class="space-y-2">
                    <div class="flex items-center">
                        <input type="checkbox" name="create_missing_producers" id="create_missing_producers" value="1"
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        <label for="create_missing_producers" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Crear productores que no existan
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="skip_existing" id="skip_existing" value="1"
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        <label for="skip_existing" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Omitir polígonos con un 'id' ya existente (evita duplicados)
                        </label>
                    </div>
                </div>

                {{-- Botones --}}
                <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('polygons.index') }}"
                       class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md shadow transition">
                        Importar
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
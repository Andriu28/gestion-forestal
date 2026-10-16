<div>
    <form wire:submit="update" id="producer-form">
        @csrf
        <div class="mt-4 grid grid-cols-1 md:grid-cols-12 gap-4 mb-4 items-start">

            {{-- Nombre --}}
            <div class="md:col-span-4">
                <x-input-label for="name" :value="__('Nombre del productor *')" />
                <x-text-input id="name" class="block mt-1 w-full" type="text"
                    wire:model.live.debounce.800ms="name"
                    :uppercase="true"
                    :letters-only="true"
                    autofocus />
                <x-input-error :messages="$errors->first('name')" class="mt-2" />
            </div>

            {{-- Apellido --}}
            <div class="md:col-span-4">
                <x-input-label for="lastname" :value="__('Apellido *')" />
                <x-text-input id="lastname" class="block mt-1 w-full" type="text"
                    wire:model.live.debounce.800ms="lastname"
                    :uppercase="true"
                    :letters-only="true" />
                <x-input-error :messages="$errors->first('lastname')" class="mt-2" />
            </div>

            {{-- Tipo --}}
            <div class="md:col-span-2">
                <x-select-input
                    id="cedula_type"
                    name="cedula_type"
                    label="Tipo *"
                    wire:model.live="cedula_type"
                    :options="[
                        'V' => 'V - Venezolano',
                        'E' => 'E - Extranjero',
                        'P' => 'P - Pasaporte',
                        'J' => 'J - Jurídico',
                        'G' => 'G - Gubernamental',
                    ]"
                />
                <x-input-error :messages="$errors->first('cedula_type')" class="mt-2" />
            </div>

            {{-- Cédula --}}
            <div class="md:col-span-2">
                <x-input-label for="cedula" :value="__('Cédula de identidad *')" />
                <div class="relative">
                    <x-text-input id="cedula" class="block mt-1 w-full" type="text"
                        inputmode="numeric"
                        wire:model.live.debounce.600ms="cedula"
                        maxlength="10"
                        :digits-only="true"
                        placeholder="Ej: 12345678" />
                    <span wire:loading wire:target="cedula"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-gray-500 dark:text-gray-400">
                        ⏳
                    </span>
                </div>
                <x-input-error :messages="$errors->first('cedula')" class="mt-2" />
            </div>

        </div>

        {{-- Código generado (solo lectura, en tiempo real) --}}
        <div class="mt-4 grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-4">
                <x-input-label for="code" :value="__('Código de productor')" />

                <x-text-input id="code"
                    type="text"
                    readonly
                    class="block mt-1 w-full font-mono bg-gray-100 dark:bg-gray-700/60 cursor-not-allowed
                        text-custom-gray dark:text-gray-200 border-stone-400/80 dark:border-gray-600
                        focus:ring-0 focus:border-stone-400/80 dark:focus:border-gray-600"
                    value="{{ $this->codePreview ?: '—' }}" />

                <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                    Se genera automáticamente a partir del tipo y la cédula.
                </p>
            </div>
        </div>

        <div class="mt-4">
            <x-input-label for="description" :value="__('Descripción *')" />
            <x-textarea-input id="description"
                wire:model.live.debounce.800ms="description"
                :uppercase="true"
                rows="3"
                placeholder="Descripción del productor..." />
            <x-input-error :messages="$errors->first('description')" class="mt-2" />
        </div>

        <!-- ===== MAPA ===== -->
        <div class="mt-6">
            <x-input-label :value="__('Ubicación (haz clic en el mapa para seleccionar)')" />

            <livewire:components.location-picker
                :latitude="$latitude"
                :longitude="$longitude"
                :address="$address"
                map-id="producer-map-edit"
                show-coordinates="true"
                show-address="true"
                show-locate-button="true"
                initial-zoom="13"
                initial-center-lon="-63.2535"
                initial-center-lat="10.6694"
                placeholder="Haz clic en el mapa para seleccionar la ubicación del productor"
                wire:key="location-picker-{{ $producer->id }}"
            />
        </div>
        <!-- ===== FIN MAPA ===== -->

        <div class="mt-4 flex items-center">
            <input type="checkbox" id="is_active" wire:model="is_active"
                class="h-5 w-5 text-green-600 focus:ring-green-500 border-gray-300 rounded
                       dark:bg-gray-700 dark:border-gray-600 dark:checked:bg-green-500
                       dark:focus:ring-green-400 dark:focus:ring-offset-gray-800">
            <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">Productor activo</label>
        </div>

        <div class="flex items-center justify-end mt-6 space-x-4">
            <x-go-back-button route="{{ route('producers.index') }}" />
            <x-primary-button wire:loading.attr="disabled" wire:target="update">
                <span wire:loading.remove wire:target="update">{{ __('Actualizar productor') }}</span>
                <span wire:loading wire:target="update">{{ __('Actualizando...') }}</span>
            </x-primary-button>
        </div>
    </form>
</div>
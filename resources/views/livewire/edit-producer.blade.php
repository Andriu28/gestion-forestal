<div>
    <form wire:submit="update" id="producer-form">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Nombre del productor *')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text"
                wire:model.live.debounce.250ms="name"
                autofocus
                oninput="this.value = this.value.replace(/[^A-Za-záéíóúÁÉÍÓÚüÜñÑ]/g, ''); if(this.value.length === 1) this.value = this.value.toUpperCase();" />
            <x-input-error :messages="$errors->first('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="lastname" :value="__('Apellido *')" />
            <x-text-input id="lastname" class="block mt-1 w-full" type="text"
                wire:model.live.debounce.250ms="lastname"
                oninput="this.value = this.value.replace(/[^A-Za-záéíóúÁÉÍÓÚüÜñÑ]/g, ''); if(this.value.length === 1) this.value = this.value.toUpperCase();" />
            <x-input-error :messages="$errors->first('lastname')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="description" :value="__('Descripción *')" />
            <textarea id="description" wire:model.live="description" rows="3"
                class="w-full px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm border border-stone-400/80 dark:border-gray-600 !bg-stone-50 dark:!bg-gray-800/50 text-custom-gray dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70"
                placeholder="Descripción del productor..."
                oninput="if(this.value.length === 1) this.value = this.value.toUpperCase();"></textarea>
            <x-input-error :messages="$errors->first('description')" class="mt-2" />
        </div>

        <!-- ===== COMPONENTE DEL MAPA ===== -->
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
                class="border border-stone-400/80 dark:border-gray-600 !bg-stone-50 dark:!bg-gray-800/50 text-custom-gray dark:text-gray-100 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70">
            <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">Productor activo</label>
        </div>

        <div class="flex items-center justify-end mt-6 space-x-4">
            <x-go-back-button route="{{ route('producers.index') }}" />
            <x-primary-button>
                {{ __('Actualizar productor') }}
            </x-primary-button>
        </div>
    </form>
</div>
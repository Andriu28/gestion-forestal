<div>
    <form wire:submit="update" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Nombre del productor *')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" 
                          wire:model.live.debounce.250ms="name" 
                          x-ref="nameInput"
                          @input="validateProducerInput($refs.nameInput)"
                          @blur="$refs.nameInput.value = formatProducerName($refs.nameInput.value)"
                          autofocus />
            <x-input-error :messages="$errors->first('name')" class="mt-2" />
            <div class="text-xs mt-1">
                <span class="text-green-600 hidden" id="name-valid">✓ Formato válido</span>
            </div>
        </div>

        <div class="mt-4">
            <x-input-label for="lastname" :value="__('Apellido')" />
            <x-text-input id="lastname" class="block mt-1 w-full" type="text" 
                          wire:model.live.debounce.250ms="lastname"
                          x-ref="lastnameInput"
                          @input="validateProducerInput($refs.lastnameInput)"
                          @blur="$refs.lastnameInput.value = formatProducerName($refs.lastnameInput.value)" />
            <x-input-error :messages="$errors->first('lastname')" class="mt-2" />
            <div class="text-xs mt-1">
                <span class="text-green-600 hidden" id="lastname-valid">✓ Formato válido</span>
            </div>
        </div>

        <!-- El resto del formulario permanece igual -->

        <div class="flex items-center justify-end mt-6 space-x-4">
            <x-go-back-button />
            <x-primary-button type="submit" :disabled="isSubmitting">
                <template x-if="!isSubmitting">
                    <span>{{ __('Actualizar productor') }}</span>
                </template>
                <template x-if="isSubmitting">
                    <span class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Procesando...
                    </span>
                </template>
            </x-primary-button>
        </div>
    </form>
</div>

@push('scripts')
<script src="{{ asset('js/producerValidation.js') }}"></script>
<script>
    window.validateProducerInput = function(input) {
        return validateProducerInput(input);
    };
    
    window.formatProducerName = function(name) {
        return formatProducerName(name);
    };
</script>
@endpush
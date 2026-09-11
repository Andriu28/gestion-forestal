<div>
    <form wire:submit="store" id="producer-form">
        @csrf
        <div class="mt-4 grid grid-cols-1 md:grid-cols-12 gap-4 mb-4 items-start">

            {{-- Nombre --}}
            <div class="md:col-span-4">
                <x-input-label for="name" :value="__('Nombre del productor *')" />
                <x-text-input id="name" class="block mt-1 w-full" type="text"
                    wire:model.live.debounce.250ms="name"
                    autofocus
                    oninput="this.value = this.value.replace(/[^A-Za-záéíóúÁÉÍÓÚüÜñÑ]/g, ''); if(this.value.length === 1) this.value = this.value.toUpperCase();" />
                <x-input-error :messages="$errors->first('name')" class="mt-2" />
            </div>

            {{-- Apellido --}}
            <div class="md:col-span-4">
                <x-input-label for="lastname" :value="__('Apellido *')" />
                <x-text-input id="lastname" class="block mt-1 w-full" type="text"
                    wire:model.live.debounce.250ms="lastname"
                    oninput="this.value = this.value.replace(/[^A-Za-záéíóúÁÉÍÓÚüÜñÑ]/g, ''); if(this.value.length === 1) this.value = this.value.toUpperCase();" />
                <x-input-error :messages="$errors->first('lastname')" class="mt-2" />
            </div> 

            {{-- Selector de nacionalidad usando el componente personalizado --}}
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

            {{-- Input numérico de cédula --}}
            <div class="md:col-span-2">
                <x-input-label for="cedula" :value="__('Cédula de identidad *')" />
                <x-text-input id="cedula" class="block mt-1 w-full" type="text"
                    inputmode="numeric"
                    wire:model.live.debounce.250ms="cedula"
                    maxlength="10"
                    placeholder="Ej: 12345678"
                    oninput="this.value = this.value.replace(/\D/g, '').slice(0, 10);" />
                <x-input-error :messages="$errors->first('cedula')" class="mt-2" />
            </div>

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
            <x-input-label :value="__('Ubicación')" />
            <livewire:components.location-picker
                :latitude="$latitude"
                :longitude="$longitude"
                :address="$address"
                map-id="producer-map"
                show-coordinates="true"
                show-address="true"
                show-locate-button="true"
                initial-zoom="13"
                initial-center-lon="-63.2535"
                initial-center-lat="10.6694"
                placeholder="Haz clic en el mapa para seleccionar la ubicación del productor"
                wire:key="location-picker"
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
            <x-primary-button>
                {{ __('Guardar productor') }}
            </x-primary-button>
        </div>
    </form>
</div>

<!-- ============================================ -->
<!-- SOLO EL CÓDIGO DE VALIDACIÓN (FormValidator) -->
<!-- ============================================ -->
<script>
class FormValidator {
    static fields = [
        {
            id: 'name',
            rules: [
                {
                    type: 'pattern',
                    pattern: /^[A-ZÁÉÍÓÚÜÑ][A-Za-záéíóúÁÉÍÓÚüÜñÑ]*$/,
                    message: 'Solo letras, sin espacios, números ni caracteres especiales. Debe empezar con mayúscula.'
                }
            ]
        },
        {
            id: 'lastname',
            rules: [
                {
                    type: 'pattern',
                    pattern: /^[A-ZÁÉÍÓÚÜÑ][A-Za-záéíóúÁÉÍÓÚüÜñÑ]*$/,
                    message: 'Solo letras, sin espacios, números ni caracteres especiales. Debe empezar con mayúscula.'
                }
            ]
        },
        {
            id: 'cedula',
            rules: [
                { type: 'custom', validator: FormValidator.validateCedula }
            ]
        }
    ];

    /**
     * Validación dinámica según el tipo de nacionalidad.
     * - V, E, G → 5 a 8 dígitos
     * - P      → 6 a 10 dígitos
     * - J      → 8 a 10 dígitos
     */
    static validateCedula(value) {
        const typeEl = document.getElementById('cedula_type');
        const type = (typeEl?.value || 'V').toUpperCase();
        const digits = (value || '').replace(/\D/g, '');

        if (!/^\d+$/.test(digits)) return false;

        const rules = {
            V: { min: 5, max: 8  },
            E: { min: 5, max: 8  },
            G: { min: 5, max: 8  },
            P: { min: 6, max: 10 },
            J: { min: 8, max: 10 },
        };
        const { min, max } = rules[type] ?? rules.V;

        return digits.length >= min && digits.length <= max;
    }

    static getCedulaMessage() {
        const typeEl = document.getElementById('cedula_type');
        const type = (typeEl?.value || 'V').toUpperCase();
        const map = {
            V: 'Cédula venezolana: 5 a 8 dígitos.',
            E: 'Cédula de extranjero: 5 a 8 dígitos.',
            P: 'Pasaporte: 6 a 10 dígitos.',
            J: 'RIF jurídico: 8 a 10 dígitos.',
            G: 'RIF gubernamental: 5 a 8 dígitos.',
        };
        return map[type] ?? map.V;
    }

    static initializeFields() {
        this.fields.forEach(field => {
            const input = document.getElementById(field.id);
            if (!input) return;

            input.addEventListener('blur', () => this.validateField(field.id));
            input.addEventListener('input', () => this.clearError(field.id));

            input.addEventListener('input', (e) => {
                const value = e.target.value;
                if (value.length === 1) {
                    e.target.value = value.toUpperCase();
                }
            });

            // Limpieza específica para cédula
            if (field.id === 'cedula') {
                input.addEventListener('input', (e) => {
                    const clean = e.target.value.replace(/\D/g, '').slice(0, 10);
                    if (e.target.value !== clean) e.target.value = clean;
                });
                input.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const paste = (e.clipboardData || window.clipboardData).getData('text');
                    const clean = paste.replace(/\D/g, '').slice(0, 10);
                    document.execCommand('insertText', false, clean);
                });
            }
        });

        // Revalidar cédula cuando cambie el tipo
        const typeEl = document.getElementById('cedula_type');
        if (typeEl) {
            typeEl.addEventListener('change', () => {
                this.clearError('cedula');
                const cedula = document.getElementById('cedula');
                if (cedula && cedula.value) this.validateField('cedula');
            });
        }
    }

    static validateField(fieldId) {
        const fieldConfig = this.fields.find(f => f.id === fieldId);
        if (!fieldConfig) return true;

        const input = document.getElementById(fieldId);
        const value = input.value.trim();
        let isValid = true;

        this.clearError(fieldId);

        for (const rule of fieldConfig.rules) {
            switch (rule.type) {
                case 'required':
                    if (!value) {
                        this.showError(fieldId, rule.message);
                        isValid = false;
                    }
                    break;

                case 'pattern':
                    if (value && !rule.pattern.test(value)) {
                        this.showError(fieldId, rule.message);
                        isValid = false;
                    }
                    break;

                case 'custom':
                    if (!rule.validator(value)) {
                        const msg = fieldId === 'cedula'
                            ? this.getCedulaMessage()
                            : 'Valor inválido.';
                        this.showError(fieldId, msg);
                        isValid = false;
                    }
                    break;
            }
            if (!isValid) break;
        }

        return isValid;
    }

    static showError(fieldId, message) {
        const input = document.getElementById(fieldId);
        const errorDiv = document.getElementById(`${fieldId}-error`);

        input.classList.add('shake-animation');
        setTimeout(() => input.classList.remove('shake-animation'), 500);

        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.classList.remove('hidden');
        } else {
            const div = document.createElement('div');
            div.id = `${fieldId}-error`;
            div.className = 'mt-2 text-sm text-red-600 dark:text-red-400';
            div.textContent = message;
            input.parentNode.appendChild(div);
        }

        input.focus();
    }

    static clearError(fieldId) {
        const errorDiv = document.getElementById(`${fieldId}-error`);
        if (errorDiv) errorDiv.classList.add('hidden');
    }

    static validateForm() {
        let isValid = true;
        this.fields.forEach(field => {
            if (!this.validateField(field.id)) isValid = false;
        });
        return isValid;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (!document.getElementById('form-validator-styles')) {
        const style = document.createElement('style');
        style.id = 'form-validator-styles';
        style.textContent = `
            .shake-animation { animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both; }
            @keyframes shake {
                10%, 90% { transform: translateX(-2px); }
                20%, 80% { transform: translateX(3px); }
                30%, 50%, 70% { transform: translateX(-3px); }
                40%, 60% { transform: translateX(3px); }
            }
        `;
        document.head.appendChild(style);
    }

    FormValidator.initializeFields();

    if (typeof Livewire !== 'undefined') {
        Livewire.hook('request', ({ fail }) => {
            if (!FormValidator.validateForm()) {
                fail();
            }
        });

        // Re-inicializar campos tras cada actualización de Livewire
        Livewire.hook('morphed', () => FormValidator.initializeFields());
    }
});
</script>
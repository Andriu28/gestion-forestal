
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

        <div class="mt-4 flex items-center">
            <input type="checkbox" id="is_active" wire:model="is_active" class="w-4 h-4 border border-stone-400/80 dark:border-gray-600 bg-stone-50 dark:bg-gray-800/50 text-custom-gold-dark dark:text-custom-gold-medium rounded shadow-sm focus:ring-0 focus:ring-offset-0 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70">
            <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">Productor activo</label>
        </div>

        <div class="flex items-center justify-end mt-6 space-x-4">
            <x-go-back-button />
            <x-primary-button>
                {{ __('Actualizar productor') }}
            </x-primary-button>
        </div>
    </form>
</div>

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
        }
    ];

    static initializeFields() {
        this.fields.forEach(field => {
            const input = document.getElementById(field.id);
            if (input) {
                input.addEventListener('blur', () => this.validateField(field.id));
                input.addEventListener('input', () => this.clearError(field.id));
                
                // Convertir primera letra a mayúscula
                input.addEventListener('input', (e) => {
                    const value = e.target.value;
                    if (value.length === 1) {
                        e.target.value = value.toUpperCase();
                    }
                });
            }
        });
    }

    static validateField(fieldId) {
        const fieldConfig = this.fields.find(f => f.id === fieldId);
        if (!fieldConfig) return true;

        const input = document.getElementById(fieldId);
        const value = input.value.trim();
        let isValid = true;

        // Limpiar error previo
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
            }
            if (!isValid) break;
        }

        return isValid;
    }

    static showError(fieldId, message) {
        const input = document.getElementById(fieldId);
        const errorDiv = document.getElementById(`${fieldId}-error`);
        
        // Agregar clase de animación shake
        input.classList.add('shake-animation');
        setTimeout(() => input.classList.remove('shake-animation'), 500);
        
        // Mostrar mensaje de error
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.classList.remove('hidden');
        } else {
            // Crear elemento de error si no existe
            const div = document.createElement('div');
            div.id = `${fieldId}-error`;
            div.className = 'mt-2 text-sm text-red-600 dark:text-red-400';
            div.textContent = message;
            input.parentNode.appendChild(div);
        }
        
        // Enfocar el campo
        input.focus();
    }

    static clearError(fieldId) {
        const input = document.getElementById(fieldId);
        const errorDiv = document.getElementById(`${fieldId}-error`);
        
        if (errorDiv) {
            errorDiv.classList.add('hidden');
        }
    }

    static validateForm() {
        let isValid = true;
        this.fields.forEach(field => {
            if (!this.validateField(field.id)) {
                isValid = false;
            }
        });
        return isValid;
    }
}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Agregar estilos para animación shake
    if (!document.getElementById('form-validator-styles')) {
        const style = document.createElement('style');
        style.id = 'form-validator-styles';
        style.textContent = `
            .shake-animation {
                animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
            }
            @keyframes shake {
                10%, 90% { transform: translateX(-2px); }
                20%, 80% { transform: translateX(3px); }
                30%, 50%, 70% { transform: translateX(-3px); }
                40%, 60% { transform: translateX(3px); }
            }
        `;
        document.head.appendChild(style);
    }
    
    // Inicializar campos
    FormValidator.initializeFields();
    
    // Configurar Livewire si existe
    if (typeof Livewire !== 'undefined') {
        Livewire.hook('request', ({ fail }) => {
            if (!FormValidator.validateForm()) {
                fail();
            }
        });
    }
});
</script>

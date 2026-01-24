<div>
    <form wire:submit="store">
        @csrf

        <div class="mt-4">
            <x-input-label for="name" :value="__('Nombre')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" 
                wire:model.live.debounce.250ms="name" 
                autofocus
                oninput="if(this.value.length === 1) this.value = this.value.toUpperCase();" />
            <x-input-error :messages="$errors->first('name')" class="mt-2" />
        </div>
    
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" wire:model.live.debounce.250ms="email" />
            <x-input-error :messages="$errors->first('email')" class="mt-2" />
        </div>

        <!-- Campo de Contraseña con botón de visibilidad -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Contraseña')" />
            <div class="relative mt-1">
                <x-text-input 
                    id="password" 
                    class="block w-full pr-10" 
                    type="password" 
                    wire:model.live.debounce.250ms="password" 
                    autocomplete="new-password" 
                />
                <button
                    type="button"
                    title="Mostrar/Ocultar Contraseña"
                    id="togglePassword"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors p-0.5 focus:outline-none"
                >
                    <!-- Icono de ojo cerrado (por defecto) -->
                    <svg id="eyeOff_password" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                    </svg>
                    <!-- Icono de ojo abierto (oculto por defecto) -->
                    <svg id="eyeOn_password" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Campo de Confirmar Contraseña con botón de visibilidad -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" />
            <div class="relative mt-1">
                <x-text-input 
                    id="password_confirmation" 
                    class="block w-full pr-10" 
                    type="password" 
                    wire:model.live.debounce.250ms="password_confirmation" 
                    autocomplete="new-password" 
                />
                <button
                    type="button"
                    title="Mostrar/Ocultar Contraseña"
                    id="togglePasswordConfirmation"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors p-0.5 focus:outline-none"
                >
                    <!-- Icono de ojo cerrado (por defecto) -->
                    <svg id="eyeOff_password_confirmation" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                    </svg>
                    <!-- Icono de ojo abierto (oculto por defecto) -->
                    <svg id="eyeOn_password_confirmation" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4 space-x-4">
            <x-go-back-button />
            <x-primary-button class="ms-4">
                {{ __('Crear Usuario') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar visibilidad para contraseña
            setupPasswordToggle('togglePassword', 'password', 'eyeOff_password', 'eyeOn_password');
            
            // Configurar visibilidad para confirmación de contraseña
            setupPasswordToggle('togglePasswordConfirmation', 'password_confirmation', 'eyeOff_password_confirmation', 'eyeOn_password_confirmation');
        });
        
        function setupPasswordToggle(buttonId, inputId, eyeOffId, eyeOnId) {
            const toggleButton = document.getElementById(buttonId);
            if (toggleButton) {
                toggleButton.addEventListener('click', function() {
                    const passwordInput = document.getElementById(inputId);
                    const eyeOff = document.getElementById(eyeOffId);
                    const eyeOn = document.getElementById(eyeOnId);
                    
                    if (passwordInput && passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        if (eyeOff) eyeOff.classList.add('hidden');
                        if (eyeOn) eyeOn.classList.remove('hidden');
                    } else if (passwordInput) {
                        passwordInput.type = 'password';
                        if (eyeOff) eyeOff.classList.remove('hidden');
                        if (eyeOn) eyeOn.classList.add('hidden');
                    }
                });
            }
        }
    </script>
</div>

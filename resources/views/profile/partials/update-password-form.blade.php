<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-200">
            {{ __('Actualizar contraseña') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Asegúrate de que tu cuenta use una contraseña larga y aleatoria para mantener la seguridad.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <!-- Campo de Contraseña actual con botón de visibilidad -->
        <div>
            <x-input-label for="update_password_current_password" :value="__('Contraseña actual')" />
            <div class="relative mt-1">
                <x-text-input 
                    id="update_password_current_password" 
                    name="current_password" 
                    type="password" 
                    class="block mt-1 w-full rounded-md border-gray-300 dark:bg-custom-gray dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" 
                    autocomplete="current-password" 
                />
                <button
                    type="button"
                    title="Mostrar/Ocultar Contraseña"
                    id="toggleCurrentPassword"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors p-0.5 focus:outline-none"
                >
                    <!-- Icono de ojo cerrado (por defecto) -->
                    <svg id="eyeOff_current_password" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                    </svg>
                    <!-- Icono de ojo abierto (oculto por defecto) -->
                    <svg id="eyeOn_current_password" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <!-- Campo de Nueva Contraseña con botón de visibilidad -->
        <div>
            <x-input-label for="update_password_password" :value="__('Nueva Contraseña')" />
            <div class="relative mt-1">
                <x-text-input 
                    id="update_password_password" 
                    name="password" 
                    type="password" 
                    class="block mt-1 w-full rounded-md border-gray-300 dark:bg-custom-gray dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" 
                    autocomplete="new-password" 
                />
                <button
                    type="button"
                    title="Mostrar/Ocultar Contraseña"
                    id="toggleNewPassword"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors p-0.5 focus:outline-none"
                >
                    <!-- Icono de ojo cerrado (por defecto) -->
                    <svg id="eyeOff_new_password" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                    </svg>
                    <!-- Icono de ojo abierto (oculto por defecto) -->
                    <svg id="eyeOn_new_password" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <!-- Campo de Confirmar Contraseña con botón de visibilidad -->
        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirmar Contraseña')" />
            <div class="relative mt-1">
                <x-text-input 
                    id="update_password_password_confirmation" 
                    name="password_confirmation" 
                    type="password" 
                    class="block mt-1 w-full rounded-md border-gray-300 dark:bg-custom-gray dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" 
                    autocomplete="new-password" 
                />
                <button
                    type="button"
                    title="Mostrar/Ocultar Contraseña"
                    id="toggleConfirmPassword"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors p-0.5 focus:outline-none"
                >
                    <!-- Icono de ojo cerrado (por defecto) -->
                    <svg id="eyeOff_confirm_password" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                    </svg>
                    <!-- Icono de ojo abierto (oculto por defecto) -->
                    <svg id="eyeOn_confirm_password" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Guardar') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Guardar.') }}</p>
            @endif
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar visibilidad para contraseña actual
            setupPasswordToggle('toggleCurrentPassword', 'update_password_current_password', 'eyeOff_current_password', 'eyeOn_current_password');
            
            // Configurar visibilidad para nueva contraseña
            setupPasswordToggle('toggleNewPassword', 'update_password_password', 'eyeOff_new_password', 'eyeOn_new_password');
            
            // Configurar visibilidad para confirmar contraseña
            setupPasswordToggle('toggleConfirmPassword', 'update_password_password_confirmation', 'eyeOff_confirm_password', 'eyeOn_confirm_password');
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
</section>

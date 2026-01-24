<x-guest-layout>
    <div class="w-full max-w-xs sm:max-w-sm md:max-w-md ">
  
        <!-- Toggle de modo oscuro -->
        <div class="flex justify-end mb-2 sm:mb-3 ">
            <button
                id="themeToggle"
                class="p-1.5 sm:p-2 rounded-md bg-white/80 dark:bg-gray-800/80 backdrop-blur border border-amber-200 dark:border-gray-600 hover:bg-white dark:hover:bg-gray-700 transition-all duration-200 shadow-sm transition transform hover:scale-[1.10] duration-300 hover:-translate-y-1 duration-300"
                title="Cambiar tema"
            >
                <!-- Icono de sol (modo claro) -->
                <svg id="sunIcon" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-custom-gold-darker dark:text-custom-gold-medium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <!-- Icono de luna (modo oscuro) -->
                <svg id="moonIcon" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-custom-gold-darker dark:text-custom-gold-medium hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                </svg>
            </button>
        </div>
        <!-- Logo y título de la empresa -->
        <div class="text-center mb-4 sm:mb-5 md:mb-6">
            <div class="logo-container inline-flex items-center justify-center w-24 h-24 sm:w-32 sm:h-32 md:w-32 md:h-32 bg-yellow-950/95 dark:bg-amber-700 rounded-full mb-2 sm:mb-3 shadow-lg">
            <!-- Icono de cacao usando SVG -->
                    <svg version="1.0" xmlns="http://www.w3.org/2000/svg" width="80.000000pt" height="80.000000pt" viewBox="0 0 172.000000 167.000000" preserveAspectRatio="xMidYMid meet">
                        <g transform="translate(0.000000,167.000000) scale(0.100000,-0.100000)" fill="#ffffffff" stroke="none">
                            <path d="M1052 1609 l-40 -20 15 -37 c24 -57 111 -228 160 -315 24 -44 43 -80 41 -81 -2 -1 -26 -11 -54 -22 l-52 -21 -13 31 c-13 28 -26 34 -148 74 -247 80 -375 81 -537 2 -170 -82 -282 -223 -360 -450 -39 -113 -39 -113 72 -159 134 -56 231 -74 376 -68 102 4 125 2 121 -9 -10 -27 19 -203 48 -284 17 -47 38
                            -106 46 -131 l16 -46 60 -7 c33 -3 111 -6 173 -6 l113 0 16 30 c8 16 15 31 15 35 0 3 -37 5 -82 5 l-83 0 60 33 c177 96 281 211 337 376 39 114 28 285 -28 447 -4 10 43 -55 105 -144 138 -203 182 -261 200 -262 8 0 20 -8 27 -18 11 -15 14 -15 28 -2 13 13 2 35 -98 190 -164 254 -277 448 -392 677 l-102 201
                            -40 -19z m-283 -369 c94 -18 309 -91 315 -107 7 -18 -20 -53 -40 -53 -8 0 -16 11 -20 25 -4 14 -15 27 -28 30 -11 3 -68 16 -126 30 -290 69 -504 -3 -647 -218 -44 -66 -112 -215 -113 -244 0 -17 56 -40 170 -70 82 -21 109 -24 200 -19 58 3 118 8 133 12 26 6 28 4 25 -17 -4 -30 -31 -36 -163 -38 -94 -1 -189
                            14 -260 41 -71 26 -145 61 -145 68 0 22 46 153 72 205 40 80 107 173 157 218 131 118 304 169 470 137z m3 -85 c138 -24 146 -34 23 -28 -203 10 -351 -47 -506 -195 -98 -92 -103 -82 -16 33 87 116 193 176 352 198 55 7 62 7 147 -8z m475 -47 c25 -32 94 -287 100 -363 16 -235 -115 -439 -362 -563 -55 -27 -183
                            -72 -207 -72 -5 0 -23 30 -39 67 -101 220 -98 454 6 628 70 117 148 176 337 259 133 58 150 63 165 44z m-372 -17 l30 -8 -41 -11 c-23 -7 -93 -23 -155 -37 -192 -42 -270 -74 -407 -167 -90 -60 -82 -38 17 54 126 117 276 177 436 176 50 0 104 -4 120 -7z m-37 -134 c-48 -45 -94 -94 -103 -109 -41 -66 -208 -128
                            -365 -134 l-105 -5 118 35 c139 42 217 79 312 149 123 90 208 147 219 147 6 0 -28 -37 -76 -83z m-43 47 c-77 -63 -206 -150 -264 -176 -59 -27 -237 -78 -321 -92 l-35 -5 35 30 c63 55 226 159 291 185 52 22 276 80 314 83 6 0 -3 -11 -20 -25z m-115 -254 c0 -6 -6 -26 -14 -45 -12 -28 -23 -36 -67 -49 -68 -21 -200
                            -20 -292 0 -40 9 -75 19 -79 23 -4 3 48 6 115 7 125 1 225 21 293 57 35 19 44 21 44 7z"/>
                            <path d="M1060 1007 c-174 -94 -275 -199 -330 -342 -29 -76 -32 -235 -7 -335 32 -120 58 -180 79 -180 34 0 167 58 236 104 156 102 228 215 252 399 12 90 9 118 -38 321 -17 77 -30 95 -67 94 -5 0 -62 -28 -125 -61z m180 -117 c46 -195 17 -366 -82 -492 -36 -46 -135 -121 -210 -159 l-57 -29 108 108 c114 115 153
                            174 189 294 28 90 34 164 25 285 -5 56 -6 99 -2 95 4 -4 17 -50 29 -102z m-155 81 c-6 -5 -34 -27 -64 -50 -149 -111 -238 -287 -248 -489 -3 -62 -9 -110 -14 -105 -17 18 -30 169 -20 238 21 145 128 294 271 378 61 35 96 48 75 28z m31 -39 c-8 -16 -53 -80 -101 -143 -100 -133 -144 -218 -180 -349 -39
                            -142 -46 -149 -39 -40 10 176 62 310 161 420 43 47 152 136 171 139 1 1 -4 -12 -12 -27z m73 -143 c1 -76 -31 -195 -75 -279 -37 -70 -130 -182 -186 -221 -26 -19 -22 -9 31 69 87 129 115 196 156 367 20 83 41 166 47 185 l12 35 7 -50 c4 -27 8 -75 8 -106z m-105 -71 c-40 -163 -86 -262 -192 -404 -50 -67 -67
                            -84 -70 -69 -5 38 57 257 97 338 36 74 177 276 192 277 4 0 -9 -64 -27 -142z"/>
                            <path d="M1713 440 c0 -25 2 -35 4 -22 2 12 2 32 0 45 -2 12 -4 2 -4 -23z"/>
                        </g>
                    </svg>
            </div>
            <h1 class="text-2xl sm:text-4xl md:text-4xl lg:text-5xl font-bold text-custom-dark dark:text-amber-100 mb-1 transition-colors">Cacao San José</h1>
            <p class="text-ms sm:text-base md:text-lg text-amber-600 dark:text-custom-gold-light transition-colors">Gestion Forestal</p>
        </div>

        <div class="card-glow shadow-2xl hover:shadow-3xl dark:shadow-gray-900/50 bg-stone-200/90 dark:bg-custom-gray rounded-2xl transition-all duration-300">
            <!-- Header del card -->
            <div class="px-3 sm:px-4 md:px-5 py-1 sm:py-2 pb-3 sm:pb-4">
                <h2 class="heading2 text-xl sm:text-2xl md:text-3xl lg:text-4xl text-center text-custom-dark dark:text-amber-100 font-black transition-colors">Iniciar Sesión</h2>
            </div>
        

            <!-- Contenido del card -->
            <div class=" px-3 sm:px-4 md:px-5 sm:pb-4 ">
                <form method="POST" id="loginForm" class="space-y-2.5 sm:space-y-3 md:space-y-5" action="{{ route('login') }}">
                    @csrf
                    <!-- Campo de email -->
                    <div class="space-y-1 sm:space-y-1.5">
                        <label for="email" class="block text-custom-dark dark:text-amber-100 font-medium text-sm sm:text-base transition-colors">
                            Correo Electrónico
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="usuario@empresa.com"
                            class="w-full px-2.5 sm:px-4 py-1.5 sm:py-2 text-sm sm:text-base border-[3px] border-stone-300/80 dark:border-gray-600 !bg-stone-50 dark:!bg-gray-800/50 text-custom-gray dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70 transition-all duration-200 transition transform hover:scale-[1.02] duration-300 hover:-translate-y-1 duration-300"
                            required
                        />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    
                    </div>

                    <!-- Campo de contraseña -->
                    <div class="space-y-1 sm:space-y-1.5">
                        <label for="password" class="block text-custom-dark dark:text-amber-100 font-medium text-sm sm:text-base transition-colors">
                            Contraseña
                        </label>
                        <div class="relative hover:-translate-y-1 duration-300">
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Contraseña"
                                class="w-full px-2.5 sm:px-4 py-1.5 sm:py-2 pr-8  text-sm sm:text-base border-[3px] border-stone-300/80  dark:border-gray-600 bg-white dark:bg-gray-800/50 text-custom-gray dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70 transition-all duration-200 transition transform hover:scale-[1.02] duration-300"
                                required
                            />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            <button
                                type="button"
                                title="Mostrar/Ocultar Contraseña"
                                id="togglePassword"
                                class="absolute right-2.5 sm:right-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors p-0.5"
                            >
                                <!-- Icono de ojo cerrado (por defecto) -->
                                <svg id="eyeOff" class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                                </svg>
                                <!-- Icono de ojo abierto (oculto por defecto) -->
                                <svg id="eyeOn"  class="w-5 h-5 sm:w-6 sm:h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Recordarme y olvidé contraseña -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-1.5 sm:space-y-0 pt-0.5">
                        <!-- <div class="flex items-center space-x-1.5">
                            <input
                                name="remember"
                                type="checkbox"
                                class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-custom-gold-darker bg-gray-100 dark:bg-gray-600 border-custom-gold-light dark:border-gray-500 rounded focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium focus:ring-2 transition-colors"
                            />
                            <label for="remember" class="text-xs text-amber-700 dark:text-custom-gold-light transition-colors">
                                Recordarme
                            </label>
                        </div> -->
                        <button type="button" class="text-xs text-custom-gold-darker dark:text-custom-gold-medium hover:text-amber-800 dark:hover:text-amber-200 hover:underline transition-colors text-left sm:text-right">
                                
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs text-custom-gold-darker dark:text-custom-gold-medium hover:text-amber-800 dark:hover:text-amber-200 hover:underline transition-colors text-left sm:text-right">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            @endif

                        </button>
                    </div>

                    <!-- Botón de submit .-->
                    <button type="submit" title="Iniciar sesión" class="relative w-full [background:linear-gradient(144deg,#3E2723,#4E342E_50%,#6D4C41)] dark:[background:linear-gradient(144deg,#3b220f,#61361d_50%,#794606)] text-white px-4 py-2 font-bold rounded-md hover:opacity-95 dark:hover:opacity-90 transition-all duration-200 shadow-sm hover:shadow-md focus:scale-[1.03] hover:scale-[1.03] active:scale-95 before:content-[''] before:absolute before:inset-0 before:rounded-md before:ring-[3px] before:ring-offset-2 before:ring-offset-[#f1ae6dd1] before:ring-[#3E2723] dark:before:ring-offset-gray-950 dark:before:ring-amber-600/70">
                        Iniciar Sesión
                    </button>
                </form>

                <!-- Separador y enlace de contacto -->
                <div class="mt-3 pt-3 border-t border-amber-200 dark:border-gray-600 transition-colors">
                   <!--  <p class="text-center text-xs text-custom-gold-darker dark:text-custom-gold-medium transition-colors">
                        ¿Necesitas acceso?
                        <button class="text-amber-800 dark:text-custom-gold-light hover:text-custom-dark dark:hover:text-amber-100 font-medium hover:underline ml-1 transition-colors">
                            Contacta al administrador
                        </button>
                    </p> -->
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 sm:mt-4 md:mt-5 text-xs text-custom-gold-darker dark:text-custom-gold-medium transition-colors px-2">
            <p>© 2025 Todos los derechos reservados.</p>
        </div>

    </div>
 

    <script>
       // Gestión del tema (modo oscuro/claro)
        class ThemeManager {
            constructor() {
                this.theme = localStorage.getItem('theme') || 'light';
                this.init();
            }

            init() {
                this.applyTheme();
                this.setupToggle();
            }

            applyTheme() {
                const html = document.documentElement;
                const sunIcon = document.getElementById('sunIcon');
                const moonIcon = document.getElementById('moonIcon');

                if (this.theme === 'dark') {
                    html.classList.add('dark');
                    if (sunIcon) sunIcon.classList.add('hidden');
                    if (moonIcon) moonIcon.classList.remove('hidden');
                } else {
                    html.classList.remove('dark');
                    if (sunIcon) sunIcon.classList.remove('hidden');
                    if (moonIcon) moonIcon.classList.add('hidden');
                }
            }

            toggleTheme() {
                this.theme = this.theme === 'light' ? 'dark' : 'light';
                localStorage.setItem('theme', this.theme);
                this.applyTheme();
                
                // Añadir feedback visual al cambiar tema
                const button = document.getElementById('themeToggle');
                if (button) {
                    button.classList.add('scale-95');
                    setTimeout(() => {
                        button.classList.remove('scale-95');
                    }, 150);
                }
            }

            setupToggle() {
                const toggleButton = document.getElementById('themeToggle');
                if (toggleButton) {
                    toggleButton.addEventListener('click', () => {
                        this.toggleTheme();
                    });
                }
            }
        }

        // Funcionalidad para mostrar/ocultar contraseña
        function setupPasswordToggle() {
            const toggleButton = document.getElementById('togglePassword');
            if (toggleButton) {
                toggleButton.addEventListener('click', function() {
                    const passwordInput = document.getElementById('password');
                    const eyeOff = document.getElementById('eyeOff');
                    const eyeOn = document.getElementById('eyeOn');
                    
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

        // Manejo del formulario de login
        function setupLoginForm() {
            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    // No prevenir el comportamiento por defecto para que Laravel maneje la autenticación
                    // Solo añadir feedback visual
                    
                    const submitButton = e.target.querySelector('button[type="submit"]');
                    if (submitButton) {
                        const originalText = submitButton.textContent;
                        submitButton.textContent = 'Iniciando...';
                        submitButton.disabled = true;
                        
                        // Restaurar el botón después de 3 segundos por si hay error
                        setTimeout(() => {
                            submitButton.textContent = originalText;
                            submitButton.disabled = false;
                        }, 3000);
                    }
                });
            }
        }

        // Efecto de focus mejorado para los inputs
        function setupInputEffects() {
            const inputs = document.querySelectorAll('input[type="email"], input[type="password"]');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.classList.add('ring-2');
                    this.parentElement.classList.add('transform', 'scale-[1.01]');
                });
                
                input.addEventListener('blur', function() {
                    this.classList.remove('ring-2');
                    this.parentElement.classList.remove('transform', 'scale-[1.01]');
                });
            });
        }

        // Inicializar todo cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            new ThemeManager();
            setupPasswordToggle();
            setupLoginForm();
            setupInputEffects();

            // Detectar preferencia del sistema si no hay tema guardado
            if (!localStorage.getItem('theme')) {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (prefersDark) {
                    localStorage.setItem('theme', 'dark');
                }
            }

            // Mejorar la experiencia táctil en dispositivos móviles
            if ('ontouchstart' in window) {
                document.body.classList.add('touch-device');
                
                // Añadir estilos específicos para dispositivos táctiles
                const style = document.createElement('style');
                style.textContent = `
                    .touch-device button:active {
                        transform: scale(0.98);
                    }
                    .touch-device input:focus {
                        transform: scale(1.01);
                    }
                `;
                document.head.appendChild(style);
            }
        });
    </script>
</x-guest-layout>



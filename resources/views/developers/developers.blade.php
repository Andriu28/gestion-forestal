{{-- resources/views/developers/developers.blade.php --}}
<x-app-layout>
    <div class="mx-auto">
        <div class="p-4 mb-6 overflow-hidden shadow-sm bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow-soft md:p-6 lg:p-6">
            <div class="text-gray-900 dark:text-gray-100">
                {{-- Cabecera de la página --}}
                <div class="mb-8">
                    <h2 class="mb-2 text-2xl font-black text-gray-900 md:text-3xl dark:text-gray-200">
                        {{ __('Equipo de Desarrollo') }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ __('Conoce a los creadores detrás del Sistema de Gestión Geográfica de Cacao San José.') }}
                    </p>
                </div>

                {{-- Grid para los dos desarrolladores --}}
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:gap-8">
                    
                    {{-- Tarjeta del Desarrollador 1 --}}
                    <div class="p-6 transition-all duration-500 border shadow-lg group bg-gradient-to-br from-stone-50 to-stone-100 dark:from-custom-gray dark:to-gray-800 rounded-2xl hover:shadow-2xl hover:-translate-y-2 border-stone-200 dark:border-gray-700">
                        <div class="flex flex-col items-center text-center">
                            {{-- Avatar con soporte para foto real --}}
                            <div class="w-32 h-32 rounded-full bg-gray-400 dark:bg-gray-600 p-1.5 mb-4 shadow-lg group-hover:scale-105 transition-transform duration-500">
                                <div class="flex items-center justify-center w-full h-full overflow-hidden rounded-full bg-stone-200 dark:bg-gray-700">
                                    {{-- PARA USAR FOTO REAL: Reemplaza el SVG con una etiqueta img --}}
                                     <img src="{{ Vite::asset('resources/img/KS.jpg') }}" alt="Kevin Salazar" class="object-cover w-full h-full"> 
                                    
                                    {{-- SVG por defecto (se muestra si no hay foto) --}}
                                    <svg class="w-16 h-16 text-gray-600 dark:text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                    </svg>
                                </div>
                            </div>

                            {{-- Información del desarrollador --}}
                            <h3 class="mb-1 text-2xl font-bold text-gray-900 dark:text-white">Kevin Salazar</h3>
                            <p class="mb-3 font-semibold text-lime-600 dark:text-lime-400">Desarrolladora Frontend & UI/UX Designer</p>
                            
                            <p class="max-w-md mb-4 text-gray-600 dark:text-gray-400">
                                Especialista en HTML, CSS, tailwindCSS, Javascript, Laravel y bases de datos geoespaciales. Apasionado por crear interfaces intuitivas y atractivas. Encargado del diseño visual, la experiencia de usuario y la implementación de componentes frontend con Tailwind CSS.
                            </p>

                            {{-- Contacto: Correo y WhatsApp --}}
                            <div class="flex justify-center mt-2 space-x-6">
                                {{-- Correo electrónico --}}
                                <a href="#" 
                                   class="flex flex-col items-center text-gray-500 transition-all duration-300 hover:text-lime-600 dark:text-gray-400 dark:hover:text-lime-400 hover:scale-110"
                                   title="Enviar correo">
                                    <svg class="mb-1 w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-xs">kevinsalazaroriginal@gmail.com</span>
                                </a>

                                {{-- WhatsApp --}}
                                <a href="#" 
                                   target="_blank"
                                   class="flex flex-col items-center text-gray-500 transition-all duration-300 hover:text-green-600 dark:text-gray-400 dark:hover:text-green-400 hover:scale-110"
                                   title="Enviar WhatsApp">
                                    <svg class="mb-1 w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M19.077 4.928C17.191 3.041 14.683 2 12.006 2c-5.35 0-9.71 4.34-9.716 9.69-.002 1.708.446 3.38 1.294 4.848L2 22l5.544-1.527c1.414.77 3.01 1.18 4.648 1.18h.004c5.35 0 9.71-4.34 9.717-9.69.004-2.588-1.002-5.02-2.887-6.907zM12.015 20.14h-.003c-1.444 0-2.86-.388-4.078-1.12l-.292-.175-3.29.866.88-3.2-.192-.306c-.73-1.17-1.116-2.52-1.115-3.92.006-4.435 3.613-8.04 8.055-8.04 2.15 0 4.17.84 5.69 2.362 1.52 1.522 2.354 3.545 2.35 5.696-.006 4.436-3.613 8.04-8.05 8.04zm4.42-6.024c-.242-.12-1.43-.706-1.652-.787-.222-.08-.384-.12-.546.12-.162.24-.63.787-.773.948-.142.16-.284.18-.526.06-.97-.48-1.76-1.11-2.46-1.86-.354-.373-.63-.78-.83-1.23-.09-.218.08-.336.24-.48l.36-.36c.12-.12.16-.2.24-.33.08-.13.04-.24-.02-.36-.06-.12-.52-1.26-.72-1.72-.19-.44-.38-.38-.52-.39h-.44c-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2.01 0 1.19.86 2.34.98 2.5.12.16 1.7 2.59 4.12 3.64 2.42 1.05 2.42.7 2.86.66.44-.04 1.43-.58 1.63-1.15.2-.56.2-1.04.14-1.15-.06-.1-.22-.16-.46-.28z"/>
                                    </svg>
                                    <span class="text-xs">WhatsApp</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Tarjeta del Desarrollador 2 --}}
                    <div class="p-6 transition-all duration-500 border shadow-lg group bg-gradient-to-br from-stone-50 to-stone-100 dark:from-custom-gray dark:to-gray-800 rounded-2xl hover:shadow-2xl hover:-translate-y-2 border-stone-200 dark:border-gray-700">
                        <div class="flex flex-col items-center text-center">
                            {{-- Avatar con soporte para foto real --}}
                            <div class="w-32 h-32 p-2 mb-4 transition-transform duration-500 bg-gray-500 rounded-full shadow-lg group-hover:scale-105">
                                <div class="flex items-center justify-center w-full h-full overflow-hidden rounded-full bg-stone-200 dark:bg-gray-700">
                                    {{-- PARA USAR FOTO REAL: Reemplaza el SVG con una etiqueta img --}}
                                    <img src="{{ Vite::asset('resources/img/GS.jpg') }}" alt="Geral Serrano" class="object-cover w-full h-full">
                                </div>
                            </div>

                            {{-- Información del desarrollador --}}
                            <h3 class="mb-1 text-2xl font-bold text-gray-900 dark:text-white">Geral Serrano</h3>
                            <p class="mb-3 font-semibold text-lime-600 dark:text-lime-400">Desarrollador Full-Stack & Especialista GIS</p>
                            
                            <p class="max-w-md mb-4 text-gray-600 dark:text-gray-400">
                                Desarrollador Full-Stack. Responsable de la integración de los servicios de Global Forest Watch para el análisis geoespacial y alertas de deforestación en tiempo real. Mi labor garantiza la integridad técnica de la plataforma, haciéndola capaz de procesar datos satelitales complejos para la gestión forestal.
                            </p>

                            {{-- Contacto: Correo y WhatsApp --}}
                            <div class="flex justify-center mt-2 space-x-6">
                                {{-- Correo electrónico --}}
                                <a href="" 
                                   class="flex flex-col items-center text-gray-500 transition-all duration-300 hover:text-lime-600 dark:text-gray-400 dark:hover:text-lime-400 hover:scale-110"
                                   title="Enviar correo">
                                    <svg class="mb-1 w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-xs">andriuserrano16121@gmail.com</span>
                                </a>

                                {{-- WhatsApp --}}
                                <a href=""
                                   target="_blank"
                                   class="flex flex-col items-center text-gray-500 transition-all duration-300 hover:text-green-600 dark:text-gray-400 dark:hover:text-green-400 hover:scale-110"
                                   title="Enviar WhatsApp">
                                    <svg class="mb-1 w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M19.077 4.928C17.191 3.041 14.683 2 12.006 2c-5.35 0-9.71 4.34-9.716 9.69-.002 1.708.446 3.38 1.294 4.848L2 22l5.544-1.527c1.414.77 3.01 1.18 4.648 1.18h.004c5.35 0 9.71-4.34 9.717-9.69.004-2.588-1.002-5.02-2.887-6.907zM12.015 20.14h-.003c-1.444 0-2.86-.388-4.078-1.12l-.292-.175-3.29.866.88-3.2-.192-.306c-.73-1.17-1.116-2.52-1.115-3.92.006-4.435 3.613-8.04 8.055-8.04 2.15 0 4.17.84 5.69 2.362 1.52 1.522 2.354 3.545 2.35 5.696-.006 4.436-3.613 8.04-8.05 8.04zm4.42-6.024c-.242-.12-1.43-.706-1.652-.787-.222-.08-.384-.12-.546.12-.162.24-.63.787-.773.948-.142.16-.284.18-.526.06-.97-.48-1.76-1.11-2.46-1.86-.354-.373-.63-.78-.83-1.23-.09-.218.08-.336.24-.48l.36-.36c.12-.12.16-.2.24-.33.08-.13.04-.24-.02-.36-.06-.12-.52-1.26-.72-1.72-.19-.44-.38-.38-.52-.39h-.44c-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2.01 0 1.19.86 2.34.98 2.5.12.16 1.7 2.59 4.12 3.64 2.42 1.05 2.42.7 2.86.66.44-.04 1.43-.58 1.63-1.15.2-.56.2-1.04.14-1.15-.06-.1-.22-.16-.46-.28z"/>
                                    </svg>
                                    <span class="text-xs">WhatsApp</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pie de página con info del sistema --}}
                <div class="pt-8 mt-12 text-center border-t border-stone-200 dark:border-gray-700">
                    <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Sistema de Gestión Geográfica v7.4.19 - Monitoreo de Deforestación') }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-500">
                        {{ __('Desarrollado con ') }} 
                        <svg class="inline-block w-4 h-4 mx-1 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                        {{ __(' por el equipo de Gestión Forestal de Cacao San José.') }}
                    </p>
                    <p class="mt-4 text-xs text-gray-400 dark:text-gray-600">
                        {{ __('© ') . date('Y') . __(' Todos los derechos reservados.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
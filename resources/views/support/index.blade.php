<x-app-layout>
    @php
        $i = 1;
    @endphp
    <div class="mx-auto">
        <div class="bg-stone-100/90 dark:bg-custom-gray shadow-sm rounded-2xl shadow-soft p-6 mb-6">
            
            <div class="mb-8 border-b border-gray-300 dark:border-gray-700 pb-4">
                <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-200 uppercase tracking-tight">
                    {{ __('Manual de Usuario') }}
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mt-2">Guía completa para el uso del Sistema de Gestión Forestal.</p>
            </div>

            <!-- Botón fijo de descarga -->
            

            <div class="flex flex-col md:flex-row gap-8">
                
                <aside class="w-full md:w-1/4">
                    <nav class="space-y-1 sticky top-6">
                        <p class="text-xs font-bold text-emerald-700 dark:text-emerald-500 uppercase px-3 mb-2 tracking-widest">Contenido</p>
                        
                        <a href="#introduccion" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-lg hover:bg-emerald-600 hover:text-white transition-all group">
                            <span class="mr-2">0{{ $i++ }}.</span> Introducción
                        </a>
                        
                        <a href="#acceso" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-lg hover:bg-emerald-600 hover:text-white transition-all group">
                            <span class="mr-2">0{{ $i++ }}.</span> Acceso al Sistema
                        </a>

                        <a href="#navegacion" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-lg hover:bg-emerald-600 hover:text-white transition-all group">
                            <span class="mr-2">0{{ $i++ }}.</span> Pagina de Inicio
                        </a>

                        <a href="#analisis" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-lg hover:bg-emerald-600 hover:text-white transition-all group">
                            <span class="mr-2">0{{ $i++ }}.</span> Analisis
                        </a>

                        <a href="#gestion-poligonos" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-lg hover:bg-emerald-600 hover:text-white transition-all group">
                            <span class="mr-2">0{{ $i++ }}.</span> Gestión de Polígonos
                        </a>

                        <a href="#gestion-productores" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-lg hover:bg-emerald-600 hover:text-white transition-all group">
                            <span class="mr-2">0{{ $i++ }}.</span> Gestión de Productores
                        </a>

                        <a href="#gestion-usuarios" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-lg hover:bg-emerald-600 hover:text-white transition-all group">
                            <span class="mr-2">0{{ $i++ }}.</span> Usuarios y Seguridad
                        </a>

                        <a href="#configuracion-perfil" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-lg hover:bg-emerald-600 hover:text-white transition-all group">
                            <span class="mr-2">0{{ $i++ }}.</span> Configuración de Perfil
                        </a>

                        <a href="#registro-actividades" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-lg hover:bg-emerald-600 hover:text-white transition-all group">
                            <span class="mr-2">0{{ $i++ }}.</span> Auditoría del Sistema
                        </a>
                    </nav>
                    <div class="fixed bottom-6 right-6 z-50">
                <a href="{{ asset('docs\manual2.pdf') }}" 
                   class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg transition-all duration-300 transform hover:scale-105 inline-flex items-center group">
                    <svg class="w-5 h-5 mr-2 group-hover:animate-bounce" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 8V2H7v6H2l8 8 8-8h-5zM0 18h20v2H0v-2z"/>
                    </svg>
                    <span>Descargar PDF</span>
                </a>
            </div>
                </aside>

                <main class="w-full md:w-3/4 space-y-12">
                    @include('support.content')
                </main>
            </div>
        </div>
    </div>

    <style>
        html { scroll-behavior: smooth; }

        .manual-img {
            width: 100%;
            max-width: 800px;
            border-radius: 0.75rem; /* rounded-xl */
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin: 1.5rem 0 0.5rem 0;
        }
        .image-caption {
            text-align: center;
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 1.5rem;
        }
        
        /* Asegurar que el botón no tape contenido en móviles */
        @media (max-width: 640px) {
            .fixed.bottom-6.right-6 {
                bottom: 1rem;
                right: 1rem;
            }
            .fixed.bottom-6.right-6 a {
                padding: 0.75rem;
                font-size: 0.875rem;
            }
        }
    </style>
</x-app-layout>
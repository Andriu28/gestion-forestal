<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistema de Gestion Geografica</title>
    @livewireStyles

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Dependencias externas -->
    <script src="https://unpkg.com/shpjs@latest/dist/shp.min.js"></script>

    <!-- Estilos locales -->
    @vite([
        'resources/css/app.css', 
        'resources/css/styleDas.css'
    ])

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">

    {{-- Estilos y scripts específicos del head --}}
    @yield('head-styles')
    @yield('head-scripts')

    <!-- =======================
         Inicialización de tema y sidebar antes de renderizar
    ======================== -->
    <script>
        // Inicializa el tema (oscuro/claro)
        const storedTheme = localStorage.getItem('theme') || 
            (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        if (storedTheme === 'dark') {
            document.documentElement.classList.add('dark');
        }

        // Inicializa el estado del sidebar antes de renderizar
        const sidebarStoredState = localStorage.getItem('sidebarCollapsed');
        const isSidebarCollapsed = sidebarStoredState === '1' && window.innerWidth > 768;
        const sidebarStyle = document.createElement('style');
        sidebarStyle.textContent = `
            #sidebar { 
                width: ${isSidebarCollapsed ? '4.6rem' : '16rem'} !important;
                transition: none !important;
            }
            .sidebar-text { 
                opacity: ${isSidebarCollapsed ? '0' : '1'} !important;
                pointer-events: ${isSidebarCollapsed ? 'none' : 'auto'} !important;
                width: ${isSidebarCollapsed ? '0 !important' : 'auto'} !important;
                min-width: ${isSidebarCollapsed ? '0 !important' : '0'} !important;
                max-width: ${isSidebarCollapsed ? '0 !important' : '100%'} !important;
                transition: none !important;
            }
            .nav-item { 
                gap: ${isSidebarCollapsed ? '0' : '0.75rem'} !important;
                transition: none !important;
            }
        `;
        document.head.appendChild(sidebarStyle);

        // Aplica sidebar colapsado antes de pintar (solo escritorio)
        if (window.innerWidth > 768 && isSidebarCollapsed) {
            const applySidebarCollapsed = () => {
                const sidebar = document.getElementById('sidebar');
                if (sidebar) {
                    sidebar.classList.add('collapsed');
                    return true;
                }
                return false;
            };
            if (!applySidebarCollapsed()) {
                const observer = new MutationObserver(() => {
                    if (applySidebarCollapsed()) observer.disconnect();
                });
                observer.observe(document.documentElement, { childList: true, subtree: true });
            }
        }

        // Remueve estilos inline después de la carga
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                const sidebar = document.getElementById('sidebar');
                const sidebarTexts = document.querySelectorAll('.sidebar-text');
                const navItems = document.querySelectorAll('.nav-item');
                if (sidebar) {
                    sidebar.style.transition = '';
                    sidebar.style.width = '';
                }
                sidebarTexts.forEach(text => {
                    text.style.transition = '';
                    text.style.opacity = '';
                    text.style.pointerEvents = '';
                    text.style.width = '';
                    text.style.minWidth = '';
                    text.style.maxWidth = '';
                });
                navItems.forEach(item => {
                    item.style.transition = '';
                    item.style.gap = '';
                });
                if (sidebarStyle.parentNode) {
                    sidebarStyle.remove();
                }
            }, 50);
        });

        // Inicializa estado del botón GFW antes de renderizar
        const gfwStoredState = localStorage.getItem('gfwLossLayerState');
        const isGfwLayerVisible = gfwStoredState === 'false' ? false : true;
        const style = document.createElement('style');
        style.textContent = `
            #icon-eye-open { display: ${isGfwLayerVisible ? 'inline-block' : 'none'}; }
            #icon-eye-closed { display: ${isGfwLayerVisible ? 'none' : 'inline-block'}; }
        `;
        document.head.appendChild(style);

        // Sincroniza el toggle de modo oscuro después de aplicar el tema
        document.addEventListener('DOMContentLoaded', function() {
            const isDark = document.documentElement.classList.contains('dark');
            const darkModeToggle = document.getElementById('darkModeToggle');
            const mobileDarkIcon = document.getElementById('mobileDarkIcon');
            if (darkModeToggle) {
                darkModeToggle.checked = isDark;
            }
            if (mobileDarkIcon) {
                if (isDark) {
                    mobileDarkIcon.innerHTML = `
                        <circle cx="12" cy="12" r="4"/>
                        <path d="M12 2v2"/><path d="M12 20v2"/>
                        <path d="m4.93 4.93 1.41 1.41"/>
                        <path d="m17.66 17.66 1.41 1.41"/>
                        <path d="M2 12h2"/><path d="M20 12h2"/>
                        <path d="m6.34 17.66-1.41 1.41"/>
                        <path d="m19.07 4.93-1.41 1.41"/>
                    `;
                } else {
                    mobileDarkIcon.innerHTML = `
                        <path d="M20.985 12.486a9 9 0 1 1-9.473-9.472c.405-.022.617.46.402.803a6 6 0 0 0 8.268 8.268c.344-.215.825-.004.803.401"/>
                    `;
                }
            }
        });
    </script>
</head>
<body class="bg-neutral-200 dark:bg-custom-dark ">
    <!-- Overlay para sidebar móvil -->
    <div id="sidebarOverlay" class="sidebar-overlay"></div>

    <!-- Botón de modo oscuro móvil -->
    <button id="mobileDarkToggle" class="mobile-dark-toggle">
        <svg id="mobileDarkIcon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sun-icon lucide-sun w-6 h-6 text-white">
            <circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>
        </svg>
    </button>

    <div class="flex h-screen ">
        @include('layouts.navigation')
        <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-6">
            {{ $slot }}
        </main>
    </div>
   
    <!-- Scripts locales .-->
    @vite([
        'resources/js/app.js',
        'resources/js/DashFunctions.js'
    ])
    
    @if(session('swal'))
    <script>
        /**
         * Muestra una alerta tipo toast usando SweetAlert2
         */
        document.addEventListener('DOMContentLoaded', function() {
            const swalData = @json(session('swal'));
            Swal.fire({
                position: "top-end",
                icon: swalData.icon,
                title: swalData.title,
                showConfirmButton: false,
                timer: 3700,
                timerProgressBar: true,
                toast: true,
                width: '380px',
                padding: '1rem',
                html: `<div class="text-center">
                         <p class="text-sm text-gray-700 dark:text-gray-300">${swalData.text ?? ''}</p>
                       </div>`,
                customClass: {
                    popup: 'rounded-xl shadow-lg bg-stone-100/95 dark:bg-custom-gray border border-gray-200 dark:border-gray-700',
                    title: 'text-lg font-semibold text-gray-900 dark:text-white mb-1',
                    htmlContainer: 'text-sm text-gray-600 dark:text-gray-300',
                    timerProgressBar: 'bg-gradient-to-r from-emerald-400 to-emerald-600'
                },
                showClass: {
                    popup: 'animate__animated animate__fadeInRight animate__faster'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutRight animate__faster'
                },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
            // Limpiar la sesión flash después de mostrar
            fetch('/clear-swal-session', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            });
        });
    </script>
    @endif
    
    @livewireScripts
    
</body>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<!-- Script que contiene el js para las animaciones del menu de perfin de usuario -->
<script src="{{ asset('js/menuUser.js') }}"></script>
</html>
// =======================
// 1. Importaciones/dependencias
// =======================
// (No hay imports en este archivo, pero aquí irían si usas módulos ES6)

// =======================
// 2. Constantes y configuraciones
// =======================
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');
const userMenuToggle = document.getElementById('userMenuToggle');
const userMenu = document.getElementById('userMenu');
const darkModeToggle = document.getElementById('darkModeToggle');
const mobileDarkToggle = document.getElementById('mobileDarkToggle');
const mobileDarkIcon = document.getElementById('mobileDarkIcon');
const htmlElement = document.documentElement;
let sidebarCollapsed = false;

// =======================
// 3. Definiciones de funciones
// =======================

/**
 * Guarda el estado del sidebar en localStorage
 * @param {boolean} collapsed - Si el sidebar está colapsado
 */
function saveSidebarState(collapsed) {
    localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0');
}

/**
 * Recupera el estado del sidebar desde localStorage
 * @returns {boolean} Estado colapsado
 */
function getSidebarState() {
    return localStorage.getItem('sidebarCollapsed') === '1';
}

/**
 * Aplica el estado guardado del sidebar al cargar la página (solo escritorio)
 */
function initializeSidebarState() {
    if (window.innerWidth > 768) {
        sidebarCollapsed = getSidebarState();
        sidebar.classList.toggle('collapsed', sidebarCollapsed);
    } else {
        sidebar.classList.remove('collapsed');
    }
}

/**
 * Actualiza el icono del modo oscuro en móvil
 * @param {boolean} isDark - Si el modo oscuro está activo
 */
function updateMobileIcon(isDark) {
    if (!mobileDarkIcon) return;
    mobileDarkIcon.innerHTML = isDark
        ? `<circle cx="12" cy="12" r="4"/>
           <path d="M12 2v2"/><path d="M12 20v2"/>
           <path d="m4.93 4.93 1.41 1.41"/>
           <path d="m17.66 17.66 1.41 1.41"/>
           <path d="M2 12h2"/><path d="M20 12h2"/>
           <path d="m6.34 17.66-1.41 1.41"/>
           <path d="m19.07 4.93-1.41 1.41"/>`
        : `<path d="M20.985 12.486a9 9 0 1 1-9.473-9.472c.405-.022.617.46.402.803a6 6 0 0 0 8.268 8.268c.344-.215.825-.004.803.401"/>`;
}

/**
 * Alterna el modo oscuro/claro y actualiza el estado en localStorage
 */
function toggleDarkMode() {
    const isDark = htmlElement.classList.contains('dark');
    htmlElement.classList.toggle('dark', !isDark);
    localStorage.setItem('theme', !isDark ? 'dark' : 'light');
    if (darkModeToggle) darkModeToggle.checked = !isDark;
    updateMobileIcon(!isDark);
}

/**
 * Actualiza el tamaño del mapa tras cambios visuales
 */
function updateMapSize() {
    if (window.deforestationMapInstance && window.deforestationMapInstance.map) {
        window.deforestationMapInstance.map.updateSize();
    }
}

/**
 * Muestra/oculta el menú de usuario
 * @param {Event} e - Evento de click
 */
function toggleUserMenu(e) {
    e.stopPropagation();
    userMenu.classList.toggle('show');
}

/**
 * Cierra el menú de usuario si se hace click fuera
 * @param {Event} e - Evento de click
 */
function closeUserMenuOnClickOutside(e) {
    if (!userMenuToggle.contains(e.target) && !userMenu.contains(e.target)) {
        userMenu.classList.remove('show');
    }
}

/**
 * Marca el ítem de navegación activo y cierra el sidebar móvil si corresponde
 * @param {Element} item - Elemento de navegación clickeado
 */
function setActiveNavItem(item) {
    document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
    item.classList.add('active');
    if (window.innerWidth <= 768) {
        sidebar.classList.remove('mobile-open');
        sidebarOverlay.classList.remove('show');
    }
}

/**
 * Aplica animación de entrada a las tarjetas
 */
function animateCardsOnLoad() {
    document.querySelectorAll('.bg-white.rounded-xl').forEach((card, idx) => {
        // EXCLUIR el modal explícitamente
        if (card.closest('#manual-polygon-modal')) {
            return;
        }
        
        // Usar transiciones MÁS ESPECÍFICAS, no "all"
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease'; // ← Solo estas propiedades
        
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, idx * 100);
    });
}s

/**
 * Inicializa DataTables con configuración mejorada para Tailwind CSS
 */
function initializeDataTables() {
    $(document).ready(function() {
        const commonConfig = {
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            },
            "dom": "<'flex justify-between items-center mb-4'<'flex-1'l><'flex-none'f>>" +
                   "<'bg-white dark:bg-gray-800 rounded-lg overflow-hidden't>" +
                   "<'flex justify-between items-center mt-4'<'flex-1'i><'flex-none'p>>",
            "drawCallback": function() {
                // Re-aplicar estilos después de cada redibujado
                setTimeout(applyCustomDataTableStyles, 50);
            },
            "initComplete": function() {
                // Aplicar estilos después de la inicialización
                setTimeout(applyCustomDataTableStyles, 100);
            }
        };

        $('#auditoria-table').DataTable({
            ...commonConfig,
            "columnDefs": [
                { "width": "20%", "targets": 0 },
                { "width": "40%", "targets": 1 },
                { "width": "40%", "targets": 2 }
            ]
        });

        $('#audit-table').DataTable({
            ...commonConfig
        });

        $('#users-table').DataTable({
            ...commonConfig,
            "columnDefs": [
                { "width": "5%", "targets": 0 },
                { "width": "20%", "targets": 1 },
                { "width": "34%", "targets": 2 },
                { "width": "20%", "targets": 3 },
                { "width": "6%", "targets": 4 }
            ]
        });

        // Aplicar estilos personalizados después de la inicialización
        setTimeout(applyCustomDataTableStyles, 200);
    });
}

/**
 * Aplica estilos personalizados de Tailwind a los elementos de DataTables
 */
function applyCustomDataTableStyles() {
    // Estilos para inputs de búsqueda y selects
    $('.dt-input').addClass('px-3 py-2 border border-gray-900 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200');
    
    // Estilos específicos para selects
    $('.dt-length select, .dt-search input').addClass('px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500');
    
    // Estilos para la información de la tabla
    $('.dataTables_info').addClass('text-gray-600 dark:text-gray-400 text-sm');
    
    // Estilos para la paginación
    $('.dataTables_paginate .paginate_button').addClass('px-3 py-1 mx-1 rounded-md bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200');
    
    // Estilo para la página actual
    $('.dataTables_paginate .paginate_button.current').addClass('bg-blue-600 text-white border-blue-600 hover:bg-blue-700');
    
    // Estilos para los botones deshabilitados
    $('.dataTables_paginate .paginate_button.disabled').addClass('opacity-50 cursor-not-allowed hover:bg-white dark:hover:bg-gray-700');
    
    // Estilos para el header de la tabla
    $('.dataTables_wrapper thead th').addClass('bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider');
    
    // Estilos para las celdas del cuerpo
    $('.dataTables_wrapper tbody td').addClass('px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 border-t border-gray-200 dark:border-gray-600');
    
    // Estilos para las filas alternas
    $('.dataTables_wrapper tbody tr:nth-child(even)').addClass('bg-gray-50 dark:bg-gray-800/50');
    
    // Estilos para el contenedor principal
    $('.dataTables_wrapper').addClass('rounded-lg overflow-hidden shadow-sm');
    
    // Estilos específicos para selects con flecha personalizada
    $('.dt-length select').css({
        'background-image': 'url("data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e")',
        'background-position': 'right 0.5rem center',
        'background-repeat': 'no-repeat',
        'background-size': '1.5em 1.5em',
        'padding-right': '2rem',
        'appearance': 'none'
    });

    // Aplicar estilos para modo oscuro
    if (htmlElement.classList.contains('dark')) {
        $('.dt-input, .dt-length select, .dt-search input').addClass('dark:bg-gray-100 dark:border-gray-600 dark:text-gray-100');
        $('.dataTables_paginate .paginate_button').addClass('dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600');
    }
}



/**
 * Re-aplica estilos cuando cambia el modo oscuro/claro
 */
function reapplyDataTableStylesOnThemeChange() {
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                setTimeout(applyCustomDataTableStyles, 100);
            }
        });
    });
    
    observer.observe(htmlElement, {
        attributes: true,
        attributeFilter: ['class']
    });
}

// =======================
// 4. Event listeners
// =======================

// Sidebar toggle (responsive y escritorio)
sidebarToggle.addEventListener('click', () => {
    if (window.innerWidth <= 768) {
        sidebar.classList.toggle('mobile-open');
        sidebarOverlay.classList.toggle('show');
    } else {
        sidebarCollapsed = !sidebarCollapsed;
        saveSidebarState(sidebarCollapsed);
        sidebar.classList.toggle('collapsed', sidebarCollapsed);

        // Animación de iconos y textos
        const icons = sidebar.querySelectorAll('.nav-item i, .sidebar-header i');
        const texts = sidebar.querySelectorAll('.sidebar-text');
        icons.forEach(icon => {
            icon.style.width = '1.75rem';
            icon.style.minWidth = '1.75rem';
            icon.style.maxWidth = '1.75rem';
        });
        if (sidebarCollapsed) {
            texts.forEach(text => text.classList.remove('delayed-opacity'));
        } else {
            texts.forEach(text => text.classList.add('delayed-opacity'));
            setTimeout(() => {
                texts.forEach(text => text.classList.remove('delayed-opacity'));
            }, 700);
        }
        setTimeout(() => {
            icons.forEach(icon => {
                icon.style.width = '';
                icon.style.minWidth = '';
                icon.style.maxWidth = '';
            });
        }, 400);

        setTimeout(updateMapSize, 400);
    }
});

// Cierra el sidebar móvil al hacer click en el overlay
sidebarOverlay.addEventListener('click', () => {
    sidebar.classList.remove('mobile-open');
    sidebarOverlay.classList.remove('show');
});

// Ajusta el sidebar al cambiar el tamaño de la ventana
window.addEventListener('resize', () => {
    if (window.innerWidth > 768) {
        sidebar.classList.remove('mobile-open');
        sidebarOverlay.classList.remove('show');
    } else {
        sidebar.classList.remove('collapsed');
    }
});

// Dropdown de usuario
userMenuToggle.addEventListener('click', toggleUserMenu);
document.addEventListener('click', closeUserMenuOnClickOutside);

// Estado activo de navegación
document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', () => setActiveNavItem(item));
});

// Animación de tarjetas al cargar
animateCardsOnLoad();

// Modo oscuro
document.addEventListener('DOMContentLoaded', function() {
    if (darkModeToggle) darkModeToggle.addEventListener('change', toggleDarkMode);
    if (mobileDarkToggle) mobileDarkToggle.addEventListener('click', toggleDarkMode);
});

// Detecta cambios en la preferencia del sistema
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (!localStorage.getItem('theme')) {
        htmlElement.classList.toggle('dark', e.matches);
        if (darkModeToggle) darkModeToggle.checked = e.matches;
        updateMobileIcon(e.matches);
    }
});

// =======================
// 5. Inicialización
// =======================

initializeSidebarState();
initializeDataTables();
reapplyDataTableStylesOnThemeChange();
// ====================================================================================================
// CONTROLADOR DE INTERFAZ DEL DASHBOARD: DashFunctions.js
// ====================================================================================================
// 
// Gestiona la interactividad visual de la plantilla principal (Dashboard Layout):
// 1. Barra lateral (Sidebar):
//    - Modo colapsado/expandido en pantallas de escritorio con persistencia en localStorage.
//    - Menú deslizable (Drawer) para pantallas móviles con capa de bloqueo (Overlay).
//    - Reajuste dinámico del lienzo del mapa (updateSize) tras animaciones para evitar distorsiones.
// 2. Modo Oscuro / Claro (Dark Mode):
//    - Detección automática de preferencias del sistema operativo (prefers-color-scheme).
//    - Alternancia manual y almacenamiento de la preferencia del usuario en localStorage.
// 3. Menús contextuales y animaciones escalonadas de entrada en tarjetas.
// ====================================================================================================

// =========================================================================
// 1. Captura de Elementos del DOM y Variables de Estado
// =========================================================================
const sidebarToggle   = document.getElementById('sidebarToggle');
const sidebar         = document.getElementById('sidebar');
const sidebarOverlay  = document.getElementById('sidebarOverlay');
const userMenuToggle  = document.getElementById('userMenuToggle');
const userMenu        = document.getElementById('userMenu');
const darkModeToggle  = document.getElementById('darkModeToggle');
const mobileDarkToggle = document.getElementById('mobileDarkToggle');
const mobileDarkIcon  = document.getElementById('mobileDarkIcon');
const htmlElement     = document.documentElement;
let sidebarCollapsed  = false;

// =========================================================================
// 2. Funciones de Persistencia y Control del Sidebar
// =========================================================================

/**
 * Guarda el estado del sidebar en el almacenamiento local del navegador (localStorage).
 * Permite que al recargar la página o navegar a otra sección se conserve el estado elegido.
 * 
 * @param {boolean} collapsed Verdadero si el sidebar está minimizado a solo iconos
 */
function saveSidebarState(collapsed) {
    localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0');
}

/**
 * Consulta en localStorage si el usuario tenía el menú lateral colapsado.
 * @returns {boolean}
 */
function getSidebarState() {
    return localStorage.getItem('sidebarCollapsed') === '1';
}

/**
 * Aplica el estado recordado del sidebar al cargar la página:
 * Solo aplica para pantallas de escritorio (ancho > 768px); en móviles siempre inicia cerrado.
 */
function initializeSidebarState() {
    if (window.innerWidth > 768) {
        sidebarCollapsed = getSidebarState();
        sidebar.classList.toggle('collapsed', sidebarCollapsed);
    } else {
        sidebar.classList.remove('collapsed');
    }
}

// =========================================================================
// 3. Control del Tema Visual (Modo Oscuro / Claro)
// =========================================================================

/**
 * Modifica el icono SVG del interruptor móvil según el tema actual:
 * - Sol con rayos cuando el modo oscuro está activo (para sugerir cambio a día).
 * - Luna creciente cuando el modo claro está activo (para sugerir cambio a noche).
 * 
 * @param {boolean} isDark Estado activo del modo oscuro
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
 * Alterna la clase CSS `.dark` en el elemento raíz `<html>`,
 * guarda la preferencia en `localStorage.setItem('theme', ...)`
 * y sincroniza interruptores de escritorio y móvil.
 */
function toggleDarkMode() {
    const isDark = htmlElement.classList.contains('dark');
    htmlElement.classList.toggle('dark', !isDark);
    localStorage.setItem('theme', !isDark ? 'dark' : 'light');
    if (darkModeToggle) darkModeToggle.checked = !isDark;
    updateMobileIcon(!isDark);
}

// =========================================================================
// 4. Sincronización Cartográfica
// =========================================================================

/**
 * Notifica a OpenLayers que debe recalcular las dimensiones de su contenedor (`map.updateSize()`):
 * Crítico en interfaces con barras laterales colapsables, ya que cuando el menú se encoge o agranda,
 * el ancho del canvas HTML cambia. Sin esta llamada, el mapa quedaría cortado o deformado.
 */
function updateMapSize() {
    if (window.deforestationMapInstance && window.deforestationMapInstance.map) {
        window.deforestationMapInstance.map.updateSize();
    }
}

// =========================================================================
// 5. Menú de Usuario y Navegación
// =========================================================================

/**
 * Despliega o repliega el menú emergente del perfil de usuario en la barra superior.
 */
function toggleUserMenu(e) {
    e.stopPropagation();
    userMenu.classList.toggle('show');
}

/**
 * Detecta clics fuera del contenedor del menú de usuario para cerrarlo automáticamente.
 */
function closeUserMenuOnClickOutside(e) {
    if (!userMenuToggle.contains(e.target) && !userMenu.contains(e.target)) {
        userMenu.classList.remove('show');
    }
}

/**
 * Resalta visualmente el enlace de navegación seleccionado y repliega el menú móvil.
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
 * Aplica una animación de entrada suave (fade-in + desplazamiento vertical hacia arriba)
 * escalonada con retraso proporcional (stagger effect) a las tarjetas informativas del panel.
 */
function animateCardsOnLoad() {
    document.querySelectorAll('.bg-white.rounded-xl').forEach((card, idx) => {
        // Excluir ventanas modales de la animación
        if (card.closest('#manual-polygon-modal')) {
            return;
        }
        
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, idx * 100);
    });
}

// =========================================================================
// 6. Registro de Escuchadores de Eventos (Event Listeners)
// =========================================================================

// Botón de colapso/apertura del sidebar (comportamiento adaptativo responsive)
sidebarToggle.addEventListener('click', () => {
    if (window.innerWidth <= 768) {
        // En móviles: abre/cierra como panel lateral flotante con cortina de fondo
        sidebar.classList.toggle('mobile-open');
        sidebarOverlay.classList.toggle('show');
    } else {
        // En escritorio: colapsa el ancho para dejar ver solo los iconos
        sidebarCollapsed = !sidebarCollapsed;
        saveSidebarState(sidebarCollapsed);
        sidebar.classList.toggle('collapsed', sidebarCollapsed);

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

        // Espera a que termine la transición CSS del sidebar (400ms) para recalcular el tamaño del mapa
        setTimeout(updateMapSize, 400);
    }
});

// Cerrar el sidebar móvil tocando el fondo oscuro
sidebarOverlay.addEventListener('click', () => {
    sidebar.classList.remove('mobile-open');
    sidebarOverlay.classList.remove('show');
});

// Ajuste automático ante cambios de orientación o tamaño de pantalla
window.addEventListener('resize', () => {
    if (window.innerWidth > 768) {
        sidebar.classList.remove('mobile-open');
        sidebarOverlay.classList.remove('show');
    } else {
        sidebar.classList.remove('collapsed');
    }
});

// Eventos del desplegable de usuario
userMenuToggle.addEventListener('click', toggleUserMenu);
document.addEventListener('click', closeUserMenuOnClickOutside);

// Eventos de selección de menú
document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', () => setActiveNavItem(item));
});

// Disparo de la animación inicial de tarjetas
animateCardsOnLoad();

// Inicialización de interruptores de modo oscuro
document.addEventListener('DOMContentLoaded', function() {
    if (darkModeToggle) darkModeToggle.addEventListener('change', toggleDarkMode);
    if (mobileDarkToggle) mobileDarkToggle.addEventListener('click', toggleDarkMode);
});

// Sincronización automática con la preferencia del sistema operativo del usuario si no hay selección manual
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (!localStorage.getItem('theme')) {
        htmlElement.classList.toggle('dark', e.matches);
        if (darkModeToggle) darkModeToggle.checked = e.matches;
        updateMobileIcon(e.matches);
    }
});

// =========================================================================
// 7. Arranque Inicial
// =========================================================================
initializeSidebarState();

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


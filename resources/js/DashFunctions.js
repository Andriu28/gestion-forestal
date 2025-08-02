
// Inicializa los iconos Lucide
lucide.createIcons();

// Sidebar toggle funcionalidad
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');
let sidebarCollapsed = false;

sidebarToggle.addEventListener('click', () => {
    if (window.innerWidth <= 768) {
        sidebar.classList.toggle('mobile-open');
        sidebarOverlay.classList.toggle('show');
    } else {
        sidebarCollapsed = !sidebarCollapsed;
        const icons = sidebar.querySelectorAll('.nav-item i, .sidebar-header i');
        const texts = sidebar.querySelectorAll('.sidebar-text');
        if (sidebarCollapsed) {
            sidebar.classList.add('collapsed');
            icons.forEach(icon => {
                icon.style.width = '1.75rem';
                icon.style.minWidth = '1.75rem';
                icon.style.maxWidth = '1.75rem';
            });
            texts.forEach(text => {
                text.classList.remove('delayed-opacity');
            });
        } else {
            sidebar.classList.remove('collapsed');
            icons.forEach(icon => {
                icon.style.width = '1.75rem';
                icon.style.minWidth = '1.75rem';
                icon.style.maxWidth = '1.75rem';
            });
            // Aplica la clase de retraso solo al expandir
            texts.forEach(text => {
                text.classList.add('delayed-opacity');
            });
            // Quita la clase después de la animación para que el siguiente cierre sea inmediato
            setTimeout(() => {
                texts.forEach(text => {
                    text.classList.remove('delayed-opacity');
                });
            }, 700); // 0.2s delay + 0.5s transition
        }
        setTimeout(() => {
            icons.forEach(icon => {
                icon.style.width = '';
                icon.style.minWidth = '';
                icon.style.maxWidth = '';
            });
        }, 400);
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
    }
});

// Dropdown del usuario
const userMenuToggle = document.getElementById('userMenuToggle');
const userMenu = document.getElementById('userMenu');
userMenuToggle.addEventListener('click', (e) => {
    e.stopPropagation();
    userMenu.classList.toggle('show');
});
document.addEventListener('click', (e) => {
    if (!userMenuToggle.contains(e.target) && !userMenu.contains(e.target)) {
        userMenu.classList.remove('show');
    }
});

// Estado activo de navegación
const navItems = document.querySelectorAll('.nav-item');
navItems.forEach(item => {
    item.addEventListener('click', (e) => {
        
        navItems.forEach(nav => nav.classList.remove('active'));
        item.classList.add('active');
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('mobile-open');
            sidebarOverlay.classList.remove('show');
        }
    });
});

// Animación de tarjetas al cargar
document.querySelectorAll('.bg-white.rounded-xl').forEach((card, idx) => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'all 0.6s ease';
    setTimeout(() => {
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
    }, idx * 100);
});

// Modo oscuro
const darkModeToggle = document.getElementById('darkModeToggle');
const mobileDarkToggle = document.getElementById('mobileDarkToggle');
const mobileDarkIcon = document.getElementById('mobileDarkIcon');
const htmlElement = document.documentElement;
const savedTheme = localStorage.getItem('theme');
const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

function initializeTheme() {
    const isDark = savedTheme === 'dark' || (!savedTheme && prefersDark);
    if (isDark) {
        htmlElement.classList.add('dark');
        if (darkModeToggle) darkModeToggle.checked = true;
        updateMobileIcon(true);
    } else {
        updateMobileIcon(false);
    }
}
function updateMobileIcon(isDark) {
    if (mobileDarkIcon) {
        mobileDarkIcon.setAttribute('data-lucide', isDark ? 'sun' : 'moon');
        lucide.createIcons();
    }
}
function toggleDarkMode() {
    const isDark = htmlElement.classList.contains('dark');
    if (isDark) {
        htmlElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
        if (darkModeToggle) darkModeToggle.checked = false;
        updateMobileIcon(false);
    } else {
        htmlElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
        if (darkModeToggle) darkModeToggle.checked = true;
        updateMobileIcon(true);
    }
}
if (darkModeToggle) darkModeToggle.addEventListener('change', toggleDarkMode);
if (mobileDarkToggle) mobileDarkToggle.addEventListener('click', toggleDarkMode);
initializeTheme();
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (!localStorage.getItem('theme')) {
        if (e.matches) {
            htmlElement.classList.add('dark');
            if (darkModeToggle) darkModeToggle.checked = true;
            updateMobileIcon(true);
        } else {
            htmlElement.classList.remove('dark');
            if (darkModeToggle) darkModeToggle.checked = false;
            updateMobileIcon(false);
        }
    }
});
htmlElement.style.transition = 'background-color 0.3s ease, color 0.3s ease';
const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
            setTimeout(() => lucide.createIcons(), 100);
        }
    });
});
observer.observe(htmlElement, { attributes: true, attributeFilter: ['class'] });

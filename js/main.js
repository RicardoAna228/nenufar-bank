// ============================================
// Nenúfar Bank - Utilidades globales
// ============================================

const APP = {
    // Usuario actual (debe coincidir con la BD)
    currentUser: 'Nicol Ocampo',
    currentDocumento: '1094899647',

    // URL base de la API interna de PHP
    apiBase: window.location.pathname.includes('/html/') ? '../api/' : 'api/',

    // Obtener saldo actual
    async getSaldo() {
        try {
            const response = await fetch(this.apiBase + 'saldo.php?usuario_documento=' + this.currentDocumento);
            const data = await response.json();
            return data.saldo || 0;
        } catch (error) {
            console.error('Error al obtener saldo:', error);
            return 0;
        }
    },

    // Formatear moneda
    fformatCurrency(amount) {
        const num = parseFloat(amount);
        if (isNaN(num)) return '$0.00';
         return '$' + num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    },

    // Mostrar toast
    showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        if (!toast) return;
        toast.textContent = message;
        toast.className = 'toast ' + type;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3500);
    },

    // Capitalizar primera letra
    ucfirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    },

    // Formatear fecha relativa
    formatDate(dateString) {
        const fecha = new Date(dateString);
        const hoy = new Date();
        const ayer = new Date(hoy);
        ayer.setDate(ayer.getDate() - 1);

        if (fecha.toDateString() === hoy.toDateString()) {
            return 'Hoy, ' + fecha.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
        } else if (fecha.toDateString() === ayer.toDateString()) {
            return 'Ayer';
        } else {
            return fecha.toLocaleDateString('es-CO', { weekday: 'short', day: 'numeric', month: 'short' });
        }
    }
};

// Cargar sidebar (componente reutilizable)
async function loadSidebar() {
    try {
        const sidebarUrl = window.location.pathname.includes('/html/')
            ? '../includes/sidebar.html'
            : 'includes/sidebar.html';
        const response = await fetch(sidebarUrl);
        const html = await response.text();
        const sidebarContainer = document.getElementById('sidebar-container');
        if (sidebarContainer) {
            sidebarContainer.innerHTML = html;

            const pathSegments = window.location.pathname.split('/');
            const htmlIndex = pathSegments.indexOf('html');
            const appRoot = htmlIndex !== -1
                ? pathSegments.slice(0, htmlIndex).join('/') + '/'
                : pathSegments.slice(0, -1).join('/') + '/';
            const linkMap = {
                'index.html': 'index.html',
                'tienda.html': 'html/tienda.html',
                'gastos.html': 'html/gastos.html',
                'registrar.html': 'html/registrar.html',
                'tamalbits.html': 'html/tamalbits.html'
            };

            const currentPage = window.location.pathname.split('/').pop() || 'index.html';
            const links = sidebarContainer.querySelectorAll('nav a');
            links.forEach(link => {
                const href = link.getAttribute('href');
                if (!href) return;
                const targetPage = href.split('/').pop();
                if (linkMap[targetPage]) {
                    link.setAttribute('href', appRoot + linkMap[targetPage]);
                }
                if (targetPage === currentPage) {
                    link.classList.add('active');
                }
            });

            const logoImg = sidebarContainer.querySelector('.logo-icon');
            if (logoImg) {
                logoImg.src = appRoot + 'images/logo.png';
            }

            // Mostrar nombre del usuario real en el sidebar
            const userNameEl = sidebarContainer.querySelector('.user-name, .nombre-usuario, [class*="user"] span, [class*="nombre"]');
            if (userNameEl) userNameEl.textContent = APP.currentUser;

            const nameEl = sidebarContainer.querySelector('#user-name');
            const initialsEl = sidebarContainer.querySelector('#user-initials');
            if (nameEl) nameEl.textContent = APP.currentUser;
                if (initialsEl) initialsEl.textContent = APP.currentUser
                    .split(' ').map(n => n[0]).join('').toUpperCase();
            }
    } catch (error) {
        console.error('Error al cargar sidebar:', error);
    }
}

// Auto-inicializar
document.addEventListener('DOMContentLoaded', () => {
    loadSidebar();
});
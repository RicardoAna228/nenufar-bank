// ============================================
// Nenúfar Bank - Utilidades globales
// ============================================

const APP = {
    // Usuario actual (debe coincidir con la BD)
    currentUser: 'Nicol Ocampo',
    currentDocumento: '1094899647',

    // URL base de la API interna de PHP
    apiBase: window.location.pathname.includes('/html/') ? '../api/' : 'api/',

    // Obtener saldo actual desde la API bancaria (NO desde BD)
    async getSaldo() {
        try {
            // ✅ CORREGIDO: No enviar usuario_documento, la API bancaria no lo necesita
            const response = await fetch(this.apiBase + 'saldo.php');
            if (!response.ok) {
                console.error('Error HTTP al obtener saldo:', response.status);
                return 0;
            }
            const data = await response.json();
            // La API devuelve el saldo directamente
            return parseFloat(data.saldo || data.balance || data.monto || 0);
        } catch (error) {
            console.error('Error al obtener saldo:', error);
            return 0;
        }
    },

    // Formatear moneda
    formatCurrency(amount) {
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
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    },

    // Formatear fecha relativa
    formatDate(dateString) {
        if (!dateString) return '—';
        const fecha = new Date(dateString);
        if (isNaN(fecha.getTime())) return '—';
        
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
        if (!response.ok) {
            console.error('Error al cargar sidebar:', response.status);
            return;
        }
        const html = await response.text();
        const sidebarContainer = document.getElementById('sidebar-container');
        if (!sidebarContainer) return;
        
        sidebarContainer.innerHTML = html;

        const logoImg = sidebarContainer.querySelector('.logo-icon');
        if (logoImg) {
            logoImg.src = window.location.pathname.includes('/html/')
                ? '../images/logo.png'
                : 'images/logo.png';
        }

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

        // Mostrar datos del usuario en el sidebar
        const nameEl = sidebarContainer.querySelector('#user-name');
        const initialsEl = sidebarContainer.querySelector('#user-initials');
        if (nameEl) nameEl.textContent = APP.currentUser;
        if (initialsEl) initialsEl.textContent = APP.currentUser
            .split(' ').map(n => n[0]).join('').toUpperCase();
            
    } catch (error) {
        console.error('Error al cargar sidebar:', error);
    }
}

// Auto-inicializar
document.addEventListener('DOMContentLoaded', () => {
    loadSidebar();
});
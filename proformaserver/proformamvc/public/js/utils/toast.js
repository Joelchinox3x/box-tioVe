/**
 * SISTEMA DE NOTIFICACIONES (TOASTS) CENTRALIZADO
 * =================================================
 * Este script maneja todas las alertas flotantes del sistema.
 * 
 * USO:
 * 1. Asegúrate de incluir este script en tu vista:
 *    <script src="<?= asset('js/utils/toast.js') ?>"></script>
 * 
 * 2. Asegúrate de tener un contenedor en tu HTML (generalmente en el layout o header):
 *    <div id="notificationContainer" class="fixed top-4 right-4 z-[9999] space-y-2 max-w-xs w-full pointer-events-none"></div>
 * 
 * 3. Llama a la función global:
 *    mostrarToast('Mensaje de éxito', 'success');
 *    mostrarToast('Algo salió mal', 'error');
 * 
 * TIPOS SOPORTADOS: 'success', 'error', 'warning', 'info'
 */


// Inyectar estilos necesarios automáticamente si no existen
const toastStyles = document.createElement('style');
toastStyles.textContent = `
  @keyframes toast-slide-in {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
  }
  @keyframes toast-slide-out {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
  }
  .animate-toast-in { animation: toast-slide-in 0.3s ease-out forwards; }
  .animate-toast-out { animation: toast-slide-out 0.3s ease-in forwards; }
  
  /* Responsive container adjustments */
  @media (max-width: 640px) {
    #notificationContainer {
        left: 0.5rem;
        right: 0.5rem;
        max-width: calc(100% - 1rem);
    }
  }
`;
document.head.appendChild(toastStyles);

/**
 * Muestra una notificación toast.
 * @param {string} mensaje - El texto a mostrar.
 * @param {string} tipo - 'success', 'error', 'warning', 'info'.
 * @param {number} duracion - Duración en ms (default 4000).
 */
function mostrarToast(mensaje, tipo = 'info', duracion = 4000) {
    let container = document.getElementById('notificationContainer');

    // Si no existe el contenedor, lo creamos dinámicamente
    if (!container) {
        container = document.createElement('div');
        container.id = 'notificationContainer';
        container.className = 'fixed top-4 right-4 z-[9999] space-y-2 max-w-xs w-full pointer-events-none pr-4';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');

    // TEMA: Detectar preferencia global o usar default
    // 'modern' = Solid colors (Original toast.js)
    // 'classic' = Soft colors (Legacy notifications.js style)
    const currentTheme = window.TOAST_THEME || 'classic'; // Default a 'classic' por preferencia usuario

    // Configuración de Temas
    const themes = {
        modern: {
            // Diseño "Duro" / Sólido
            base: "text-white px-4 py-3 rounded-xl shadow-xl flex items-center gap-3 min-w-[280px] border border-white/10 backdrop-blur-md pointer-events-auto cursor-pointer hover:scale-[1.02] transition-transform animate-toast-in",
            variants: {
                success: { bg: 'bg-green-500', icon: 'ph-check-circle', title: 'ÉXITO' },
                error: { bg: 'bg-red-500', icon: 'ph-warning-circle', title: 'ERROR' },
                warning: { bg: 'bg-amber-500', icon: 'ph-warning', title: 'ATENCIÓN' },
                info: { bg: 'bg-blue-500', icon: 'ph-info', title: 'INFO' }
            },
            render: (cfg, msg) => `
                <i class="ph-bold ${cfg.icon} text-2xl bg-white/20 p-1.5 rounded-lg"></i>
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-wider opacity-90">${cfg.title}</p>
                    <p class="text-sm font-medium leading-tight break-words">${msg}</p>
                </div>
                <button onclick="cerrarToast(this.parentElement)" class="opacity-60 hover:opacity-100 transition p-1 hover:bg-white/20 rounded-full">
                    <i class="ph-bold ph-x"></i>
                </button>
            `
        },
        classic: {
            // Diseño "Suave" / Light (Legacy notifications.js)
            base: "border-l-4 p-3 rounded-lg shadow-lg flex items-center gap-2 min-w-[280px] pointer-events-auto cursor-pointer animate-toast-in bg-white",
            variants: {
                success: { css: 'bg-green-50 border-green-500 text-green-800', icon: 'ph-check-circle', title: 'Éxito' },
                error: { css: 'bg-red-50 border-red-500 text-red-800', icon: 'ph-x-circle', title: 'Error' },
                warning: { css: 'bg-yellow-50 border-yellow-500 text-yellow-800', icon: 'ph-warning', title: 'Advertencia' },
                info: { css: 'bg-blue-50 border-blue-500 text-blue-800', icon: 'ph-info', title: 'Información' }
            },
            render: (cfg, msg) => `
                 <i class="ph-bold ${cfg.icon} text-lg flex-shrink-0 mt-0.5"></i>
                  <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold truncate">${cfg.title}</p>
                    <p class="text-[10px] font-medium mt-0.5">${msg}</p>
                  </div>
                  <button onclick="cerrarToast(this.parentElement)" class="ml-2 flex-shrink-0 text-current opacity-50 hover:opacity-100">
                    <i class="ph-bold ph-x text-sm"></i>
                  </button>
            `
        }
    };

    const selectedTheme = themes[currentTheme] || themes.classic;
    const variantConfig = selectedTheme.variants[tipo] || selectedTheme.variants.info;

    // Construir clases
    // Si es classic, el bg viene en la variante. Si es modern, el bg viene en la variante también pero se aplica distinto.
    // Para simplificar, en modern el base no tiene bg, en classic tampoco.
    // Modern: variant.bg se agrega a base.
    // Classic: variant.css se agrega a base.

    let finalClasses = selectedTheme.base;
    if (currentTheme === 'modern') {
        finalClasses += ` ${variantConfig.bg}`;
    } else {
        finalClasses += ` ${variantConfig.css}`;
    }

    toast.className = finalClasses;
    toast.innerHTML = selectedTheme.render(variantConfig, mensaje);

    // Click para cerrar manualmente
    toast.onclick = (e) => {
        if (!e.target.closest('button')) cerrarToast(toast);
    };

    container.appendChild(toast);

    // Auto-cierre
    if (duracion > 0) {
        setTimeout(() => {
            if (toast && toast.parentElement) {
                cerrarToast(toast);
            }
        }, duracion);
    }
}

function cerrarToast(toast) {
    toast.classList.remove('animate-toast-in');
    toast.classList.add('animate-toast-out');

    // Remover del DOM al terminar la animación
    setTimeout(() => {
        if (toast.parentElement) toast.remove();
    }, 300);
}

// Función alias para compatibilidad con código antiguo
function mostrarNotificacion(titulo, mensaje, tipo) {
    // El titulo se ignora en el diseño modern/classic actual (usan titulos prefijados), 
    // pero podriamos pasarlo si quisieramos en el futuro.
    mostrarToast(mensaje, tipo);
}

// Alias globales para compatibilidad con notifications.js legacy
window.notify = mostrarNotificacion;
window.notifySuccess = (titulo, mensaje, duracion) => mostrarToast(mensaje, 'success', duracion);
window.notifyError = (titulo, mensaje, duracion) => mostrarToast(mensaje, 'error', duracion);
window.notifyWarning = (titulo, mensaje, duracion) => mostrarToast(mensaje, 'warning', duracion);
window.notifyInfo = (titulo, mensaje, duracion) => mostrarToast(mensaje, 'info', duracion);

// Escuchar eventos flash de PHP automáticamente si existen
// Escuchar eventos flash de PHP automáticamente si existen
document.addEventListener('DOMContentLoaded', () => {
    // Buscar elementos ocultos que contengan mensajes flash
});

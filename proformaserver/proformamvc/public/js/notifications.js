/**
 * Sistema de Notificaciones Global
 * Uso: mostrarNotificacion('Título', 'Mensaje', 'success|info|warning|error')
 */

// Crear contenedor de notificaciones si no existe
document.addEventListener('DOMContentLoaded', function() {
  if (!document.getElementById('notificationContainer')) {
    const container = document.createElement('div');
    container.id = 'notificationContainer';
    container.className = 'fixed top-2 right-2 z-50 space-y-2 max-w-xs';
    document.body.appendChild(container);
  }
});

/**
 * Muestra una notificación toast
 * @param {string} titulo - Título de la notificación
 * @param {string} mensaje - Mensaje de la notificación
 * @param {string} tipo - Tipo: 'success', 'info', 'warning', 'error'
 * @param {number} duracion - Duración en milisegundos (default: 3000)
 */
function mostrarNotificacion(titulo, mensaje, tipo = 'info', duracion = 3000) {
  const container = document.getElementById('notificationContainer');
  
  if (!container) {
    console.error('Contenedor de notificaciones no encontrado');
    return;
  }

  // Colores según tipo
  const colores = {
    success: 'bg-green-50 border-green-500 text-green-800',
    info: 'bg-blue-50 border-blue-500 text-blue-800',
    warning: 'bg-yellow-50 border-yellow-500 text-yellow-800',
    error: 'bg-red-50 border-red-500 text-red-800'
  };

  const iconos = {
    success: 'ph-check-circle',
    info: 'ph-info',
    warning: 'ph-warning',
    error: 'ph-x-circle'
  };

  const notificacion = document.createElement('div');
  notificacion.className = `${colores[tipo]} border-l-4 p-3 rounded-lg shadow-lg animate-slide-in`;
  notificacion.innerHTML = `
    <div class="flex items-start gap-2">
      <i class="ph-bold ${iconos[tipo]} text-lg flex-shrink-0 mt-0.5"></i>
      <div class="flex-1 min-w-0">
        <p class="text-xs font-bold truncate">${titulo}</p>
        <p class="text-[10px] font-medium mt-0.5">${mensaje}</p>
      </div>
      <button onclick="this.closest('.animate-slide-in, .animate-slide-out').remove()" class="ml-2 flex-shrink-0 text-current opacity-50 hover:opacity-100">
        <i class="ph-bold ph-x text-sm"></i>
      </button>
    </div>
  `;

  container.appendChild(notificacion);

  // Eliminar después de la duración especificada
  setTimeout(() => {
    notificacion.classList.remove('animate-slide-in');
    notificacion.classList.add('animate-slide-out');
    setTimeout(() => notificacion.remove(), 300);
  }, duracion);
}

// Alias para facilitar el uso
window.notify = mostrarNotificacion;

// Funciones auxiliares para cada tipo
window.notifySuccess = (titulo, mensaje, duracion) => mostrarNotificacion(titulo, mensaje, 'success', duracion);
window.notifyError = (titulo, mensaje, duracion) => mostrarNotificacion(titulo, mensaje, 'error', duracion);
window.notifyWarning = (titulo, mensaje, duracion) => mostrarNotificacion(titulo, mensaje, 'warning', duracion);
window.notifyInfo = (titulo, mensaje, duracion) => mostrarNotificacion(titulo, mensaje, 'info', duracion);

/**
 * Módulo de UI
 * Maneja notificaciones, animaciones y efectos visuales.
 */

window.mostrarNotificacion = function (titulo, mensaje, tipo = 'info') {
    const container = document.getElementById('notificationContainer');
    if (!container) return;

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
    notificacion.className = `${colores[tipo]} border-l-4 p-2 rounded-xl shadow-xl animate-slide-in`;
    notificacion.innerHTML = `
    <div class="flex items-start gap-2.5">
      <i class="ph-bold ${iconos[tipo]} text-xl flex-shrink-0 mt-0.5"></i>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-bold truncate">${titulo}</p>
        <p class="text-xs font-medium mt-1">${mensaje}</p>
      </div>
    </div>
  `;

    container.appendChild(notificacion);

    // Eliminar después de 3 segundos
    setTimeout(() => {
        notificacion.classList.remove('animate-slide-in');
        notificacion.classList.add('animate-slide-out');
        setTimeout(() => notificacion.remove(), 300);
    }, 3000);
};

window.activarAtencionMaquinaria = function () {
    const maquinariaSection = document.getElementById('maquinariaSection');
    const clienteSection = document.getElementById('clienteSection');
    const productoSearchInput = document.getElementById('productoSearchInput');

    /*
    if (maquinariaSection) maquinariaSection.classList.add('maquinaria-attention');
    if (clienteSection) clienteSection.classList.add('maquinaria-attention');

    // PINTAR BORDE DEL INPUT DE PRODUCTO
    if (productoSearchInput) {
        productoSearchInput.classList.remove('border-slate-200');
        productoSearchInput.classList.add('border-blue-400'); // Usando blue según preferencia
    }
    */

    // Hacer scroll suave a la sección de maquinaria
    setTimeout(() => {
        if (maquinariaSection) {
            maquinariaSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }, 300);

    // Mostrar notificación
    window.mostrarNotificacion('¡Siguiente paso!', 'Ahora selecciona la maquinaria para la cotización', 'success');
};

window.toggleImageUpload = function (idx) {
    const checkbox = document.getElementById(`check_img_${idx}`);
    const container = document.getElementById(`upload_container_${idx}`);
    const imageText = document.getElementById(`imageText_${idx}`);

    if (checkbox && checkbox.checked) {
        if (container) container.classList.remove('hidden');
        if (imageText) imageText.classList.remove('hidden');

        // Configurar el event listener para el input de imágenes
        const imageInput = document.getElementById(`imagenesInput_${idx}`);
        if (imageInput && !imageInput.dataset.listenerAttached) {
            // Importante: handleImagePreview debe estar disponible globalmente o importado
            // Como estamos en un entorno modular simulado (window), asumimos acceso global si items.js lo expone
            // O verificamos si está en window
            if (window.handleImagePreview) {
                imageInput.addEventListener('change', function (e) {
                    window.handleImagePreview(e, idx);
                });
                imageInput.dataset.listenerAttached = 'true';
            }
        }
    } else {
        if (container) container.classList.add('hidden');
        if (imageText) imageText.classList.add('hidden');
        // Limpiar previews al desactivar
        const preview = document.getElementById(`imagePreview_${idx}`);
        if (preview) preview.innerHTML = '';
    }
};
